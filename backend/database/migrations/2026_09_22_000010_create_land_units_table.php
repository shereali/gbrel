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
        if (!Schema::hasTable('land_units')) {
            Schema::create('land_units', function (Blueprint $table) {
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
                DB::table('land_units')->insert([
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
        Schema::dropIfExists('land_units');
    }
};
