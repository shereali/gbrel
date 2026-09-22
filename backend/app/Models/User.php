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

    public function isAdmin(): bool
    {
        $perms = $this->getEffectivePermissions();
        if (in_array('*', $perms)) return true;
        return in_array($this->role, ['admin', 'property_manager', 'legal_compliance', 'finance_auditor']);
    }

    public function toAuthPayload(): array
    {
        $roleObj = $this->roleRelation ?: ($this->role ? Role::where('slug', $this->role)->first() : null);
        $perms = $this->getEffectivePermissions();
        $isAdmin = $this->isAdmin();
        $isSuperAdmin = $this->role === 'admin' || in_array('*', $perms);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role ?? ($roleObj ? $roleObj->slug : 'buyer'),
            'role_id' => $roleObj ? $roleObj->id : $this->role_id,
            'role_name' => $roleObj ? $roleObj->name : ucfirst($this->role ?? 'Buyer'),
            'phone' => $this->phone ?? '+880 1819-000000',
            'region' => $this->region ?? 'Dhaka HQ',
            'status' => $this->status ?? 'Active',
            'avatar' => $this->avatar ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=200&auto=format&fit=crop',
            'permissions' => $perms,
            'is_admin' => $isAdmin,
            'is_super_admin' => $isSuperAdmin
        ];
    }
}
