<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Lets staff with the users.manage permission set a new password for a user (Admin → Users → Reset password).
 */
class UserPasswordController extends Controller
{
    /** POST /api/users/{id}/password */
    public function update(Request $request, int $id): JsonResponse
    {
        $input = $request->validate([
            'password' => ['required', 'string', 'min:8', 'max:100', 'confirmed'],
        ], [
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The two passwords do not match.',
        ]);

        $user = User::findOrFail($id);
        if ($user->role === 'admin' && $request->user()?->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Only an administrator can change an administrator\'s password.'], 403);
        }

        $user->forceFill(['password' => Hash::make($input['password'])])->save();

        return response()->json(['success' => true, 'message' => 'Password changed for '.$user->name.'.']);
    }
}
