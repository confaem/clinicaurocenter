{{--
    Sidebar dinámico — estructura del tema Dreams EMR.
    Fuente de datos: session('usuario_menu_tree') construido por SessionDataService
    (solo incluye nodos con permiso 'acceso'). Soporta 3 niveles:
        nivel 1 -> <li class="submenu">            (o enlace simple)
        nivel 2 -> <li> dentro del <ul>
        nivel 3 -> <li class="submenu submenu-two">
--}}
@php
    $menuTree = session('usuario_menu_tree', []);
    $bloqueAnterior = null;

    // Devuelve la URL de una ruta solo si existe (evita excepciones con enlaces vacíos)
    $url = function ($enlace) {
        return ($enlace && \Illuminate\Support\Facades\Route::has($enlace))
            ? route($enlace)
            : 'javascript:void(0);';
    };
    $activo = function ($enlace) {
        return $enlace && \Illuminate\Support\Facades\Route::has($enlace) && request()->routeIs($enlace);
    };
@endphp

<div class="sidebar" id="sidebar">

    {{-- ===================== Logo ===================== --}}
    <div class="sidebar-logo">
        <div>
            <a href="{{ route('dashboard') }}" class="logo logo-normal">
                <img src="{{ asset('assets/img/logo.svg') }}" alt="{{ config('app.name') }}">
            </a>
            <a href="{{ route('dashboard') }}" class="logo-small">
                <img src="{{ asset('assets/img/logo-small.svg') }}" alt="{{ config('app.name') }}">
            </a>
            <a href="{{ route('dashboard') }}" class="dark-logo">
                <img src="{{ asset('assets/img/logo-dark.svg') }}" alt="{{ config('app.name') }}">
            </a>
        </div>
        <button class="sidenav-toggle-btn btn border-0 p-0 active" id="toggle_btn" type="button" aria-label="Contraer menú">
            <i class="ti ti-arrow-bar-to-left"></i>
        </button>
        <button class="sidebar-close" type="button" aria-label="Cerrar menú">
            <i class="ti ti-x align-middle"></i>
        </button>
    </div>

    {{-- ===================== Menú ===================== --}}
    <div class="sidebar-inner" data-simplebar>
        <div id="sidebar-menu" class="sidebar-menu">
            <ul role="menu" aria-label="Menú de navegación">

                @forelse($menuTree as $raiz)

                    {{-- Separador de bloque (PRINCIPAL / CLINICO / COMERCIAL / ADMIN / REPORTES) --}}
                    @if($raiz['bloque'] && $raiz['bloque'] !== $bloqueAnterior)
                        <li class="menu-title" aria-disabled="true"><span>{{ $raiz['bloque'] }}</span></li>
                        @php $bloqueAnterior = $raiz['bloque']; @endphp
                    @endif

                    @if(!empty($raiz['hijos']))

                        @php
                            $padreActivo = collect($raiz['hijos'])->contains(function ($hijo) {
                                if (!empty($hijo['hijos'])) {
                                    return collect($hijo['hijos'])->contains(fn ($nieto) => $nieto['enlace'] && request()->routeIs($nieto['enlace']));
                                }
                                return $hijo['enlace'] && request()->routeIs($hijo['enlace']);
                            });
                        @endphp

                        <li class="submenu {{ $padreActivo ? 'active' : '' }}">
                            <a href="javascript:void(0);">
                                <i class="{{ $raiz['icono'] ?: 'ti ti-circle' }}"></i>
                                <span>{{ $raiz['nombre'] }}</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                @foreach($raiz['hijos'] as $hijo)

                                    @if(!empty($hijo['hijos']))
                                        <li class="submenu submenu-two">
                                            <a href="javascript:void(0);">
                                                {{ $hijo['nombre'] }}
                                                <span class="menu-arrow inside-submenu"></span>
                                            </a>
                                            <ul>
                                                @foreach($hijo['hijos'] as $nieto)
                                                    <li>
                                                        <a href="{{ $url($nieto['enlace']) }}"
                                                           class="{{ $activo($nieto['enlace']) ? 'active' : '' }}">
                                                            {{ $nieto['nombre'] }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </li>
                                    @else
                                        <li>
                                            <a href="{{ $url($hijo['enlace']) }}"
                                               class="{{ $activo($hijo['enlace']) ? 'active' : '' }}">
                                                {{ $hijo['nombre'] }}
                                            </a>
                                        </li>
                                    @endif

                                @endforeach
                            </ul>
                        </li>

                    @else

                        <li>
                            <a href="{{ $url($raiz['enlace']) }}" class="{{ $activo($raiz['enlace']) ? 'active' : '' }}">
                                <i class="{{ $raiz['icono'] ?: 'ti ti-circle' }}"></i>
                                <span>{{ $raiz['nombre'] }}</span>
                            </a>
                        </li>

                    @endif

                @empty
                    <li class="menu-title" aria-disabled="true"><span>MENÚ</span></li>
                    <li>
                        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="ti ti-layout-board"></i><span>Dashboard</span>
                        </a>
                    </li>
                @endforelse

            </ul>
        </div>
    </div>
</div>
