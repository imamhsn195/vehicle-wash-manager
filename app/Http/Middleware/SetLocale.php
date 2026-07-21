<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const LOCALES = ['en', 'bn', 'ar', 'hi', 'ur'];

    public const LABELS = [
        'en' => 'English',
        'bn' => 'বাংলা',
        'ar' => 'العربية',
        'hi' => 'हिन्दी',
        'ur' => 'اردو',
    ];

    public const NATIVE_HINTS = [
        'en' => 'English',
        'bn' => 'Bangla',
        'ar' => 'Arabic',
        'hi' => 'Hindi',
        'ur' => 'Urdu',
    ];

    public static function options(): array
    {
        return self::LABELS;
    }

    public static function label(string $locale): string
    {
        return self::LABELS[$locale] ?? $locale;
    }

    public static function hint(string $locale): string
    {
        return self::NATIVE_HINTS[$locale] ?? $locale;
    }

    public static function isRtl(string $locale): bool
    {
        return in_array($locale, ['ar', 'ur'], true);
    }

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->locale
            ?? session('locale')
            ?? config('app.locale', 'en');

        if (! in_array($locale, self::LOCALES, true)) {
            $locale = 'en';
        }

        App::setLocale($locale);
        session(['locale' => $locale]);

        return $next($request);
    }
}
