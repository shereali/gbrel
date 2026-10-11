<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Paid e-book orders ("The Hidden Business of Land" on /book), paid through EasyPay (bKash/Nagad/Rocket).
 * public_id is the unguessable id the buyer's thank-you link carries; the buyer can reopen it any time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_orders', function (Blueprint $table) {
            $table->id();
            $table->string('public_id', 40)->unique();
            $table->uuid('request_id')->nullable()->unique();
            $table->unsignedBigInteger('lead_id')->nullable()->index();
            $table->string('name', 100);
            $table->string('phone', 25)->index();
            $table->string('email', 254)->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('BDT');
            $table->string('status', 20)->default('pending')->index();
            $table->string('gateway', 20)->default('easypay');
            $table->string('gateway_txn_id', 100)->nullable()->unique();
            $table->text('checkout_url')->nullable();
            $table->string('trx_id', 100)->nullable();
            $table->string('payment_method', 40)->nullable();
            $table->string('sender_number', 30)->nullable();
            $table->decimal('paid_amount', 10, 2)->nullable();
            $table->decimal('fee', 10, 2)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->json('tracking')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_orders');
    }
};
