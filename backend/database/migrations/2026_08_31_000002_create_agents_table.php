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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title');
            $table->string('agency')->default('GBREL Real Estate Advisory');
            $table->string('state')->default('Dhaka North');
            $table->string('city')->default('Dhaka');
            $table->string('photo')->nullable();
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('whatsapp')->nullable();
            $table->text('bio')->nullable();
            $table->integer('experience_years')->default(5);
            $table->decimal('rating', 3, 2)->default(4.9);
            $table->integer('review_count')->default(20);
            $table->integer('active_listings_count')->default(5);
            $table->json('specialties')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
