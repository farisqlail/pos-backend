<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Http\Resources\MenuResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    // Menampilkan semua menu
    public function index()
    {
        $menus = Menu::all(); // Ambil semua data menu
        return MenuResource::collection($menus);
    }

    // Menampilkan menu berdasarkan ID
    public function show($id)
    {
        $menu = Menu::find($id); // Cari menu berdasarkan ID

        if (!$menu) {
            return response()->json(['message' => 'Menu not found'], 404); // Jika menu tidak ditemukan
        }

        return new MenuResource($menu); // Kembalikan sebagai resource
    }

    // Menambahkan menu baru
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Menambahkan menu baru
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

    // Mengupdate menu berdasarkan ID
    public function update(Request $request, $id)
    {
        $menu = Menu::find($id); // Cari menu berdasarkan ID

        if (!$menu) {
            return response()->json(['message' => 'Menu not found'], 404);
        }

        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric',
            'stock' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Update menu
        $menu->update($request->all());

        return response()->json([
            'message' => 'Menu updated successfully',
            'menu' => new MenuResource($menu),
        ]);
    }

    // Menghapus menu berdasarkan ID
    public function destroy($id)
    {
        $menu = Menu::find($id); // Cari menu berdasarkan ID

        if (!$menu) {
            return response()->json(['message' => 'Menu not found'], 404);
        }

        // Hapus menu
        $menu->delete();

        return response()->json([
            'message' => 'Menu deleted successfully',
        ]);
    }
}
