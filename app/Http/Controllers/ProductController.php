<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection(
            Product::with(['category', 'productPhotos'])->get()
        );
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load(['category', 'productPhotos']));
    }

    public function store(Request $request): ProductResource
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'saleprice' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'status' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if (empty($validated['sku'])) {
            $validated['sku'] = $this->generateSku();
        }

        $product = Product::create($validated);

        return new ProductResource($product->load(['category', 'productPhotos']));
    }

    public function update(Request $request, string $id): ProductResource
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'sometimes|nullable|exists:categories,id',
            'name' => 'sometimes|required|string|max:255',
            'sku' => 'sometimes|nullable|string|max:255|unique:products,sku,'.$product->id,
            'price' => 'sometimes|required|numeric|min:0',
            'saleprice' => 'sometimes|nullable|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'status' => 'sometimes|nullable|string|max:255',
            'brand' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
        ]);

        if (array_key_exists('sku', $validated) && empty($validated['sku'])) {
            $validated['sku'] = $this->generateSku();
        }

        $product->update($validated);

        return new ProductResource($product->load(['category', 'productPhotos']));
    }

    public function destroy(Request $request, string $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        Product::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }

    private function generateSku()
    {
        do {
            $sku = 'SKU-'.strtoupper(Str::random(8));
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }
}
