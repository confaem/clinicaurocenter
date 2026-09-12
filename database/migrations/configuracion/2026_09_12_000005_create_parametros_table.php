<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Parámetros del sistema (configuración por clave/valor).
 * Ejemplos: PAG_MAESTRO, IGV, MONEDA, FORMATO_IMPRESION, SERIE_DEFECTO.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametros', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('descripcion', 250)->nullable();
            $table->text('valor')->nullable();
            $table->string('tipo', 20)->default('texto')->comment('texto | numero | booleano | json');
            $table->boolean('estado')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parametros');
    }
};
