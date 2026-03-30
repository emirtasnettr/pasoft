<?php

namespace App\Http\Middleware;

use App\Support\Installer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Kurulum bittikten sonra sihirbaza tekrar erişimi kapatır.
 */
class BlockCompletedInstallWizard
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('production') && Installer::isComplete()) {
            abort(404);
        }

        return $next($request);
    }
}
