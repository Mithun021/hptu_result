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

    public function reappearOverallResult()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.reappear.overall-result.index', compact('courses', 'exams'));
    }

    public function fetchReappearOverallResult(Request $request)
    {
        $examId       = (int) $request->exam_id;
        $courseType   = $request->course_type;
        $courseId     = (int) $request->course_id;
        $schemeId     = $request->scheme_id ? (int) $request->scheme_id : null;
        $branchId     = (int) $request->branch_id;
        $semesterId   = (int) $request->semester_id;
        $batchId     = (int) $request->batch_id;

        $instituteId = $request->institute_id !== null && $request->institute_id !== ''
            ? (int) $request->institute_id
            : null;

        if ($courseType !== 'Semester Based') {
            return response('Only Semester Based result is supported.', 422);
        }

        /* BASIC DATA */
        $exam = \App\Models\Exam::findOrFail($examId);
        $course = \App\Models\Course::findOrFail($courseId);
        $branch = \App\Models\Branch::findOrFail($branchId);
        $scheme = \App\Models\Scheme::findOrFail($schemeId);
        $batch = \App\Models\Batch::findOrFail($batchId);
        $semester = \App\Models\Semester::findOrFail($semesterId);

        $className = ($semester->name ?? '') . ' Semester';

        /* PASS PERCENTAGE */
        $passPercentage = \App\Models\PassPercentage::where('course_id', $courseId)
            ->where('scheme_id', $schemeId)
            ->first();

        $overallPassPercent = (float) ($passPercentage->overall_pass_percentage ?? 40);
        $percentageType = $passPercentage->percentage_type ?? 'Separate';

        /* SUBJECTS */
        $subjects = \App\Models\Subject::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('scheme_id', $schemeId)
            ->where('semester_id', $semesterId)
            ->whereNull('annual_semester_id')
            ->orderBy('orders')
            ->orderBy('id')
            ->get();

        $subjectIds = $subjects->pluck('id')->toArray();

        /* GRADES */
        $grades = \App\Models\MarksGrade::where('course_id', $courseId)
            ->where('scheme_id', $schemeId)
            ->orderBy('percent_from')
            ->get();

        /* REAPPEAR STUDENTS */
        $reappearDetails = \App\Models\ReappearStudentDetail::with([
            'student',
            'reappearSubjects',
        ])
            ->where('exam_id', $examId)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('scheme_id', $schemeId)
            ->where('semester_id', $semesterId)
            ->where('batch_id', $batchId)
            ->when($instituteId !== null, function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            })
            ->get()
            ->filter(fn($detail) => $detail->student !== null)
            ->unique('student_id')
            ->values();

        $students = $reappearDetails->pluck('student')
            ->filter()
            ->unique('id')
            ->sortBy('university_roll_no')
            ->values();

        $studentIds = $students->pluck('id')->toArray();

        /* STUDENT SUBJECTS */
        $studentSubjectRows = \App\Models\StudentSubject::whereIn('student_id', $studentIds)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('scheme_id', $schemeId)
            ->where('semester_id', $semesterId)
            ->whereNull('annual_semester_id')
            ->whereIn('subject_id', $subjectIds)
            ->get();

        $studentSubjectsMap = $studentSubjectRows
            ->groupBy('student_id')
            ->map(fn($rows) => $rows->pluck('subject_id')->unique()->values());

        /* REAPPEAR APPLIED SUBJECT MAP */
        $appliedSubjectMap = [];

        foreach ($reappearDetails as $detail) {
            $appliedSubjectMap[$detail->student_id] =
                $detail->reappearSubjects->pluck('subject_id')
                    ->unique()
                    ->values()
                    ->toArray();
        }

        /* INSTITUTES */
        $institutes = \App\Models\Institute::whereIn(
            'id',
            $students->pluck('institute_id')->filter()->unique()
        )->pluck('name', 'id');

        /* INTERNAL MARKS - NO exam_id FILTER */
        $midSemMarks = \App\Models\MidSemMark::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('scheme_id', $schemeId)
            ->where('semester_id', $semesterId)
            ->where('batch_id', $batchId)
            ->where('marks_type', 'internal')
            ->whereIn('student_id', $studentIds)
            ->whereIn('subject_id', $subjectIds)
            ->orderByDesc('id')
            ->get()
            ->groupBy('student_id')
            ->map(fn($rows) => $rows->unique('subject_id')->keyBy('subject_id'));

        /* CURRENT REAPPEAR MARKS */
        $currentReappearMarks = \App\Models\ReappearEndSemMark::where('exam_id', $examId)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('scheme_id', $schemeId)
            ->where('semester_id', $semesterId)
            ->where('batch_id', $batchId)
            ->where('marks_type', 'external')
            ->whereIn('student_id', $studentIds)
            ->whereIn('subject_id', $subjectIds)
            ->when($instituteId !== null, function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            })
            ->get()
            ->groupBy('student_id')
            ->map(fn($rows) => $rows->keyBy('subject_id'));

        /*
        |--------------------------------------------------------------------------
        | PREVIOUS EXAM SESSION IDS
        |--------------------------------------------------------------------------
        | Helper returns exam sessions chronologically (oldest to newest).
        | Reverse them to prioritize newest previous session first.
        */
        $previousExamIds = getPreviousExamSessionIds($examId);
        $previousExamIds = array_reverse($previousExamIds);

        /* PREVIOUS REAPPEAR MARKS */
        $previousReappearMarks = collect();

        if (!empty($previousExamIds)) {

            $previousReappearMarks = \App\Models\ReappearEndSemMark::whereIn(
                'exam_id',
                $previousExamIds
            )
                ->where('course_type', $courseType)
                ->where('course_id', $courseId)
                ->where('branch_id', $branchId)
                ->where('scheme_id', $schemeId)
                ->where('semester_id', $semesterId)
                ->where('batch_id', $batchId)
                ->where('marks_type', 'external')
                ->whereIn('student_id', $studentIds)
                ->whereIn('subject_id', $subjectIds)
                ->when($instituteId !== null, function ($q) use ($instituteId) {
                    $q->where('institute_id', $instituteId);
                })
                ->get();

            $examOrder = array_flip($previousExamIds);

            $previousReappearMarks = $previousReappearMarks
                ->groupBy('student_id')
                ->map(function ($studentRows) use ($examOrder) {
                    return $studentRows->groupBy('subject_id')
                        ->map(function ($subjectRows) use ($examOrder) {
                            return $subjectRows
                                ->sortBy(function ($row) use ($examOrder) {
                                    return $examOrder[$row->exam_id] ?? PHP_INT_MAX;
                                })
                                ->values();
                        });
                });
        }

        /*
        |--------------------------------------------------------------------------
        | REGULAR EXTERNAL MARKS - FALLBACK
        |--------------------------------------------------------------------------
        | IMPORTANT:
        | Do NOT use the current Reappear exam ID here.
        |
        | Use previous exam session IDs to retrieve Regular EndSem marks.
        | The exam session ordering comes from getPreviousExamSessionIds()
        | (month/year chronology), NOT numeric exam_id ordering.
        |
        | Current Reappear -> Previous Reappear -> Regular EndSem fallback
        */
        $regularExternalMarks = collect();

        if (!empty($previousExamIds)) {

            $regularExternalRows = \App\Models\EndSemMark::whereIn(
                'exam_id',
                $previousExamIds
            )
                ->where('course_type', $courseType)
                ->where('course_id', $courseId)
                ->where('branch_id', $branchId)
                ->where('scheme_id', $schemeId)
                ->where('semester_id', $semesterId)
                ->where('batch_id', $batchId)
                ->where('marks_type', 'external')
                ->whereIn('student_id', $studentIds)
                ->whereIn('subject_id', $subjectIds)
                ->when($instituteId !== null, function ($q) use ($instituteId) {
                    $q->where('institute_id', $instituteId);
                })
                ->get();

            /*
            * $previousExamIds is already newest-first.
            * Map its chronology for sorting Regular marks.
            */
            $regularExamOrder = array_flip($previousExamIds);

            /*
            * For each student + subject:
            * Sort by chronological priority and take the first.
            * Only Regular EndSemMark records are used here.
            */
            $regularExternalMarks = $regularExternalRows
                ->groupBy('student_id')
                ->map(function ($studentRows) use ($regularExamOrder) {
                    return $studentRows->groupBy('subject_id')
                        ->map(function ($subjectRows) use ($regularExamOrder) {
                            return $subjectRows
                                ->sortBy(function ($row) use ($regularExamOrder) {
                                    return $regularExamOrder[$row->exam_id] ?? PHP_INT_MAX;
                                })
                                ->values()
                                ->first();
                        });
                });
        }

        /* PREVIOUS SEMESTER GP SHEETS */
        $previousGpSheets = collect();
        $previousReappearGpSheets = collect();

        $previousSemesters = $semesterId > 1
            ? range(1, $semesterId - 1)
            : [];

        $reappearExamIds = array_values(array_unique(
            array_merge($previousExamIds, [$examId])
        ));

        if (!empty($previousSemesters)) {

            $previousGpSheets = \App\Models\ResultGpSheet::where('course_type', $courseType)
                ->where('course_id', $courseId)
                ->where('branch_id', $branchId)
                ->where('scheme_id', $schemeId)
                ->where('batch_id', $batchId)
                ->whereIn('semester_id', $previousSemesters)
                ->whereIn('exam_id', $previousExamIds)
                ->when($instituteId !== null, function ($q) use ($instituteId) {
                    $q->where('institute_id', $instituteId);
                })
                ->get()
                ->groupBy('student_id');

            $previousReappearGpSheets = \App\Models\ReappearResultGpSheet::where('course_type', $courseType)
                ->where('course_id', $courseId)
                ->where('branch_id', $branchId)
                ->where('scheme_id', $schemeId)
                ->where('batch_id', $batchId)
                ->whereIn('semester_id', $previousSemesters)
                ->whereIn('exam_id', $reappearExamIds)
                ->when($instituteId !== null, function ($q) use ($instituteId) {
                    $q->where('institute_id', $instituteId);
                })
                ->get()
                ->groupBy('student_id');
        }

        /* GRADE HELPER */
        $getGrade = function ($percentage) use ($grades) {
            return $grades->first(function ($grade) use ($percentage) {
                return $percentage >= (float) $grade->percent_from
                    && $percentage <= (float) $grade->percent_upto;
            });
        };

        /* MARK VALUE */
        $markValue = function ($mark) {
            if (!$mark) {
                return null;
            }

            if (strtolower(trim($mark->exam_status ?? '')) !== 'present') {
                return 0;
            }

            return (float) ($mark->obtained_marks ?? 0)
                + (float) ($mark->grace_marks ?? 0);
        };

        /* SPECIAL STATUS */
        $specialStatus = function ($mark) {
            if (!$mark) {
                return '';
            }

            $status = strtoupper(trim($mark->exam_status ?? ''));

            return in_array($status, ['AB', 'UMC', 'I/F']) ? $status : '';
        };

        /* RETOTAL VALUE */
        $retotalValue = function ($normal, $retotal) {
            if ($retotal !== null && $retotal !== '' && (float) $retotal != 0) {
                return (float) $retotal;
            }

            return (float) ($normal ?? 0);
        };

        /* SUBJECTS WITH MARKS */
        $displaySubjectIds = [];

        foreach ($subjects as $subject) {
            foreach ($students as $student) {

                $internalRecord = $midSemMarks->get($student->id, collect())
                    ->get($subject->id);

                $regularRecord = $regularExternalMarks
                    ->get($student->id, collect())
                    ->get($subject->id);

                $currentRecord = $currentReappearMarks
                    ->get($student->id, collect())
                    ->get($subject->id);

                $previousRecords = $previousReappearMarks
                    ->get($student->id, collect())
                    ->get($subject->id, collect());

                if ($internalRecord || $regularRecord || $currentRecord || $previousRecords->isNotEmpty()) {
                    $displaySubjectIds[] = $subject->id;
                    break;
                }
            }
        }

        $filteredSubjects = $subjects
            ->whereIn('id', $displaySubjectIds)
            ->values();

        /* CURRENT REAPPEAR MARKS MAP FOR BLADE */
        $reappearCurrentMarksMap = [];

        foreach ($students as $student) {
            $reappearCurrentMarksMap[$student->id] = $currentReappearMarks
                ->get($student->id, collect())
                ->keyBy('subject_id');
        }

        /* PREPARE STUDENT MARKS */
        $marks = [];

        foreach ($students as $student) {

            $studentId = $student->id;

            $studentInternal = $midSemMarks
                ->get($studentId, collect())
                ->keyBy('subject_id');

            $studentRegularExternal = $regularExternalMarks
                ->get($studentId, collect());

            $studentCurrentReappearExternal = $currentReappearMarks
                ->get($studentId, collect())
                ->keyBy('subject_id');

            $studentPreviousReappearExternal = $previousReappearMarks
                ->get($studentId, collect());

            $studentSubjectIds = $studentSubjectsMap
                ->get($studentId, collect());

            $studentSubjectMarks = [];
            $currentCreditPoint = 0;
            $currentGradePointEarn = 0;
            $failedSubjects = [];

            foreach ($filteredSubjects as $subject) {

                if (!$studentSubjectIds->contains($subject->id)) {
                    continue;
                }

                $internalRecord = $studentInternal->get($subject->id);

                /*
                * EXTERNAL PRIORITY:
                * 1. Current Reappear
                * 2. Previous Reappear (newest chronological first)
                * 3. Regular EndSemMark (newest relevant regular exam first)
                */
                $externalRecord = $studentCurrentReappearExternal
                    ->get($subject->id);

                if (!$externalRecord) {
                    $previousRows = $studentPreviousReappearExternal
                        ->get($subject->id, collect());

                    foreach ($previousRows as $previousMark) {
                        if ($previousMark) {
                            $externalRecord = $previousMark;
                            break;
                        }
                    }
                }

                /* REGULAR FALLBACK */
                if (!$externalRecord) {
                    $externalRecord = $studentRegularExternal
                        ->get($subject->id);
                }

                $hasInternal = !is_null($internalRecord);
                $hasExternal = !is_null($externalRecord);

                if (!$hasInternal && !$hasExternal) {
                    continue;
                }

                $credits = (float) ($subject->credits ?? 0);

                $internalValue = $markValue($internalRecord);
                $externalValue = $markValue($externalRecord);

                $internalSpecial = $specialStatus($internalRecord);
                $externalSpecial = $specialStatus($externalRecord);

                $total = null;
                $percentage = 0;
                $grade = '';
                $gradePoint = 0;
                $creditScore = 0;
                $result = 'FAIL';

                if ($externalSpecial !== '') {

                    $total = $externalSpecial;
                    $grade = $externalSpecial;
                    $result = $externalSpecial;

                } elseif (!$hasInternal || !$hasExternal) {

                    $total = 0;
                    $grade = 'R';
                    $result = 'R';

                } elseif (
                    $internalSpecial !== ''
                    && $externalSpecial !== ''
                    && $internalSpecial === $externalSpecial
                ) {

                    $total = $externalSpecial;
                    $grade = $externalSpecial;
                    $result = $externalSpecial;

                } elseif ($percentageType === 'Aggregate') {

                    $total = (float) $internalValue + (float) $externalValue;

                    $maximum = (float) ($subject->internal_marks ?? 0)
                        + (float) ($subject->external_marks ?? 0);

                    $percentage = $maximum > 0
                        ? ($total * 100) / $maximum
                        : 0;

                    $matchedGrade = $getGrade($percentage);

                    if ($percentage < $overallPassPercent || !$matchedGrade) {
                        $grade = 'R';
                        $result = 'FAIL';
                    } else {
                        $grade = $matchedGrade->grade_name;
                        $gradePoint = (float) $matchedGrade->grade_point;
                        $result = 'PASS';
                    }

                } else {

                    $total = (float) $internalValue + (float) $externalValue;

                    $internalPass = (float) ($subject->internal_marks ?? 0)
                        * $overallPassPercent / 100;

                    $externalPass = (float) ($subject->external_marks ?? 0)
                        * $overallPassPercent / 100;

                    $internalPassed = $internalValue >= $internalPass;
                    $externalPassed = $externalValue >= $externalPass;

                    $maximum = (float) ($subject->internal_marks ?? 0)
                        + (float) ($subject->external_marks ?? 0);

                    $percentage = $maximum > 0
                        ? ($total * 100) / $maximum
                        : 0;

                    $matchedGrade = $getGrade($percentage);

                    if (!$externalPassed && !$internalPassed) {
                        $grade = 'I/F';
                        $result = 'FAIL';
                    } elseif (!$externalPassed && $internalPassed) {
                        $grade = 'R';
                        $result = 'FAIL';
                    } elseif ($externalPassed && !$internalPassed) {
                        $grade = 'I';
                        $result = 'FAIL';
                    } else {
                        $grade = $matchedGrade->grade_name ?? 'R';
                        $gradePoint = (float) ($matchedGrade->grade_point ?? 0);
                        $result = ($grade === 'R' || $grade === 'F')
                            ? 'FAIL'
                            : 'PASS';
                    }
                }

                if ($externalSpecial === '' && $internalSpecial !== '' && $result !== 'PASS') {
                    $total = $internalSpecial;
                    $grade = $internalSpecial;
                    $result = $internalSpecial;
                    $gradePoint = 0;
                    $percentage = 0;
                }

                if (in_array($grade, ['R', 'F', 'AB', 'UMC', 'I/F', 'I'])) {
                    $gradePoint = 0;
                }

                if ($result !== 'PASS') {
                    $gradePoint = 0;
                }

                $creditScore = $gradePoint * $credits;
                $currentCreditPoint += $credits;
                $currentGradePointEarn += $creditScore;

                if ($result !== 'PASS') {
                    $failedSubjects[] = $subject->subject_code;
                }

                $studentSubjectMarks[$subject->id] = [
                    'internal_marks' => $internalSpecial ?: $internalValue,
                    'external_marks' => $externalSpecial ?: $externalValue,
                    'total' => $total,
                    'percentage' => $percentage,
                    'grade' => $grade,
                    'grade_point' => $gradePoint,
                    'credit_score' => $creditScore,
                    'result' => $result,
                ];
            }

            /* SGPA */
            $sgpa = $currentCreditPoint > 0
                ? round($currentGradePointEarn / $currentCreditPoint, 2)
                : 0;

            /* PREVIOUS SEMESTER CREDIT / EARN */
            $previousCreditPoint = 0;
            $previousGradePointEarn = 0;
            $previousResultStatus = 'N/A';

            if ($semesterId > 1) {

                $regularRows = $previousGpSheets->get($studentId, collect());
                $reappearRows = $previousReappearGpSheets->get($studentId, collect());

                foreach ($previousSemesters as $prevSem) {

                    $regularSemRows = $regularRows
                        ->where('semester_id', $prevSem)
                        ->keyBy('subject_id');

                    $reappearSemRows = $reappearRows
                        ->where('semester_id', $prevSem)
                        ->keyBy('subject_id');

                    $allPreviousSubjectIds = $regularSemRows->keys()
                        ->merge($reappearSemRows->keys())
                        ->unique();

                    foreach ($allPreviousSubjectIds as $prevSubjectId) {

                        $row = $reappearSemRows->get($prevSubjectId)
                            ?? $regularSemRows->get($prevSubjectId);

                        if (!$row) {
                            continue;
                        }

                        $credits = $retotalValue(
                            $row->credits ?? 0,
                            $row->retotalling_credits ?? null
                        );

                        $gpEarn = $retotalValue(
                            $row->credit_score ?? 0,
                            $row->retotalling_credit_score ?? null
                        );

                        $previousCreditPoint += $credits;
                        $previousGradePointEarn += $gpEarn;
                    }
                }

                $previousResultStatus = 'PASS';

                foreach ($previousSemesters as $prevSem) {

                    $regularSemRows = $regularRows
                        ->where('semester_id', $prevSem)
                        ->keyBy('subject_id');

                    $reappearSemRows = $reappearRows
                        ->where('semester_id', $prevSem)
                        ->keyBy('subject_id');

                    $allPreviousSubjectIds = $regularSemRows->keys()
                        ->merge($reappearSemRows->keys())
                        ->unique();

                    foreach ($allPreviousSubjectIds as $prevSubjectId) {

                        $row = $reappearSemRows->get($prevSubjectId)
                            ?? $regularSemRows->get($prevSubjectId);

                        if (!$row) {
                            continue;
                        }

                        $resultValue = $row->retotalling_result ?: $row->result;

                        if ($resultValue !== 'PASS') {
                            $previousResultStatus = 'FAIL';
                        }

                        if (in_array($resultValue, ['AB', 'UMC', 'I/F'])) {
                            $previousResultStatus = 'FAIL';
                        }
                    }
                }

            } else {

                $previousCreditPoint = $currentCreditPoint;
                $previousGradePointEarn = $currentGradePointEarn;
                $previousResultStatus = empty($failedSubjects) ? 'PASS' : 'FAIL';
            }

            /* CGPA */
            $cgpaDenominator = $currentCreditPoint + $previousCreditPoint;

            $cgpa = $cgpaDenominator > 0
                ? round(
                    ($currentGradePointEarn + $previousGradePointEarn) / $cgpaDenominator,
                    2
                )
                : 0;

            /* RESULT / REMARK */
            $overallResult = implode(', ', $failedSubjects);
            $remark = empty($failedSubjects) ? 'PASS' : 'FAIL';

            $marks[$studentId] = [
                'subjects' => $studentSubjectMarks,
                'result' => $overallResult,
                'current_credit_point' => $currentCreditPoint,
                'current_grade_point_earn' => $currentGradePointEarn,
                'sgpa' => $sgpa,
                'previous_credit_point' => $previousCreditPoint,
                'previous_grade_point_earn' => $previousGradePointEarn,
                'cgpa' => $cgpa,
                'previous_result_status' => $previousResultStatus,
                'remark' => $remark,
            ];
        }

        return view(
            'backend.result.reappear.overall-result.result',
            compact(
                'exam',
                'course',
                'branch',
                'scheme',
                'batch',
                'semester',
                'className',
                'subjects',
                'filteredSubjects',
                'students',
                'midSemMarks',
                'regularExternalMarks',
                'currentReappearMarks',
                'previousReappearMarks',
                'grades',
                'marks',
                'institutes',
                'overallPassPercent',
                'percentageType',
                'previousGpSheets',
                'previousReappearGpSheets',
                'previousSemesters',
                'instituteId',
                'appliedSubjectMap',
                'reappearCurrentMarksMap'
            )
        )->render();
    }



}
