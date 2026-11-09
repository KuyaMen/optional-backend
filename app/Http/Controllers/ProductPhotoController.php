<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductPhotoResource;
use App\Models\ProductPhoto;
use Illuminate\Http\Request;

class ProductPhotoController extends Controller
{
    public function index()
    {
        return ProductPhotoResource::collection(ProductPhoto::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'photo' => 'required|string|max:255',
        ]);

        $productPhoto = ProductPhoto::create($validated);

        return new ProductPhotoResource($productPhoto);
    }

    public function show(string $id)
    {
        return new ProductPhotoResource(ProductPhoto::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $productPhoto = ProductPhoto::findOrFail($id);

        $validated = $request->validate([
            'product_id' => 'sometimes|required|exists:products,id',
            'photo' => 'sometimes|required|string|max:255',
        ]);

        $productPhoto->update($validated);

        return new ProductPhotoResource($productPhoto);
    }

    public function destroy(string $id)
    {
        ProductPhoto::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Product photo deleted successfully'
        ]);
    }
}
