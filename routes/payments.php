<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

$accounts = array_keys(config('payments.accounts'));

$accountPattern = implode(
    '|',
    array_map('preg_quote', $accounts)
);


// Customer pages
Route::middleware('web')->group(function () use ($accountPattern) {

    // Route::get(
    //     '/pay/{account}/create',
    //     [PaymentController::class, 'create']
    // )
    // ->where('account', $accountPattern)
    // ->name('payment.create');
    Route::match(['get', 'post'], '/pay/{account}/create', [PaymentController::class, 'create'])->where('account', $accountPattern)->name('payment.create');

    Route::post(
        '/pay/{account}',
        [PaymentController::class, 'store']
    )
    ->where('account', $accountPattern)
    ->name('payment.store');

    Route::get(
        '/payment/{payment}/result',
        [PaymentController::class, 'result']
    )
    ->middleware('signed')
    ->name('payment.result');

});


// Bank → our server
// Outside web middleware to avoid CSRF 419

Route::post(
    '/payment/{account}/callback',
    [PaymentController::class, 'callback']
)
->where('account', $accountPattern)
->name('payment.callback');

Route::post(
    '/payment/{account}/webhook',
    [PaymentController::class, 'webhook']
)
->where('account', $accountPattern)
->name('payment.webhook');