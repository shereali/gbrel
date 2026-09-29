<?php

use App\Models\Property;
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
        // 1. Fix Property #5 price (22 Lakh BDT per share)
        if (Schema::hasTable('properties')) {
            $prop = Property::where('slug', 'dhaur-kamarpara-8-5-katha-b-g-9-land-share')
                ->orWhere('id', 5)
                ->first();

            if ($prop) {
                $details = is_array($prop->buyer_details) ? $prop->buyer_details : [];
                $details['priceBasis'] = 'Per share';
                $details['shareCount'] = 36;
                $details['shareLandSize'] = 0.236;

                $prop->update([
                    'price' => 2200000,
                    'price_unit' => 'প্রতি শেয়ার ৳ ২২ লাখ',
                    'hide_price' => false,
                    'tagline' => '৮.৫ কাঠা জমি | রাজউক প্রস্তাবিত B+G+9 প্ল্যান পাস | ১২৫০ বর্গফুট ফ্ল্যাট | প্রতি শেয়ার ৳ ২২ লাখ',
                    'buyer_details' => $details,
                ]);
            }
        }

        // 2. Fix admin and staff user credentials and ensure active status
        if (! app()->runningUnitTests() && Schema::hasTable('users')) {
            $roleAdmin = Role::where('slug', 'admin')->first();
            $adminEmail = env('ADMIN_EMAIL', 'admin@gbrel.com');
            $adminPassword = (string) (env('ADMIN_PASSWORD') ?: 'admin123');

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

            // Re-activate and ensure staff accounts with seeder passwords
            $rolePropertyManager = Role::where('slug', 'property_manager')->first();
            $roleLegal = Role::where('slug', 'legal_compliance')->first();
            $roleAgent = Role::where('slug', 'agent')->first();
            $roleBuyer = Role::where('slug', 'buyer')->first();

            $staffAccounts = [
                'manager@gbrel.com' => [
                    'name' => 'Tariqul Islam (Property Director)',
                    'password' => 'manager123',
                    'role' => 'property_manager',
                    'role_id' => $rolePropertyManager?->id,
                    'phone' => '+880 1711-445566',
                ],
                'legal@gbrel.com' => [
                    'name' => 'Barrister Shafiul Alam (Legal Panel Head)',
                    'password' => 'legal123',
                    'role' => 'legal_compliance',
                    'role_id' => $roleLegal?->id,
                    'phone' => '+880 1912-334455',
                ],
                'agent@gbrel.com' => [
                    'name' => 'Tanvir Ahmed (Senior Luxury Advisor)',
                    'password' => 'agent123',
                    'role' => 'agent',
                    'role_id' => $roleAgent?->id,
                    'phone' => '+880 1819-987654',
                ],
                'buyer@gbrel.com' => [
                    'name' => 'Shere Ali (VIP Investor)',
                    'password' => 'buyer123',
                    'role' => 'buyer',
                    'role_id' => $roleBuyer?->id,
                    'phone' => '+880 1711-234567',
                ],
            ];

            foreach ($staffAccounts as $email => $data) {
                User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $data['name'],
                        'password' => Hash::make($data['password']),
                        'email_verified_at' => now(),
                        'role' => $data['role'],
                        'role_id' => $data['role_id'],
                        'phone' => $data['phone'],
                        'status' => 'Active',
                    ]
                );
            }
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
