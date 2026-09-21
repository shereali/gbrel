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
        if (!Schema::hasTable('divisions')) {
            Schema::create('divisions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->string('bn_name')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            $divisions = [
                ['name' => 'Dhaka North', 'bn_name' => 'ঢাকা উত্তর'],
                ['name' => 'Dhaka South', 'bn_name' => 'ঢাকা দক্ষিণ'],
                ['name' => 'Chittagong', 'bn_name' => 'চট্টগ্রাম'],
                ['name' => 'Sylhet', 'bn_name' => 'সিলেট'],
                ['name' => "Cox's Bazar", 'bn_name' => 'কক্সবাজার'],
                ['name' => 'Gazipur', 'bn_name' => 'গাজীপুর'],
                ['name' => 'Narayanganj', 'bn_name' => 'নারায়ণগঞ্জ'],
                ['name' => 'Rajshahi', 'bn_name' => 'রাজশাহী'],
                ['name' => 'Khulna', 'bn_name' => 'খুলনা'],
                ['name' => 'Barisal', 'bn_name' => 'বরিশাল'],
                ['name' => 'Rangpur', 'bn_name' => 'রংপুর'],
                ['name' => 'Mymensingh', 'bn_name' => 'ময়মনসিংহ']
            ];

            foreach ($divisions as $index => $div) {
                DB::table('divisions')->insert([
                    'name' => $div['name'],
                    'slug' => Str::slug($div['name']),
                    'bn_name' => $div['bn_name'],
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
        Schema::dropIfExists('divisions');
    }
};
