<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Owner-submitted listings: who submitted, where the review stands, and the private facts only staff see.
     */
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('review_status')->nullable()->index();
            $table->text('review_note')->nullable();
            $table->json('owner_details')->nullable();
            $table->json('owner_pending_changes')->nullable();
            $table->json('owner_agreement')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_id');
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['review_status', 'review_note', 'owner_details', 'owner_pending_changes', 'owner_agreement', 'submitted_at', 'reviewed_at', 'published_at']);
        });
    }
};
