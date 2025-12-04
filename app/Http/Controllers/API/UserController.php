<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of all users (Admin only).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Check if user is Admin using Gate
        Gate::authorize('view-users');

        $users = User::with('role')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Users retrieved successfully',
            'data' => UserResource::collection($users),
        ], 200);
    }

    /**
     * Update the specified user.
     *
     * @param UpdateUserRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $authenticatedUser = $request->user();

        // Check authorization: User can update own profile OR Admin can update any user
        if ($authenticatedUser->id !== $user->id && !$authenticatedUser->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. You can only update your own profile.',
            ], 403);
        }

        // Update user fields
        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Reload user with role relationship
        $user->load('role');

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => new UserResource($user),
        ], 200);
    }

    /**
     * Remove the specified user.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $authenticatedUser = $request->user();

        // Check authorization: User can delete own account OR Admin can delete any user
        if ($authenticatedUser->id !== $user->id && !$authenticatedUser->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. You can only delete your own account.',
            ], 403);
        }

        // Prevent deletion of the last Admin account
        if ($user->isAdmin()) {
            $adminCount = User::whereHas('role', function ($query) {
                $query->where('name', 'Admin');
            })->count();

            if ($adminCount <= 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete the last admin account.',
                ], 403);
            }
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully',
        ], 200);
    }
}
