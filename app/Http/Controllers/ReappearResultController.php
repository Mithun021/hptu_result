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
        | 1. Fetch Students from Paid Reappear Examination Forms
        |--------------------------------------------------------------------------
        */

        $formsQuery = ExaminationForm::with([
            'choosedSubjects:id,examination_form_id,subject_id'
        ])
        ->where('exam_id', $request->exam_id)
        ->where('course_id', $request->course_id)
        ->where('branch_id', $request->branch_id)
        ->where('batch_id', $request->batch_id)
        ->where('institute_id', $request->institute_id)
        ->where('exam_type', 'Reappear')
        ->where('payment_status', 1);

        // Scheme Filter
        if (!empty($request->scheme_id)) {
            $formsQuery->where('scheme_id', $request->scheme_id);
        }

        // Semester Based Filter
        if ($request->course_type == 'Semester Based') {
            $formsQuery->where('semester_id', $request->semester_id);
        }

        // Student Roll Number Filter
        if (!empty($request->student_roll_number)) {

            $formsQuery->whereHas('student', function ($q) use ($request) {
                $q->where(
                    'university_roll_no',
                    $request->student_roll_number
                );
            });
        }

        $forms = $formsQuery->get([
            'id',
            'student_id'
        ]);


        /*
        |--------------------------------------------------------------------------
        | 2. Get Applied Students
        |--------------------------------------------------------------------------
        */

        $studentIds = $forms
            ->pluck('student_id')
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | 3. Get Selected Reappear Subjects from Examination Forms
        |--------------------------------------------------------------------------
        */

        $checkedSubjects = [];

        foreach ($forms as $form) {

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
        | 4. Fetch Student's Enrolled Subjects
        |--------------------------------------------------------------------------
        */

        $subjectQuery = StudentSubject::with([
            'student:id,student_name,father_name,mother_name,university_roll_no',
            'subject:id,name,subject_code,type'
        ])
        ->whereIn('student_id', $studentIds)
        ->where('course_type', $request->course_type)
        ->where('course_id', $request->course_id)
        ->where('branch_id', $request->branch_id);

        // Scheme Filter
        if (!empty($request->scheme_id)) {
            $subjectQuery->where('scheme_id', $request->scheme_id);
        }

        // Semester / Annual Semester Filter
        if ($request->course_type == 'Semester Based') {

            $subjectQuery->where(
                'semester_id',
                $request->semester_id
            );

        } else {

            $subjectQuery->where(
                'annual_semester_id',
                $request->annual_semester_id
            );
        }

        // Only Theory Subjects
        $subjectQuery->whereHas('subject', function ($q) {
            $q->where('type', 'Theory');
        });

        $subjectRecords = $subjectQuery->get();


        /*
        |--------------------------------------------------------------------------
        | 5. Student Wise Subject Grouping
        |--------------------------------------------------------------------------
        */

        $students = $subjectRecords->groupBy('student_id');


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

    public function saveReappearStudents(Request $request)
    {
        $request->validate([
            'exam_id'            => 'required',
            'course_type'        => 'required',
            'course_id'          => 'required',
            'branch_id'          => 'required',
            'batch_id'           => 'required',
            'institute_id'       => 'required',
            'subject_ids'        => 'required|array',
        ]);

        DB::beginTransaction();

        try {

            foreach ($request->subject_ids as $studentId => $subjectIds) {

                /*
                |--------------------------------------------------------------------------
                | Create / Update Reappear Student Detail
                |--------------------------------------------------------------------------
                */

                $reappearStudent = ReappearStudentDetail::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'exam_id'    => $request->exam_id,
                    ],
                    [
                        'course_type'        => $request->course_type,
                        'course_id'          => $request->course_id,
                        'scheme_id'          => $request->scheme_id,
                        'branch_id'          => $request->branch_id,
                        'semester_id'        => $request->semester_id,
                        'annual_semester_id' => $request->annual_semester_id,
                        'batch_id'           => $request->batch_id,
                        'institute_id'       => $request->institute_id,
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | Delete Old Reappear Subjects
                |--------------------------------------------------------------------------
                */

                ReappearSubject::where(
                    'reappear_student_detail_id',
                    $reappearStudent->id
                )->delete();

                /*
                |--------------------------------------------------------------------------
                | Insert Newly Selected Reappear Subjects
                |--------------------------------------------------------------------------
                */

                if (!empty($subjectIds)) {

                    $insertData = [];

                    foreach (array_unique($subjectIds) as $subjectId) {

                        $insertData[] = [
                            'reappear_student_detail_id' => $reappearStudent->id,
                            'subject_id'                 => $subjectId,
                            'created_at'                 => now(),
                            'updated_at'                 => now(),
                        ];
                    }

                    if (!empty($insertData)) {
                        ReappearSubject::insert($insertData);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Reappear subjects saved successfully.'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
