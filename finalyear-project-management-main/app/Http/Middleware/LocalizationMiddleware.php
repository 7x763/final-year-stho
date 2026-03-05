<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sessionLocale = session()->get('locale');
        $cookieLocale = $request->cookie('filament_language_switch_locale');
        $defaultLocale = config('app.locale', 'vi');

        $locale = $sessionLocale ?? $cookieLocale ?? $defaultLocale;

        if (! in_array($locale, ['en', 'vi'])) {
            $locale = 'vi';
        }

        \Illuminate\Support\Facades\App::setLocale($locale);
        config(['app.locale' => $locale]);
        
        \Illuminate\Support\Facades\Log::info("LocalizationMiddleware: Source detect - Session: [{$sessionLocale}], Cookie: [{$cookieLocale}], Default: [{$defaultLocale}]. Final: [{$locale}]");

        return $next($request);
    }
}
