<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('wht_total', 15, 2)->default(0)->after('tax_total');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('wht_rate', 5, 2)->default(0)->after('amount');
            $table->decimal('wht_amount', 15, 2)->default(0)->after('wht_rate');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['wht_rate', 'wht_amount']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('wht_total');
        });
    }
};
