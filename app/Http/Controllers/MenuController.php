<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Http\Resources\MenuResource;
use App\Http\Resources\MenuAllResource;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        // Fetch stocks for today with stock greater than 0
        $stocks = Stock::with(['menu'])->where('date', $today)
            ->where('stock', '>', 0)
            ->get();
        // Prepare an array to hold the results
        $result = [];

        foreach ($stocks as $stock) {
            $menu = Menu::where('id', $stock->id_menu)->first();
            if ($menu) {
                $result[] = [
                    'id' => $menu->id,
                    'name' => $menu->name,
                    'price' => $menu->price,
                    'stock' => [
                        'id' => $stock->id,
                        'id_menu' => $stock->id_menu,
                        'stock' => $stock->stock,
                        'date' => $stock->date,
                    ],
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Menu stocks retrieved successfully.',
            'data' => $result,
        ]);
    }

    public function indexAll()
    {
        $today = now()->toDateString();

        $menus = Menu::with(['stock' => function ($query) use ($today) {
            $query->where('date', $today);
        }])->get();

        return MenuAllResource::collection($menus);
    }

    public function show($id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json(['message' => 'Menu not found'], 404);
        }

        return new MenuResource($menu);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'id_stock' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $menu = Menu::create([
            'name' => $request->name,
            'price' => $request->price,
            'id_stock' => $request->id_stock,
        ]);

        return response()->json([
            'message' => 'Menu created successfully',
            'menu' => new MenuResource($menu),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json(['message' => 'Menu not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric',
            'id_stock' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $menu->update($request->all());

        return response()->json([
            'message' => 'Menu updated successfully',
            'menu' => new MenuResource($menu),
        ]);
    }

    public function destroy($id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json(['message' => 'Menu not found'], 404);
        }

        $menu->delete();

        return response()->json([
            'message' => 'Menu deleted successfully',
        ]);
    }
}
