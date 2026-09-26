<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class OwnerAccountController extends Controller
{
    /**
     * Property owners create their own account with a phone number. Staff accounts are created only from the admin panel.
     */
    public function register(Request $request): JsonResponse
    {
        $request->merge([
            'phone' => PhoneNumber::normalize($request->input('phone')),
            'email' => $request->filled('email') ? strtolower(trim((string) $request->input('email'))) : null,
            'name' => trim((string) $request->input('name')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:'.PhoneNumber::PATTERN, Rule::unique('users', 'phone')],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'max:100', 'confirmed'],
        ], [
            'phone.regex' => 'সঠিক মোবাইল নম্বর দিন, যেমন 01712345678।',
            'phone.unique' => 'এই নম্বরে আগেই অ্যাকাউন্ট আছে। সাইন ইন করুন।',
            'email.unique' => 'এই ইমেইলে আগেই অ্যাকাউন্ট আছে।',
            'password.min' => 'পাসওয়ার্ড অন্তত ৮ অক্ষরের হতে হবে।',
            'password.confirmed' => 'দুটি পাসওয়ার্ড মেলেনি।',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'owner',
            'role_id' => Role::where('slug', 'owner')->value('id'),
            'status' => 'Active',
        ]);

        $token = bin2hex(random_bytes(32));
        Cache::put("gbrel_auth_token_{$token}", $user->id, now()->addDays(30));

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user->toAuthPayload(),
        ], 201);
    }
}
