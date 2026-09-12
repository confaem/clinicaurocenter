{{--
    Topbar — estructura del tema Dreams EMR (@see dreamsemr/html/index.html)
    Datos dinámicos: usuario de sesión (SessionDataService).
--}}
<header class="navbar-header">
    <div class="page-container topbar-menu">

        {{-- ============ Zona izquierda: botón móvil + logo + toggle ============ --}}
        <div class="d-flex align-items-center gap-2">
            <a id="mobile_btn" class="mobile-btn" href="#sidebar" aria-label="Abrir menú">
                <i class="ti ti-menu-deep fs-24"></i>
            </a>

            <a href="{{ route('dashboard') }}" class="logo">
                <span class="logo-light">
                    <span class="logo-lg"><img src="{{ asset('assets/img/logo.svg') }}" alt="{{ config('app.name') }}"></span>
                </span>
                <span class="logo-dark">
                    <span class="logo-lg"><img src="{{ asset('assets/img/logo-dark.svg') }}" alt="{{ config('app.name') }}"></span>
                </span>
                <span class="logo-small">
                    <span class="logo-lg"><img src="{{ asset('assets/img/logo-small.svg') }}" alt="{{ config('app.name') }}"></span>
                </span>
            </a>

            <button class="sidenav-toggle-btn btn border-0 p-0 active" id="toggle_btn2" type="button" aria-label="Contraer menú">
                <i class="ti ti-arrow-bar-to-right"></i>
            </button>

            {{-- Buscador global (pendiente de implementar) --}}
            <div class="me-auto d-flex align-items-center header-search d-lg-flex d-none">
                <div class="input-icon position-relative me-2">
                    <input type="search" class="form-control" placeholder="Buscar paciente, documento..." disabled>
                    <span class="input-icon-addon d-inline-flex p-0 header-search-icon"><i class="ti ti-search"></i></span>
                </div>
            </div>
        </div>

        {{-- ============ Zona derecha: acciones ============ --}}
        <div class="d-flex align-items-center">

            {{-- Pantalla completa --}}
            <div class="header-item">
                <div class="dropdown me-2">
                    <a href="javascript:void(0);" class="btn topbar-link btnFullscreen" aria-label="Pantalla completa">
                        <i class="ti ti-maximize"></i>
                    </a>
                </div>
            </div>

            {{-- Notificaciones --}}
            <div class="header-item">
                <div class="dropdown me-2">
                    <button class="topbar-link btn dropdown-toggle drop-arrow-none" data-bs-toggle="dropdown" data-bs-offset="0,24" type="button" aria-label="Notificaciones">
                        <i class="ti ti-bell-check fs-16"></i>
                    </button>
                    <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg">
                        <div class="p-2 border-bottom">
                            <h6 class="m-0 fs-16 fw-semibold">Notificaciones</h6>
                        </div>
                        <div class="text-center text-muted py-4 fs-13">Sin notificaciones por ahora</div>
                    </div>
                </div>
            </div>

            {{-- Modo claro / oscuro --}}
            <div class="header-item d-flex me-2">
                <button class="topbar-link btn" id="light-dark-mode" type="button" aria-label="Cambiar tema">
                    <i class="ti ti-moon fs-16"></i>
                </button>
            </div>

            {{-- Menú de usuario --}}
            <div class="dropdown profile-dropdown d-flex align-items-center justify-content-center">
                <a href="javascript:void(0);" class="topbar-link dropdown-toggle drop-arrow-none position-relative"
                   data-bs-toggle="dropdown" data-bs-offset="0,22" aria-label="Menú de usuario">
                    <img src="{{ asset('assets/img/avatars/avatar-31.jpg') }}" width="32" class="rounded-2 d-flex" alt="Usuario">
                    <span class="online text-success"><i class="ti ti-circle-filled d-flex bg-white rounded-circle border border-1 border-white"></i></span>
                </a>

                <div class="dropdown-menu dropdown-menu-end dropdown-menu-md p-2">
                    <div class="d-flex align-items-center bg-light rounded p-2 mb-2">
                        <img src="{{ asset('assets/img/avatars/avatar-31.jpg') }}" class="rounded-circle" width="42" height="42" alt="Usuario">
                        <div class="ms-2">
                            <p class="fw-medium text-dark mb-0">
                                {{ session('usuario_display_name', auth()->user()->name ?? 'Usuario') }}
                            </p>
                            <span class="d-block fs-13">{{ session('usuario_perfil_nombre', 'Sin Perfil') }}</span>
                        </div>
                    </div>

                    <a href="javascript:void(0);" class="dropdown-item">
                        <i class="ti ti-user-circle me-1 align-middle"></i>
                        <span class="align-middle">Mi perfil</span>
                    </a>
                    <a href="javascript:void(0);" class="dropdown-item">
                        <i class="ti ti-settings me-1 align-middle"></i>
                        <span class="align-middle">Configuración</span>
                    </a>

                    <div class="pt-2 mt-2 border-top">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="ti ti-logout me-1 fs-17 align-middle"></i>
                                <span class="align-middle">Cerrar sesión</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</header>
