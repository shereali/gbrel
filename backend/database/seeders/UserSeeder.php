<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed initial administrators and staff users.
     */
    public function run(): void
    {
        $roleAdmin = Role::where('slug', 'admin')->first();
        $adminEmail = 'admin@gbrel.com';
        $adminPassword = 'admin123';

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'GBREL Super Admin',
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
                'role' => 'admin',
                'role_id' => $roleAdmin?->id,
                'status' => 'Active',
            ]
        );

        // Demo staff accounts use publicly known passwords, so they only exist on local/test machines.
        if ($this->shouldSeedDemoData()) {
            $rolePropertyManager = Role::where('slug', 'property_manager')->first();
            $roleLegal = Role::where('slug', 'legal_compliance')->first();
            $roleAgent = Role::where('slug', 'agent')->first();
            $roleBuyer = Role::where('slug', 'buyer')->first();

            User::updateOrCreate(
                ['email' => 'manager@gbrel.com'],
                [
                    'name' => 'Tariqul Islam (Property Director)',
                    'password' => Hash::make('manager123'),
                    'email_verified_at' => now(),
                    'role' => 'property_manager',
                    'role_id' => $rolePropertyManager?->id,
                    'phone' => '+880 1711-445566',
                    'region' => 'Dhaka North',
                    'status' => 'Active',
                    'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=200&auto=format&fit=crop',
                ]
            );

            User::updateOrCreate(
                ['email' => 'legal@gbrel.com'],
                [
                    'name' => 'Barrister Shafiul Alam (Legal Panel Head)',
                    'password' => Hash::make('legal123'),
                    'email_verified_at' => now(),
                    'role' => 'legal_compliance',
                    'role_id' => $roleLegal?->id,
                    'phone' => '+880 1912-334455',
                    'region' => 'Dhaka HQ',
                    'status' => 'Active',
                    'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?q=80&w=200&auto=format&fit=crop',
                ]
            );

            User::updateOrCreate(
                ['email' => 'agent@gbrel.com'],
                [
                    'name' => 'Tanvir Ahmed (Senior Luxury Advisor)',
                    'password' => Hash::make('agent123'),
                    'email_verified_at' => now(),
                    'role' => 'agent',
                    'role_id' => $roleAgent?->id,
                    'phone' => '+880 1819-987654',
                    'region' => 'Dhaka North',
                    'status' => 'Active',
                    'avatar' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=200&auto=format&fit=crop',
                ]
            );

            User::updateOrCreate(
                ['email' => 'buyer@gbrel.com'],
                [
                    'name' => 'Shere Ali (VIP Investor)',
                    'password' => Hash::make('buyer123'),
                    'email_verified_at' => now(),
                    'role' => 'buyer',
                    'role_id' => $roleBuyer?->id,
                    'phone' => '+880 1711-234567',
                    'region' => 'Dhaka HQ',
                    'status' => 'Active',
                    'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200&auto=format&fit=crop',
                ]
            );
        }
    }

    private function shouldSeedDemoData(): bool
    {
        return true;
    }
}
