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
        if (!Schema::hasTable('transaction_types')) {
            Schema::create('transaction_types', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->string('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            $types = [
                ['name' => 'Sale', 'description' => 'Outright property sale and sub-deed registration'],
                ['name' => 'Lease', 'description' => 'Commercial and residential tenancy lease'],
                ['name' => 'Joint Venture', 'description' => 'Landowner and developer joint partnership development'],
                ['name' => 'Auction', 'description' => 'Public competitive bidding and luxury asset tenders']
            ];

            foreach ($types as $index => $t) {
                DB::table('transaction_types')->insert([
                    'name' => $t['name'],
                    'slug' => Str::slug($t['name']),
                    'description' => $t['description'],
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
        Schema::dropIfExists('transaction_types');
    }
};
