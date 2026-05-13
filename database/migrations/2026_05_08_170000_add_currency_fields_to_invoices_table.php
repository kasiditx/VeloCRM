<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('currency', 3)->default('USD')->after('status');
            $table->decimal('exchange_rate', 12, 6)->default(1)->after('currency');
        });

        $currency = strtoupper((string) (
            DB::table('settings')->where('key', 'default_currency')->value('value')
            ?: DB::table('settings')->where('key', 'currency_code')->value('value')
            ?: 'USD'
        ));

        DB::table('invoices')->update([
            'currency' => substr($currency, 0, 3),
            'exchange_rate' => 1,
        ]);
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate']);
        });
    }
};
