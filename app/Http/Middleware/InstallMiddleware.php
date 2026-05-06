<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InstallMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Redirects to /install if the app is not yet installed.
     * Blocks access to /install if the app is already installed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $installed = file_exists(storage_path('installed'));
        $isInstallerRoute = str_starts_with($request->path(), 'install');

        // App NOT installed → force all non-installer routes to /install
        if (! $installed && ! $isInstallerRoute) {
            return redirect('/install');
        }

        // App IS installed → block installer routes
        if ($installed && $isInstallerRoute) {
            abort(404);
        }

        return $next($request);
    }
}
