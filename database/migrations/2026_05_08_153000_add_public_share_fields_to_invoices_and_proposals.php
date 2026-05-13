<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->uuid('public_token')->nullable()->unique()->after('notes');
            $table->timestamp('public_viewed_at')->nullable()->after('public_token');
            $table->string('public_viewed_ip', 45)->nullable()->after('public_viewed_at');
        });

        Schema::table('proposals', function (Blueprint $table): void {
            $table->uuid('public_token')->nullable()->unique()->after('status');
            $table->timestamp('public_viewed_at')->nullable()->after('public_token');
            $table->string('public_viewed_ip', 45)->nullable()->after('public_viewed_at');
        });

        DB::table('invoices')
            ->whereNull('public_token')
            ->orderBy('id')
            ->each(fn (object $invoice) => DB::table('invoices')
                ->where('id', $invoice->id)
                ->update(['public_token' => (string) Str::uuid()]));

        DB::table('proposals')
            ->whereNull('public_token')
            ->orderBy('id')
            ->each(fn (object $proposal) => DB::table('proposals')
                ->where('id', $proposal->id)
                ->update(['public_token' => (string) Str::uuid()]));
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropColumn(['public_token', 'public_viewed_at', 'public_viewed_ip']);
        });

        Schema::table('proposals', function (Blueprint $table): void {
            $table->dropColumn(['public_token', 'public_viewed_at', 'public_viewed_ip']);
        });
    }
};
