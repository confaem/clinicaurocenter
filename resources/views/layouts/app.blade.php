<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'Panel') — {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width initial-scale=1.0">

    {{-- Token CSRF: obligatorio para las peticiones AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/apple-icon.png') }}">

    {{-- Theme config JS: debe cargarse ANTES del CSS para evitar el parpadeo de tema (FOUC) --}}
    <script src="{{ asset('assets/js/theme-script.js') }}"></script>

    {{-- CSS base del tema --}}
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/tabler-icons/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/simplebar/simplebar.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="app-style">

    {{-- Overrides del proyecto (nunca editar style.css del vendor) --}}
    <link rel="stylesheet" href="{{ asset('assets/css/urocenter.css') }}">

    @stack('styles')
</head>
<body>

<div class="main-wrapper">

    @include('components.layouts.topbar')
    @include('components.layouts.sidebar')

    <div class="page-wrapper">
        <div class="content container-fluid">

            {{-- Alertas de sesión --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ti ti-circle-check me-1"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ti ti-alert-triangle me-1"></i><strong>Alerta:</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            {{-- Encabezado de página + breadcrumb (opcional por vista) --}}
            @hasSection('breadcrumb')
                <div class="page-head d-flex align-items-start justify-content-between flex-wrap gap-2">
                    <div>
                        <h4 class="page-head-title">@yield('page-title')</h4>
                        <p class="page-head-subtitle">@yield('page-subtitle')</p>
                    </div>
                    <div class="text-end">
                        @yield('breadcrumb')
                    </div>
                </div>
            @endif

            @yield('content')

        </div>{{-- /.content --}}

        @include('components.layouts.footer')
    </div>{{-- /.page-wrapper --}}

</div>{{-- /.main-wrapper --}}

{{-- JS base --}}
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/plugins/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('assets/js/script.js') }}"></script>
<script src="{{ asset('assets/js/urocenter.js') }}"></script>

@stack('scripts')
</body>
</html>
