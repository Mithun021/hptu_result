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
use App\Models\ReappearEndSemMark;
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

    public function reappearResultPrepare()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.reappear.reappear-result.index', compact('courses', 'exams'));
    }

    // public function fetchReappearResult(Request $request){
    //     dd($request->all());
    // }

    public function fetchReappearResult(Request $request)
    {
        // Only Semester Based for now
        if ($request->course_type !== 'Semester Based') {
            return response('Only Semester Based is supported currently.', 422);
        }

        $exam = Exam::findOrFail($request->exam_id);
        $batch = Batch::find($request->batch_id);
        $subject = Subject::with(['course', 'branch'])
            ->findOrFail($request->subject_id);

        $marksType = $request->marks_type;

        // Maximum marks for selected marks type
        $maxMarks = match ($marksType) {
            'external' => $subject->external_marks ?? 0,
            default => 0,
        };

        /*
        |--------------------------------------------------------------------------
        | Previous Exam Sessions
        |--------------------------------------------------------------------------
        */

        $previousExamIds = getPreviousExamSessionIds($exam->id);

        // Latest previous exam first
        $previousExamIds = array_reverse($previousExamIds);

        /*
        |--------------------------------------------------------------------------
        | Reappear Students
        |--------------------------------------------------------------------------
        */

        $studentDetailsQuery = ReappearStudentDetail::with([
            'student',
            'reappearSubjects'
        ])
        ->where('exam_id', $request->exam_id)
        ->where('course_type', $request->course_type)
        ->where('course_id', $request->course_id)
        ->where('scheme_id', $request->scheme_id)
        ->where('branch_id', $request->branch_id)
        ->where('batch_id', $request->batch_id)
        ->where('semester_id', $request->semester_id)
        ->whereNull('annual_semester_id');

        // Institute optional
        if ($request->filled('institute_id')) {
            $studentDetailsQuery->where(
                'institute_id',
                $request->institute_id
            );
        }

        $studentDetails = $studentDetailsQuery->get()
            ->filter(function ($detail) use ($request) {

                // Student must have applied for this subject
                return $detail->reappearSubjects->contains(
                    'subject_id',
                    $request->subject_id
                );
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Prepare Student Marks
        |--------------------------------------------------------------------------
        */

        $students = collect();

        foreach ($studentDetails as $detail) {

            $student = $detail->student;

            if (!$student) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Current Exam Reappear Marks
            |--------------------------------------------------------------------------
            */

            $currentReappearMarkQuery = ReappearEndSemMark::where([
                'exam_id'       => $request->exam_id,
                'student_id'    => $student->id,
                'subject_id'    => $request->subject_id,
                'course_type'   => $request->course_type,
                'course_id'     => $request->course_id,
                'scheme_id'     => $request->scheme_id,
                'branch_id'     => $request->branch_id,
                'batch_id'      => $request->batch_id,
                'semester_id'   => $request->semester_id,
                'marks_type'    => $marksType,
            ])
            ->whereNull('annual_semester_id');

            if ($request->filled('institute_id')) {
                $currentReappearMarkQuery->where(
                    'institute_id',
                    $request->institute_id
                );
            }

            $reappearMark = $currentReappearMarkQuery->first();

            /*
            |--------------------------------------------------------------------------
            | Previous Reappear Marks
            |--------------------------------------------------------------------------
            */

            $previousMark = null;

            foreach ($previousExamIds as $previousExamId) {

                $previousReappearQuery = ReappearEndSemMark::where([
                    'exam_id'       => $previousExamId,
                    'student_id'    => $student->id,
                    'subject_id'    => $request->subject_id,
                    'course_type'   => $request->course_type,
                    'course_id'     => $request->course_id,
                    'scheme_id'     => $request->scheme_id,
                    'branch_id'     => $request->branch_id,
                    'batch_id'      => $request->batch_id,
                    'semester_id'   => $request->semester_id,
                    'marks_type'    => $marksType,
                ])
                ->whereNull('annual_semester_id');

                if ($request->filled('institute_id')) {
                    $previousReappearQuery->where(
                        'institute_id',
                        $request->institute_id
                    );
                }

                $previousReappear = $previousReappearQuery->first();

                if ($previousReappear) {
                    $previousMark = $previousReappear;
                    break;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | If Previous Reappear Marks Not Found
            | Fetch Previous Regular End Sem Marks
            |--------------------------------------------------------------------------
            */

            $endSemMark = null;

            if (!$previousMark) {

                foreach ($previousExamIds as $previousExamId) {

                    $regularMarkQuery = EndSemMark::where([
                        'exam_id'       => $previousExamId,
                        'student_id'    => $student->id,
                        'subject_id'    => $request->subject_id,
                        'course_type'   => $request->course_type,
                        'course_id'     => $request->course_id,
                        'scheme_id'     => $request->scheme_id,
                        'branch_id'     => $request->branch_id,
                        'batch_id'      => $request->batch_id,
                        'semester_id'   => $request->semester_id,
                        'marks_type'    => $marksType,
                    ])
                    ->whereNull('annual_semester_id');

                    if ($request->filled('institute_id')) {
                        $regularMarkQuery->where(
                            'institute_id',
                            $request->institute_id
                        );
                    }

                    $endSemMark = $regularMarkQuery->first();

                    if ($endSemMark) {
                        break;
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Build Row
            |--------------------------------------------------------------------------
            */

            $row = new \stdClass();

            $row->student = $student;

            // Editable current reappear entry
            $row->reappear_mark = $reappearMark;

            // Previous reappear record, if available
            $row->previous_mark = $previousMark;

            // Regular reference marks, if no previous reappear exists
            $row->end_sem_mark = $endSemMark;

            $students->push($row);
        }

        /*
        |--------------------------------------------------------------------------
        | Return Result Blade
        |--------------------------------------------------------------------------
        */

        return view(
            'backend.result.reappear.reappear-result.render-result-sheet',
            compact(
                'request',
                'exam',
                'batch',
                'subject',
                'students',
                'maxMarks'
            )
        );
    }

    public function saveReappearMark(Request $request)
    {
        /* ================= VALIDATION ================= */

        $request->validate([
            'exam_id'        => 'required',
            'student_id'     => 'required',
            'subject_id'     => 'required',
            'course_type'    => 'required',
            'course_id'      => 'required',
            'branch_id'      => 'required',
            'batch_id'       => 'required',
            'institute_id'   => 'nullable',
            'marks_type'     => 'required',
            'max_marks'      => 'required|numeric|min:0',
            'obtained_marks' => 'nullable|numeric|min:0',
            'grace_marks'    => 'nullable|numeric',
            'exam_status'    => 'required|in:present,absent,umc,i/f',
        ]);

        /* ================= INSTITUTE ID ================= */

        $instituteId = $request->institute_id;

        if (empty($instituteId)) {
            $instituteId = Student::where('id', $request->student_id)
                ->value('institute_id');
        }

        /* ================= MARKS VALIDATION ================= */

        if (
            $request->exam_status == 'present' &&
            $request->obtained_marks !== null &&
            $request->obtained_marks > $request->max_marks
        ) {
            return response()->json([
                'status'  => false,
                'message' => 'Obtained marks cannot be greater than Maximum Marks (' . $request->max_marks . ').'
            ], 422);
        }

        /* ================= PREPARE DATA ================= */

        $data = [
            'exam_id'            => $request->exam_id,
            'student_id'         => $request->student_id,
            'subject_id'         => $request->subject_id,
            'course_type'        => $request->course_type,
            'course_id'          => $request->course_id,
            'branch_id'          => $request->branch_id,
            'batch_id'           => $request->batch_id,
            'semester_id'        => $request->semester_id,
            'annual_semester_id' => $request->annual_semester_id,
            'scheme_id'          => $request->scheme_id,
            'institute_id'       => $instituteId,
            'marks_type'         => $request->marks_type,
        ];

        /* ================= SAVE / UPDATE ================= */

        ReappearEndSemMark::updateOrCreate(
            $data,
            [
                'max_marks' => $request->max_marks,

                'obtained_marks' => $request->exam_status == 'present'
                    ? $request->obtained_marks
                    : null,

                'grace_marks' => $request->grace_marks,

                'exam_status' => $request->exam_status,

                'is_locked' => 0,
            ]
        );

        /* ================= RESPONSE ================= */

        return response()->json([
            'status'  => true,
            'message' => 'Saved Successfully'
        ]);
    }

    public function updateLockStatus(Request $request)
    {
        /* ================= VALIDATION ================= */

        $request->validate([
            'student_ids'  => 'required|array|min:1',
            'student_ids.*' => 'required',
            'is_locked'    => 'required|in:0,1',
            'exam_id'      => 'required',
            'subject_id'   => 'required',
            'course_type'  => 'required',
            'course_id'    => 'required',
            'branch_id'    => 'required',
            'batch_id'     => 'required',
            'institute_id' => 'nullable',
        ]);

        /* ================= BASE QUERY ================= */

        $query = ReappearEndSemMark::whereIn('student_id', $request->student_ids)
            ->where('exam_id', $request->exam_id)
            ->where('subject_id', $request->subject_id)
            ->where('course_type', $request->course_type)
            ->where('course_id', $request->course_id)
            ->where('branch_id', $request->branch_id)
            ->where('batch_id', $request->batch_id);

        /* ================= INSTITUTE FILTER ================= */

        if ($request->filled('institute_id')) {
            $query->where('institute_id', $request->institute_id);
        }

        /* ================= SCHEME FILTER ================= */

        if ($request->filled('scheme_id')) {
            $query->where('scheme_id', $request->scheme_id);
        }

        /* ================= SEMESTER FILTER ================= */

        if ($request->course_type == 'Semester Based') {

            $query->where('semester_id', $request->semester_id)
                ->whereNull('annual_semester_id');

        } else {

            $query->where('annual_semester_id', $request->annual_semester_id)
                ->whereNull('semester_id');
        }

        /* ================= UPDATE LOCK STATUS ================= */

        $updated = $query->update([
            'is_locked' => $request->is_locked
        ]);

        /* ================= RESPONSE ================= */

        return response()->json([
            'status'  => true,
            'message' => $updated . ' record(s) ' .
                ($request->is_locked == 1 ? 'locked' : 'unlocked') .
                ' successfully.'
        ]);
    }

}
