<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\SigninController;
use App\Http\Controllers\SignupController;

use Illuminate\Support\Facades\Route;

Route::get('/signup', [SignupController::class, 'index']);
Route::post('/signup', [SignupController::class, 'store']);

Route::get('/', function () { return redirect('/signin'); });
Route::get('/signin', [SigninController::class, 'index']);
Route::get('/auth/{type}/{provider}', [SigninController::class, 'auth']);

// routes/web.php

Route::get('/schedule/{id}', [AppointmentController::class, 'showForm'])->name('schedule.form');
Route::post('/schedule/{id}', [AppointmentController::class, 'store'])->name('schedule.store');
