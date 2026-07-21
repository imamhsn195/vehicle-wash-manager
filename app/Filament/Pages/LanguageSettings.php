<?php

namespace App\Filament\Pages;

use App\Http\Middleware\SetLocale;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class LanguageSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static string $view = 'filament.pages.language-settings';

    protected static ?string $navigationLabel = 'Language';

    protected static ?string $title = 'Language';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 99;

    public ?string $locale = 'en';

    public function mount(): void
    {
        $this->locale = auth()->user()?->locale ?? app()->getLocale();
    }

    public function save(): void
    {
        $locale = $this->locale;

        if (! in_array($locale, SetLocale::LOCALES, true)) {
            return;
        }

        $user = auth()->user();
        $user->locale = $locale;
        $user->save();

        session(['locale' => $locale]);
        app()->setLocale($locale);

        Notification::make()
            ->title(__('Language').': '.$this->localeLabel($locale))
            ->success()
            ->send();
    }

    public function localeOptions(): array
    {
        return [
            'en' => 'English',
            'bn' => 'বাংলা (Bangla)',
            'ar' => 'العربية (Arabic)',
            'hi' => 'हिन्दी (Hindi)',
            'ur' => 'اردو (Urdu)',
        ];
    }

    protected function localeLabel(string $locale): string
    {
        return $this->localeOptions()[$locale] ?? $locale;
    }
}
