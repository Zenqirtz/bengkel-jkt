<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\BahanController;
use App\Http\Controllers\Api\SaldoBahanController;
// use App\Http\Controllers\Api\CabangController;

Route::get('/news', NewsController::class);
Route::get('/bahan', BahanController::class);
Route::get('/saldobahan', SaldoBahanController::class);
// Route::get('/cabangs', CabangController::class);
