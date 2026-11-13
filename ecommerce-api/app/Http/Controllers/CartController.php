<?php

namespace App\Http\Controllers;

use App\Http\Resources\CartResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return CartResource::collection(
            $request->user()->carts()->with(['product.category', 'product.productPhotos'])->get()
        );
    }

    public function store(Request $request): CartResource
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $cart = $request->user()->carts()->where('product_id', $product->id)->first();
        $quantity = ($cart?->quantity ?? 0) + $validated['quantity'];

        if ($quantity > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => ['The requested quantity exceeds the available stock.'],
            ]);
        }

        if ($cart) {
            $cart->increment('quantity', $validated['quantity']);
        } else {
            $cart = $request->user()->carts()->create([
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
            ]);
        }

        return new CartResource($cart->load(['product.category', 'product.productPhotos']));
    }

    public function show(Request $request, string $id): CartResource
    {
        $cart = $request->user()->carts()
            ->with(['product.category', 'product.productPhotos'])
            ->findOrFail($id);

        return new CartResource($cart);
    }

    public function update(Request $request, string $id): CartResource
    {
        $cart = $request->user()->carts()->with('product')->findOrFail($id);
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($validated['quantity'] > $cart->product->stock) {
            throw ValidationException::withMessages([
                'quantity' => ['The requested quantity exceeds the available stock.'],
            ]);
        }

        $cart->update($validated);

        return new CartResource($cart->load(['product.category', 'product.productPhotos']));
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $request->user()->carts()->findOrFail($id)->delete();

        return response()->json([
            'message' => 'Cart item deleted successfully',
        ]);
    }
}
