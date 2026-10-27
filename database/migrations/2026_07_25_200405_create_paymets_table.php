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
        Schema::create('payments', function (Blueprint $table) {

            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();

            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('UGX');
            $table->string('phone_number', 20); // international format, digits only

            // MarzPay's transaction reference — unique so a webhook retry
            // can never create a second payment row for the same charge.
            $table->string('marzpay_reference')->unique();

            $table->enum('status', ['pending', 'successful', 'failed', 'refunded'])->default('pending');
            $table->enum('type', ['collection', 'refund'])->default('collection');

            // Full webhook body, kept verbatim — invaluable for investigating
            // a disputed "I paid but it shows pending" complaint later.
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
