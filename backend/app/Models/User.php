<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'custom_permissions' => 'array',
            'role_id' => 'integer',
        ];
    }

    public function roleRelation()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function getEffectivePermissions(): array
    {
        $rolePerms = [];
        if ($this->roleRelation) {
            $rolePerms = $this->roleRelation->permissions ?? [];
        } elseif ($this->role) {
            $matchedRole = Role::where('slug', $this->role)->first();
            if ($matchedRole) {
                $rolePerms = $matchedRole->permissions ?? [];
            }
        }

        $custom = $this->custom_permissions ?? [];
        return array_values(array_unique(array_merge($rolePerms, $custom)));
    }

    public function hasPermission(string $slug): bool
    {
        $all = $this->getEffectivePermissions();
        return in_array('*', $all) || in_array($slug, $all);
    }
}
