<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Shared MySQL/MariaDB (cPanel) often caps index length at 1000 bytes.
        // utf8mb4 varchar(255) = 1020 bytes and breaks password_reset_tokens / unique emails.
        Schema::defaultStringLength(191);
    }
}
