<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

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
        $valToStore = is_array($value) || is_object($value) ? json_encode($value) : (string)$value;
        static::updateOrCreate(['key' => $key], ['value' => $valToStore]);
    }
}
