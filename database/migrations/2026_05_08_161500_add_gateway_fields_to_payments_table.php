<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('gateway')->nullable()->after('payment_method');
            $table->string('status')->default('paid')->after('gateway');
            $table->string('external_reference')->nullable()->after('transaction_id');
            $table->json('raw_payload')->nullable()->after('notes');
            $table->timestamp('verified_at')->nullable()->after('raw_payload');
            $table->index(['gateway', 'transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['gateway', 'transaction_id']);
            $table->dropColumn([
                'gateway',
                'status',
                'external_reference',
                'raw_payload',
                'verified_at',
            ]);
        });
    }
};
