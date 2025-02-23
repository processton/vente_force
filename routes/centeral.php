<?php

declare(strict_types=1);

use Crater\Http\Controllers\Central\SetupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web'
])->group(function () {
    Route::get('/', function () {
        return 'Centeral';
    });
    foreach (config('tenancy.central_domains') as $domain) {
        Route::domain($domain)->group(function () {

            Route::prefix('tenant')->group(function () {
                Route::get('/', function () {
                    return 'Tenant';
                });
                Route::post('/register', [SetupController::class, 'store']);
            });
        });
    }

});
