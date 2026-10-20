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
        Schema::table('media', function (Blueprint $table) {
            $table->unsignedBigInteger('original_size')->nullable()->after('size');
            $table->string('compression_status', 20)->default('pending')->after('original_size');
            $table->unsignedBigInteger('saved_bytes')->default(0)->after('compression_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn(['original_size', 'compression_status', 'saved_bytes']);
        });
    }
};
