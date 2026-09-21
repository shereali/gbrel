<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'permissions' => 'array',
        'is_system' => 'boolean'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function hasPermission(string $permissionSlug): bool
    {
        $perms = $this->permissions ?? [];
        return in_array('*', $perms) || in_array($permissionSlug, $perms);
    }
}
