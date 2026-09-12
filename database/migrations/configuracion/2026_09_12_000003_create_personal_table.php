<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Personal de la clínica (base para médicos, recepción, caja, farmacia…).
 * `users.personal_id` apunta aquí (ver migración 000005).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal', function (Blueprint $table) {
            $table->id();

            $table->string('tipo_documento', 20)->default('DNI')->comment('DNI | CE | PASAPORTE');
            $table->string('numero_documento', 20)->unique();

            $table->string('nombres');
            $table->string('apellidos');

            $table->date('fecha_nacimiento')->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('email_personal')->nullable();
            $table->string('direccion')->nullable();

            // Nivel más específico del ubigeo (permite derivar departamento/provincia)
            $table->foreignId('ubigeo_id')->nullable()->constrained('distritos');

            $table->string('cargo')->nullable();
            $table->date('fecha_ingreso')->nullable();

            $table->boolean('estado')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal');
    }
};
