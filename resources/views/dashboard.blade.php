@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Resumen general de la clínica')

@section('breadcrumb')
    {{ Breadcrumbs::render('dashboard') }}
@endsection

@section('content')
    <div class="row g-3">

        @php
            $indicadores = [
                ['titulo' => 'Pacientes registrados', 'valor' => '0', 'icono' => 'ti ti-users', 'color' => 'primary'],
                ['titulo' => 'Citas de hoy', 'valor' => '0', 'icono' => 'ti ti-calendar-time', 'color' => 'success'],
                ['titulo' => 'Atenciones del día', 'valor' => '0', 'icono' => 'ti ti-stethoscope', 'color' => 'info'],
                ['titulo' => 'Ventas del día', 'valor' => 'S/ 0.00', 'icono' => 'ti ti-cash', 'color' => 'warning'],
            ];
        @endphp

        @foreach ($indicadores as $indicador)
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 fs-13 text-muted">{{ $indicador['titulo'] }}</p>
                                <h4 class="mb-0 fw-semibold">{{ $indicador['valor'] }}</h4>
                            </div>
                            <span class="avatar avatar-md rounded bg-{{ $indicador['color'] }}-subtle text-{{ $indicador['color'] }}">
                                <i class="{{ $indicador['icono'] }} fs-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Estado del proyecto</h5>
                    <span class="badge badge-soft-info">Base instalada</span>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Base técnica de <strong>{{ config('app.name') }}</strong> en Laravel {{ app()->version() }}.
                        Los indicadores se conectarán cuando se implementen los módulos Clínico y Comercial.
                    </p>

                    <ul class="mb-0 fs-14">
                        <li>Autenticación propia con rate limiting y sesiones en base de datos.</li>
                        <li>RBAC dinámico: perfiles, permisos por menú y menú lateral desde base de datos.</li>
                        <li>Plantilla visual <strong>Dreams EMR</strong> integrada en <code>public/assets</code>.</li>
                        <li>Maestras iniciales y Configuración (Empresa, Parámetros, Terminales, Ubigeo, Personal).</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
