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
        if ($this->role === 'admin') {
            return true;
        }
        $all = $this->getEffectivePermissions();

        return in_array('*', $all) || in_array($slug, $all);
    }

    public function isAdmin(): bool
    {
        $perms = $this->getEffectivePermissions();
        if (in_array('*', $perms)) {
            return true;
        }

        return in_array($this->role, ['admin', 'property_manager', 'legal_compliance', 'finance_auditor']);
    }

    public function isLeadAdmin(): bool
    {
        return $this->role === 'admin' || in_array('*', $this->getEffectivePermissions());
    }

    /**
     * Staff are every role except the public ones (buyers and property owners).
     * Any user with active permissions or a non-buyer/owner role is also staff.
     */
    public function isStaff(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (count($this->getEffectivePermissions()) > 0) {
            return true;
        }

        if ($this->role_id) {
            $roleSlug = $this->roleRelation?->slug;
            if ($roleSlug && ! in_array($roleSlug, ['buyer', 'owner', 'guest'], true)) {
                return true;
            }
        }

        return ! in_array($this->role, [null, '', 'buyer', 'owner', 'guest'], true);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
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
            'phone' => $this->phone,
            'region' => $this->region,
            'status' => $this->status ?? 'Active',
            'avatar' => $this->avatar,
            'permissions' => $perms,
            'is_admin' => $isAdmin,
            'is_super_admin' => $isSuperAdmin,
            'is_staff' => $this->isStaff(),
        ];
    }
}
