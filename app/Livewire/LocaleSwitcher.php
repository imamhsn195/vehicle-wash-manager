<?php

namespace App\Livewire;

use App\Http\Middleware\SetLocale;
use Filament\Notifications\Notification;
use Livewire\Component;

class LocaleSwitcher extends Component
{
    public function setLocale(string $locale): void
    {
        if (! in_array($locale, SetLocale::LOCALES, true)) {
            return;
        }

        $user = auth()->user();

        if ($user) {
            $user->forceFill(['locale' => $locale])->save();
        }

        session(['locale' => $locale]);
        app()->setLocale($locale);

        Notification::make()
            ->title(__('Language updated'))
            ->body(SetLocale::label($locale))
            ->success()
            ->send();

        $this->redirect(url()->previous() ?: url('/admin'), navigate: false);
    }

    public function render()
    {
        return view('livewire.locale-switcher', [
            'current' => app()->getLocale(),
            'locales' => SetLocale::LOCALES,
        ]);
    }
}
