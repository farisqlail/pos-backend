<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Http\Resources\MenuResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::all(); 
        return MenuResource::collection($menus);
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
            'stock' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $menu = Menu::create([
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
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
            'stock' => 'sometimes|integer',
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
