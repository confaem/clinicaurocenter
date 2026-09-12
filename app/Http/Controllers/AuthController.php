<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SessionDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Autenticación propia de Clínica UroCenter.
 *
 * Replicada de FlowStock (docs/analisis/analisis-arquitectura-flowstock.md §2.5 y §10):
 *  - Rate limiting (5 intentos por email|IP)
 *  - Protección contra session fixation (session()->regenerate())
 *  - Logout seguro (invalidate + regenerateToken)
 *  - Carga del contexto RBAC en sesión tras autenticar
 *
 * No se usa Jetstream, Breeze ni Livewire (doc 01 §4 y §4.1).
 */
class AuthController extends Controller
{
    public function __construct(protected SessionDataService $sessionService) {}

    /**
     * Formulario de inicio de sesión.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Procesa el inicio de sesión.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $this->ensureIsNotRateLimited($request);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        // Protección contra session fixation
        $request->session()->regenerate();

        // Contexto RBAC: perfiles, permisos y árbol de menús
        $this->sessionService->cargarDatosEnSesion(Auth::user());

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Bienvenido a ' . config('app.name'));
    }

    /**
     * Formulario de registro.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Registro de un usuario nuevo.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'estado'   => true,
        ]);

        Auth::login($user);

        // Regeneramos la sesión tras un cambio de privilegio
        $request->session()->regenerate();

        $this->sessionService->cargarDatosEnSesion($user);

        return redirect(route('dashboard'));
    }

    /**
     * Cierre de sesión seguro.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('login'));
    }

    /**
     * Bloquea la petición si se superaron los intentos permitidos.
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        Log::warning('Login bloqueado por intentos fallidos', [
            'email' => $request->input('email'),
            'ip'    => $request->ip(),
        ]);

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Clave de throttling: email normalizado + IP.
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower((string) $request->input('email')) . '|' . $request->ip());
    }
}
