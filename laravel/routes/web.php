<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'Rihal',
        'version' => '1.0.0',
        'status' => 'running',
        'environment' => config('app.env'),
    ]);
});

Route::get('/up', function () {
    return response()->json(['status' => 'ok']);
})->name('healthcheck');
