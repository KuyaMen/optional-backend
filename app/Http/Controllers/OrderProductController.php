<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderProductResource;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderProductController extends Controller
{
    public function index(string $order)
    {
        Order::findOrFail($order);

        return OrderProductResource::collection(
            OrderProduct::where('order_id', $order)->get()
        );
    }

    public function store(Request $request, string $order)
    {
        Order::findOrFail($order);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'total' => 'nullable|numeric|min:0',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        $orderProduct = OrderProduct::create([
            'order_id' => $order,
            'product_id' => $product->id,
            'quantity' => $validated['quantity'],
            'total' => $validated['total']
                ?? ($product->saleprice ?? $product->price) * $validated['quantity'],
        ]);

        return new OrderProductResource($orderProduct);
    }

    public function show(string $order, string $product)
    {
        return new OrderProductResource($this->findLineItem($order, $product));
    }

    public function update(Request $request, string $order, string $product)
    {
        $orderProduct = $this->findLineItem($order, $product);

        $validated = $request->validate([
            'quantity' => 'sometimes|required|integer|min:1',
            'total' => 'sometimes|nullable|numeric|min:0',
        ]);

        $orderProduct->update($validated);

        return new OrderProductResource($orderProduct->refresh());
    }

    public function destroy(string $order, string $product)
    {
        $this->findLineItem($order, $product)->delete();

        return response()->json([
            'message' => 'Order item deleted successfully'
        ]);
    }

    private function findLineItem(string $order, string $product): OrderProduct
    {
        $orderProduct = OrderProduct::where('order_id', $order)
            ->where('product_id', $product)
            ->first();

        if ($orderProduct === null) {
            abort(404, 'Order item not found.');
        }

        return $orderProduct;
    }
}
