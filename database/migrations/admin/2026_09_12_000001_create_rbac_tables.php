<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Núcleo del RBAC dinámico: permisos, perfiles, menú y sus pivotes.
 *
 * Orden obligatorio: `perfiles` y `menu` deben existir antes de crear
 * `perfil_user` y `perfil_menu_permiso` (claves foráneas).
 *
 * Lee/actualiza: docs/analisis/analisis-arquitectura-flowstock.md §10 y §12.3
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Catálogo dinámico de acciones (ver, crear, editar, …)
        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 100);
            $table->string('descripcion', 250)->nullable();
            $table->boolean('estado')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // 2. Perfiles (roles)
        Schema::create('perfiles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('descripcion', 250)->nullable();

            // Reemplaza el bypass hardcodeado por `id === 1` de FlowStock (deuda D8)
            $table->boolean('es_superadmin')->default(false);

            $table->boolean('estado')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // 3. Menú jerárquico multinivel (alimenta el sidebar)
        Schema::create('menu', function (Blueprint $table) {
            $table->id();
            $table->string('bloque', 50)->nullable()->comment('PRINCIPAL | CLINICO | COMERCIAL | ADMIN | REPORTES');
            $table->string('nivel', 100)->comment('Código jerárquico: 01, 02, 0201, 020101');
            $table->string('nombre', 100);
            $table->string('enlace', 150)->nullable()->comment('Nombre de la ruta Laravel (clave de autorización)');
            $table->string('icono', 100)->nullable()->comment('Clase Tabler, por ejemplo: ti ti-users');
            $table->foreignId('parent_id')->nullable()->constrained('menu')->cascadeOnDelete();
            $table->integer('orden')->default(0);
            $table->boolean('estado')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('parent_id');
            $table->index('orden');
            $table->index('bloque');
            $table->index('nivel');
        });

        // 4. Pivote usuarios ↔ perfiles (M:M)
        Schema::create('perfil_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('perfil_id')->constrained('perfiles')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['user_id', 'perfil_id'], 'perfil_user_unique');
        });

        // 5. Matriz perfil × menú × permiso (el corazón del RBAC)
        Schema::create('perfil_menu_permiso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perfil_id')->constrained('perfiles')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('menu')->cascadeOnDelete();
            $table->foreignId('permiso_id')->constrained('permisos')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['perfil_id', 'menu_id', 'permiso_id'], 'perfil_menu_permiso_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfil_menu_permiso');
        Schema::dropIfExists('perfil_user');
        Schema::dropIfExists('menu');
        Schema::dropIfExists('perfiles');
        Schema::dropIfExists('permisos');
    }
};
