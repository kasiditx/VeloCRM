<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            if (! Schema::hasColumn('leads', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('customers', function (Blueprint $table): void {
            if (! Schema::hasColumn('customers', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('invoices', function (Blueprint $table): void {
            if (! Schema::hasColumn('invoices', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('proposals', function (Blueprint $table): void {
            if (! Schema::hasColumn('proposals', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });
    }
};
