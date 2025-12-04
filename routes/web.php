<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EstimateController;


Route::get('/', function () {
    return view('welcome');
});


Route::middleware(['web'])
    ->prefix('admin/custom')
    ->group(function () {

        Route::get('/estimates/create', [EstimateController::class, 'create'])
            ->name('estimates.custom.create');

        Route::post('/estimates/store', [EstimateController::class, 'store'])
            ->name('estimates.custom.store');

        Route::get('/estimates/{estimate}/edit', [EstimateController::class, 'edit'])
            ->name('estimates.custom.edit');

        Route::post('/estimates/{estimate}/update', [EstimateController::class, 'update'])
            ->name('estimates.custom.update');
    });
