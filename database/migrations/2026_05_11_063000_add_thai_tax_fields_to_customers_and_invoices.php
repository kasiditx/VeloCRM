<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            $table->string('tax_id', 13)->nullable()->after('address');
            $table->string('branch')->nullable()->after('tax_id');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->string('tax_id', 13)->nullable()->after('customer_id');
            $table->string('branch')->nullable()->after('tax_id');
        });

        DB::table('invoices')
            ->select(['invoices.id', 'customers.tax_id', 'customers.branch'])
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->orderBy('invoices.id')
            ->each(function (object $row): void {
                DB::table('invoices')
                    ->where('id', $row->id)
                    ->update([
                        'tax_id' => $row->tax_id,
                        'branch' => $row->branch,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropColumn(['tax_id', 'branch']);
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropColumn(['tax_id', 'branch']);
        });
    }
};
