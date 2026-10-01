<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One-time reset links were replaced by setting a password directly from Admin → Users,
 * so the table they used is no longer needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('password_reset_links');
    }

    public function down(): void
    {
        Schema::create('password_reset_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }
};
