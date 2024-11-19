<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Menu;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::all();
        return response()->json($transactions);
    }

    public function show($id)
    {
        $transaction = Transaction::with(['menus'])->where('no_nota', $id)->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        return response()->json($transaction);
    }

    public function store(Request $request)
    {
        $request->validate([
            'transaction_id' => 'nullable|exists:transactions,id',
            'total' => 'required',
            'status_payment' => 'required',
            'status_transactions' => 'required',
            'menus' => 'required|array',
            'menus.*.id_menu' => 'required|exists:menus,id',
            'menus.*.quantity' => 'required|integer|min:1',
            'menus.*.id_promo' => 'nullable|exists:promos,id',
            'menus.*.user_id' => 'required|exists:users,id'
        ]);

        $grandTotal = 0;
        $menusData = [];

        $userId = $request->menus[0]['user_id'];

        foreach ($request->menus as $menuData) {
            $menu = Menu::findOrFail($menuData['id_menu']);
            $quantity = $menuData['quantity'];

            $grandTotal += ($menu->price * $quantity) - $request->discount_amount;
            $menusData[] = [
                'id_menu' => $menu->id,
                'id_promo' => $request->id_promo,
                'quantity' => $quantity,
                'grand_total' => $menu->price * $quantity,
            ];

            $menu->decrement('stock', $quantity);
        }

        $noNota = 'TRX' . date('Ymd') . strtoupper(uniqid());

        $transactionId = DB::table('transactions')->insertGetId([
            'user_id' => $userId,
            'id_menu' => $menusData[0]['id_menu'],
            'id_promo' => $request->id_promo,
            'no_nota' => $noNota,
            'status_transaction' => $request->status_transactions,
            'status_payment' => 'paid',
            'payment' => $request->payment,
            'discount_amount' => $request->discount_amount,
            'grand_total' => $request->total,
            'quantity' => collect($menusData)->sum('quantity'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($menusData as $menuData) {
            DB::table('menu_transaction')->insert([
                'transaction_id' => $transactionId,
                'menu_id' => $menuData['id_menu'],
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

        $request->validate([
            'menus' => 'required|array',
            'menus.*.id_menu' => 'required|exists:menus,id',
            'menus.*.quantity' => 'required|integer|min:1',
        ]);

        $grandTotal = 0;
        $discountAmount = 0;

        $menusData = [];
        foreach ($request->menus as $menuData) {
            $menu = Menu::find($menuData['id_menu']);
            $quantity = $menuData['quantity'];
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

            $menu->decrement('stock', $quantity);
        }

        $transaction->update([
            'status_transaction' => 'pending',
            'status_payment' => 'unpaid',
            'discount_amount' => $discountAmount,
            'grand_total' => $grandTotal,
        ]);

        $transaction->menus()->sync($menusData);

        return response()->json([
            'message' => 'Transaction updated successfully',
            'transaction' => $transaction
        ]);
    }

    public function destroy($id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $menu = Menu::find($transaction->id_menu);
        $menu->increment('stock', $transaction->quantity);

        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted successfully']);
    }
}
