<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations to ensure admin@gbrel.com has a known password (admin123).
     */
    public function up(): void
    {
        $roleAdmin = Role::where('slug', 'admin')->first();

        User::updateOrCreate(
            ['email' => 'admin@gbrel.com'],
            [
                'name' => 'GBREL Super Admin',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
                'role' => 'admin',
                'role_id' => $roleAdmin ? $roleAdmin->id : 1,
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
