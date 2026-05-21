<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('locale');

        if (! is_string($locale) || $locale === '') {
            $locale = $request->hasSession()
                ? $request->session()->get('locale', config('app.locale'))
                : config('app.locale');
        }

        if (! in_array($locale, ['en', 'th'], true)) {
            $locale = config('app.fallback_locale', 'en');
        }

        app()->setLocale($locale);

        if ($request->hasSession() && $request->query->has('locale')) {
            $request->session()->put('locale', $locale);
        }

        return $next($request);
    }
}
