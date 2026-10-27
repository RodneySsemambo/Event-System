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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_profile_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('provider_profile_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('event_type');
            $table->date('event_date');
            $table->string('location');
            $table->unsignedInteger('guest_count');
            $table->text('notes')->nullable();

            $table->enum('status', ['pending', 'accepted', 'declined', 'cancelled', 'completed'])
                ->default('pending');
            $table->timestamps();


            $table->index(['provider_profile_id', 'status']);
            $table->index('event_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
