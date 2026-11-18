<?php

use App\Http\Controllers\AttendanceSheetController;
use App\Http\Controllers\CutlistController;
use App\Http\Controllers\institute\AssignSubjectController;
use App\Http\Controllers\institute\MidSemMarksController;
use App\Http\Controllers\institute\StudentController;
use App\Http\Controllers\institute\StudentSubjectController;
use App\Http\Controllers\institute\TeacherController;
use Illuminate\Support\Facades\Route;

Route::middleware(['institute.auth'])->prefix('institute')->group(function () {
    Route::get('dashboard', fn() => view('institute.dashboard'))->name('institute.dashboard');

    Route::get('mid-sem-marks', [MidSemMarksController::class, 'midSem'])->name('marks.mid-sem');

    Route::post('mid-sem-marks/render-result', [MidSemMarksController::class, 'renderResult'])
    ->name('mid-sem-marks.render-result');

    Route::get('mid-sem-marks/data', [MidSemMarksController::class, 'datatable'])
        ->name('mid-sem-marks.data');

    Route::post('mid-sem-marks/save', [MidSemMarksController::class, 'saveMarks'])
    ->name('mid-sem-marks.save');

    Route::post('mid-sem-marks/lock-unlock',[MidSemMarksController::class,'lockUnlock'])->name('mid-sem-marks.lock-unlock');

    Route::post('mid-sem-marks/import',[MidSemMarksController::class,'import'])->name('mid-sem-marks.import');

    Route::post('mid-sem-marks/export',[MidSemMarksController::class,'export'])->name('mid-sem-marks.export');

    Route::resource('teachers', TeacherController::class);

    Route::get('teacher/assign-subject', [AssignSubjectController::class, 'assign_subject'])
        ->name('assign.subject');

    Route::get('teacher/create-assign-subject', [AssignSubjectController::class, 'create_assign_subject'])
        ->name('create.assign.subject');

    Route::post('teacher/save-assign-subject', [AssignSubjectController::class, 'save_assign_subject'])
        ->name('save.assign.subject');

    Route::get('teacher/edit-assign-subject/{id}', [
        AssignSubjectController::class,
        'edit_assign_subject'
    ])->name('edit.assign.subject');

    Route::put('teacher/update-assign-subject/{id}', [
        AssignSubjectController::class,
        'update_assign_subject'
    ])->name('update.assign.subject');

    Route::delete('teacher/delete-assign-subject/{id}', [
        AssignSubjectController::class,
        'delete_assign_subject'
    ])->name('delete.assign.subject');


    // Student Management ---------------------------------------
    Route::name('institute.')->group(function () {
        Route::get('students', [StudentController::class, 'index'])->name('students.index');
        Route::get('students/data', [StudentController::class, 'data'])->name('students.data');
        Route::get('students/create', [StudentController::class, 'create'])->name('students.create');
        Route::post('students/store', [StudentController::class, 'store'])->name('students.store');
        Route::get('students/edit/{id}', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('students/update/{id}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('students/destroy/{id}', [StudentController::class, 'destroy'])->name('students.destroy');
        Route::post('students/import', [StudentController::class, 'importStudent'])->name('students.import');
        Route::post('students/promote', [StudentController::class, 'promote'])
        ->name('students.promote');
    });

    Route::name('student-subject.')->group(function () {
        Route::get('student-subjects', [StudentSubjectController::class, 'index'])->name('index');
        Route::get('student-subjects/create', [StudentSubjectController::class, 'create'])->name('create');
        Route::post('/student-subject/render', [StudentSubjectController::class, 'render'])->name('render');
        Route::post('/student-subject/store', [StudentSubjectController::class, 'store'])->name('store');
        Route::get('/student-subject/list', [StudentSubjectController::class, 'list'])->name('list');
        Route::post('/student-subject/import', [StudentSubjectController::class, 'import'])->name('import');
        Route::get('student-subjects/export-sample', [StudentSubjectController::class, 'exportSample'])
        ->name('export-sample');
    });

    Route::get('cutlist', [CutlistController::class, 'index']) ->name('institute.cutlist.index');
    Route::get('attendance-sheet', [AttendanceSheetController::class, 'index']) ->name('institute.attendance-sheet.index');
    Route::get('attendance-sheet/detail', [AttendanceSheetController::class, 'show']) ->name('institute.attendance-sheet.detail');

});
