@extends('layouts.guest')

@section('title', 'Iniciar sesión')

@section('content')
    <form method="POST" action="{{ route('login.attempt') }}" class="d-flex justify-content-center align-items-center" id="formLogin">
        @csrf

        <div class="d-flex flex-column justify-content-lg-center flex-fill">
            <div class="card border-1 p-lg-3 shadow-md rounded-3 m-0">
                <div class="card-body">

                    <div class="mb-4">
                        <a href="{{ url('/') }}">
                            <img src="{{ asset('assets/img/logo-dark.svg') }}" class="img-fluid logo m-atuo" alt="{{ config('app.name') }}">
                        </a>
                    </div>

                    <div class="mb-3">
                        <h5 class="mb-1 fw-bold">Bienvenido</h5>
                        <p class="text-muted mb-0 fs-13">Ingrese sus credenciales para acceder al sistema.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger py-2 fs-13" role="alert">
                            <i class="ti ti-alert-triangle me-1"></i>{{ $errors->first() }}
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label" for="email">Correo electrónico<span class="text-danger ms-1">*</span></label>
                        <div class="input-group input-group-flat">
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                   class="form-control border-end-0 @error('email') is-invalid @enderror"
                                   autocomplete="username" autofocus required>
                            <span class="input-group-text bg-white"><i class="ti ti-mail fs-14 text-dark"></i></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Contraseña<span class="text-danger ms-1">*</span></label>
                        <div class="input-group input-group-flat pass-group">
                            <input type="password" id="password" name="password"
                                   class="form-control pass-input @error('password') is-invalid @enderror"
                                   autocomplete="current-password" required>
                            <span class="input-group-text toggle-password" role="button" aria-label="Mostrar contraseña">
                                <i class="ti ti-eye-off"></i>
                            </span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="form-check form-check-md mb-0">
                            <input class="form-check-input" id="remember_me" type="checkbox" name="remember" value="1">
                            <label for="remember_me" class="form-check-label mt-0 text-body">Recordarme</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="submit" class="btn bg-primary text-white w-100" id="btnLogin">Ingresar</button>
                    </div>

                    <div class="text-center">
                        <h6 class="fw-normal fs-14 text-body mb-0">
                            ¿No tiene una cuenta?
                            <a href="{{ route('register') }}" class="ms-1 text-primary">Regístrese</a>
                        </h6>
                    </div>

                </div>{{-- /.card-body --}}
            </div>{{-- /.card --}}
        </div>
    </form>

    @push('scripts')
        <script>
            // Anti doble submit (patrón del tema)
            document.getElementById('formLogin').addEventListener('submit', function () {
                Urocenter.lockButton(document.getElementById('btnLogin'), 'Ingresando...');
            });
        </script>
    @endpush
@endsection
