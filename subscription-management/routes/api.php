<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterDeviceController;
use App\Http\Controllers\GoogleVerificationController;
use App\Http\Controllers\MainPaymentController;
use App\Http\Controllers\SubscriptionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test', [RegisterDeviceController::class, 'create'])->name('createe');
Route::get('/register', [RegisterDeviceController::class, 'create'])->name('createe');
Route::get("/purchase", [MainPaymentController::class, 'paymentprocessrs'])->name("process");
Route::post("/googleverification", [GoogleVerificationController::class, 'verificationprocess'])->name("verificationprocessa");
Route::post("/eventChange", [SubscriptionController::class, 'process'])->name("eventProcess");
Route::post("/checksubscription", [SubscriptionController::class, 'checksubs'])->name("checksubsc");
Route::get("/crontest", [SubscriptionController::class, 'CronTest'])->name("crontestq");

Route::fallback(function () {
    return response()->json(['message' => 'Not Found.'], 404);
});
