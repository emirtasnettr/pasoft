<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sistem yöneticisi rolü yalnızca /admin paneline erişebilir; CRM modüllerine erişemez.
 */
class EnsureCrmAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user !== null && $user->hasRole('system_admin')) {
            return redirect()->route('admin.settings.edit');
        }

        return $next($request);
    }
}
