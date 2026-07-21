<?php

use App\Http\Controllers\DeployWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::post('/deploy/webhook', DeployWebhookController::class)
    ->middleware('throttle:10,1')
    ->name('deploy.webhook');
