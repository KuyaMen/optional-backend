<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Http\Resources\CartResource;

class CartController extends Controller
{
    public function index(Request $request)
    {
        return CartResource::collection(
            Cart::where('user_id', $request->user()->id)->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $validated['user_id'] = $request->user()->id;

        $cart = Cart::create($validated);

        return new CartResource($cart);
    }

    public function show(Request $request, string $id)
    {
        $cart = Cart::findOrFail($id);

        if ($cart->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return new CartResource($cart);
    }

    public function update(Request $request, string $id)
    {
        $cart = Cart::findOrFail($id);
    
        if ($cart->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'product_id' => 'sometimes|required|exists:products,id',
            'quantity' => 'sometimes|required|integer|min:1',
        ]);

        $cart->update($validated);

        return new CartResource($cart);
    }

    public function destroy(Request $request, string $id)
    {
        $cart = Cart::findOrFail($id);

        if ($cart->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $cart->delete();

        return response()->json([
            'message' => 'Cart item deleted successfully'
        ]);
    }
}