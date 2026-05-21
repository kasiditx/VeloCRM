<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_filter_views', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('resource', 80);
            $table->string('name', 120);
            $table->json('filters');
            $table->timestamps();

            $table->unique(['user_id', 'resource', 'name']);
            $table->index(['user_id', 'resource']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_filter_views');
    }
};
