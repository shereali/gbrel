<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'hide_price')) {
                $table->boolean('hide_price')->default(false)->after('price_unit');
            }
            if (!Schema::hasColumn('properties', 'price_display_text')) {
                $table->string('price_display_text')->nullable()->default('Price on Application')->after('hide_price');
            }
            if (!Schema::hasColumn('properties', 'hide_agent_photo')) {
                $table->boolean('hide_agent_photo')->default(false)->after('agent_id');
            }
            if (!Schema::hasColumn('properties', 'hide_agent_contact')) {
                $table->boolean('hide_agent_contact')->default(false)->after('hide_agent_photo');
            }
            if (!Schema::hasColumn('properties', 'hide_exact_address')) {
                $table->boolean('hide_exact_address')->default(false)->after('address');
            }
            if (!Schema::hasColumn('properties', 'hide_floor_plan')) {
                $table->boolean('hide_floor_plan')->default(false)->after('hide_exact_address');
            }
            if (!Schema::hasColumn('properties', 'hide_mortgage_calculator')) {
                $table->boolean('hide_mortgage_calculator')->default(false)->after('hide_floor_plan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $cols = [
                'hide_price',
                'price_display_text',
                'hide_agent_photo',
                'hide_agent_contact',
                'hide_exact_address',
                'hide_floor_plan',
                'hide_mortgage_calculator'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('properties', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
