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
            $table->uuid('request_id')->nullable()->unique();
            $table->string('callback_time')->nullable();
            $table->string('next_step')->nullable();
            $table->string('form_version')->nullable();
            $table->timestamp('contact_consented_at')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropUnique(['request_id']);
            $table->dropColumn(['request_id', 'callback_time', 'next_step', 'form_version', 'contact_consented_at', 'utm_content', 'utm_term']);
        });
    }
};
