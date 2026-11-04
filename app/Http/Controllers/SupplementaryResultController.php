<?php

namespace App\Http\Controllers;

use App\Models\AcademicAdjustment;
use App\Models\AnnualSemester;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\EndSemMark;
use App\Models\Exam;
use App\Models\Institute;
use App\Models\MarksGrade;
use App\Models\MidSemMark;
use App\Models\PassPercentage;
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
use App\Models\SuppleStudentDetail;
use App\Models\SuppleSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplementaryResultController extends Controller
{
    public function suppleMappedSubject()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.supplement.assign-subjects', compact('courses', 'exams'));
    }

    public function fetchSuppleMappedSubject(Request $request)
    {
        $query = ResultGpSheet::with([
            'student:id,student_name,father_name,mother_name,university_roll_no',
            'subject:id,name,subject_code'
        ])
        // ->where('result', 'SUPPLE')
        ->when(
            $request->course_type == 'Semester Based',
            function ($q) {
                $q->where('result', 'SUPPLE');
            },
            function ($q) {
                $q->whereIn('result', ['FAIL', 'SUPPLE']);
            }
        )
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

        // Semester / Annual
        if ($request->course_type == 'Semester Based') {
            $query->where('semester_id', $request->semester_id);
        } else {
            $query->where('annual_semester_id', $request->annual_semester_id);
        }

        // Student filter
        if (!empty($request->student_roll_number)) {

            $query->whereHas('student', function ($q) use ($request) {
                $q->where('university_roll_no', $request->student_roll_number);
            });
        }

        $records = $query->get();

        // Student wise group
        $students = $records->groupBy('student_id');

        $existingSupple = SuppleStudentDetail::with('suppleSubjects')
        ->where('exam_id', $request->exam_id)
        ->where('course_type', $request->course_type)
        ->where('course_id', $request->course_id)
        ->where('branch_id', $request->branch_id)
        ->where('batch_id', $request->batch_id)
        ->where('institute_id', $request->institute_id)
        ->when($request->scheme_id, function ($q) use ($request) {
            $q->where('scheme_id', $request->scheme_id);
        })
        ->when($request->course_type == 'Semester Based', function ($q) use ($request) {
            $q->where('semester_id', $request->semester_id);
        }, function ($q) use ($request) {
            $q->where('annual_semester_id', $request->annual_semester_id);
        })
        ->get();

        $checkedSubjects = [];

        foreach ($existingSupple as $student) {

            $checkedSubjects[$student->student_id] =
                $student->suppleSubjects
                    ->pluck('subject_id')
                    ->toArray();
        }

        return view(
            'backend.result.supplement.fetch-data.student-supple-subjects',
            [
                'students'=>$students,
                'request'=>$request,
                'checkedSubjects' => $checkedSubjects,
            ]
        );
    }

    public function saveSuppleStudents(Request $request)
    {
        $request->validate([
            'exam_id' => 'required',
            'course_type' => 'required',
            'course_id' => 'required',
            'branch_id' => 'required',
            'batch_id' => 'required',
            'institute_id' => 'required',
            'subject_ids' => 'required|array',
        ]);

        DB::beginTransaction();

        try {

            foreach ($request->subject_ids as $studentId => $subjectIds) {

                // Agar kisi student ka koi subject select nahi hua
                if (empty($subjectIds) || count($subjectIds) == 0) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Create / Update Student Detail
                |--------------------------------------------------------------------------
                */

                $suppleStudent = SuppleStudentDetail::updateOrCreate(

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
                | Delete Old Subjects
                |--------------------------------------------------------------------------
                */

                SuppleSubject::where(
                    'supple_student_detail_id',
                    $suppleStudent->id
                )->delete();

                /*
                |--------------------------------------------------------------------------
                | Insert New Subjects
                |--------------------------------------------------------------------------
                */

                $insertData = [];

                foreach ($subjectIds as $subjectId) {

                    $insertData[] = [
                        'supple_student_detail_id' => $suppleStudent->id,
                        'subject_id'               => $subjectId,
                        'created_at'               => now(),
                        'updated_at'               => now(),
                    ];
                }

                SuppleSubject::insert($insertData);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Supplementary subjects saved successfully.'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function suppleResultPrepare()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.supplement.supple-result.index', compact('courses', 'exams'));
    }

    public function fetchSuppleResult(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Subject
        |--------------------------------------------------------------------------
        */

        $subject = Subject::findOrFail($request->subject_id);

        /*
        |--------------------------------------------------------------------------
        | Exam
        |--------------------------------------------------------------------------
        */

        $exam = Exam::findOrFail($request->exam_id);

        /*
        |--------------------------------------------------------------------------
        | Students who have selected this supplementary subject
        |--------------------------------------------------------------------------
        */

        $query = SuppleStudentDetail::with([
            'student:id,student_name,father_name,university_roll_no',
            'suppleSubjects'
        ])
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

        // Semester / Annual
        if ($request->course_type == 'Semester Based') {

            $query->where('semester_id', $request->semester_id);

        } else {

            $query->where('annual_semester_id', $request->annual_semester_id);
        }

        /*
        |--------------------------------------------------------------------------
        | Only those students who selected requested subject
        |--------------------------------------------------------------------------
        */

        $query->whereHas('suppleSubjects', function ($q) use ($request) {

            $q->where('subject_id', $request->subject_id);

        });

        $students = $query->get();

        /*
        |--------------------------------------------------------------------------
        | Fetch Locked End Semester Marks
        |--------------------------------------------------------------------------
        */

        $endSemMarks = EndSemMark::where('exam_id', $request->exam_id)
            ->where('subject_id', $request->subject_id)
            ->where('course_type', $request->course_type)
            ->where('course_id', $request->course_id)
            ->where('branch_id', $request->branch_id)
            ->where('batch_id', $request->batch_id)
            ->where('institute_id', $request->institute_id)
            ->where('marks_type', $request->marks_type)
            ->where('is_locked', 1)
            ->when(!empty($request->scheme_id), function ($q) use ($request) {

                $q->where('scheme_id', $request->scheme_id);

            })
            ->when(
                $request->course_type == 'Semester Based',

                function ($q) use ($request) {

                    $q->where('semester_id', $request->semester_id);

                },

                function ($q) use ($request) {

                    $q->where('annual_semester_id', $request->annual_semester_id);

                }
            )
            ->get()
            ->keyBy('student_id');


        // Supple Result data -----------------------
        $suppleMarks = SuppleEndSemMark::where('exam_id', $request->exam_id)
        ->where('subject_id', $request->subject_id)
        ->where('course_type', $request->course_type)
        ->where('course_id', $request->course_id)
        ->where('branch_id', $request->branch_id)
        ->where('batch_id', $request->batch_id)
        ->where('institute_id', $request->institute_id)
        ->where('marks_type', $request->marks_type)
        ->when(!empty($request->scheme_id), function ($q) use ($request) {
            $q->where('scheme_id', $request->scheme_id);
        })
        ->when(
            $request->course_type == 'Semester Based',
            fn($q) => $q->where('semester_id', $request->semester_id),
            fn($q) => $q->where('annual_semester_id', $request->annual_semester_id)
        )
        ->get()
        ->keyBy('student_id');

        /*
        |--------------------------------------------------------------------------
        | Attach Previous End Semester Marks
        |--------------------------------------------------------------------------
        */

        foreach ($students as $student) {

            $student->end_sem_mark = $endSemMarks[$student->student_id] ?? null;

            $student->supple_mark = $suppleMarks[$student->student_id] ?? null;

        }
        
        $maxMarks = $subject->external_marks;

        if ($request->course_type === 'Annual Based') {

            $maxMarks = match ($request->marks_type) {

                'theory_paper1'    => $subject->paper1,
                'theory_paper2'    => $subject->paper2,
                'practical_paper1' => $subject->practical_paper1,
                'practical_paper2' => $subject->practical_paper2,

                default => 0,
            };
        }

        /*
        |--------------------------------------------------------------------------
        | Render
        |--------------------------------------------------------------------------
        */

        return view(
            'backend.result.supplement.supple-result.render-result-sheet',
            [
                'students' => $students,
                'subject'  => $subject,
                'exam'     => $exam,
                'request'  => $request,
                'maxMarks' => $maxMarks,
            ]
        );
    }

    public function saveSuppleMark(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'exam_id' => 'required',
            'student_id' => 'required',
            'subject_id' => 'required',
            'course_type' => 'required',
            'course_id' => 'required',
            'branch_id' => 'required',
            'batch_id' => 'required',
            'institute_id' => 'required',
            'marks_type' => 'required',
            'max_marks' => 'required|numeric',
            'grace_marks' => 'nullable|numeric',
        ]);


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

        $data = [

            'exam_id' => $request->exam_id,

            'student_id' => $request->student_id,

            'subject_id' => $request->subject_id,

            'course_type' => $request->course_type,

            'course_id' => $request->course_id,

            'branch_id' => $request->branch_id,

            'batch_id' => $request->batch_id,

            'semester_id' => $request->semester_id,

            'annual_semester_id' => $request->annual_semester_id,

            'scheme_id' => $request->scheme_id,

            'institute_id' => $request->institute_id,

            'marks_type' => $request->marks_type,
        ];

        SuppleEndSemMark::updateOrCreate(

            $data,

            [

                'max_marks' => $request->max_marks,

                'obtained_marks' => $request->obtained_marks,

                'grace_marks'    => $request->grace_marks,

                'exam_status' => $request->exam_status,

                'is_locked' => 0,

            ]

        );

        return response()->json([
            'status' => true,
            'message' => 'Saved Successfully'
        ]);
    }

    public function updateLockStatus(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'is_locked' => 'required|in:0,1',
            'exam_id' => 'required',
            'subject_id' => 'required',
            'course_type' => 'required',
            'course_id' => 'required',
            'branch_id' => 'required',
            'batch_id' => 'required',
            'institute_id' => 'required',
        ]);

        $query = SuppleEndSemMark::whereIn('student_id', $request->student_ids)
            ->where('exam_id', $request->exam_id)
            ->where('subject_id', $request->subject_id)
            ->where('course_type', $request->course_type)
            ->where('course_id', $request->course_id)
            ->where('branch_id', $request->branch_id)
            ->where('batch_id', $request->batch_id)
            ->where('institute_id', $request->institute_id);

        if (!empty($request->scheme_id)) {
            $query->where('scheme_id', $request->scheme_id);
        }

        if ($request->course_type == 'Semester Based') {
            $query->where('semester_id', $request->semester_id);
        } else {
            $query->where('annual_semester_id', $request->annual_semester_id);
        }

        $updated = $query->update([
            'is_locked' => $request->is_locked
        ]);

        return response()->json([
            'status' => true,
            'message' => $updated . ' record(s) updated successfully.'
        ]);
    }

    public function suppleSubjectWiseResult()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.supplement.subject-wise.index', compact('courses', 'exams'));
    }
    
    public function fetchSuppleSubjectWiseResult(Request $request)
    {
        // dd($request->all());
        $examId            = $request->exam_id;
        $courseType        = $request->course_type;
        $courseId          = $request->course_id;
        $branchId          = $request->branch_id;
        $batchId           = $request->batch_id;
        $schemeId          = $request->scheme_id;
        $semesterId        = $request->semester_id;
        $annualSemesterId  = $request->annual_semester_id;
        $instituteId       = $request->institute_id;
        $subjectId         = $request->subject_id;
        $studentId         = $request->student_id ?? null;
        /*
        =============================
        BASIC DATA
        =============================
        */

        $exam    = Exam::find($examId);
        $course  = Course::find($courseId);
        $branch  = Branch::find($branchId);
        $batch   = Batch::find($batchId);
        $subject = Subject::find($subjectId);

        /*
        =============================
        PASS PERCENTAGE
        =============================
        */

        $passPercentage = PassPercentage::where('course_id', $courseId)->first();

        /*
        =============================
        SEMESTER
        =============================
        */

        if ($courseType == "Semester Based") {

            $semester  = Semester::find($semesterId);
            $className = $semester->name . " Semester";

        } else {

            $semester  = AnnualSemester::find($annualSemesterId);
            $className = $semester->name;
        }

        /*
        =============================
        SUPPLEMENTARY STUDENTS IDS
        =============================
        */

        $suppleStudentIds = SuppleStudentDetail::where('exam_id', $examId)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->where('institute_id', $instituteId)

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {

                $q->where('semester_id', $semesterId);

            }, function ($q) use ($annualSemesterId) {

                $q->where('annual_semester_id', $annualSemesterId);

            })

            ->whereHas('suppleSubjects', function ($q) use ($subjectId) {

                $q->where('subject_id', $subjectId);

            })

            ->pluck('student_id')
            ->toArray();

        /*
        =============================
        STUDENTS
        =============================
        */

        $students = Student::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->whereIn('id', $suppleStudentIds)

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            // ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {

            //     $q->where('semester_id', $semesterId)
            //     ->whereNull('annual_semester_id');

            // })

            // ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {

            //     $q->where('annual_semester_id', $annualSemesterId)
            //     ->whereNull('semester_id');

            // })

            ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

            ->when($studentId, fn($q) => $q->where('id', $studentId))

            ->orderBy('institute_id')
            ->orderBy('university_roll_no')
            ->get();

        /*
        =============================
        MID SEM MARKS
        =============================
        */

        $midSemMarks = MidSemMark::where('exam_id', $examId)
            ->where('subject_id', $subjectId)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->where('is_locked', 1)
            ->where('university_forword', 1)

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($studentId, fn($q) => $q->where('student_id', $studentId))

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {

                $q->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');

            })

            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {

                $q->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id');

            })

            ->get()
            ->groupBy(['student_id', 'marks_type']);

        /*
        =============================
        END SEM MARKS
        =============================
        */

        if ($courseType == 'Annual Based') {

            /*
            --------------------------------
            Annual : Multiple marks_type
            --------------------------------
            */

            $endSemMarks = EndSemMark::where('exam_id', $examId)
                ->where('subject_id', $subjectId)
                ->where('course_type', $courseType)
                ->where('course_id', $courseId)
                ->where('branch_id', $branchId)
                ->where('batch_id', $batchId)
                ->where('institute_id', $instituteId)
                ->where('is_locked', 1)

                ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

                ->when($studentId, fn($q) => $q->where('student_id', $studentId))

                ->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id')

                ->get()
                ->groupBy(['student_id', 'marks_type']);

        } else {

            /*
            --------------------------------
            Semester : Existing Logic
            --------------------------------
            */

            // $endSemMarks = EndSemMark::where('exam_id', $examId)
            //     ->where('subject_id', $subjectId)
            //     ->where('is_locked', 1)

            //     ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            //     ->when($studentId, fn($q) => $q->where('student_id', $studentId))

            //     ->where('semester_id', $semesterId)
            //     ->whereNull('annual_semester_id')

            //     ->get()
            //     ->keyBy('student_id');

            $query = EndSemMark::where('exam_id', $examId)
                ->where('subject_id', $subjectId)
                ->where('is_locked', 1)

                ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

                ->when($studentId, fn($q) => $q->where('student_id', $studentId))

                ->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');

            if ($course->course == 'Diploma') {

                $endSemMarks = $query
                    ->get()
                    ->groupBy(['student_id', 'marks_type']);

            } else {


                $endSemMarks = $query
                    ->get()
                    ->keyBy('student_id');
            }
        }

        /*
        =============================
        SUPPLEMENTARY END SEM MARKS
        =============================
        */

        $suppleEndSemMarks = SuppleEndSemMark::where('exam_id', $examId)
            ->where('subject_id', $subjectId)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->where('institute_id', $instituteId)
            ->where('is_locked', 1)

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($studentId, fn($q) => $q->where('student_id', $studentId))

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {

                $q->where('semester_id', $semesterId);

            }, function ($q) use ($annualSemesterId) {

                $q->where('annual_semester_id', $annualSemesterId);

            })

            ->get()
            ->groupBy(['student_id', 'marks_type']);

        /*
        =============================
        ACADEMIC ADJUSTMENTS
        =============================
        */

        $academicAdjustments = AcademicAdjustment::where('exam_id', $examId)

            ->where('course_type', $courseType)

            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

            ->where('subject_id', $subjectId)

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($studentId, fn($q) => $q->where('student_id', $studentId))

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {

                $q->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');

            })

            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {

                $q->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id');

            })

            ->get();

        $adjustmentMarks = [];

        foreach ($academicAdjustments as $adjustment) {

            if ($courseType == 'Annual Based') {

                $adjustmentMarks
                    [$adjustment->student_id]
                    [$adjustment->marks_type]
                    = $adjustment->adjustment_marks;

            } else {

                $adjustmentMarks
                    [$adjustment->student_id]
                    [$adjustment->marks_type]
                    = $adjustment->adjustment_marks;
            }
        }

        /*
        =============================
        STUDENT SUBJECTS
        =============================
        */

        $studentSubjects = StudentSubject::where([
            'subject_id'  => $subjectId,
            'course_type' => $courseType,
            'course_id'   => $courseId,
            'branch_id'   => $branchId,
        ])

        ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

        ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {

            $q->where('semester_id', $semesterId);

        }, function ($q) use ($annualSemesterId) {

            $q->where('annual_semester_id', $annualSemesterId);

        })

        ->pluck('student_id')
        ->toArray();

        /*
        =============================
        VIEW
        =============================
        */

        $view = $courseType == 'Annual Based'
            ? 'backend.result.supplement.subject-wise.annual-result'
            : 'backend.result.supplement.subject-wise.result';

        return view(
            $view,
            compact(
                'exam',
                'course',
                'branch',
                'batch',
                'subject',
                'students',
                'midSemMarks',
                'endSemMarks',
                'className',
                'passPercentage',
                'studentSubjects',
                'adjustmentMarks',
                'suppleEndSemMarks',
                'suppleStudentIds',
            )
        )->render();
    }
    
    public function suppleOverallResult()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.supplement.overall-result.index', compact('courses', 'exams'));
    }


    public function fetchSuppleOverallResult(Request $request)
    {
        $examId           = $request->exam_id;
        $courseType       = $request->course_type;
        $courseId         = $request->course_id;
        $branchId         = $request->branch_id;
        $batchId          = $request->batch_id;
        $schemeId         = $request->scheme_id;
        $semesterId       = $request->semester_id;
        $annualSemesterId = $request->annual_semester_id;
        $instituteId      = $request->institute_id;

        /*
        =============================
        BASIC DATA
        =============================
        */
        $grades = MarksGrade::where('course_id', $courseId)->get();
        $exam   = Exam::find($examId);
        $course = Course::find($courseId);
        $branch = Branch::find($branchId);
        $batch  = Batch::find($batchId);

        /*
        =============================
        PASS %
        =============================
        */
        $passPercentage     = PassPercentage::where('course_id', $courseId)->first();
        $overallPassPercent = $passPercentage->overall_pass_percentage ?? 50;

        /*
        =============================
        SEM / ANNUAL NAME
        =============================
        */
        if ($courseType == 'Semester Based') {
            $semester  = Semester::find($semesterId);
            $className = $semester->name . ' Semester';
        } else {
            $semester  = AnnualSemester::find($annualSemesterId);
            $className = $semester->name;
        }

        /*
        =============================
        SUBJECTS
        =============================
        */
        $subjects = Subject::where('course_id', $courseId)
            ->where('branch_id', $branchId)
            // ->where(function ($query) {
            //     $query->where('is_nc', 0)
            //           ->orWhereNull('is_nc');
            // })

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');
            })

            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                $q->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id');
            })

            ->orderBy('id')
            ->get();

        /*
        =============================
        ONLY SUPPLEMENTARY STUDENTS
        =============================
        */
        // $suppleStudentIds = SuppleStudentDetail::where('exam_id', $examId)
        //     ->where('course_type', $courseType)
        //     ->where('course_id', $courseId)
        //     ->where('branch_id', $branchId)
        //     ->where('batch_id', $batchId)

        //     ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

        //     ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

        //     ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
        //         $q->where('semester_id', $semesterId);
        //     })

        //     ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
        //         $q->where('annual_semester_id', $annualSemesterId);
        //     })

        //     ->pluck('student_id')
        //     ->unique()
        //     ->toArray();

        /*
        =============================
        STUDENTS
        =============================
        */
        $students = Student::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            // ->whereIn('id', $suppleStudentIds)

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

            ->orderBy('institute_id')
            ->orderBy('university_roll_no')
            ->get();

        /*
        =============================
        STUDENT SUBJECTS
        =============================
        */

        $studentSubjects = StudentSubject::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');
            })

            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                $q->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id');
            })

            ->get()
            ->groupBy('student_id');

        /*
        =============================
        ORIGINAL RESULT GP SHEET
        =============================
        */
        $resultGpSheet = ResultGpSheet::where('exam_id', $examId)

            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');
            })

            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                $q->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id');
            })

            ->get()
            ->groupBy(['student_id', 'subject_id']);

        /*
        =============================
        CURRENT SUPPLE RESULT GP SHEET
        =============================
        */

        $suppleResultGpSheet = SuppleResultGpSheet::where('exam_id', $examId)

            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

            ->when($schemeId, fn($q) => $q->where('scheme_id',$schemeId))
            ->when($instituteId, fn($q) => $q->where('institute_id',$instituteId))

            ->when($courseType == 'Semester Based', function($q) use($semesterId){
                $q->where('semester_id',$semesterId)
                ->whereNull('annual_semester_id');
            })

            ->when($courseType == 'Annual Based', function($q) use($annualSemesterId){
                $q->where('annual_semester_id',$annualSemesterId)
                ->whereNull('semester_id');
            })

            ->get()
            ->groupBy(['student_id','subject_id']);

        /*
        =============================
        SUPPLE SUBJECT MAP
        =============================
        */
        $suppleRows = SuppleStudentDetail::with('suppleSubjects')

            ->where('exam_id', $examId)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

            ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            })

            // ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
            //     $q->where('annual_semester_id', $annualSemesterId);
            // })
            
            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                $q->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id');
            })

            ->get();

        $suppleSubjectMap = [];

        foreach ($suppleRows as $row) {

            foreach ($row->suppleSubjects as $subject) {

                $suppleSubjectMap[$row->student_id][$subject->subject_id] = true;
            }
        }

        /*
        =============================
        MID SEM MARKS
        =============================
        */
        $midSemMarks = MidSemMark::where('exam_id', $examId)

            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->where('is_locked', 1)
            ->where('university_forword', 1)

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');
            })

            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                $q->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id');
            })

            ->get()
            ->groupBy(['student_id', 'subject_id', 'marks_type']);

        /*
        =============================
        SUPPLEMENTARY END SEM MARKS
        =============================
        */
        $suppleEndSemMarks = SuppleEndSemMark::where('exam_id', $examId)

            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->where('is_locked', 1)

            ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');
            })

            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                $q->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id');
            })

            ->get()
            ->groupBy(['student_id', 'subject_id', 'marks_type']);


        /*
        =============================
        PREVIOUS SEMESTER GP SHEET
        =============================
        */

        /*
        ==========================================================
        PREVIOUS SEMESTER GP SHEET
        Priority:
        1. SuppleResultGpSheet
        2. ResultGpSheet
        ==========================================================
        */

        $previousGpSheets = collect();

        $missingPublishedSemesters = [];

        $previousSemesterSummary = [];

        if ($courseType == 'Semester Based' && $semesterId > 1) {

            $requiredPreviousSemesters = range(1, $semesterId - 1);

            foreach ($requiredPreviousSemesters as $prevSemester) {

                foreach ($students as $student) {

                    /*
                    ----------------------------------------
                    Check Supplementary Summary Exists
                    ----------------------------------------
                    */

                    $suppleSummaryExists = SuppleResultSummary::where([
                        'student_id'  => $student->id,
                        'course_type' => $courseType,
                        'course_id'   => $courseId,
                        'branch_id'   => $branchId,
                        'batch_id'    => $batchId,
                        'semester_id' => $prevSemester,
                    ])
                    ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))
                    ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))
                    ->exists();

                    /*
                    ----------------------------------------
                    Check Regular Summary Exists
                    ----------------------------------------
                    */

                    $regularSummaryExists = StudentResultSummary::where([
                        'student_id'  => $student->id,
                        'course_type' => $courseType,
                        'course_id'   => $courseId,
                        'branch_id'   => $branchId,
                        'batch_id'    => $batchId,
                        'semester_id' => $prevSemester,
                    ])
                    ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))
                    ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))
                    ->exists();
                    /*
                    ----------------------------------------
                    First Preference : Supplementary GP Sheet
                    ----------------------------------------
                    */

                    $suppleRows = SuppleResultGpSheet::with('subject')->where([
                        'student_id'   => $student->id,
                        'course_type'  => $courseType,
                        'course_id'    => $courseId,
                        'branch_id'    => $branchId,
                        'batch_id'     => $batchId,
                        'semester_id'  => $prevSemester,
                    ])

                    ->when($schemeId, fn($q)=>$q->where('scheme_id',$schemeId))
                    ->when($instituteId, fn($q)=>$q->where('institute_id',$instituteId))

                    ->get();

                    /*
                    ----------------------------------------
                    Check Student was Enrolled in Previous Semester
                    ----------------------------------------
                    */

                    $previousSemesterEnrolled = StudentSubject::where([
                        'student_id'  => $student->id,
                        'course_type' => $courseType,
                        'course_id'   => $courseId,
                        'branch_id'   => $branchId,
                        'semester_id' => $prevSemester,
                    ])

                    ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

                    ->exists();

                    /*
                    ----------------------------------------
                    Did student appear in Supplementary?
                    ----------------------------------------
                    */

                    $previousSuppleApplied = SuppleStudentDetail::where([
                        'student_id'   => $student->id,
                        'course_type'  => $courseType,
                        'course_id'    => $courseId,
                        'branch_id'    => $branchId,
                        'batch_id'     => $batchId,
                        'semester_id'  => $prevSemester,
                    ])
                    ->when($schemeId, fn($q)=>$q->where('scheme_id',$schemeId))
                    ->when($instituteId, fn($q)=>$q->where('institute_id',$instituteId))
                    ->exists();

                    if (!$previousSemesterEnrolled) {
                        continue;
                    }

                    if($suppleSummaryExists && $suppleRows->isNotEmpty()){

                        foreach($suppleRows as $row){

                            $previousGpSheets->push($row);

                        }

                        continue;

                    }

                    /*
                    ----------------------------------------
                    Second Preference : Regular GP Sheet
                    ----------------------------------------
                    */

                    $regularRows = ResultGpSheet::with('subject')->where([
                        'student_id'   => $student->id,
                        'course_type'  => $courseType,
                        'course_id'    => $courseId,
                        'branch_id'    => $branchId,
                        'batch_id'     => $batchId,
                        'semester_id'  => $prevSemester,
                    ])

                    ->when($schemeId, fn($q)=>$q->where('scheme_id',$schemeId))
                    ->when($instituteId, fn($q)=>$q->where('institute_id',$instituteId))

                    ->get();

                    if($regularSummaryExists && $regularRows->isNotEmpty()){

                        foreach($regularRows as $row){

                            $previousGpSheets->push($row);

                        }

                    }else{

                        // $missingPublishedSemesters[$prevSemester] = $prevSemester;
                        // $missingPublishedSemesters[$student->id][$prevSemester] = $prevSemester;
                        if (

                            $previousSemesterEnrolled &&

                            $previousSuppleApplied &&

                            !$suppleSummaryExists

                        ) {

                            $missingPublishedSemesters[$student->id][$prevSemester] = $prevSemester;

                        }

                    }

                }

            }

            // $missingPublishedSemesters = array_values($missingPublishedSemesters);

            $previousGpSheets = $previousGpSheets->groupBy('student_id');



            foreach ($previousGpSheets as $studentId => $rows) {

                $creditHours = 0;
                $weightedGp = 0;

                foreach ($rows as $row) {
                    
                    $isNc = (int) optional($row->subject)->is_nc === 1;

                    if ($isNc) {
                        continue;
                    }

                    $creditHours += $row->credits;

                    $gp = in_array($row->result, ['FAIL', 'SUPPLE'])
                        ? 0
                        : $row->result_gp;

                    $weightedGp += ($row->credits * $gp);
                }

                $previousSemesterSummary[$studentId] = [
                    'credits'     => $creditHours,
                    'weighted_gp' => $weightedGp,
                ];
            }

        }

        /*
        =============================
        VIEW
        =============================
        */
        $view = $courseType == 'Annual Based'
            ? 'backend.result.supplement.overall-result.annual-result'
            : 'backend.result.supplement.overall-result.result';

        return view(
            $view,
            compact(
                'exam',
                'courseType',
                'course',
                'schemeId',
                'branch',
                'batch',
                'semesterId',
                'annualSemesterId',
                'instituteId',
                'subjects',
                'className',
                'students',
                'studentSubjects',
                'resultGpSheet',
                'suppleSubjectMap',
                'midSemMarks',
                'suppleEndSemMarks',
                'overallPassPercent',
                'grades',
                'previousGpSheets',
                'missingPublishedSemesters',
                'suppleResultGpSheet',
                'previousSemesterSummary',
            )
        )->render();
    }
    

    public function saveSuppleOverallResult(Request $request)
    {
        DB::beginTransaction();

        try {

            $summaryData = json_decode($request->summary_data, true) ?? [];
            $gpSheetData = json_decode($request->gp_sheet_data, true) ?? [];

            // dd($gpSheetData);

            /*
            |--------------------------------------------------------------------------
            | Student Result Summary
            |--------------------------------------------------------------------------
            */

            foreach ($summaryData as $row) {

                SuppleResultSummary::updateOrCreate(

                    [

                        'student_id'         => $row['student_id'],
                        'exam_id'            => $row['exam_id'],
                        'course_type'        => $row['course_type'],
                        'course_id'          => $row['course_id'],
                        'scheme_id'          => $row['scheme_id'],
                        'branch_id'          => $row['branch_id'],
                        'semester_id'        => $row['semester_id'],
                        'annual_semester_id' => $row['annual_semester_id'],
                        'batch_id'           => $row['batch_id'],
                        'institute_id'       => $row['institute_id'],

                    ],

                    [

                        'gpa'             => $row['gpa'],
                        'cgpa'            => $row['cgpa'],
                        'result_status'   => $row['result_status'],
                        'grade_status'    => $row['grade_status'],
                        'remarks'         => $row['remarks'],
                        'is_supple'       => $row['is_supple'] ?? 0,
                        'result_declare'  => 0,

                    ]

                );

            }

            /*
            |--------------------------------------------------------------------------
            | GP Sheet
            |--------------------------------------------------------------------------
            */

            foreach ($gpSheetData as $row) {

                SuppleResultGpSheet::updateOrCreate(

                    [

                        'student_id'         => $row['student_id'],
                        'subject_id'         => $row['subject_id'],
                        'exam_id'            => $row['exam_id'],
                        'course_type'        => $row['course_type'],
                        'course_id'          => $row['course_id'],
                        'scheme_id'          => $row['scheme_id'],
                        'branch_id'          => $row['branch_id'],
                        'semester_id'        => $row['semester_id'],
                        'annual_semester_id' => $row['annual_semester_id'],
                        'batch_id'           => $row['batch_id'],
                        'institute_id'       => $row['institute_id'],

                    ],

                    [

                        'credits'    => $row['credits'],
                        'result'     => $row['result'],
                        'result_gp'  => $row['result_gp'],
                        'grade'      => $row['grade'],
                        'is_supple'  => $row['is_supple'] ?? 0,

                    ]

                );

            }

            DB::commit();

            return response()->json([

                'status'=>true,
                'message'=>'Supplementary Result Saved Successfully.'

            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([

                'status'=>false,
                'message'=>$e->getMessage(),
                'line'=>$e->getLine()

            ],500);

        }
    }
    
    public function suppleResultOverview(){
        $exams = Exam::orderBy('id', 'desc')->get();
        $courses = Course::all();
        return view('backend.result.supplement.result-overview.index', compact('exams', 'courses'));
    }

    public function fetchSuppleResultOverview(Request $request)
    {
        // dd($request->all());
        /*
        =============================
        GET REQUEST DATA
        =============================
        */
        $examId            = $request->exam_id;
        $courseType        = $request->course_type;
        $courseId          = $request->course_id;
        $branchId          = $request->branch_id;
        $batchId           = $request->batch_id;
        $schemeId          = $request->scheme_id;
        $semesterId        = $request->semester_id;
        $annualSemesterId  = $request->annual_semester_id;
        $instituteId       = $request->institute_id;
        $rollNo            = $request->student_roll_number;

        /*
        =============================
        STUDENT ID FROM ROLL NUMBER
        =============================
        */
        $studentId = null;

        if ($rollNo) {
            $student = Student::where('university_roll_no', $rollNo)
                ->first();

            if ($student) {
                $studentId = $student->id;
            }
        }

        /*
        =============================
        BASIC DATA
        =============================
        */
        $grades = MarksGrade::where('course_id', $courseId)->get();
        $exam    = Exam::find($examId);
        $course  = Course::find($courseId);
        $branch  = Branch::find($branchId);
        $batch   = Batch::find($batchId);
        $institute = Institute::find($instituteId);
        $scheme = $schemeId ? Scheme::find($schemeId) : null;

        /*
        =============================
        SEMESTER / CLASS NAME
        =============================
        */
        if ($courseType == "Semester Based") {
            $semester  = Semester::find($semesterId);
            $className = $semester->name . " Semester";
        } else {
            $semester  = AnnualSemester::find($annualSemesterId);
            $className = $semester->name;
        }

        /*
        ==========================================================
        LOAD SUPPLE RESULT OF SAME FILTER
        ==========================================================
        */

        $results = SuppleResultSummary::with([
            'student',
            'course',
            'branch',
            'scheme',
            'batch',
            'institute'
        ])
        ->where('exam_id', $examId)
        ->where('course_type', $courseType)
        ->where('course_id', $courseId)
        ->where('branch_id', $branchId)
        ->where('batch_id', $batchId)

        ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

        ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
            $q->where('semester_id', $semesterId)
            ->whereNull('annual_semester_id');
        })

        ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
            $q->where('annual_semester_id', $annualSemesterId)
            ->whereNull('semester_id');
        })

        ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

        ->when($studentId, fn($q) => $q->where('student_id', $studentId))

        ->orderBy('student_id')
        ->get();

    foreach ($results as $row) {
        $row->supple_id = $row->id;
        $row->is_supple = true;
    }




        /*
        =============================
        RETURN VIEW
        =============================
        */
        // dd($results);

        return view(
            'backend.result.supplement.result-overview.result',
            compact(
                'results',
                'exam',
                'course',
                'branch',
                'scheme',
                'batch',
                'schemeId',
                'institute',
                'className',
                'grades'
            )
        )->render();
    }

    public function declareSuppleResult(Request $request)
    {
        try {

            DB::beginTransaction();

            $ids = $request->ids;

            if (empty($ids)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No data selected'
                ], 400);
            }

            SuppleResultSummary::whereIn('id', $ids)
                ->update([
                    'result_declare' => $request->result_declare
                ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Supplementary result status updated successfully!'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update supplementary result status'
                // 'error' => $e->getMessage(), // Uncomment for debugging if needed
            ], 500);
        }
    }
    

}
