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
        Schema::create('provider_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('business_name');
            $table->string('location');
            $table->string('whatsapp_number', 20)->nullable();
            $table->string('bio')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->string('banner_path')->nullable();
            $table->decimal('price_range_min', 10, 2)->nullable();
            $table->decimal('price_range_max', 10, 2)->nullable();
            $table->enum('verification_status', ['unverified', 'verified', 'rejected'])->default('unverified');
            $table->enum('availability_status', ['available', 'busy', 'unavailable'])->default('available');

            $table->timestamps();

            $table->index('location');
            $table->index(['category_id', 'verification_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_profiles');
    }
};
