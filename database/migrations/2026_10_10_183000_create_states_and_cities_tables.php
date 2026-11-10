<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('states')) {
            Schema::create('states', function (Blueprint $table): void {
                $table->id();
                $table->unsignedSmallInteger('ibge_code')->unique();
                $table->char('code', 2)->unique();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('cities')) {
            Schema::create('cities', function (Blueprint $table): void {
                $table->id();
                $table->unsignedInteger('ibge_code')->unique();
                $table->foreignId('state_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->timestamps();
                $table->index(['state_id', 'name']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
    }
};
