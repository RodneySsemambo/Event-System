<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'quoted_amount')) {
                $table->decimal('quoted_amount', 12, 2)->nullable()->after('guest_count');
            }
            $table->decimal('commission_amount', 12, 2)->nullable()->after('quoted_amount');
            $table->decimal('provider_payout', 12, 2)->nullable()->after('commission_amount');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['commission_amount', 'provider_payout']);
        });
    }
};
