<?php

use App\Http\Controllers\student\ExaminationFormController;
use App\Http\Controllers\student\ProfileController;
use App\Http\Controllers\student\StudentSubjectsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['student.auth'])->prefix('student')->name('student.')->group(function () {
    Route::get('dashboard', fn() => view('student.dashboard'))->name('dashboard');
    Route::post('profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/verify-subjects', [StudentSubjectsController::class, 'index'])->name('verify-subjects.index');
    Route::post('/verify-subjects', [StudentSubjectsController::class, 'store'])->name('verify-subjects.store');
    // Examination Form Routes
    Route::get('/examination-form', [ExaminationFormController::class, 'index'])->name('examination-form.index');
    Route::post('/examination-form', [ExaminationFormController::class, 'store'])->name('examination-form.store');
});
