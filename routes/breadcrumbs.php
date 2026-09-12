<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

/*
|--------------------------------------------------------------------------
| Migas de pan (Diglactic)
|--------------------------------------------------------------------------
| Convención: el nombre de la breadcrumb es idéntico al nombre de la ruta.
| Un módulo nuevo ⇒ una breadcrumb nueva (ver §8 del análisis de FlowStock).
*/

// Dashboard
Breadcrumbs::for('dashboard', function (BreadcrumbTrail $trail) {
    $trail->push('Dashboard', route('dashboard'));
});

// Administración > Configuración
Breadcrumbs::for('configuracion', function (BreadcrumbTrail $trail) {
    $trail->push('Configuración');
});

// Administración > Configuración > Empresa
Breadcrumbs::for('configuracion.empresa.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->parent('configuracion');
    $trail->push('Empresa', route('configuracion.empresa.index'));
});
