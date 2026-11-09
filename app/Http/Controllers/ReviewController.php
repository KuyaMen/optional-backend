<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        return ReviewResource::collection(Review::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'user_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'status' => 'nullable|string|in:active,hidden',
        ]);

        $review = Review::create($validated);

        return new ReviewResource($review);
    }

    public function show(string $id)
    {
        return new ReviewResource(Review::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $review = Review::findOrFail($id);

        $validated = $request->validate([
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'sometimes|nullable|string',
            'status' => 'sometimes|required|string|in:active,hidden',
        ]);

        $review->update($validated);

        return new ReviewResource($review);
    }

    public function destroy(string $id)
    {
        Review::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Review deleted successfully'
        ]);
    }
}
