<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Cria uma observação opcional por usuário, com integridade referencial. */
    public function up(): void
    {
        Schema::create('user_observations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();
        });
    }

    /** Remove a tabela de observações. */
    public function down(): void
    {
        Schema::dropIfExists('user_observations');
    }
};
