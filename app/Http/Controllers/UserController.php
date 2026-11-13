<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        return UserResource::collection(User::all());
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname'  => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users,email',
            'password'  => 'required|string|min:6',
            'role'      => 'nullable|string|max:255',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return new UserResource($user);
    }

    public function show(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        if ($request->user()->id !== $user->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return new UserResource($user);
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        if ($request->user()->id !== $user->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'firstname' => 'sometimes|required|string|max:255',
            'lastname'  => 'sometimes|required|string|max:255',
            'email'     => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password'  => 'sometimes|required|string|min:6',
            'role'      => 'sometimes|nullable|string|max:255',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->fill($validated)->save();

        return new UserResource($user);
    }

    public function destroy(Request $request, string $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $user = User::findOrFail($id);

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}