<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('individuals', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('nickname')->nullable();
            $table->string('cpf', 11)->unique();
            $table->string('rg', 30);
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table): void {
            $table->id();
            $table->string('corporate_name');
            $table->string('trade_name');
            $table->string('cnpj', 14)->unique();
            $table->string('state_registration_indicator');
            $table->string('state_registration')->nullable();
            $table->string('municipal_registration')->nullable();
            $table->string('tax_regime');
            $table->string('cnpj_status')->nullable();
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->morphs('supplierable');
            $table->unique(['supplierable_type', 'supplierable_id']);
            $table->boolean('active')->default(true);
            $table->string('postal_code', 8);
            $table->string('street');
            $table->string('number');
            $table->string('complement')->nullable();
            $table->string('district');
            $table->string('reference')->nullable();
            $table->char('state', 2);
            $table->string('city');
            $table->boolean('condominium')->default(false);
            $table->string('condominium_address')->nullable();
            $table->string('condominium_number', 30)->nullable();
            $table->longText('observation')->nullable();
            $table->timestamps();
        });

        Schema::create('contacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->string('name')->nullable();
            $table->string('company')->nullable();
            $table->string('position')->nullable();
            $table->timestamps();
            $table->index(['supplier_id', 'is_primary']);
        });

        Schema::create('contact_methods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 10);
            $table->string('value');
            $table->string('label')->nullable();
            $table->timestamps();
            $table->index(['kind', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_methods');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('individuals');
    }
};
