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
        if (!Schema::hasTable('property_statuses')) {
            Schema::create('property_statuses', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->string('color_code')->default('#10B981');
                $table->string('badge_label')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            $statuses = [
                ['name' => 'Draft', 'color_code' => '#F59E0B', 'badge_label' => 'Private Draft'],
                ['name' => 'Active', 'color_code' => '#10B981', 'badge_label' => 'Live on Portal'],
                ['name' => 'Under Offer', 'color_code' => '#38BDF8', 'badge_label' => 'Under Offer'],
                ['name' => 'Sold', 'color_code' => '#A855F7', 'badge_label' => 'Sold Mandate'],
                ['name' => 'Delisted', 'color_code' => '#64748B', 'badge_label' => 'Delisted']
            ];

            foreach ($statuses as $index => $s) {
                DB::table('property_statuses')->insert([
                    'name' => $s['name'],
                    'slug' => Str::slug($s['name']),
                    'color_code' => $s['color_code'],
                    'badge_label' => $s['badge_label'],
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
        Schema::dropIfExists('property_statuses');
    }
};
