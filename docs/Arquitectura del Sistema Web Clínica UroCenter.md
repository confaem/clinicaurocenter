# Sistema Web Clínica UroCenter

## 1. Documento de Arquitectura Técnica

**Proyecto:** UroCenter  
**Tipo:** Sistema Web de Gestión Clínica  
**Versión del documento:** 1.0  
**Fecha:** 07/09/2026  
**Estado:** Diseño inicial  

---

# 2. Objetivo del Sistema

UroCenter será un sistema web integral para la gestión de las operaciones clínicas, comerciales y administrativas de una clínica.

El sistema deberá integrar el flujo completo desde la gestión del paciente y su atención médica hasta los procesos comerciales asociados, incluyendo ventas, caja, farmacia, inventario y facturación electrónica.

La arquitectura debe permitir que los módulos clínicos, comerciales y administrativos trabajen de manera integrada, evitando duplicidad de información y manteniendo una separación clara de responsabilidades.

---

# 3. Arquitectura Funcional

El sistema se organizará en tres grandes áreas funcionales:

```text
                              UROCENTER
                                  │
        ┌─────────────────────────┼─────────────────────────┐
        │                         │                         │
      CLÍNICO                 COMERCIAL                    ADMIN
        │                         │                         │
        ├── Pacientes             ├── POS                  ├── Usuarios
        ├── Admisión              │    ├── Consulta        ├── Roles
        ├── Citas                 │    ├── Procedimientos   ├── Permisos
        ├── Consulta              │    └── Farmacia        ├── Menús
        ├── Tópico                ├── Ventas               └── Configuración
        ├── Procedimientos        ├── Facturación
        ├── Historia Clínica      ├── Caja
        ├── Médicos               ├── Farmacia
        ├── Personal              └── Inventario
        ├── Especialidades
        ├── Consultorios
        ├── CIE-10
        └── Controles
                    │
                    ▼
                 REPORTES
```

---

# 4. Stack Tecnológico

La implementación utilizará las siguientes tecnologías:

| Tecnología | Versión / Definición |
|---|---|
| Framework | Laravel 12 |
| Lenguaje | PHP |
| Frontend | Blade |
| UI | Bootstrap 5 |
| JavaScript | JavaScript nativo (ES6+), sin jQuery |
| Build Tool | Vite |
| Base de datos | MySQL 8 |
| Autenticación | Autenticación propia (AuthController) con RBAC dinámico |
| ORM | Eloquent |
| Control de versiones | Git |
| Editor | Visual Studio Code |

## 4.1 Restricción importante

El proyecto **NO utilizará Livewire**.

Tampoco utilizará **jQuery**.

La interacción dinámica del sistema se desarrollará mediante:

- Blade
- Bootstrap 5
- JavaScript nativo (ES6+)
- Peticiones `fetch` / AJAX cuando sea necesario

---

# 5. Plantilla Base

El sistema utilizará **Dreams EMR** como plantilla visual y base de interfaz para el sistema clínico.

Dreams EMR será utilizado principalmente para:

- Layout general
- Menú lateral
- Navbar
- Dashboard
- Componentes visuales
- Formularios
- Tablas
- Modales
- Calendarios
- Componentes clínicos
- Componentes administrativos
- Componentes de facturación disponibles en la plantilla

La plantilla no debe considerarse como el sistema funcional final.

La lógica de negocio de UroCenter será desarrollada específicamente para las necesidades de la clínica.

---

# 6. Principios de Arquitectura

El desarrollo deberá respetar los siguientes principios:

## 6.1 Separación de responsabilidades

Cada módulo tendrá responsabilidades claramente definidas.

```text
CLÍNICO
    Gestión de atención médica

COMERCIAL
    Gestión económica y operativa

ADMIN
    Gestión y seguridad del sistema
```

## 6.2 No duplicidad de información

Una entidad debe existir una sola vez y ser reutilizada por los diferentes módulos.

Ejemplo:

```text
PACIENTE
   │
   ├── Citas
   ├── Admisiones
   ├── Consultas
   ├── Procedimientos
   └── Ventas
```

No se crearán tablas independientes como:

```text
pacientes_consulta
pacientes_farmacia
pacientes_procedimientos
```

salvo que exista una razón funcional específica.

---

# 7. Arquitectura Clínica

El módulo clínico estará compuesto por:

```text
CLÍNICO
│
├── Pacientes
├── Historia Clínica
├── Admisión
├── Citas
├── Consulta Externa
├── Tópico
├── Procedimientos
├── Médicos
├── Personal
├── Especialidades
├── Consultorios
├── CIE-10
└── Controles Médicos
```

---

# 8. Entidad Paciente

El paciente será una de las entidades centrales del sistema.

Un paciente podrá tener:

- Una historia clínica
- Múltiples admisiones
- Múltiples citas
- Múltiples consultas
- Múltiples atenciones de tópico
- Múltiples procedimientos
- Múltiples recetas
- Múltiples ventas asociadas

Conceptualmente:

```text
PACIENTE
   │
   ├── HISTORIA CLÍNICA
   ├── ADMISIONES
   ├── CITAS
   ├── CONSULTAS
   ├── TÓPICO
   ├── PROCEDIMIENTOS
   ├── RECETAS
   └── VENTAS
```

---

# 9. Historia Clínica

Cada paciente tendrá una historia clínica.

Relación inicial:

```text
PACIENTE 1 ───────── 1 HISTORIA_CLINICA
```

La historia clínica permitirá centralizar el historial médico del paciente.

Las atenciones clínicas estarán relacionadas con ella.

---

# 10. Admisión

La admisión representa el ingreso administrativo del paciente a la clínica.

Flujo conceptual:

```text
PACIENTE
   │
   ▼
ADMISIÓN
   │
   ├── CONSULTA
   ├── TÓPICO
   └── PROCEDIMIENTO
```

La admisión no debe confundirse con la consulta médica.

---

# 11. Citas

La cita representa el agendamiento de una atención.

Una cita estará relacionada con:

- Paciente
- Médico
- Especialidad
- Consultorio
- Fecha
- Hora
- Estado
- Información de pago

Conceptualmente:

```text
PACIENTE
    │
    ▼
   CITA
    │
    ├── MÉDICO
    ├── ESPECIALIDAD
    └── CONSULTORIO
```

---

# 12. Flujo de Cita y Consulta Externa

Este será uno de los flujos principales del sistema.

```text
PACIENTE
    │
    ▼
CREAR CITA
    │
    ▼
PAGO DE CITA
    │
    ▼
CITA PAGADA
    │
    ▼
CREAR CONSULTA EXTERNA
    │
    ▼
ESTADO = PENDIENTE
    │
    ▼
MÉDICO VISUALIZA CONSULTA
    │
    ▼
INICIA ATENCIÓN
    │
    ▼
REGISTRA INFORMACIÓN CLÍNICA
    │
    ▼
FINALIZA CONSULTA
    │
    ▼
ESTADO = ATENDIDA
```

Una consulta no debe generarse como atendida automáticamente.

Al generarse desde una cita pagada, su estado inicial será:

```text
PENDIENTE
```

---

# 13. Consulta Externa

La consulta externa será la entidad principal para registrar la atención médica.

Contendrá inicialmente:

- Paciente
- Médico
- Especialidad
- Consultorio
- Cita relacionada
- Historia clínica
- Estado
- Anamnesis
- Evaluación clínica
- Tratamiento / Receta
- Observaciones

## 13.1 Información clínica

```text
ANAMNESIS
    Tipo: Texto

EVALUACIÓN CLÍNICA
    Tipo: Texto

TRATAMIENTO / RECETA
    Tipo: Texto

OBSERVACIONES
    Tipo: Texto
```

---

# 14. Estado de la Consulta

La consulta tendrá estados controlados.

Estado inicial:

```text
PENDIENTE
```

Flujo recomendado:

```text
PENDIENTE
    │
    ▼
EN_ATENCION
    │
    ▼
ATENDIDA
```

También podrá existir:

```text
CANCELADA
```

Por lo tanto:

```text
PENDIENTE
EN_ATENCION
ATENDIDA
CANCELADA
```

---

# 15. Diagnósticos CIE-10

Los diagnósticos no se almacenarán como texto dentro de la consulta.

Se utilizará un maestro de códigos CIE-10.

Arquitectura:

```text
CIE10
   │
   ▼
CONSULTA_DIAGNOSTICO
   │
   ▼
CONSULTA_EXTERNA
```

Una consulta podrá tener múltiples diagnósticos.

Ejemplo:

```text
Consulta #000125

N40.0    Hiperplasia de próstata       P
R35.0    Polaquiuria                    D
N39.0    Infección urinaria             R
```

El detalle del diagnóstico tendrá:

```text
CIE-10
TIPO
OBSERVACIONES
```

El tipo será inicialmente:

```text
P
D
R
```

> La interpretación funcional exacta de P, D y R deberá confirmarse antes de implementar la tabla definitiva.

---

# 16. Control Médico

Una consulta podrá generar uno o varios controles.

No se recomienda limitar el modelo a un único campo `fecha_control`.

Se utilizará una entidad independiente:

```text
CONTROL_MEDICO
```

Relación:

```text
CONSULTA_EXTERNA 1 ───────── N CONTROL_MEDICO
```

Ejemplo:

```text
CONSULTA #125
    │
    ├── CONTROL 01 → 30/09/2026
    ├── CONTROL 02 → 30/10/2026
    └── CONTROL 03 → 30/11/2026
```

---

# 17. Tópico

Tópico será una atención clínica independiente de Consulta Externa.

```text
PACIENTE
   │
   ▼
ADMISIÓN
   │
   ▼
TÓPICO
```

Inicialmente podrá contemplar:

- Motivo de atención
- Signos vitales
- Evaluación
- Diagnóstico
- Tratamiento
- Observaciones

La estructura detallada de tópico se definirá posteriormente.

---

# 18. Procedimientos

Se distinguirá entre:

## Maestro de procedimiento

Representa los servicios que ofrece la clínica.

```text
PROCEDIMIENTO
```

Ejemplos:

```text
Ecografía
Cistoscopía
Biopsia
Tratamiento
Procedimiento urológico
```

## Procedimiento realizado

Representa la ejecución de un procedimiento sobre un paciente.

```text
PROCEDIMIENTO_ATENCION
```

Conceptualmente:

```text
PROCEDIMIENTO
      │
      ▼
PROCEDIMIENTO_ATENCION
      │
      ├── PACIENTE
      ├── MÉDICO
      └── ATENCIÓN
```

---

# 19. Personal y Médicos

Se evitará duplicar información de personas.

La estructura conceptual será:

```text
PERSONAL
    │
    ├── MÉDICO
    ├── ENFERMERO
    ├── ADMINISTRATIVO
    └── OTROS
```

Los médicos tendrán información específica adicional, como:

- CMP
- Especialidades
- Información profesional

---

# 20. Especialidades

Existirá un maestro:

```text
ESPECIALIDAD
```

Un médico podrá tener una o varias especialidades.

Relación:

```text
MEDICO N ───────── N ESPECIALIDAD
```

Mediante:

```text
MEDICO_ESPECIALIDAD
```

---

# 21. Consultorios

Los consultorios estarán relacionados con las especialidades.

```text
ESPECIALIDAD
     │
     ├── CONSULTORIO 01
     ├── CONSULTORIO 02
     └── CONSULTORIO 03
```

Esto permitirá posteriormente controlar la agenda y disponibilidad de consultorios.

---

# 22. Arquitectura Comercial

El módulo comercial será:

```text
COMERCIAL
│
├── POS
├── Ventas
├── Facturación
├── Caja
├── Farmacia
└── Inventario
```

---

# 23. POS

UroCenter tendrá tres puntos de venta funcionales:

```text
POS
│
├── POS CONSULTA
├── POS PROCEDIMIENTOS
└── POS FARMACIA
```

Aunque visualmente estarán separados, internamente utilizarán un motor común de ventas.

No se crearán tres sistemas de ventas independientes.

---

# 24. Venta

Existirá una entidad central:

```text
VENTA
```

La venta tendrá un origen.

Valores iniciales:

```text
CONSULTA
PROCEDIMIENTO
FARMACIA
```

Conceptualmente:

```text
                    VENTA
                      │
          ┌───────────┼───────────┐
          │           │           │
       CONSULTA  PROCEDIMIENTO  FARMACIA
```

Esto permitirá separar las operaciones sin duplicar la lógica comercial.

---

# 25. Facturación

La facturación será transversal a los diferentes POS.

Flujo:

```text
POS
 │
 ▼
VENTA
 │
 ▼
COMPROBANTE
 │
 ├── BOLETA
 ├── FACTURA
 ├── NOTA DE CRÉDITO
 └── NOTA DE DÉBITO
```

La facturación deberá permitir posteriormente integración con SUNAT.

Flujo esperado:

```text
VENTA
  │
  ▼
COMPROBANTE
  │
  ▼
XML
  │
  ▼
FIRMA DIGITAL
  │
  ▼
SUNAT
  │
  ▼
CDR
  │
  ▼
PDF
  │
  ▼
IMPRESIÓN
```

El desarrollo de la integración SUNAT se realizará posteriormente.

---

# 26. Series de Comprobantes

Las series podrán separarse por origen.

Ejemplo:

```text
CONSULTA
    B001 / F001

PROCEDIMIENTOS
    B002 / F002

FARMACIA
    B003 / F003
```

Esto permitirá mantener separados los procesos comerciales sin crear tres motores de facturación.

Las series serán configurables.

---

# 27. Caja

La caja administrará los movimientos económicos.

Conceptualmente:

```text
CAJA
│
├── Apertura
├── Ingresos
├── Egresos
├── Ventas
├── Pagos
├── Arqueo
└── Cierre
```

Las ventas y pagos deberán poder relacionarse con la caja correspondiente.

---

# 28. Farmacia

Farmacia será un componente comercial especializado.

```text
FARMACIA
│
├── Medicamentos
├── Productos
├── Recetas
├── Dispensación
├── Compras
├── Lotes
├── Inventario
└── Kardex
```

La farmacia utilizará el inventario central del sistema.

---

# 29. Inventario

El inventario deberá trabajar con productos y lotes.

Conceptualmente:

```text
PRODUCTO
   │
   ├── Categoría
   ├── Unidad de medida
   ├── Laboratorio
   └── Presentación
          │
          ▼
         LOTE
          │
          ├── Fecha de vencimiento
          ├── Stock
          └── Costo
```

Los movimientos generarán información para el kardex.

```text
INVENTARIO
     │
     ▼
MOVIMIENTO_INVENTARIO
     │
     ▼
KARDEX
```

---

# 30. Compras

Las compras estarán relacionadas con proveedores.

```text
PROVEEDOR
    │
    ▼
COMPRA
    │
    ▼
DETALLE_COMPRA
    │
    ▼
PRODUCTO / LOTE
```

Una compra podrá generar automáticamente el ingreso correspondiente al inventario.

---

# 31. Distribución Médico / Clínica

Los procedimientos podrán tener una distribución económica entre médico y clínica.

Ejemplo:

```text
PROCEDIMIENTO
-------------------------
Precio: S/ 500.00

Médico:  70%
Clínica: 30%
```

Se recomienda manejar esta información mediante una entidad independiente:

```text
PROCEDIMIENTO_DISTRIBUCION
```

Esto permitirá modificar las reglas sin alterar directamente la definición del procedimiento.

Posteriormente podrá generarse:

```text
LIQUIDACION_MEDICO
```

para determinar los montos correspondientes a cada médico.

---

# 32. Administración

El módulo administrativo será:

```text
ADMIN
│
├── Usuarios
├── Roles
├── Permisos
├── Menús
└── Configuración
```

---

# 33. Usuarios

Los usuarios del sistema tendrán acceso mediante la **autenticación propia** del sistema (`AuthController` + middleware `auth`), integrada con el RBAC dinámico.

Conceptualmente:

```text
USUARIO
   │
   ▼
ROL
   │
   ▼
PERMISOS
```

---

# 34. Roles

Ejemplos iniciales:

```text
SUPER ADMINISTRADOR
ADMINISTRADOR
MÉDICO
RECEPCIÓN
CAJERO
FARMACIA
ALMACÉN
```

Los roles definitivos serán establecidos durante el diseño de seguridad.

---

# 35. Permisos

Los permisos deberán permitir controlar las operaciones de cada módulo.

Ejemplo:

```text
PACIENTES

Ver       ✓
Crear     ✓
Editar    ✓
Eliminar  ✗
```

Los permisos podrán manejar acciones como:

```text
VER
CREAR
EDITAR
ELIMINAR
```

y otras acciones específicas cuando sea necesario.

---

# 36. Menús

El sistema tendrá un menú jerárquico.

Ejemplo:

```text
CLÍNICO
│
├── Pacientes
├── Admisión
├── Citas
├── Consulta Externa
├── Tópico
└── Procedimientos

COMERCIAL
│
├── POS
│   ├── Consulta
│   ├── Procedimientos
│   └── Farmacia
├── Ventas
├── Facturación
├── Caja
├── Farmacia
└── Inventario

ADMIN
│
├── Usuarios
├── Roles
├── Permisos
└── Configuración
```

---

# 37. Configuración

La configuración tendrá dos tipos de información.

## Configuración estructural

Entidades propias del negocio:

```text
Empresa / Clínica
Sucursal
Especialidades
Consultorios
Almacenes
Series
Cajas
```

## Parámetros del sistema

Valores configurables:

```text
Moneda
IGV
Formatos de impresión
Configuraciones de facturación
Parámetros generales
```

No toda configuración debe almacenarse como un parámetro genérico.

---

# 38. Entidad Transversal: Atención

Se incorporará conceptualmente una entidad:

```text
ATENCION
```

Su objetivo es representar el acto de atención del paciente.

Podrá relacionarse con:

```text
ATENCION
│
├── CONSULTA EXTERNA
├── TÓPICO
└── PROCEDIMIENTO
```

La finalidad es evitar que todo el sistema dependa obligatoriamente de una cita.

Por ejemplo:

```text
Paciente sin cita
      │
      ▼
   Admisión
      │
      ▼
   Atención
      │
      └── Tópico
```

Mientras que:

```text
Paciente
   │
   ▼
Cita
   │
   ▼
Pago
   │
   ▼
Consulta Externa
```

---

# 39. Integración entre Clínico y Comercial

La arquitectura deberá mantener una clara separación entre la atención clínica y la operación comercial.

Ejemplo de consulta:

```text
PACIENTE
   │
   ▼
CITA
   │
   ▼
PAGO
   │
   ├──────────► CAJA
   │
   ▼
CONSULTA EXTERNA
   │
   ▼
ATENCIÓN MÉDICA
```

Para procedimientos:

```text
PACIENTE
   │
   ▼
PROCEDIMIENTO
   │
   ▼
VENTA
   │
   ├── CAJA
   └── COMPROBANTE
```

Para farmacia:

```text
RECETA
   │
   ▼
DISPENSACIÓN
   │
   ▼
VENTA FARMACIA
   │
   ├── INVENTARIO
   ├── CAJA
   └── COMPROBANTE
```

---

# 40. Arquitectura de Facturación Centralizada

Aunque existan diferentes puntos de venta, la facturación será centralizada.

```text
             ┌──────────────┐
             │ POS CONSULTA │
             └──────┬───────┘
                    │
             ┌──────▼───────┐
             │ POS PROC.    │
             └──────┬───────┘
                    │
             ┌──────▼───────┐
             │ POS FARMACIA │
             └──────┬───────┘
                    │
                    ▼
                 VENTA
                    │
                    ▼
              FACTURACIÓN
                    │
                    ▼
                  SUNAT
```

Esto permite:

- Un solo motor de facturación.
- Series independientes.
- Reportes por origen.
- Control centralizado.
- Menor duplicación de código.

---

# 41. Reportes

Los reportes serán un componente transversal.

```text
                         REPORTES
                            │
              ┌─────────────┴─────────────┐
              │                           │
           CLÍNICOS                   COMERCIALES
              │                           │
       ├── Pacientes                ├── Ventas
       ├── Citas                    ├── Compras
       ├── Consultas                ├── Caja
       ├── Diagnósticos             ├── Inventario
       ├── Tópico                   ├── Kardex
       ├── Procedimientos           ├── Farmacia
       └── Controles                └── Facturación
```

También existirán reportes administrativos y de producción médica.

---

# 42. Reportes Médicos

Se deberán contemplar:

- Atenciones por médico.
- Atenciones por especialidad.
- Consultas por período.
- Procedimientos realizados.
- Producción por médico.
- Diagnósticos CIE-10.
- Controles médicos.
- Liquidación médico / clínica.

---

# 43. Reportes Comerciales

Se deberán contemplar:

- Ventas por día.
- Ventas por período.
- Ventas por POS.
- Ventas de farmacia.
- Ventas de consultas.
- Ventas de procedimientos.
- Compras.
- Inventario.
- Kardex.
- Productos por vencer.
- Movimientos de caja.
- Comprobantes emitidos.
- Facturación por origen.

---

# 44. Modelo Funcional General

El modelo funcional inicial queda definido de la siguiente manera:

```text
                              UROCENTER
                                  │
        ┌─────────────────────────┼─────────────────────────┐
        │                         │                         │
      CLÍNICO                 COMERCIAL                    ADMIN
        │                         │                         │
        │                         │                         │
    Pacientes                     POS                    Usuarios
        │                         │                       Roles
    Admisión                      │                       Permisos
        │                    ┌────┼────┐                 Menús
      Citas                   │    │    │                Config.
        │                  Consulta Proc. Farma.
        ▼                       │    │    │
     ATENCIÓN                   └────┼────┘
        │                            │
   ┌────┼────┐                       ▼
   │    │    │                     VENTA
Consulta Tópico Proc.               │
   │                                ├── Caja
   │                                ├── Inventario
   │                                └── Comprobante
   │
   ├── Anamnesis
   ├── Evaluación Clínica
   ├── Tratamiento/Receta
   ├── Observaciones
   ├── Diagnósticos CIE-10
   └── Control
                  │
                  ▼
               REPORTES
```

---

# 45. Principales Entidades Identificadas

## Clínico

```text
Paciente
Historia Clínica
Admisión
Atención
Cita
Consulta Externa
Diagnóstico CIE-10
Diagnóstico de Consulta
Control Médico
Tópico
Procedimiento
Procedimiento Atención
Personal
Médico
Especialidad
Médico Especialidad
Consultorio
```

## Comercial

```text
Cliente
Producto
Categoría
Unidad de Medida
Laboratorio
Presentación
Proveedor
Almacén
Lote
Compra
Detalle Compra
Inventario
Movimiento Inventario
Kardex
Venta
Detalle Venta
Caja
Movimiento Caja
Comprobante
Detalle Comprobante
Serie
Pago
```

## Farmacia

```text
Medicamento
Principio Activo
Receta
Detalle Receta
Dispensación
```

## Administración

```text
Usuario
Rol
Permiso
Módulo
Menú
Configuración
Parámetro
```

## Distribución

```text
Procedimiento Distribución
Liquidación Médico
```

---

# 46. Reglas de Negocio Iniciales

Las siguientes reglas quedan establecidas para el diseño:

### RN-001
Una cita pertenece a un paciente.

### RN-002
Una cita puede estar asociada a un médico, especialidad y consultorio.

### RN-003
Una cita pagada genera una Consulta Externa.

### RN-004
La Consulta Externa generada inicia en estado `PENDIENTE`.

### RN-005
El médico solo deberá visualizar las consultas pendientes que le correspondan.

### RN-006
Una consulta podrá contener múltiples diagnósticos.

### RN-007
Cada diagnóstico de consulta deberá utilizar un código CIE-10.

### RN-008
Cada diagnóstico tendrá un tipo `P`, `D` o `R`.

### RN-009
Una consulta podrá generar uno o varios controles médicos.

### RN-010
Tópico será independiente de Consulta Externa.

### RN-011
Procedimiento será independiente de Consulta Externa, aunque podrá originarse desde una atención clínica.

### RN-012
Los POS de Consulta, Procedimientos y Farmacia utilizarán un motor central de ventas.

### RN-013
Cada venta tendrá un origen.

### RN-014
La facturación será centralizada.

### RN-015
Las series de comprobantes podrán configurarse por origen.

### RN-016
Las ventas de farmacia deberán afectar el inventario.

### RN-017
Las compras deberán generar movimientos de ingreso de inventario cuando corresponda.

### RN-018
Los productos que manejen vencimiento deberán trabajar con lotes.

### RN-019
Los procedimientos podrán tener una distribución porcentual entre médico y clínica.

### RN-020
Los reportes consumirán información de los módulos operativos.

---

# 47. Estado del Diseño

Este documento representa la **Arquitectura Funcional Inicial 1.0** de UroCenter.

Todavía NO se han definido:

- Campos definitivos de las tablas.
- Tipos de datos.
- Llaves primarias.
- Llaves foráneas.
- Índices.
- Constraints.
- Catálogos definitivos.
- Estados definitivos.
- Modelo físico MySQL.
- Migraciones Laravel.
- Controllers.
- Models.
- Services.
- Repositories.
- Policies.
- Rutas.
- Interfaces Blade.

Estos elementos serán definidos en las siguientes etapas.

---

# 48. Orden de Desarrollo

El proyecto deberá avanzar en el siguiente orden:

```text
FASE 01
Arquitectura funcional
        │
        ▼
FASE 02
Entidades y relaciones
        │
        ▼
FASE 03
Modelo lógico de base de datos
        │
        ▼
FASE 04
Modelo físico MySQL 8
        │
        ▼
FASE 05
Migraciones Laravel 12
        │
        ▼
FASE 06
Models y relaciones Eloquent
        │
        ▼
FASE 07
Autenticación / Roles / Permisos
        │
        ▼
FASE 08
Módulo Clínico
        │
        ▼
FASE 09
Módulo Comercial
        │
        ▼
FASE 10
Facturación electrónica
        │
        ▼
FASE 11
Farmacia / Inventario
        │
        ▼
FASE 12
Reportes
        │
        ▼
FASE 13
Pruebas e integración
```

---

# 49. Regla para Desarrollo con IA

Las herramientas de IA utilizadas durante el desarrollo, incluyendo DeepSeek, deberán respetar este documento como referencia arquitectónica.

Antes de generar código que modifique la estructura del sistema, se deberá verificar:

1. Si la entidad ya existe.
2. Si la relación ya fue definida.
3. Si existe una regla de negocio asociada.
4. Si la modificación afecta otros módulos.
5. Si genera duplicidad de información.
6. Si modifica el modelo de base de datos.
7. Si rompe la integración entre módulos.

No se deberá crear código que contradiga esta arquitectura sin actualizar previamente el documento técnico.

---

# 50. Próximo Documento

El siguiente documento será:

```text
02-modelo-entidades-urocenter.md
```

En él se definirá detalladamente:

```text
Entidad
   │
   ├── Descripción
   ├── Propósito
   ├── Atributos
   ├── Relaciones
   ├── Cardinalidad
   ├── Reglas de negocio
   └── Dependencias
```

Posteriormente se construirá:

```text
03-modelo-relacional-mysql.md
```

y finalmente las migraciones Laravel 12.

---

## Fin del documento