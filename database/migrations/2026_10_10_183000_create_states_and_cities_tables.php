<?php

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

        Schema::table('suppliers', function (Blueprint $table): void {
            if (! Schema::hasColumn('suppliers', 'state_id')) {
                $table->foreignId('state_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('suppliers', 'city_id')) {
                $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table): void {
            if (Schema::hasColumn('suppliers', 'city_id')) {
                $table->dropConstrainedForeignId('city_id');
            }
            if (Schema::hasColumn('suppliers', 'state_id')) {
                $table->dropConstrainedForeignId('state_id');
            }
        });

        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
    }
};
