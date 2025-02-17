<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StockCheckController extends Controller
{
    public function index()
    {
        try {
            $today = Carbon::today()->toDateString();

            $menusWithEmptyStock = Stock::where('stock', '<=', 0)
                ->where('date', $today)
                ->whereNotIn('id_menu', function ($query) use ($today) {
                    $query->select('id_menu')
                        ->from('stocks')
                        ->where('stock', '>', 0)
                        ->where('date', $today);
                })
                ->with('menu')
                ->get();

            if ($menusWithEmptyStock->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No menus with empty stock found.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Menus with empty stock retrieved successfully.',
                'data' => $menusWithEmptyStock
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}
