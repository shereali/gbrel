<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetLink extends Model
{
    public const LIFETIME_MINUTES = 60;

    protected $guarded = ['id'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /** The link for a token, only while it is unused and not expired. */
    public static function findUsable(?string $token): ?self
    {
        if (! is_string($token) || strlen($token) < 20 || strlen($token) > 100) {
            return null;
        }

        return static::where('token_hash', static::hashToken($token))
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }
}
