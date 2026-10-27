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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('rental_price', 10, 2);
            $table->unsignedInteger('quantity_available')->default(1);
            $table->string('image_path')->nullable();
            $table->timestamps();

            $table->index('provider_profile_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
