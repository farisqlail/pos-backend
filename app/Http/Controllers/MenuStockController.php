<?php

// app/Http/Controllers/MenuStockController.php    

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;

class MenuStockController extends Controller
{
    public function index($id_menu)
    {
        $menuStocks = Stock::where('id_menu', $id_menu)->get(); 

        return response()->json([
            'success' => true,
            'message' => 'Menu stocks retrieved successfully.',
            'data' => $menuStocks
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_menu' => 'required|integer',
            'stock' => 'required|integer',
            'date' => 'required|date',
        ]);

        $menuStock = Stock::create($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Menu stock created successfully.',
            'data' => $menuStock
        ], 201);
    }

    public function show($id)
    {
        $menuStock = Stock::findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Menu stock retrieved successfully.',
            'data' => $menuStock
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_menu' => 'required|integer',
            'stock' => 'required|integer',
            'date' => 'required|date',
        ]);

        $menuStock = Stock::findOrFail($id);
        $menuStock->update($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Menu stock updated successfully.',
            'data' => $menuStock
        ]);
    }

    public function destroy($id)
    {
        $menuStock = Stock::findOrFail($id);
        $menuStock->delete();
        return response()->json([
            'success' => true,
            'message' => 'Menu stock deleted successfully.'
        ], 204);
    }
}
