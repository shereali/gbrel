<?php

namespace App\Models;

use App\Support\SiteSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        $forget = fn () => Cache::forget(SiteSettings::CACHE_KEY);
        static::saved($forget);
        static::deleted($forget);
    }

    public static function getVal(string $key, mixed $default = null): mixed
    {
        try {
            $setting = static::where('key', $key)->first();
            if ($setting && $setting->value !== null) {
                $decoded = json_decode($setting->value, true);

                return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $setting->value;
            }
        } catch (\Throwable $e) {
            //
        }

        return $default;
    }

    public static function setVal(string $key, mixed $value): void
    {
        $valToStore = is_array($value) || is_object($value) ? json_encode($value) : (string) $value;
        static::updateOrCreate(['key' => $key], ['value' => $valToStore]);
    }
}
