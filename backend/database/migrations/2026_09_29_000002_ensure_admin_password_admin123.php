<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (app()->runningUnitTests() || ! Schema::hasTable('users')) {
            return;
        }

        $roleAdmin = Role::where('slug', 'admin')->first();

        // 1. Force set admin@gbrel.com password to admin123 and status to Active
        User::updateOrCreate(
            ['email' => 'admin@gbrel.com'],
            [
                'name' => 'GBREL Super Admin',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
                'role' => 'admin',
                'role_id' => $roleAdmin?->id,
                'status' => 'Active',
            ]
        );

        // 2. Ensure all staff and demo accounts are Active with known passwords
        $staff = [
            'manager@gbrel.com' => ['name' => 'Tariqul Islam (Property Director)', 'password' => 'manager123', 'role' => 'property_manager'],
            'legal@gbrel.com' => ['name' => 'Barrister Shafiul Alam (Legal Panel Head)', 'password' => 'legal123', 'role' => 'legal_compliance'],
            'agent@gbrel.com' => ['name' => 'Tanvir Ahmed (Senior Luxury Advisor)', 'password' => 'agent123', 'role' => 'agent'],
            'buyer@gbrel.com' => ['name' => 'Shere Ali (VIP Investor)', 'password' => 'buyer123', 'role' => 'buyer'],
        ];

        foreach ($staff as $email => $data) {
            $role = Role::where('slug', $data['role'])->first();
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                    'email_verified_at' => now(),
                    'role' => $data['role'],
                    'role_id' => $role?->id,
                    'status' => 'Active',
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
