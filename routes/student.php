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
    Route::get( '/examination-form/calculate-fee', [ExaminationFormController::class, 'calculateExamFee'] )->name('examination-form.calculate-fee');
    Route::get('/examination-form/check-block-status', [ExaminationFormController::class, 'checkBlockStatus'] )->name('examination-form.check-block-status');
    Route::get('/examination-forms', [ExaminationFormController::class, 'filledExamForms'])->name('filled-examination-forms.index');
    // Pending Examination Forms
    Route::get('/examination-forms/pending/{id}', [ExaminationFormController::class, 'pendingExamForms'])->name('examination-forms.pending');
    // Update Pending Examination Form Fee
    Route::post('/examination-forms/{id}/update-fee', [
        ExaminationFormController::class,
        'updatePendingExamFee'
    ])->name('examination-forms.update-fee');

    // Payment Proceed Page
    Route::get('/examination-forms/{id}/payment-proceed', [
        ExaminationFormController::class,
        'paymentProceed'
    ])->name('examination-forms.payment-proceed');
    // Paid Examination Forms
    Route::get('/examination-forms/paid/{id}', [ExaminationFormController::class, 'paidExamForm'])
        ->name('examination-forms.paid');

    // Admit Card
    Route::get('/admit-card/{id}', [ExaminationFormController::class, 'admitCard'])
        ->name('admit-cards.show');
});
