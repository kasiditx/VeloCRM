<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_fields', function (Blueprint $table): void {
            $table->id();
            $table->string('model_type');
            $table->string('key');
            $table->string('label_th')->nullable();
            $table->string('label_en');
            $table->string('type');
            $table->json('options')->nullable();
            $table->timestamps();

            $table->unique(['model_type', 'key']);
            $table->index('model_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};
