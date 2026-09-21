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
        if (!Schema::hasTable('brochures')) {
            Schema::create('brochures', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('file_url');
                $table->string('file_name')->nullable();
                $table->string('file_size')->nullable();
                $table->string('file_type')->default('PDF');
                $table->unsignedBigInteger('property_id')->nullable()->index();
                $table->string('category')->default('Property Brochure');
                $table->integer('download_count')->default(0);
                $table->boolean('is_public')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brochures');
    }
};
