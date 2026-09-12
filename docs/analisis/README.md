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

---

## Decisiones pendientes

| # | Decisión | Opciones | Impacto |
|---|---|---|---|
| P1 | **Librería de tablas** | A) `datatable-vanilla.js` de Dreams EMR (recomendada) · B) `vanilla-datatables` de FlowStock · C) `simple-datatables` | Solo el renderizado; el backend (`getData` → `{data, can}`) no cambia |
| P2 | **Iconos** | A) Tabler `<i class="ti ti-*">` (recomendada) · B) mantener `iconify` (`tabler:*`, `solar:*`) | Define el valor guardado en `menu.icono` — decidir **antes** de sembrar el menú |
| P3 | **Consistencia del doc 01** | Actualizar §4 y §4.1: `Laravel Jetstream` → auth custom; `JavaScript / jQuery` → JS nativo | Evita que la arquitectura se contradiga (regla del doc 01 §49) |

---

## Próximos pasos

1. Resolver P1, P2 y P3.
2. Instalar el kit (§15 de [Doc B](analisis-arquitectura-flowstock.md#15-kit-de-inicio-propuesto-preludio-de-la-instalación)).
3. Elaborar el **Doc 02 — Modelo de entidades** y luego el **Doc 03 — Modelo relacional MySQL 8**.
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

---

*Última actualización: 2026-09-12*
