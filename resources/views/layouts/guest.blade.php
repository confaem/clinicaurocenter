<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'Acceso') — {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/apple-icon.png') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/tabler-icons/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/simplebar/simplebar.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="app-style">
    <link rel="stylesheet" href="{{ asset('assets/css/urocenter.css') }}">

    @stack('styles')
</head>
<body>

<div class="main-wrapper">
    <div class="container-fluid position-relative z-1">
        <div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100 bg-white">
            <div class="row">

                {{-- ============ Columna izquierda: formulario ============ --}}
                <div class="col-lg-6 col-md-12 col-sm-12">
                    <div class="row justify-content-center align-items-center overflow-auto flex-wrap vh-100">
                        <div class="col-md-8 mx-auto">
                            @yield('content')
                        </div>
                    </div>
                </div>

                {{-- ============ Columna derecha: ilustración del tema ============ --}}
                <div class="col-lg-6 p-0">
                    <div class="login-backgrounds login-covers bg-primary d-lg-flex align-items-center justify-content-center d-none flex-wrap position-relative h-100 z-0">
                        <div class="authentication-card">
                            <div class="authen-overlay-item w-100">
                                <div class="authen-head text-center">
                                    <h1 class="text-white fs-28 fw-bold mb-2">Gestión clínica integral</h1>
                                    <p class="text-light fw-normal text-light mb-0">
                                        {{ config('app.name') }} — historia clínica, citas, farmacia, caja y facturación en un solo sistema.
                                    </p>
                                </div>
                            </div>
                            <div class="auth-person">
                                <img src="{{ asset('assets/img/auth/auth-img-06.png') }}" alt="Personal de salud" class="img-fluid">
                            </div>
                        </div>
                        <img src="{{ asset('assets/img/auth/auth-img-01.png') }}" alt="" class="position-absolute top-0 start-0">
                        <img src="{{ asset('assets/img/auth/auth-img-02.png') }}" alt="" class="img-fluid position-absolute top-0 end-0">
                        <img src="{{ asset('assets/img/auth/auth-img-03.png') }}" alt="" class="img-fluid position-absolute auth-img-01">
                        <img src="{{ asset('assets/img/auth/auth-img-04.png') }}" alt="" class="img-fluid position-absolute auth-img-02">
                        <img src="{{ asset('assets/img/auth/auth-img-05.png') }}" alt="" class="img-fluid position-absolute bottom-0">
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/plugins/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/js/script.js') }}"></script>
<script src="{{ asset('assets/js/urocenter.js') }}"></script>

{{-- Mostrar / ocultar contraseña (patrón del tema: .pass-group / .toggle-password) --}}
<script>
    document.querySelectorAll('.toggle-password').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            const group = toggle.closest('.pass-group');
            if (!group) return;
            const input = group.querySelector('.pass-input');
            const icon = toggle.querySelector('i');
            const visible = input.getAttribute('type') === 'text';
            input.setAttribute('type', visible ? 'password' : 'text');
            if (icon) icon.className = visible ? 'ti ti-eye-off' : 'ti ti-eye';
        });
    });
</script>

@stack('scripts')
</body>
</html>
