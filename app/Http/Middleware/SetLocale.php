<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const LOCALES = ['en', 'bn', 'ar', 'hi', 'ur'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->locale
            ?? session('locale')
            ?? config('app.locale', 'en');

        if (! in_array($locale, self::LOCALES, true)) {
            $locale = 'en';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
