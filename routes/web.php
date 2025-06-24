<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SigninController;
use App\Http\Controllers\SignupController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// public routes
Route::get('/', [HomeController::class,'index'])->name('home');
Route::get('/signup', [SignupController::class, 'index']);
Route::post('/signup', [SignupController::class, 'store']);
Route::get('/signin', [SigninController::class, 'index'])->name('signin');
Route::get('/auth/{type}/{provider}', [SigninController::class, 'auth']);
Route::get('/schedule/{id}', [AppointmentController::class, 'showForm'])->name('schedule.form');
Route::post('/schedule/{id}', [AppointmentController::class, 'store'])->name('schedule.store');

// private routes
// admin routes

// client routes
Route::get('/dashboard', [DashboardController::class, 'client'])->name('client.dashboard');
