<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index()
    {
        return OrderResource::collection(Order::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'order_number' => 'nullable|string|unique:orders,order_number',
            'status' => 'nullable|string|in:pending,processing,shipped,delivered,cancelled',
            'grand_total' => 'nullable|numeric|min:0',
            'item_count' => 'nullable|integer|min:0',
            'payment_status' => 'nullable|string|in:unpaid,paid,refunded,failed',
            'payment_method' => 'nullable|string|max:255',
            'shipping_address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['order_number'] = $validated['order_number']
            ?? 'ORD-'.strtoupper(Str::random(10));

        $order = Order::create($validated);

        return new OrderResource($order);
    }

    public function show(string $id)
    {
        return new OrderResource(Order::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|required|string|in:pending,processing,shipped,delivered,cancelled',
            'grand_total' => 'sometimes|required|numeric|min:0',
            'item_count' => 'sometimes|required|integer|min:0',
            'payment_status' => 'sometimes|required|string|in:unpaid,paid,refunded,failed',
            'payment_method' => 'sometimes|nullable|string|max:255',
            'shipping_address' => 'sometimes|nullable|string',
            'notes' => 'sometimes|nullable|string',
        ]);

        $order->update($validated);

        return new OrderResource($order);
    }

    public function destroy(string $id)
    {
        Order::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Order deleted successfully'
        ]);
    }
}
