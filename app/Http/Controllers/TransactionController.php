<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Menu;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $day = $request->query('day', now()->day);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $transactions = Transaction::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->when($day, function ($query) use ($day) {
                $query->whereDay('created_at', $day);
            })
            ->get();

        return response()->json($transactions);
    }

    public function getDashboardData(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfDay());
        $endDate = $request->query('end_date', now()->endOfDay());

        $request->validate([
            'start_date' => 'date|nullable',
            'end_date' => 'date|nullable',
        ]);
        $transactions = Transaction::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->get();
        $totalTransactions = $transactions->count();
        $totalRevenue = $transactions->sum('grand_total');
        $totalItemsSold = $transactions->sum('quantity');
        $popularPaymentMethod = $transactions->groupBy('payment')->sortByDesc(function ($group) {
            return $group->count();
        })->keys()->first();
        $totalDiscount = $transactions->sum('discount_amount');
        $completedTransactions = $transactions->where('status_transaction', 'completed')->count();
        $pendingTransactions = $transactions->where('status_transaction', 'pending')->count();
        $topSellingProductId = $transactions->groupBy('id_menu')->map(function ($group) {
            return $group->sum('quantity');
        })->sortDesc()->keys()->first();
        $topSellingProduct = Menu::find($topSellingProductId);
        $topPromotionId = $transactions->groupBy('id_promo')->map(function ($group) {
            return $group->count();
        })->sortDesc()->keys()->first();
        $topPromotion = Promo::find($topPromotionId);
        $paymentMethodRevenue = $transactions->groupBy('payment')->map(function ($group) {
            return $group->sum('grand_total');
        });
        $dailyTransactions = $transactions->groupBy(function ($transaction) {
            return $transaction->created_at->format('Y-m-d');
        })->map(function ($group, $date) {
            return [
                'date' => $date,
                'count' => $group->count(),
            ];
        })->values();

        $dashboardData = [
            'total_transactions' => $totalTransactions,
            'total_revenue' => $totalRevenue,
            'total_items_sold' => $totalItemsSold,
            'popular_payment_method' => $popularPaymentMethod,
            'total_discount' => $totalDiscount,
            'completed_transactions' => $completedTransactions,
            'pending_transactions' => $pendingTransactions,
            'top_selling_product' => $topSellingProduct ? $topSellingProduct->name : 'N/A',
            'top_promotion' => $topPromotion ? $topPromotion->name : 'N/A',
            'payment_method_revenue' => $paymentMethodRevenue,
            'daily_transactions' => $dailyTransactions,
        ];

        return response()->json($dashboardData);
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
            'user_id' => 'required',
            'menus' => 'required|array',
            'menus.*.id_menu' => 'required|exists:menus,id',
            'menus.*.quantity' => 'required|integer|min:1',
            'menus.*.id_promo' => 'nullable|exists:promos,id',
        ]);

        $grandTotal = 0;
        $menusData = [];
        $today = Carbon::today();

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
            'user_id' => $request->user_id,
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
            'data' => [
                'id' => $transactionId,
                'no_nota' => $noNota,
                'user_id' => $request->user_id,
                'status_transaction' => 'completed',
                'status_payment' => 'paid',
                'grand_total' => $grandTotal,
                'payment' => $request->payment,
                'date_payment' => $today,
                'quantity' => collect($menusData)->sum('quantity'),
                'menus' => $menusData,
            ],
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::where('no_nota', $id)->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $request->validate([
            'status_transaction' => 'required|string|in:completed,proses',

        ]);

        $transaction->update([
            'status_transaction' => $request->status_transaction,
        ]);

        return response()->json([
            'message' => 'Transaction status updated successfully',
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
