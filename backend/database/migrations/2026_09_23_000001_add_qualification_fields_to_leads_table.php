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
        Schema::table('leads', function (Blueprint $table) {
            $table->unsignedBigInteger('property_id')->nullable()->after('id');
            $table->string('buyer_category')->nullable()->after('lead_type');
            $table->string('investment_readiness')->nullable()->after('buyer_category');
            $table->string('budget_range')->nullable()->after('investment_readiness');
            $table->string('preferred_contact')->default('WhatsApp')->after('budget_range');
            $table->string('utm_source')->nullable()->after('status');
            $table->string('utm_medium')->nullable()->after('utm_source');
            $table->string('utm_campaign')->nullable()->after('utm_medium');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'property_id',
                'buyer_category',
                'investment_readiness',
                'budget_range',
                'preferred_contact',
                'utm_source',
                'utm_medium',
                'utm_campaign',
            ]);
        });
    }
};
