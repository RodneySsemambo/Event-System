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
        Schema::create('rental_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_profile_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('inventory_item_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('quantity')->default(1);
            $table->enum('status', ['pending', 'accepted', 'declined', 'completed', 'cancelled'])
                ->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['inventory_item_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_orders');
    }
};
