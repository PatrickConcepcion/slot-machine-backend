<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpinController;

Route::group(['prefix' => 'v1'], function () {
    Route::post('spin', [SpinController::class, 'spin']);
});