<?php

use App\Http\Controllers\Slack\Commands\AddDebtController;
use App\Http\Middleware\VerifySlackSignature;
use Illuminate\Support\Facades\Route;

Route::prefix('slack/commands')
    ->name('slack.commands.')
    ->middleware(VerifySlackSignature::class)
    ->group(function () {
        Route::post('add', AddDebtController::class)->name('add');
    });
