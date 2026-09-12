<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campos de RBAC y personal en `users`.
 *
 * Debe ejecutarse DESPUÉS de `personal` y `perfiles` (claves foráneas).
 * Orden garantizado por el timestamp 2026_09_12_000007 frente a
 * 000003 (personal) y 000001 (rbac).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('personal_id')->nullable()->after('password')->constrained('personal');
            $table->foreignId('perfil_id')->nullable()->after('personal_id')->constrained('perfiles')
                ->comment('Perfil principal (compatibilidad); la asignación real es perfil_user');
            $table->boolean('estado')->default(true)->after('perfil_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['personal_id']);
            $table->dropForeign(['perfil_id']);
            $table->dropColumn(['personal_id', 'perfil_id', 'estado']);
        });
    }
};
