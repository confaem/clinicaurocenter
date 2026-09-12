<?php

namespace App\Http\Middleware;

use App\Services\SessionDataService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refresca el contexto RBAC en cada petición autenticada.
 *
 * Registrado como `append` del grupo `web` en bootstrap/app.php.
 * Gracias a la "versión RBAC" de SessionDataService, el refresco no ejecuta
 * consultas a menos que los permisos hayan cambiado (ver §9.5 del análisis).
 */
class HandleSessionData
{
    public function __construct(protected SessionDataService $sessionService) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Usuario desactivado: se cierra la sesión de inmediato
            if (! $user->estado) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Su usuario fue desactivado. Contacte al administrador.');
            }

            $this->sessionService->cargarDatosEnSesion($user);
        }

        return $next($request);
    }
}
