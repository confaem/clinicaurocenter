<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas (autenticación)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

/*
|--------------------------------------------------------------------------
| Rutas privadas (requieren sesión)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    /*
    |----------------------------------------------------------------------
    | ADMINISTRACIÓN
    |----------------------------------------------------------------------
    | Usuarios, Perfiles, Permisos y Menús.
    */

    /*
    |----------------------------------------------------------------------
    | CONFIGURACIÓN
    |----------------------------------------------------------------------
    | Contrato AJAX: index / data / store / edit / update / toggle
    | (ver docs/analisis/analisis-arquitectura-flowstock.md §4)
    */
    Route::prefix('configuracion')->name('configuracion.')->group(function () {

        Route::controller(\App\Http\Controllers\Configuracion\EmpresaController::class)->group(function () {
            Route::get('/empresa', 'index')->name('empresa.index');
            Route::get('/empresa/data', 'getData')->name('empresa.data');
            Route::post('/empresa', 'store')->name('empresa.store');
            Route::get('/empresa/{id}/edit', 'edit')->name('empresa.edit');
            Route::post('/empresa/{id}', 'update')->name('empresa.update');
            Route::post('/empresa/{id}/toggle', 'toggle')->name('empresa.toggle');
        });

    });
});
