<?php

namespace App\Http\Middleware;

use App\Support\Installer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Kurulum tamamlanmadıysa uygulama sayfalarına erişimi engeller; /install ve /up hariç.
 */
class EnsureApplicationInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('production')) {
            return $next($request);
        }

        if (Installer::isComplete()) {
            return $next($request);
        }

        if ($request->is('install') || $request->is('install/*')) {
            return $next($request);
        }

        if ($request->is('up')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Kurulum henüz tamamlanmadı. Tarayıcıdan /install adresini açın.',
            ], 503);
        }

        return redirect('/install');
    }
}
