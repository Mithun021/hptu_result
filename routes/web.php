<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnnualSemesterController;
use App\Http\Controllers\AttendanceSheetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\BlockStudentController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CpSheetController;
use App\Http\Controllers\CutlistController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DmcSheetController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExamCenterController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamFeeController;
use App\Http\Controllers\FrontsheetController;
use App\Http\Controllers\GraceMarkPolicyController;
use App\Http\Controllers\GraceMarksController;
use App\Http\Controllers\InstituteController;
use App\Http\Controllers\ManageExamFormController;
use App\Http\Controllers\MarksGradeController;
use App\Http\Controllers\MidSemExamController;
use App\Http\Controllers\PassPercentageController;
use App\Http\Controllers\PermissionCategoryController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PortalFeatureController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\RetotallingMappedSubjectController;
use App\Http\Controllers\RetotallingResultController;
use App\Http\Controllers\SchemeController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SupplementaryResultController;
use App\Models\AnnualSemester;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| College Forgot Password
|--------------------------------------------------------------------------
*/

Route::get(
    '/forgot-password/college',
    [AdminController::class, 'collegeForgotPassword']
)->name('college.forgot-password');


Route::post(
    '/forgot-password/college',
    [AdminController::class, 'collegeSendResetLink']
)->name('college.forgot-password.send');


Route::get(
    '/reset-password/college/{token}',
    [AdminController::class, 'collegeResetPassword']
)->name('college.reset-password');


Route::post(
    '/reset-password/college',
    [AdminController::class, 'collegeUpdatePassword']
)->name('college.reset-password.update');


/*
|--------------------------------------------------------------------------
| Student Forgot Password
|--------------------------------------------------------------------------
*/

Route::get(
    '/forgot-password/student',
    [AdminController::class, 'studentForgotPassword']
)->name('student.forgot-password');


Route::post(
    '/forgot-password/student',
    [AdminController::class, 'studentSendResetLink']
)->name('student.forgot-password.send');


Route::get(
    '/reset-password/student/{token}',
    [AdminController::class, 'studentResetPassword']
)->name('student.reset-password');


Route::post(
    '/reset-password/student',
    [AdminController::class, 'studentUpdatePassword']
)->name('student.reset-password.update');

/*
|--------------------------------------------------------------------------
| Admin User Forgot Password
|--------------------------------------------------------------------------
*/


Route::get('/forgot-password/user', [AdminController::class, 'userForgotPassword'])
    ->name('user.forgot-password');

Route::post('/forgot-password/user', [AdminController::class, 'userSendResetLink'])
    ->name('user.forgot-password.send');

Route::get('/reset-password/user/{token}', [AdminController::class, 'userResetPassword'])
    ->name('password.reset');

Route::post('/reset-password/user', [AdminController::class, 'userUpdatePassword'])
    ->name('user.reset-password.update');



Route::middleware(['auth'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admindashboard.get');
});


// Global Function -----------------------------------------------
Route::get('/branches/{course_id}', [BranchController::class, 'getByCourse'])->name('branches.byCourse');
Route::get('/institute/branches/{course_id}', [BranchController::class, 'getBranchesByCourse'])->name('institute.branches.byCourse');
Route::get('/courses/{id}/details', [CourseController::class, 'getByCourse'])->name('courses.getByCourse');
Route::get('/subjects/filter', [SubjectController::class, 'getSubjects']);
Route::get('/institute/subjects/filter', [SubjectController::class, 'getSubjectsByInstitute'])->name('institute.subjects.filter');
Route::get('/result/subjects/filter', [SubjectController::class, 'getFilterSubjects'])->name('result.subjects.filter');
Route::get('/result/subjects/theory-paper/filter', [SubjectController::class, 'getFilterTheoryPaperSubjects'])->name('result.subjects.theory-paper.filter');
Route::get('/student/examination-form/verified-subjects', [SubjectController::class, 'getStudentExamFormVerifiedSubjects'])->name('student.examination-form.verified-subjects');
Route::get('/result/subjects/ncCourse/filter', [SubjectController::class, 'getFilterNcSubjects'])->name('result.subjects.ncCourse.filter');
Route::get('/get-mid-sem-exams', [MidSemExamController::class, 'getMidSemExamsCourseBased'])->name('coursebased.get.mid.sem.exams');

Route::middleware(['backend.auth'])->prefix('backend')->group(function () {

    Route::get('/mail-config/index', [AdminController::class, 'mailConfig'])
        ->name('backend.mail-config.index');

    Route::post('/mail-config/update', [AdminController::class, 'mailConfigUpdate'])
        ->name('backend.mail-config.update');

    Route::get('/send-mail', [AdminController::class, 'sendMail'])
        ->name('backend.send-mail.index');

    Route::post('/send-mail', [AdminController::class, 'sendMailStore'])
        ->name('backend.send-mail.store');

    Route::resource('institutes', InstituteController::class);
    Route::resource('semesters',SemesterController::class);
    Route::resource('annual-semesters',AnnualSemesterController::class);
    Route::resource('sessions',BatchController::class);
    Route::resource('schemes',SchemeController::class);
    Route::resource('courses',CourseController::class);
    Route::resource('branches',BranchController::class);
    Route::resource('subjects',SubjectController::class);
    Route::resource('subjects',SubjectController::class);
    Route::resource('exams', ExamController::class);
    Route::resource('exam-fees', ExamFeeController::class) ->names('exam-fees');
    Route::resource('exam-centers', ExamCenterController::class) ->names('exam-centers');
    Route::resource('mid-sem-exams', MidSemExamController::class);
    Route::get('/block-students', [BlockStudentController::class, 'index'])
        ->name('block-students.index');

    Route::post('/block-students/store', [BlockStudentController::class, 'store'])
        ->name('block-students.store');

    Route::get('/block-students/data', [BlockStudentController::class, 'getData'])
        ->name('block-students.data');

    Route::get('/block-students/{id}/edit', [BlockStudentController::class, 'edit'])
        ->name('block-students.edit');

    Route::put('/block-students/{id}', [BlockStudentController::class, 'update'])
        ->name('block-students.update');

    Route::delete('/block-students/{id}', [BlockStudentController::class, 'destroy'])
        ->name('block-students.destroy');
    Route::resource('pass-percentages', PassPercentageController::class)->except(['create', 'show']);
    Route::resource('marks-grades', MarksGradeController::class)->except(['create', 'show']);
    Route::resource('grace-marks', GraceMarksController::class);
    Route::resource('grace-mark-policies', GraceMarkPolicyController::class);
    Route::resource('departments', DepartmentController::class);

    Route::get('subject/data',[SubjectController::class,'data'])->name('subjects.data');
    Route::post('/subjects/import', [SubjectController::class, 'importSubject'])->name('subjects.import');
    Route::resource('students',StudentController::class);
    Route::get('student/data',[StudentController::class,'data'])->name('students.data');
    Route::post('/student/import', [StudentController::class, 'importStudent'])->name('students.import');

    // Portal Feature Settings
    Route::get('/setting', [PortalFeatureController::class, 'index'])
        ->name('setting.index');

    Route::post('/setting/update', [PortalFeatureController::class, 'update'])
        ->name('setting.update');

    Route::get('/roles', [PermissionController::class, 'roles'])->name('roles');
    Route::post('/roles/store', [PermissionController::class, 'roles_store'])->name('roles.store');
    Route::get('/roles/{roles_id}/edit', [PermissionController::class, 'edit_roles'])->name('roles.edit');
    Route::post('/roles/{roles_id}/update', [PermissionController::class, 'update_roles'])->name('roles.update');
    Route::get('/roles/{roles_id}/destroy', [PermissionController::class, 'destroy_roles'])->name('roles.destroy');

    Route::get('/employee', [EmployeeController::class, 'index'])->name('employee');
    Route::post('/employee/store', [EmployeeController::class, 'employee_store'])->name('employee.store');
    Route::get('/employee/{employee_id}/edit', [EmployeeController::class, 'edit_employee'])->name('employee.edit');
    Route::post('/employee/{employee_id}/update', [EmployeeController::class, 'update_employee'])->name('employee.update');
    Route::get('/employee/{employee_id}/destroy', [EmployeeController::class, 'destroy_employee'])->name('employee.destroy');

    Route::get('/permission', [PermissionController::class, 'permission'])->name('permission');
    Route::post('/permission/store', [PermissionController::class, 'permission_store'])->name('permission.store');

    Route::resource('/permission-categories',PermissionCategoryController::class);

    Route::get('/logout', [AuthController::class, 'logout'])->name('logout.backend');

    Route::prefix('results')->name('results.')->group(function () {

        Route::get('/internal-marks', [ResultController::class, 'internalMarks'])
            ->name('internal-marks');

        Route::get('/internal-marks/fetch', [ResultController::class, 'fetchInternalMarks'])
            ->name('internal-marks.fetch');

        Route::get('/subject-wise/internal-marks', [ResultController::class, 'internalMarksSubjectWise'])
            ->name('subject-wise.internal-marks');

        Route::get('/subject-wise/internal-marks/fetch', [ResultController::class, 'fetchInternalMarksSubjectWise'])
            ->name('subject-wise.internal-marks.fetch');

        Route::post( '/internal-marks/lock-unlock', [ResultController::class, 'updateInternalMarksLockStatus'] )->name('internal-marks.lock-unlock');

        Route::post( '/internal-marks/grace-marks', [ResultController::class, 'updateGraceMarks'] )->name('internal-marks.grace-marks');

        Route::get('/external-marks', [ResultController::class, 'externalMarks'])
            ->name('external-marks');

        Route::get('/subject-wise/external-marks', [ResultController::class, 'externalMarksSubjectWise'])
            ->name('subject-wise.external-marks');

        Route::get('/subject-wise/external-marks/fetch', [ResultController::class, 'fetchExternalMarksSubjectWise'])
            ->name('subject-wise.external-marks.fetch');

        Route::get('/external-marks/fetch', [ResultController::class, 'fetchExternalMarks'])
            ->name('external-marks.fetch');

        Route::post('/external-marks/save', [ResultController::class,'saveExternalMarks'])
            ->name('external-marks.save');

        Route::post(
            '/external-marks/update-lock',
            [ResultController::class, 'updateExternalMarksLock']
        )->name('external-marks.update-lock');

        Route::get( 'external-marks/export', [ResultController::class, 'exportExternalMarks'] )->name('external-marks.export');
        Route::post( 'external-marks/import', [ResultController::class, 'importExternalMarks'] )->name('external-marks.import');

        Route::get('/result-prepare', [ResultController::class, 'resultPrepare'])
            ->name('result-prepare');

        Route::get('/result-prepare/fetch', [ResultController::class, 'fetchEndSemResult'])
            ->name('result-prepare.fetch');

        Route::get('/endsem-result', [ResultController::class, 'endSemResultPrepare'])
            ->name('end-sem.result-prepare');

        Route::get('/endsem-result/fetch', [ResultController::class, 'finalFetchEndSemResult'])
            ->name('final-result.fetch');

        Route::post('/fetch-result-detail', [ResultController::class, 'fetchEndSemResult'])
        ->name('fetch.result.detail');

        Route::post('/save-result-sheet', [ResultController::class, 'saveResultSheet'])
        ->name('save.result.sheet');

        Route::get('/result-overview', [ResultController::class, 'resultOverview'])
            ->name('overview');

        Route::get('/result-overview/fetch', [ResultController::class, 'fetchResultOverview'])
            ->name('fetch.overview');

        Route::post('/result-declare', [ResultController::class, 'declareResult'])
            ->name('declare');

        Route::get('/result-dmc', [ResultController::class, 'resultDmc'])
            ->name('dmc');

        Route::get('/transcript', [ResultController::class, 'resultTranscript'])
            ->name('transcript');

        Route::get('/transcript/fetch', [ResultController::class, 'fetchTranscript'])
            ->name('transcript.fetch');

        Route::get('/result-grace', [ResultController::class, 'resultGrace'])
            ->name('grace');

        Route::post( '/external-marks/save-grace-marks', [ResultController::class, 'saveGraceMarks'] )->name('external-marks.save-grace-marks');

        // below is saving grace for all subject,
        Route::post('/save-result-grace', [ResultController::class, 'saveResultGrace'])
            ->name('save-grace');

        Route::post( '/save-academic-adjustment', [ResultController::class, 'saveAcademicAdjustment'] )->name('save.academic.adjustment');
        Route::post( '/save-annual-moderation', [ResultController::class, 'saveAnnualModeration'] )->name('save.annual.moderation');

         // Supplementary Result Routes
        Route::get('/supple-mapped-subject', [SupplementaryResultController::class, 'suppleMappedSubject'])->name('supple-mapped-subject');
        Route::get('/fetch-supple-mapped-subject', [SupplementaryResultController::class, 'fetchSuppleMappedSubject'])->name('supple-assign-subjects.fetch');
        Route::post('/supplement/save',[SupplementaryResultController::class,'saveSuppleStudents'])->name('supplement.save');
        Route::get('/supple-result-prepare', [SupplementaryResultController::class, 'suppleResultPrepare'])->name('supple-result-prepare');
        Route::get('/supple-result-prepare/fetch', [SupplementaryResultController::class, 'fetchSuppleResult']) ->name('supple-result-prepare.fetch');
        Route::post( '/supplementary-result/save-mark', [SupplementaryResultController::class, 'saveSuppleMark'] )->name('supplementary-result.save-mark');
        Route::post( '/supplementary-result/update-lock-status', [SupplementaryResultController::class, 'updateLockStatus'] )->name('supplementary-result.update-lock-status');
        Route::get('/supple-subject-wise-result', [SupplementaryResultController::class, 'suppleSubjectWiseResult'])->name('supple.subject-wise-result');
        Route::get('/supple-subject-wise-result-prepare/fetch', [SupplementaryResultController::class, 'fetchSuppleSubjectWiseResult'])->name('supple-subject-wise-result-prepare.fetch');

        Route::get('/supple-overall-result', [SupplementaryResultController::class, 'suppleOverallResult'])->name('supple.overall-result');
        Route::get('/supple-overall-result-fetch', [SupplementaryResultController::class, 'fetchSuppleOverallResult'])->name('supple.fetch-overall-result');
        Route::post('/supple-overall-result-save', [SupplementaryResultController::class, 'saveSuppleOverallResult'] )->name('supple.save-overall-result');
        Route::get('/supple-result-overview', [SupplementaryResultController::class, 'suppleResultOverview'])->name('supple-overview');
        Route::get('/supple-result-overview/fetch', [SupplementaryResultController::class, 'fetchSuppleResultOverview'])->name('fetch.supple-overview');
        Route::post('/supple-result-declare', [SupplementaryResultController::class, 'declareSuppleResult'])->name('supple-declare');

        Route::get('/cp-sheet/end-sem', [CpSheetController::class, 'endSemIndex'])->name('cp-sheet.end-sem');
        Route::get('/cp-sheet/fetch-result-cp-sheet', [CpSheetController::class, 'fetchRegularCpSheet'])->name('cp-sheet.fetch.regular-result');
        Route::get('/cp-sheet/supple', [CpSheetController::class, 'suppleIndex'])->name('cp-sheet.supple');
        Route::get('/cp-sheet/fetch-supple-result-cp-sheet', [CpSheetController::class, 'fetchSuppleCpSheet'])->name('cp-sheet.fetch.supple-result');

        Route::get('/result-dmc/regular', [DmcSheetController::class, 'regularDmcIndex'])->name('regular.dmc');
        Route::get('/result-dmc/fetch-regular-dmc-sheet', [DmcSheetController::class, 'fetchRegularDmcSheet'])->name('dmc-sheet.fetch.regular-result');

        Route::get('/frontsheet/nc-course', [FrontsheetController::class, 'ncCourseIndex'])->name('frontsheet.nc-course');
        Route::get('/frontsheet/nc-course/fetch', [FrontsheetController::class, 'fetchNcCourseIndex'])
            ->name('frontsheet.nc-course.fetch');
        Route::get('/frontsheet/end-sem', [FrontsheetController::class, 'endSemIndex'])->name('frontsheet.end-sem');
        Route::get('/frontsheet/end-sem-course/fetch', [FrontsheetController::class, 'fetchEndSemCourseIndex'])
            ->name('frontsheet.end-sem-course.fetch');
    });

    // Retotal Result Routes
    Route::prefix('retotal-result')->name('retotal-result.')->group(function () {
        // Mapped Subjects Routes
        Route::get('/mapped-subjects', [RetotallingMappedSubjectController::class, 'index'])->name('mapped-subjects.index');
        Route::get('/mapped-subjects/fetch-students-mapped-subject-list', [RetotallingMappedSubjectController::class, 'fetchStudentsMappedSubjectList'])->name('mapped-subjects.fetch-students-mapped-subject-list');
        Route::delete('/mapped-subjects/delete/{id}', [ RetotallingMappedSubjectController::class, 'deleteMappedSubject' ])->name('mapped-subjects.delete');
        Route::get('/mapped-subjects/create', [RetotallingMappedSubjectController::class, 'create'])->name('mapped-subjects.create');
        Route::get('/mapped-subjects/fetch-students', [RetotallingMappedSubjectController::class, 'fetchStudents'])->name('mapped-subjects.fetch-students');
        Route::post('/mapped-subjects/save-student-subject', [RetotallingMappedSubjectController::class, 'saveRetotallingSubjects'])->name('mapped-subjects.save-student-subject');

        // External Marks Routes
        Route::get('/regular/external-marks', [RetotallingResultController::class, 'regularExternalMarks'])->name('regular.external-marks.index');
        Route::get('/regular/external-marks/fetch', [RetotallingResultController::class, 'fetchRegularExternalMarks'])->name('regular.external-marks.fetch');
        Route::get('/regular/external-marks/subject-wise', [RetotallingResultController::class, 'regularExternalMarksSubjectWise'])->name('regular.external-marks.subject-wise.index');
        Route::get('/regular/external-marks/subject-wise/fetch', [RetotallingResultController::class, 'fetchRegularExternalMarksSubjectWise'])->name('regular.external-marks.subject-wise.fetch');
        Route::post('/regular/external-marks/save', [RetotallingResultController::class,'saveRegularRetotallingExternalMarks'])->name('regular.external-marks.save');
        Route::post('/regular/external-marks/update-lock', [RetotallingResultController::class, 'updateRegularRetotallingExternalMarksLock'])->name('regular.external-marks.update-lock');
        Route::get( 'external-marks/export', [RetotallingResultController::class, 'exportExternalMarks'] )->name('external-marks.export');
        Route::post( 'external-marks/import', [RetotallingResultController::class, 'importExternalMarks'] )->name('external-marks.import');

        // Result Preview Routes
        Route::prefix('regular')->name('regular.')->group(function () {
            Route::get( '/result-preview/subjectwise', [RetotallingResultController::class, 'regularResultPreviewSubjectWiseIndex'] )->name('result-preview.subjectwise.index');
            Route::get( '/result-preview/subjectwise/fetch', [RetotallingResultController::class, 'fetchRegularResultPreviewSubjectWise'] )->name('result-preview.subjectwise.fetch');
            Route::post( '/result-preview/subjectwise/fetch-details', [RetotallingResultController::class, 'fetchRegularResultPreviewSubjectWise'] )->name('result-preview.subjectwise.fetch-details');
            Route::get( '/result-preview/overall', [RetotallingResultController::class, 'overallRegularResultPreviewIndex'] )->name('result-preview.overall.index');
            Route::get( '/result-preview/overall/fetch', [RetotallingResultController::class, 'fetchOverallRegularResultPreview'] )->name('result-preview.overall.fetch');

        });
    });

    // Examination form Data ---------------------------------------------

    Route::get('/examination-form', [ManageExamFormController::class, 'index']) ->name('admin.examination-form.index');
    Route::get('/examination-form/fetch', [ManageExamFormController::class, 'fetchExamForms']) ->name('admin.examination-form.fetch');
    Route::get('/examination-form/{id}/edit', [ManageExamFormController::class, 'edit']) ->name('admin.examination-form.edit');
    Route::get('download-exam-form', [ManageExamFormController::class, 'downloadExamForm']) ->name('admin.download-exam-form');
    Route::get('download-exam-form-sheet', [ManageExamFormController::class, 'downloadExamFormSheet'])->name('admin.download-exam-form-sheet');

    Route::get('cutlist', [CutlistController::class, 'index']) ->name('admin.cutlist.index');
    Route::get('attendance-sheet', [AttendanceSheetController::class, 'index']) ->name('admin.attendance-sheet.index');
    Route::get('attendance-sheet/detail', [AttendanceSheetController::class, 'show']) ->name('admin.attendance-sheet.detail');
    Route::put('/examination-form/{id}/update', [ManageExamFormController::class, 'update'])->name('admin.examination-form.update');


});

// Route::get('/backend/login', function () {
//     return view('backend.login');
// });
Route::get('backend/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('backend/login', [AuthController::class, 'login'])->name('adminlogin.post');

Route::get('student/login', [AuthController::class, 'studentLoginForm'])->name('student.login');
Route::post('student/login', [AuthController::class, 'studentLogin'])->name('student.login.submit');
Route::get('institute/login', [AuthController::class, 'instituteLoginForm'])->name('institute.login');
Route::post('institute/login', [AuthController::class, 'instituteLogin'])->name('institute.login.submit');

Route::post('student/logout', [AuthController::class, 'studentLogout'])->name('student.logout');
Route::post('institute/logout', [AuthController::class, 'instituteLogout'])->name('institute.logout');

require __DIR__ . '/institute.php';
require __DIR__ . '/student.php';
