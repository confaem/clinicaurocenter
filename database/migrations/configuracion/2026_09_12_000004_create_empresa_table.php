<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Empresa / sede de la clínica (emisora de comprobantes).
 * En UroCenter existirá además la entidad Sucursal (doc 01 §37).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa', function (Blueprint $table) {
            $table->id();

            $table->string('ruc', 15)->unique();
            $table->string('razon_social', 250);
            $table->string('nombre_comercial', 250)->nullable();
            $table->string('direccion', 250)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('logo', 250)->nullable();

            $table->boolean('estado')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa');
    }
};
