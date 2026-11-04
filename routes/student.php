<?php

use App\Http\Controllers\student\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['student.auth'])->prefix('student')->name('student.')->group(function () {
    Route::get('dashboard', fn() => view('student.dashboard'))->name('dashboard');
    Route::post('profile/update', [ProfileController::class, 'update'])->name('profile.update');
});