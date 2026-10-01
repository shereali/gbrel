<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetLink;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Password reset without email or SMS: staff create a one-time link for a user and send it themselves
 * (for example on WhatsApp). The user opens it and chooses a new password.
 */
class PasswordResetLinkController extends Controller
{
    private const INVALID = 'এই লিংকটি কাজ করছে না। এটি আগেই ব্যবহার হয়েছে বা মেয়াদ শেষ। GBREL টিমের কাছ থেকে নতুন লিংক নিন।';

    /** POST /api/users/{id}/password-reset (users.manage). The token is shown once and never stored in plain text. */
    public function create(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $actor = $request->user();
        if ($user->role === 'admin' && $actor?->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Only an administrator can reset an administrator\'s password.'], 403);
        }

        $token = Str::random(48);
        $link = DB::transaction(function () use ($user, $actor, $token) {
            // Only the newest link works, so an old link that was shared by mistake stops working.
            PasswordResetLink::where('user_id', $user->id)->whereNull('used_at')->update(['used_at' => now()]);

            return PasswordResetLink::create([
                'user_id' => $user->id,
                'token_hash' => PasswordResetLink::hashToken($token),
                'created_by' => $actor?->id,
                'expires_at' => now()->addMinutes(PasswordResetLink::LIFETIME_MINUTES),
            ]);
        });

        return response()->json([
            'success' => true,
            'data' => ['token' => $token, 'expires_at' => $link->expires_at, 'minutes' => PasswordResetLink::LIFETIME_MINUTES],
        ], 201);
    }

    /** POST /api/auth/reset-password/check. Lets the page tell a dead link from a good one before asking for a password. */
    public function check(Request $request): JsonResponse
    {
        $link = PasswordResetLink::findUsable($request->input('token'));
        if (! $link) {
            return response()->json(['success' => false, 'message' => self::INVALID], 422);
        }

        return response()->json(['success' => true, 'data' => ['name' => $link->user->name]]);
    }

    /** POST /api/auth/reset-password */
    public function reset(Request $request): JsonResponse
    {
        $input = $request->validate([
            'token' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:8', 'max:100', 'confirmed'],
        ], [
            'password.min' => 'পাসওয়ার্ড কমপক্ষে ৮ অক্ষরের হতে হবে।',
            'password.confirmed' => 'দুটি পাসওয়ার্ড মিলছে না।',
        ]);

        $link = PasswordResetLink::findUsable($input['token']);
        if (! $link) {
            return response()->json(['success' => false, 'message' => self::INVALID], 422);
        }

        DB::transaction(function () use ($link, $input) {
            // The conditional update makes a link work once even if it is submitted twice at the same moment.
            $claimed = PasswordResetLink::whereKey($link->id)->whereNull('used_at')->update(['used_at' => now()]);
            abort_if($claimed === 0, 422, self::INVALID);

            $link->user->forceFill(['password' => Hash::make($input['password'])])->save();
        });

        return response()->json(['success' => true, 'message' => 'পাসওয়ার্ড বদলানো হয়েছে। নতুন পাসওয়ার্ড দিয়ে সাইন ইন করুন।']);
    }
}
