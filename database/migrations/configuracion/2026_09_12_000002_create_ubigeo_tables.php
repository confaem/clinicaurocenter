<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Maestro geográfico (Ubigeo Perú): paises → departamentos → provincias → distritos.
 * `distritos.ubigeo` (6 dígitos) es el código oficial de INEI.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paises', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 5)->unique()->comment('Código ISO: PE');
            $table->string('nombre');
            $table->boolean('es_peru')->default(false);
            $table->boolean('estado')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 2)->comment('Código INEI: 14 (Lambayeque)');
            $table->string('nombre');
            $table->foreignId('pais_id')->constrained('paises');
            $table->boolean('estado')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['codigo', 'pais_id']);
        });

        Schema::create('provincias', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 2);
            $table->string('nombre');
            $table->foreignId('departamento_id')->constrained('departamentos');
            $table->boolean('estado')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['codigo', 'departamento_id']);
        });

        Schema::create('distritos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 2);
            $table->string('nombre');
            $table->foreignId('provincia_id')->constrained('provincias');
            $table->string('ubigeo', 6)->unique()->comment('Código INEI de 6 dígitos');
            $table->boolean('estado')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distritos');
        Schema::dropIfExists('provincias');
        Schema::dropIfExists('departamentos');
        Schema::dropIfExists('paises');
    }
};
