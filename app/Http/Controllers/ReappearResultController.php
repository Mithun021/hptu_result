<?php

namespace App\Http\Controllers;

use App\Models\AcademicAdjustment;
use App\Models\AnnualSemester;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\EndSemMark;
use App\Models\Exam;
use App\Models\ExaminationForm;
use App\Models\Institute;
use App\Models\MarksGrade;
use App\Models\MidSemMark;
use App\Models\PassPercentage;
use App\Models\ReappearStudentDetail;
use App\Models\ResultGpSheet;
use App\Models\Scheme;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentResultSummary;
use App\Models\StudentSubject;
use App\Models\Subject;
use App\Models\SuppleEndSemMark;
use App\Models\SuppleResultGpSheet;
use App\Models\SuppleResultSummary;
use App\Models\ReappearSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReappearResultController extends Controller
{
    public function reappearMappedSubject()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.reappear.assign-subjects', compact('courses', 'exams'));
    }

    public function fetchReappearMappedSubject(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Fetch FAIL students and their failed subjects
        |--------------------------------------------------------------------------
        */

        $query = ResultGpSheet::with([
            'student:id,student_name,father_name,mother_name,university_roll_no',
            'subject:id,name,subject_code'
        ])
        ->where('result', 'FAIL')
        ->where('exam_id', $request->exam_id)
        ->where('course_type', $request->course_type)
        ->where('course_id', $request->course_id)
        ->where('branch_id', $request->branch_id)
        ->where('batch_id', $request->batch_id)
        ->where('institute_id', $request->institute_id);

        // Scheme
        if (!empty($request->scheme_id)) {
            $query->where('scheme_id', $request->scheme_id);
        }

        // Semester / Annual Semester
        if ($request->course_type == 'Semester Based') {
            $query->where('semester_id', $request->semester_id);
        } else {
            $query->where('annual_semester_id', $request->annual_semester_id);
        }

        // Student filter
        if (!empty($request->student_roll_number)) {

            $query->whereHas('student', function ($q) use ($request) {
                $q->where(
                    'university_roll_no',
                    $request->student_roll_number
                );
            });
        }

        $records = $query->get();

        // Student-wise grouping
        $students = $records->groupBy('student_id');


        /*
        |--------------------------------------------------------------------------
        | 2. Fetch already saved Reappear subjects
        |--------------------------------------------------------------------------
        */

        $existingSupple = ReappearStudentDetail::with([
            'reappearSubjects'
        ])
        ->where('exam_id', $request->exam_id)
        ->where('course_type', $request->course_type)
        ->where('course_id', $request->course_id)
        ->where('branch_id', $request->branch_id)
        ->where('batch_id', $request->batch_id)
        ->where('institute_id', $request->institute_id)

        ->when($request->scheme_id, function ($q) use ($request) {
            $q->where('scheme_id', $request->scheme_id);
        })

        ->when(
            $request->course_type == 'Semester Based',
            function ($q) use ($request) {
                $q->where('semester_id', $request->semester_id);
            },
            function ($q) use ($request) {
                $q->where(
                    'annual_semester_id',
                    $request->annual_semester_id
                );
            }
        )
        ->get();


        /*
        |--------------------------------------------------------------------------
        | 3. Prepare previously saved checked subjects
        |--------------------------------------------------------------------------
        */

        $checkedSubjects = [];

        foreach ($existingSupple as $studentDetail) {

            $studentId = $studentDetail->student_id;

            $subjectIds = $studentDetail->reappearSubjects
                ->pluck('subject_id')
                ->toArray();

            $checkedSubjects[$studentId] = array_values(
                array_unique(
                    array_merge(
                        $checkedSubjects[$studentId] ?? [],
                        $subjectIds
                    )
                )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 4. Fetch paid Reappear Examination Forms
        |--------------------------------------------------------------------------
        */

        $reappearForms = ExaminationForm::with([
            'choosedSubjects:id,examination_form_id,subject_id'
        ])
        ->where('exam_id', $request->exam_id)
        ->where('course_id', $request->course_id)
        ->where('branch_id', $request->branch_id)
        ->where('batch_id', $request->batch_id)
        ->where('institute_id', $request->institute_id)
        ->where('exam_type', 'Reappear')
        ->where('payment_status', 1)

        // Scheme
        ->when($request->scheme_id, function ($q) use ($request) {
            $q->where('scheme_id', $request->scheme_id);
        })

        // Semester
        ->when(
            $request->course_type == 'Semester Based',
            function ($q) use ($request) {
                $q->where('semester_id', $request->semester_id);
            }
        )

        // Only students available in the current FAIL result
        ->whereIn('student_id', $students->keys())

        ->get([
            'id',
            'student_id'
        ]);


        /*
        |--------------------------------------------------------------------------
        | 5. Merge Examination Form selected subjects
        |--------------------------------------------------------------------------
        */

        foreach ($reappearForms as $form) {

            $studentId = $form->student_id;

            $selectedSubjectIds = $form->choosedSubjects
                ->pluck('subject_id')
                ->toArray();

            $checkedSubjects[$studentId] = array_values(
                array_unique(
                    array_merge(
                        $checkedSubjects[$studentId] ?? [],
                        $selectedSubjectIds
                    )
                )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 6. Return Blade View
        |--------------------------------------------------------------------------
        */

        return view(
            'backend.result.reappear.fetch-data.student-reappear-subjects',
            [
                'students' => $students,
                'request' => $request,
                'checkedSubjects' => $checkedSubjects,
            ]
        );
    }


}
