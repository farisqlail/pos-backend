<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Menu;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    // Menampilkan semua transaksi
    public function index()
    {
        $transactions = Transaction::all(); // Ambil semua data transaksi
        return response()->json($transactions);
    }

    // Menampilkan transaksi berdasarkan ID
    public function show($id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        return response()->json($transaction);
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'transaction_id' => 'nullable|exists:transactions,id',
            'menus' => 'required|array',
            'menus.*.id_menu' => 'required|exists:menus,id',
            'menus.*.quantity' => 'required|integer|min:1',
            'menus.*.id_promo' => 'nullable|exists:promos,id',
            'menus.*.user_id' => 'required|exists:users,id'
        ]);

        $grandTotal = 0;
        $discountAmount = 0;
        $menusData = [];

        $userId = $request->menus[0]['user_id'];

        foreach ($request->menus as $menuData) {
            $menu = Menu::findOrFail($menuData['id_menu']);
            $quantity = $menuData['quantity'];

            $discountAmountForItem = 0;
            if (isset($menuData['id_promo'])) {
                $promo = Promo::find($menuData['id_promo']);
                if ($promo) {
                    $discountAmountForItem = ($promo->discount / 100) * $menu->price * $quantity;
                }
            }

            $grandTotal += ($menu->price * $quantity) - $discountAmountForItem;
            $menusData[] = [
                'id_menu' => $menu->id,
                'id_promo' => $menuData['id_promo'] ?? null,
                'quantity' => $quantity,
                'discount_amount' => $discountAmountForItem,
                'grand_total' => ($menu->price * $quantity) - $discountAmountForItem,
            ];

            $menu->decrement('stock', $quantity);
        }

        $noNota = 'TRX' . date('Ymd') . strtoupper(uniqid());

        $transactionId = DB::table('transactions')->insertGetId([
            'user_id' => $userId,
            'id_menu' => $menusData[0]['id_menu'],
            'id_promo' => $menusData[0]['id_promo'],
            'no_nota' => $noNota,
            'status_transaction' => 'completed',
            'status_payment' => 'paid',
            'discount_amount' => $discountAmount,
            'grand_total' => $grandTotal,
            'quantity' => collect($menusData)->sum('quantity'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($menusData as $menuData) {
            DB::table('menu_transaction')->insert([
                'transaction_id' => $transactionId,
                'quantity' => $menuData['quantity'],
                'price' => $menuData['grand_total'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Transaction created successfully',
            'transaction' => [
                'id' => $transactionId,
                'no_nota' => $noNota,
                'user_id' => $userId,
                'status_transaction' => 'completed',
                'status_payment' => 'paid',
                'grand_total' => $grandTotal,
                'quantity' => collect($menusData)->sum('quantity'),
                'menus' => $menusData,
            ],
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // Validasi input
        $request->validate([
            'menus' => 'required|array', // Menunggu array menus
            'menus.*.id_menu' => 'required|exists:menus,id', // Setiap menu harus ada di database
            'menus.*.quantity' => 'required|integer|min:1', // Kuantitas harus lebih dari 0
        ]);

        // Hitung total grand total dan diskon
        $grandTotal = 0;
        $discountAmount = 0;

        $menusData = [];
        foreach ($request->menus as $menuData) {
            $menu = Menu::find($menuData['id_menu']);
            $quantity = $menuData['quantity'];

            // Menghitung harga menu dengan diskon jika ada promo
            $discountAmountForItem = 0;
            $promo = Promo::find($menuData['id_promo']);
            if ($promo) {
                $discountAmountForItem = ($promo->discount / 100) * $menu->price * $quantity;
            }

            $grandTotal += ($menu->price * $quantity) - $discountAmountForItem;

            $menusData[] = [
                'menu_id' => $menu->id,
                'quantity' => $quantity,
                'price' => $menu->price,
            ];

            // Mengurangi stok menu setelah transaksi
            $menu->decrement('stock', $quantity);
        }

        // Update transaksi
        $transaction->update([
            'status_transaction' => 'pending', // Status transaksi diupdate
            'status_payment' => 'unpaid', // Status pembayaran
            'discount_amount' => $discountAmount,
            'grand_total' => $grandTotal,
        ]);

        // Update relasi menu
        $transaction->menus()->sync($menusData);

        return response()->json([
            'message' => 'Transaction updated successfully',
            'transaction' => $transaction
        ]);
    }

    // Menghapus transaksi berdasarkan ID
    public function destroy($id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // Kembalikan stok menu yang terjual
        $menu = Menu::find($transaction->id_menu);
        $menu->increment('stock', $transaction->quantity);

        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted successfully']);
    }
}
