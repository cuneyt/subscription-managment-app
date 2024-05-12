<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RegisterDeviceController;
Route::get('/', function () {
    return view('welcome');
});
