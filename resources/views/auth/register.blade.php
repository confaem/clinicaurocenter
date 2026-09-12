@extends('layouts.guest')

@section('title', 'Registro')

@section('content')
    <form method="POST" action="{{ route('register.store') }}" class="d-flex justify-content-center align-items-center" id="formRegister">
        @csrf

        <div class="d-flex flex-column justify-content-lg-center flex-fill">
            <div class="card border-1 p-lg-3 shadow-md rounded-3 m-0">
                <div class="card-body">

                    <div class="mb-4 text-center">
                        <a href="{{ url('/') }}">
                            <x-brand.logo variante="vertical" clase="brand-logo brand-logo-lg" :con-chip="false" />
                        </a>
                    </div>

                    <div class="mb-3">
                        <h5 class="mb-1 fw-bold">Crear cuenta</h5>
                        <p class="text-muted mb-0 fs-13">Registre un usuario para acceder al sistema.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger py-2 fs-13" role="alert">
                            <i class="ti ti-alert-triangle me-1"></i>{{ $errors->first() }}
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label" for="name">Nombre completo<span class="text-danger ms-1">*</span></label>
                        <div class="input-group input-group-flat">
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                   class="form-control border-end-0 @error('name') is-invalid @enderror" required>
                            <span class="input-group-text bg-white"><i class="ti ti-user fs-14 text-dark"></i></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="email">Correo electrónico<span class="text-danger ms-1">*</span></label>
                        <div class="input-group input-group-flat">
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                   class="form-control border-end-0 @error('email') is-invalid @enderror" required>
                            <span class="input-group-text bg-white"><i class="ti ti-mail fs-14 text-dark"></i></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Contraseña<span class="text-danger ms-1">*</span></label>
                        <div class="input-group input-group-flat pass-group">
                            <input type="password" id="password" name="password"
                                   class="form-control pass-input @error('password') is-invalid @enderror" required>
                            <span class="input-group-text toggle-password" role="button" aria-label="Mostrar contraseña">
                                <i class="ti ti-eye-off"></i>
                            </span>
                        </div>
                        <small class="text-muted">Mínimo 8 caracteres.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password_confirmation">Confirmar contraseña<span class="text-danger ms-1">*</span></label>
                        <div class="input-group input-group-flat pass-group">
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="form-control pass-input" required>
                            <span class="input-group-text toggle-password" role="button" aria-label="Mostrar contraseña">
                                <i class="ti ti-eye-off"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="submit" class="btn bg-primary text-white w-100" id="btnRegister">Registrarme</button>
                    </div>

                    <div class="text-center">
                        <h6 class="fw-normal fs-14 text-body mb-0">
                            ¿Ya tiene una cuenta?
                            <a href="{{ route('login') }}" class="ms-1 text-primary">Inicie sesión</a>
                        </h6>
                    </div>

                </div>{{-- /.card-body --}}
            </div>{{-- /.card --}}
        </div>
    </form>

    @push('scripts')
        <script>
            document.getElementById('formRegister').addEventListener('submit', function () {
                Urocenter.lockButton(document.getElementById('btnRegister'), 'Registrando...');
            });
        </script>
    @endpush
@endsection
