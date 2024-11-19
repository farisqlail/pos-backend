<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use App\Http\Resources\PromoResource;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index()
    {
        $promos = Promo::all(); 
        return PromoResource::collection($promos); 
    }

    public function show($id)
    {
        $promo = Promo::find($id);

        if (!$promo) {
            return response()->json(['message' => 'Promo not found'], 404);
        }

        return new PromoResource($promo); 
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'discount' => 'required|integer',
        ]);

        $promo = Promo::create([
            'name' => $request->name,
            'discount' => $request->discount,
        ]);

        return response()->json([
            'message' => 'Promo created successfully',
            'promo' => new PromoResource($promo)
        ], 201); 
    }

    public function update(Request $request, $id)
    {
        $promo = Promo::find($id);

        if (!$promo) {
            return response()->json(['message' => 'Promo not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'discount' => 'required|integer',
        ]);

        $promo->update([
            'name' => $request->name,
            'discount' => $request->discount,
        ]);

        return response()->json([
            'message' => 'Promo updated successfully',
            'promo' => new PromoResource($promo)
        ]);
    }

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
