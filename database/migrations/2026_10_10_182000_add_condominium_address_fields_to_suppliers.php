<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table): void {
            if (! Schema::hasColumn('suppliers', 'condominium_address')) {
                $table->string('condominium_address')->nullable();
            }
            if (! Schema::hasColumn('suppliers', 'condominium_number')) {
                $table->string('condominium_number', 30)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table): void {
            $table->dropColumn(['condominium_address', 'condominium_number']);
        });
    }
};
