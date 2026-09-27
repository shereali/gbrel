<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Makes sure admin@gbrel.com exists. The password comes from ADMIN_PASSWORD; nothing is written without it.
     */
    public function up(): void
    {
        $password = (string) env('ADMIN_PASSWORD', '');
        if ($password === '') {
            return;
        }

        $roleAdmin = Role::where('slug', 'admin')->first();
        if (! $roleAdmin) {
            return; // Fresh install: DatabaseSeeder creates the roles and the admin.
        }

        User::updateOrCreate(
            ['email' => 'admin@gbrel.com'],
            [
                'name' => 'GBREL Super Admin',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'role' => 'admin',
                'role_id' => $roleAdmin->id,
                'status' => 'Active',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
