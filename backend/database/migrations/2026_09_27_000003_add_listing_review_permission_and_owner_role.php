<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds the "review owner listings" permission, grants it to the property and legal roles,
     * and creates the public "owner" role used by sign-up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles')) {
            return;
        }

        $now = now();
        DB::table('permissions')->updateOrInsert(
            ['slug' => 'listings.review'],
            ['name' => 'Review Owner Listings', 'module' => 'Properties', 'description' => 'Review property owner submissions, verify documents, approve and publish', 'updated_at' => $now, 'created_at' => $now]
        );

        foreach (['property_manager', 'legal_compliance'] as $slug) {
            $role = DB::table('roles')->where('slug', $slug)->first();
            if ($role) {
                $permissions = json_decode($role->permissions ?? '[]', true) ?: [];
                if (! in_array('listings.review', $permissions, true)) {
                    $permissions[] = 'listings.review';
                    DB::table('roles')->where('id', $role->id)->update(['permissions' => json_encode($permissions), 'updated_at' => $now]);
                }
            }
        }

        if (! DB::table('roles')->where('slug', 'owner')->exists()) {
            DB::table('roles')->insert([
                'name' => 'Property Owner',
                'slug' => 'owner',
                'description' => 'Submits and updates their own property for GBREL to verify and sell.',
                'permissions' => json_encode([]),
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles')) {
            return;
        }
        DB::table('permissions')->where('slug', 'listings.review')->delete();
        DB::table('roles')->where('slug', 'owner')->delete();
    }
};
