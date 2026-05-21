<?php

declare(strict_types=1);

use App\Support\InvoiceDocuments;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            if (! Schema::hasColumn('invoices', 'document_type')) {
                $table->string('document_type', 32)
                    ->default(InvoiceDocuments::DEFAULT_TYPE)
                    ->after('number')
                    ->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            if (Schema::hasColumn('invoices', 'document_type')) {
                $table->dropIndex(['document_type']);
                $table->dropColumn('document_type');
            }
        });
    }
};
