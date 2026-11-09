<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        return PaymentResource::collection(Payment::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'type' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'status' => 'nullable|string|in:pending,paid,failed,refunded',
        ]);

        $payment = Payment::create($validated);

        return new PaymentResource($payment);
    }

    public function show(string $id)
    {
        return new PaymentResource(Payment::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $payment = Payment::findOrFail($id);

        $validated = $request->validate([
            'type' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|string|in:pending,paid,failed,refunded',
        ]);

        $payment->update($validated);

        return new PaymentResource($payment);
    }

    public function destroy(string $id)
    {
        Payment::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Payment deleted successfully'
        ]);
    }
}
