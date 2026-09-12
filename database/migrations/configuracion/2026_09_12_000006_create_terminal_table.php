<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Terminales (puestos de trabajo): caja, admisión, consultorio, farmacia.
 * Se relaciona con las series de comprobantes (doc 01 §26).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terminal', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 100);
            $table->string('descripcion', 250)->nullable();
            $table->boolean('estado')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terminal');
    }
};
