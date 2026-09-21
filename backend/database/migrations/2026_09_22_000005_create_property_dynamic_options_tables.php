<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Property Categories Table
        if (!Schema::hasTable('property_categories')) {
            Schema::create('property_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            $categories = [
                ['name' => 'Land Share', 'description' => 'Co-ownership project with shared land stake', 'icon' => 'pie-chart'],
                ['name' => 'Flat', 'description' => 'Luxury multi-family apartments and flats', 'icon' => 'home'],
                ['name' => 'Plot', 'description' => 'Residential & boundary-demarcated plots', 'icon' => 'grid'],
                ['name' => 'Land', 'description' => 'Freehold acreage and development land in Bigha', 'icon' => 'map'],
                ['name' => 'Hotel', 'description' => 'Beachfront hotels and hospitality suites', 'icon' => 'briefcase'],
                ['name' => 'Duplex', 'description' => 'Duplex villas and sky penthouses', 'icon' => 'layers'],
                ['name' => 'Commercial', 'description' => 'Corporate office spaces and commercial hubs', 'icon' => 'building'],
                ['name' => 'Penthouse', 'description' => 'Top-floor skyline residential penthouses', 'icon' => 'award']
            ];

            foreach ($categories as $index => $cat) {
                DB::table('property_categories')->insert([
                    'name' => $cat['name'],
                    'slug' => Str::slug($cat['name']),
                    'description' => $cat['description'],
                    'icon' => $cat['icon'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // 2. Property Divisions / Regions Table
        if (!Schema::hasTable('property_divisions')) {
            Schema::create('property_divisions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->string('bn_name')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            $divisions = [
                'Dhaka North',
                'Dhaka South',
                'Chittagong',
                'Sylhet',
                "Cox's Bazar",
                'Gazipur',
                'Narayanganj',
                'Rajshahi',
                'Khulna',
                'Barisal',
                'Rangpur',
                'Mymensingh'
            ];

            foreach ($divisions as $index => $div) {
                DB::table('property_divisions')->insert([
                    'name' => $div,
                    'slug' => Str::slug($div),
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // 3. Property Transaction Types Table
        if (!Schema::hasTable('property_transaction_types')) {
            Schema::create('property_transaction_types', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            $types = ['Sale', 'Lease', 'Joint Venture', 'Auction'];
            foreach ($types as $index => $t) {
                DB::table('property_transaction_types')->insert([
                    'name' => $t,
                    'slug' => Str::slug($t),
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // 4. Property Lifecycle Statuses Table
        if (!Schema::hasTable('property_statuses')) {
            Schema::create('property_statuses', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->string('color_code')->default('#10B981');
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            $statuses = [
                ['name' => 'Draft', 'color_code' => '#F59E0B'],
                ['name' => 'Active', 'color_code' => '#10B981'],
                ['name' => 'Under Offer', 'color_code' => '#38BDF8'],
                ['name' => 'Sold', 'color_code' => '#A855F7'],
                ['name' => 'Delisted', 'color_code' => '#64748B']
            ];

            foreach ($statuses as $index => $s) {
                DB::table('property_statuses')->insert([
                    'name' => $s['name'],
                    'slug' => Str::slug($s['name']),
                    'color_code' => $s['color_code'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // 5. Property Land Units Table
        if (!Schema::hasTable('property_land_units')) {
            Schema::create('property_land_units', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->string('symbol')->nullable();
                $table->decimal('sqft_multiplier', 12, 4)->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            $landUnits = [
                ['name' => 'Katha', 'symbol' => 'katha', 'sqft_multiplier' => 720.0000],
                ['name' => 'Bigha', 'symbol' => 'bigha', 'sqft_multiplier' => 14400.0000],
                ['name' => 'Shotok', 'symbol' => 'shotok', 'sqft_multiplier' => 435.6000],
                ['name' => 'Decimal', 'symbol' => 'dec', 'sqft_multiplier' => 435.6000],
                ['name' => 'Sqft', 'symbol' => 'sqft', 'sqft_multiplier' => 1.0000],
                ['name' => 'Acre', 'symbol' => 'acre', 'sqft_multiplier' => 43560.0000]
            ];

            foreach ($landUnits as $index => $u) {
                DB::table('property_land_units')->insert([
                    'name' => $u['name'],
                    'slug' => Str::slug($u['name']),
                    'symbol' => $u['symbol'],
                    'sqft_multiplier' => $u['sqft_multiplier'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_land_units');
        Schema::dropIfExists('property_statuses');
        Schema::dropIfExists('property_transaction_types');
        Schema::dropIfExists('property_divisions');
        Schema::dropIfExists('property_categories');
    }
};
