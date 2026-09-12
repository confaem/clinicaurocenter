# Documentación de Clínica UroCenter

Repositorio: `https://github.com/confaem/clinicaurocenter.git` · Rama `main`

---

## Estructura de la documentación

```text
docs/
├── Arquitectura del Sistema Web Clínica UroCenter.md    ← Doc 01. Arquitectura funcional (base)
├── analisis/
│   ├── README.md                                        ← Este índice
│   ├── analisis-template-dreamsemr.md                    ← Doc A. Plantilla visual adoptada
│   └── analisis-arquitectura-flowstock.md                ← Doc B. Arquitectura reutilizable / kit de inicio
├── 02-modelo-entidades-urocenter.md                     ← PENDIENTE (previsto en el doc 01 §50)
└── 03-modelo-relacional-mysql.md                        ← PENDIENTE (previsto en el doc 01 §50)
```

> Los documentos de análisis van **sin numeración** y en su propia carpeta para no colisionar con la serie numerada de arquitectura (`02-`, `03-`…) definida al final del doc 01.

---

## Índice

| Documento | Estado | Contenido |
|---|---|---|
| [Doc 01 · Arquitectura del Sistema Web Clínica UroCenter](../Arquitectura%20del%20Sistema%20Web%20Clínica%20UroCenter.md) | Vigente (v1.0) | Objetivo, arquitectura funcional (Clínico / Comercial / Admin), stack, plantilla base, principios, módulos, entidades conceptuales, reglas de negocio, orden de desarrollo |
| [Doc A · Análisis del Template Dreams EMR](analisis-template-dreamsemr.md) | Completado | Identidad de la plantilla, inventario de assets, anatomía del layout, sidebar/topbar/footer, theming, componentes, 152 páginas y su mapeo a los módulos de UroCenter, brechas, plan de conversión a Blade, riesgos |
| [Doc B · Análisis de Arquitectura FlowStock](analisis-arquitectura-flowstock.md) | Completado | Stack, estructura de carpetas, contrato CRUD, Form Requests, modelos, rutas, breadcrumbs, sesión, RBAC, migraciones, tablas maestras, seeders, patrón frontend, kit de inicio, deuda técnica a no heredar |
| Doc 02 · Modelo de entidades | Pendiente | Entidad → descripción, propósito, atributos, relaciones, cardinalidad, reglas de negocio, dependencias |
| Doc 03 · Modelo relacional MySQL | Pendiente | Modelo físico: tipos, PK, FK, índices, constraints |
| Migraciones Laravel 12 | Pendiente | Derivadas del doc 03 |

---

## Decisiones adoptadas

| # | Decisión | Detalle |
|---|---|---|
| 1 | **Stack** | Laravel 12 + Blade + Bootstrap 5 + MySQL 8 + Vite (solo assets propios) |
| 2 | **Sin Livewire** | Restricción del doc 01 §4.1. La interacción se resuelve con Blade + JS nativo + AJAX |
| 3 | **Sin jQuery** | Ni el template Dreams EMR ni FlowStock usan jQuery (verificado) |
| 4 | **Autenticación** | Auth **custom** replicado de FlowStock (rate limiting, `session()->regenerate()`, `invalidate()` + `regenerateToken()`). **No** Jetstream, **no** Breeze |
| 5 | **Plantilla visual** | **Dreams EMR**: copiar `dreamsemr/html/assets/**` → `public/assets/**` y recrear los layouts como Blade propios (patrón usado en FlowStock con el tema *Silvar*) |
| 6 | **Kit de inicio** | RBAC + maestras de FlowStock **adaptadas**: `menu`, `perfiles`, `permisos`, `perfil_user`, `perfil_menu_permiso`, `empresa`, `parametros`, `terminal`, `personal`, `paises → distritos`, `users` |
| 7 | **Tailwind** | No se adopta (el tema trae su propio CSS) |
| 8 | **Documentación** | Los análisis van en `docs/analisis/` sin numeración |
| 9 | **Librería de tablas** | **`datatable-vanilla.js` de Dreams EMR**, alimentado por el endpoint `getData` → `{data, can}` de FlowStock. Se usa el CSS `dataTables.bootstrap5.min.css` del tema; **no** se carga `jquery.dataTables.min.js` |
| 10 | **Iconos** | **Tabler directo** (`<i class="ti ti-*">`), sin `iconify`. `menu.icono` guardará valores `ti ti-*` |
| 11 | **Base de datos** | **`clinica_urocenter`** (MySQL 8, Laragon `mysql-8.4.3`) · usuario `root`, sin contraseña en local |

---

## Decisiones cerradas

| # | Decisión | Resolución |
|---|---|---|
| P1 | Librería de tablas | **Cerrada**: `datatable-vanilla.js` del tema (decisión 9) |
| P2 | Iconos | **Cerrada**: Tabler directo (decisión 10) |
| P3 | Consistencia del doc 01 | **Cerrada**: doc 01 §4 actualizado (`Autenticación propia con RBAC dinámico`, `JavaScript nativo sin jQuery`), §4.1 (se añade "tampoco utilizará jQuery") y §33 (auth propia) |

---

## Estado de la instalación (2026-09-12)

Kit base **instalado y verificado** sobre Laravel 12.69.2 (PHP 8.3.30) y MySQL 8.4.3.

| Pieza | Estado |
|---|---|
| Proyecto Laravel 12 (`composer create-project`) | ✅ Instalado en `clinicaurocenter/` |
| Base de datos | ✅ `clinica_urocenter` (utf8mb4 / utf8mb4_unicode_ci) |
| `.env` | ✅ `APP_NAME="Clínica UroCenter"`, `APP_URL=http://clinicaurocenter.test`, locale `es`, `APP_TIMEZONE=America/Lima`, SESSION/CACHE/QUEUE en BD |
| Paquetes | ✅ `diglactic/laravel-breadcrumbs ^10.0`, `laravel-lang/common` (dev) + traducciones `lang/es` |
| Assets del tema | ✅ `dreamsemr/html/assets/**` → `public/assets/**` (51,6 MB) · SCSS fuente en `resources/theme-scss/` |
| Layouts Blade | ✅ `layouts/app`, `layouts/guest`, `components/layouts/{topbar,sidebar,footer}` |
| JS/CSS propios | ✅ `public/assets/js/urocenter.js` (DataTable remoto sin jQuery) y `public/assets/css/urocenter.css` |
| Autenticación propia | ✅ `AuthController` (throttle 5 intentos, `session()->regenerate()`, logout seguro) |
| Núcleo RBAC | ✅ `PermisoHelper`, `SessionDataService`, `HandleSessionData`, `@can_do`, `User::tienePermiso/permisosPorMenu/esSuperadmin` |
| Migraciones | ✅ 7 nuevas (RBAC, ubigeo, personal, empresa, parámetros, terminal, campos RBAC en `users`) registradas en `AppServiceProvider` |
| Seeders | ✅ Permisos (9), Perfiles (6), Menú (bloques PRINCIPAL/CLINICO/COMERCIAL/ADMIN/REPORTES), Usuario admin, Ubigeo (1833 distritos) |
| CRUD de referencia | ✅ `Configuracion/EmpresaController` + `EmpresaRequest` + vista con DataTable remoto, modal y SweetAlert2 |
| Breadcrumbs | ✅ `routes/breadcrumbs.php` (dashboard, configuración, empresa) |
| Pruebas | ✅ `tests/Feature/KitBaseTest.php` — **12 pruebas / 63 aserciones**, sobre SQLite en memoria |

### Deudas del análisis cerradas en esta instalación

| Deuda | Cómo se resolvió |
|---|---|
| **D1** migraciones de dominio no registradas | Todas las carpetas se registran con `loadMigrationsFrom()`; validado con `migrate` limpio y `RefreshDatabase` |
| **D2** rutas `update`/`toggle` mal enlazadas | El contrato CRUD se implementó completo en `EmpresaController` |
| **D8** superadmin por `id === 1` | `perfiles.es_superadmin` + `User::esSuperadmin()`; además el superadmin recibe el **árbol completo** de menús |
| **D9** `MenuSeeder` destructivo | `MenuSeeder` idempotente con `updateOrCreate` (sin `truncate`) |
| **D12** RBAC recargado en cada request | Versión RBAC en caché (`SessionDataService::VERSION_KEY`): si no cambió, no se repiten consultas |
| **D13** sin pruebas | `KitBaseTest` cubre login, RBAC, 403, 422 y CRUD completo |
| **D14** credenciales fijas en el seeder | `UROCENTER_ADMIN_EMAIL` / `UROCENTER_ADMIN_PASSWORD`; si no se define, se genera aleatoria y se muestra una vez |
| **D17** Form Request sin usar | Un `EmpresaRequest` usado en `store()` **y** `update()` |
| **M1** superadmin hardcodeado | Igual que D8 |

### Marca (logo)

Los recursos de marca viven en `public/assets/img/brand/` y se generan desde los originales:

```text
public/assets/img/brand/
├── logo-horizontal.png     900x206   topbar, sidebar expandido y panel
├── logo-vertical.png       269x178   pantalla de acceso, documentos e impresión
├── logo-icono.png          512x423   solo el símbolo (PNG con transparencia)
├── favicon.png              64x64
├── apple-icon.png          180x180
├── origen-cliente/         originales entregados (LOGO1.jpg, LOGO2.jpg, LOGO3.jpeg, logo.png, logo.jpeg, favicon.ico)
└── origen-tema/            logos originales de Dreams EMR (respaldo para revertir)
```

- **Generación**: `php scripts/generar-marca.php` — recorta el fondo claro de los JPG, escala y aplana sobre blanco; el símbolo conserva su transparencia.
- **Única fuente de verdad**: `config/urocenter.php` (clave `marca`) consumida por el componente Blade `<x-brand.logo variante="horizontal|vertical|icono" />`.
- **Cómo cambiar el logo**: reemplazar los archivos de `brand/` conservando los nombres, o colocar el nuevo original en `origen-cliente/` y volver a ejecutar el script.
- El logo entregado es un JPG con fondo claro, por lo que se presenta sobre una "píldora" blanca (`.brand-chip`) para que se vea correcto también con el tema oscuro.
- El panel *Theme Customizer* de demostración del tema está desactivado (sus imágenes de vista previa usaban rutas relativas).

Pendiente: reemplazar el logo cuando el cliente entregue una versión **SVG o PNG con transparencia** (permitiría prescindir de la píldora blanca).

### Entorno local (Laragon)

- **DocumentRoot**: los auto virtual hosts de Laragon apuntan a la raíz del proyecto, pero Laravel debe servirse desde `public/`.
  Editar `c:\laragon\etc\apache2\sites-enabled\auto.clinicaurocenter.test.conf` y dejar `DocumentRoot "C:/laragon/www/clinicaurocenter/public"`, luego **Reload** en Laragon.
- Alternativa sin tocar Apache: `php artisan serve --port=8123` (los assets usan el host de la petición, así que el tema carga igual).
- `php artisan storage:link` ya ejecutado (logos de empresa en `public/storage`).

---

## Próximos pasos

1. ~~Resolver P1, P2 y P3~~ (cerradas — ver "Decisiones cerradas").
2. ~~Instalar el kit~~ (completado — ver "Estado de la instalación").
3. Implementar los módulos ADMIN (Usuarios, Perfiles, Permisos, Menús) y el resto de Configuración (Parámetros, Terminales, Personal, Ubigeo).
4. Elaborar el **Doc 02 — Modelo de entidades** y luego el **Doc 03 — Modelo relacional MySQL 8**.
4. Generar las migraciones Laravel 12 a partir del doc 03.

---

## Hallazgos críticos registrados

| # | Hallazgo | Dónde se documenta |
|---|---|---|
| H1 | `database/migrations/perfiles/` de FlowStock **no está registrada** en `loadMigrationsFrom()` ⇒ un `migrate` limpio falla por las FK a `perfiles` | Doc B §16 D1 |
| H2 | Rutas `update`/`toggle` mal enlazadas en los CRUD geográficos (Pais, Departamento, Provincia, Distrito) y en Personal/Usuario | Doc B §4.3 y §16 D2 |
| H3 | `initDataTable()` duplicado en cada vista de FlowStock | Doc B §16 D3 |
| H4 | Superadmin hardcodeado por `id === 1` / `perfil_id == 1` | Doc B §10.7 M1 y §16 D8 |
| H5 | `MenuSeeder` usa `truncate` + `SET FOREIGN_KEY_CHECKS=0` (destructivo) | Doc B §16 D9 |
| H6 | El layout de Dreams EMR está **duplicado en 152 HTML** (sin partials) ⇒ conversión manual a Blade | Doc A §3.3 y §11 |
| H7 | Dreams EMR **no trae** POS, inventario/almacén, compras, caja ni CIE-10 | Doc A §10 |
| H8 | Las layouts apuntaban a los logos del tema (`img/logo.svg`, `img/favicon.png`); al reemplazarlos por la marca del cliente había **imágenes rotas** | Resuelto: recursos en `img/brand/`, componente `<x-brand.logo>` y pruebas que verifican su existencia |

---

*Última actualización: 2026-09-12*
