<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use App\Http\Resources\PromoResource;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    // Menampilkan semua promo
    public function index()
    {
        $promos = Promo::all(); // Ambil semua data promo
        return PromoResource::collection($promos); // Kembalikan sebagai koleksi resource
    }

    // Menampilkan promo berdasarkan ID
    public function show($id)
    {
        $promo = Promo::find($id);

        if (!$promo) {
            return response()->json(['message' => 'Promo not found'], 404);
        }

        return new PromoResource($promo); // Mengembalikan resource untuk satu promo
    }

    // Menambahkan promo baru
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'discount' => 'required|integer',
        ]);

        // Membuat promo baru
        $promo = Promo::create([
            'name' => $request->name,
            'discount' => $request->discount,
        ]);

        return response()->json([
            'message' => 'Promo created successfully',
            'promo' => new PromoResource($promo)
        ], 201); // Status code 201: Created
    }

    // Mengupdate promo berdasarkan ID
    public function update(Request $request, $id)
    {
        $promo = Promo::find($id);

        if (!$promo) {
            return response()->json(['message' => 'Promo not found'], 404);
        }

        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'discount' => 'required|integer',
        ]);

        // Update data promo
        $promo->update([
            'name' => $request->name,
            'discount' => $request->discount,
        ]);

        return response()->json([
            'message' => 'Promo updated successfully',
            'promo' => new PromoResource($promo)
        ]);
    }

    // Menghapus promo berdasarkan ID
    public function destroy($id)
    {
        $promo = Promo::find($id);

        if (!$promo) {
            return response()->json(['message' => 'Promo not found'], 404);
        }

        $promo->delete();

        return response()->json(['message' => 'Promo deleted successfully']);
    }
}
