<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userRole = Auth::user()->role;

        if (!in_array($userRole, $roles)) {
            // Redirect ke halaman default sesuai role, bukan abort 403
            if ($userRole === 'admin') {
                return redirect()->route('reports.index')
                    ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }

            if ($userRole === 'cashier') {
                return redirect()->route('pos.index')
                    ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }

            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}