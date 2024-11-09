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
        ]);

        $grandTotal = 0;
        $discountAmount = 0;
        $menusData = [];

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

        foreach ($menusData as $menuData) {
            $transaction = DB::table('transactions')->insertGetId([
                'id_menu' => $menuData['id_menu'],
                'id_promo' => $menuData['id_promo'],
                'status_transaction' => 'completed',
                'status_payment' => 'paid',
                'discount_amount' => $discountAmount,
                'grand_total' => $grandTotal,
                'quantity' => collect($menusData)->sum('quantity'),
            ]);
        }

        foreach ($menusData as $menuData) {
            DB::table('menu_transaction')->insert([
                'transaction_id' => $transaction,
                'quantity' => $menuData['quantity'],
                'price' => $menuData['grand_total'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Transaction created successfully',
            'transaction' => [
                'id' => $transaction,
                'status_transaction' => 'completed',
                'status_payment' => 'paid',
                'grand_total' => $grandTotal,
                'quantity' => collect($menusData)->sum('quantity'),
                'menus' => $menusData,
            ],
        ], 201);
    }


    // Mengupdate transaksi berdasarkan ID
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
