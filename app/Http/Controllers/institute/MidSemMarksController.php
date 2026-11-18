<?php

namespace App\Http\Controllers\institute;

use App\Http\Controllers\Controller;
use App\Models\AnnualSemester;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\ExaminationForm;
use App\Models\Institute;
use App\Models\MidSemExam;
use App\Models\MidSemMark;
use App\Models\Scheme;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentSubject;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MidSemMarksController extends Controller
{
    public function midSem(){
        $user = auth('institute')->user();
        $institute_id = $user->account_type === 'institute'
                            ? $user->id
                            : $user->institute_id;
        $courses = Course::whereHas('institutes', function ($q) use ($institute_id) {
            $q->where('institutes.id', $institute_id);
        })->get();
        $mid_sem_exams = MidSemExam::where('status', 1)->latest()->get();
        return view('institute.mid-sem-marks.index',compact('courses','mid_sem_exams'));
    }


    public function renderResult(Request $request)
    {
        /**
         * =========================
         * INSTITUTE
         * =========================
         */
        $authUser = auth('institute')->user();

        $instituteId = $authUser->account_type === 'institute'
            ? $authUser->id
            : $authUser->institute_id;


        /**
         * =========================
         * SUBJECT
         * =========================
         */
        $subject = Subject::findOrFail($request->subject_id);


        /**
         * =========================
         * MAX MARKS LOGIC
         * =========================
         */
        if ($request->course_type === 'Semester Based') {

            // Semester Based: Only Internal Marks
            $maxMarks = $subject->internal_marks ?? 0;

        } else {

            // Annual Based
            $maxMarks = match ($request->marks_type) {
                'Internal 1' => $subject->internal1,
                'Internal 2' => $subject->internal2,
                'Internal 3' => $subject->internal3,
                default => 0
            };

        }


        /**
         * =========================
         * EXAMINATION FORM QUERY
         * =========================
         * Only paid examination forms
         * and students who selected
         * the requested subject.
         */
        $examinationForms = ExaminationForm::where('exam_type', 'Regular')
            ->where('course_id', $request->course_id)
            ->where('branch_id', $request->branch_id)
            ->where('batch_id', $request->batch_id)
            ->where('institute_id', $instituteId)
            ->where('payment_status', 1)

            ->when(
                $request->scheme_id,
                fn($q) => $q->where('scheme_id', $request->scheme_id)
            )

            ->when(
                $request->course_type === 'Semester Based',
                function ($q) use ($request) {
                    $q->where('semester_id', $request->semester_id);
                },
                function ($q) use ($request) {
                    $q->where('annual_semester_id', $request->annual_semester_id);
                }
            )

            // Student must have selected this subject
            ->whereHas('choosedSubjects', function ($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            });


        /**
         * =========================
         * ELIGIBLE STUDENT IDS
         * =========================
         */
        $studentSubjects = $examinationForms
            ->pluck('student_id')
            ->unique()
            ->values()
            ->toArray();


        /**
         * =========================
         * STUDENTS
         * =========================
         * Only students having:
         * 1. Paid examination form
         * 2. Selected requested subject
         */
        $students = Student::with([
                'course',
                'branch',
                'semester',
                'annual_semester',
                'scheme',
                'batch',
                'institute'
            ])
            ->whereIn('id', $studentSubjects)
            ->where('course_type', $request->course_type)
            ->where('course_id', $request->course_id)
            ->where('branch_id', $request->branch_id)
            ->where('institute_id', $instituteId)
            ->where('batch_id', $request->batch_id)

            ->when(
                $request->scheme_id,
                fn($q) => $q->where('scheme_id', $request->scheme_id)
            )

            ->when(
                $request->course_type === 'Semester Based',
                fn($q) => $q->where('semester_id', $request->semester_id),
                fn($q) => $q->where('annual_semester_id', $request->annual_semester_id)
            )

            ->orderByRaw("
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(university_roll_no, '-', 2), '-', -1) AS UNSIGNED) ASC,
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(university_roll_no, '-', 4), '-', -1) AS UNSIGNED) ASC
            ")
            ->get();


        /**
         * =========================
         * MARKS QUERY
         * =========================
         */
        $marksQuery = MidSemMark::where([
            'mid_sem_exam_id' => $request->mid_sem_exam_id,
            'subject_id'      => $request->subject_id,
            'institute_id'    => $instituteId,
            'course_type'     => $request->course_type,
            'course_id'       => $request->course_id,
            'branch_id'       => $request->branch_id,
            'batch_id'        => $request->batch_id,
            'marks_type'      => $request->marks_type,
        ]);

        if ($request->scheme_id) {
            $marksQuery->where('scheme_id', $request->scheme_id);
        }

        if ($request->course_type === 'Semester Based') {

            $marksQuery->where('semester_id', $request->semester_id)
                ->whereNull('annual_semester_id');

        } else {

            $marksQuery->where('annual_semester_id', $request->annual_semester_id)
                ->whereNull('semester_id');

        }

        $allMarks = $marksQuery->get()->keyBy('student_id');


        /**
         * =========================
         * COMMON DATA
         * =========================
         */
        $exam = MidSemExam::find($request->mid_sem_exam_id);

        $batch = Batch::find($request->batch_id);

        $semester = null;
        $annualSemester = null;

        if ($request->course_type === 'Semester Based') {

            $semester = Semester::find($request->semester_id);

        } else {

            $annualSemester = AnnualSemester::find($request->annual_semester_id);

        }

        $institute = Institute::find($instituteId);


        /**
         * =========================
         * RESULT VIEW
         * =========================
         */
        return view('institute.mid-sem-marks.result', compact(
            'students',
            'allMarks',
            'maxMarks',
            'subject',
            'exam',
            'batch',
            'semester',
            'annualSemester',
            'instituteId',
            'institute',
            'studentSubjects'
        ));
    }


    public function saveMarks(Request $request)
    {
        /* ================= VALIDATION ================= */
        // $request->validate([
        //     'scheme_id' => 'required|exists:schemes,id',
        // ]);

        $studentId = $request->student_id;
        $mid_sem_exam_id = $request->mid_sem_exam_id;

        $authUser = auth('institute')->user();

        $instituteId = $authUser->account_type === 'institute'
            ? auth('institute')->id()
            : $authUser->institute_id;

        $exam_id = MidSemExam::find($mid_sem_exam_id)->exam_id ?? null;

        $subject = Subject::findOrFail($request->subject_id);

        /**
         * MAX MARKS (Semester + Annual)
         */
        if ($request->course_type === 'Semester Based') {

            $maxMarks = match ($request->marks_type) {
                'Internal' => $subject->internal_marks,
                default     => 0
            };

        } else {

            $maxMarks = match ($request->marks_type) {
                'Internal 1' => $subject->internal1,
                'Internal 2' => $subject->internal2,
                'Internal 3' => $subject->internal3,
                default      => 0
            };
        }

        /*
        |--------------------------------------------------------------------------
        | Record Check
        |--------------------------------------------------------------------------
        */

        $marksQuery = MidSemMark::where([
            'exam_id'         => $exam_id,
            'mid_sem_exam_id' => $request->mid_sem_exam_id,
            'student_id'      => $studentId,
            'subject_id'      => $request->subject_id,
            'institute_id'    => $instituteId,
            'course_type'     => $request->course_type,
            'course_id'       => $request->course_id,
            'scheme_id'       => $request->scheme_id,
            'branch_id'       => $request->branch_id,
            'batch_id'        => $request->batch_id,
            'marks_type'      => $request->marks_type,
        ]);

        if ($request->course_type === 'Semester Based') {

            $marksQuery->where('semester_id', $request->semester_id)
                ->whereNull('annual_semester_id');

        } else {

            $marksQuery->where('annual_semester_id', $request->annual_semester_id)
                ->whereNull('semester_id');
        }

        $record = $marksQuery->first();

        /*
        |--------------------------------------------------------------------------
        | Data Prepare
        |--------------------------------------------------------------------------
        */

        $data = [
            'exam_id'            => $exam_id,
            'mid_sem_exam_id'    => $request->mid_sem_exam_id,
            'student_id'         => $studentId,
            'subject_id'         => $request->subject_id,
            'institute_id'       => $instituteId,
            'course_type'        => $request->course_type,
            'course_id'          => $request->course_id,
            'branch_id'          => $request->branch_id,
            'batch_id'           => $request->batch_id,
            'scheme_id'          => $request->scheme_id,

            'semester_id'        => $request->course_type === 'Semester Based'
                                    ? $request->semester_id
                                    : null,

            'annual_semester_id' => $request->course_type === 'Annual Based'
                                    ? $request->annual_semester_id
                                    : null,

            'marks_type'         => $request->marks_type,
            'max_marks'          => $maxMarks,

            'obtained_marks'     => $request->exam_status === 'absent'
                                    ? 0
                                    : min((float) $request->marks, $maxMarks),

            'exam_status'        => $request->exam_status ?? 'present',
            'upload_by'          => $instituteId,
        ];

        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */

        if ($record) {
            $record->update($data);
        } else {
            $record = MidSemMark::create($data);
        }

        return response()->json([
            'status' => true,
            'id' => $record->id
        ]);
    }

// Backup function -------------------------------------------------------------------------------
    // public function saveMarks(Request $request)
    // {
    //     /* ================= VALIDATION ================= */

    //     $rules = [
    //         'student_id'      => 'required|exists:students,id',
    //         'mid_sem_exam_id' => 'required|exists:mid_sem_exams,id',
    //         'course_type'     => 'required|in:Semester Based,Annual Based',
    //         'course_id'       => 'required|exists:courses,id',
    //         'scheme_id'       => 'required|exists:schemes,id',
    //         'branch_id'       => 'required|exists:branches,id',
    //         'batch_id'        => 'required|exists:batches,id',
    //         'subject_id'      => 'required|exists:subjects,id',
    //         'marks_type'      => 'required',
    //         'exam_status'     => 'nullable|in:present,absent',
    //         'marks'           => 'nullable|numeric|min:0',
    //         'grade'           => 'nullable|string',
    //     ];

    //     // Semester Based Validation
    //     if ($request->course_type === 'Semester Based') {

    //         $rules['semester_id'] = 'required|exists:semesters,id';

    //         $rules['marks_type'] = 'required|in:Internal';

    //     }

    //     // Annual Based Validation
    //     if ($request->course_type === 'Annual Based') {

    //         $rules['annual_semester_id'] = 'required|exists:annual_semesters,id';

    //         $rules['marks_type'] = 'required|in:Internal 1,Internal 2,Internal 3';

    //     }

    //     $request->validate($rules, [
    //         'student_id.required' => 'Student is required.',
    //         'mid_sem_exam_id.required' => 'Mid-Sem Exam Name Required.',
    //         'course_type.required' => 'Course Type Required.',
    //         'course_id.required' => 'Course Required.',
    //         'scheme_id.required' => 'Scheme Required.',
    //         'branch_id.required' => 'Branch Required.',
    //         'batch_id.required' => 'Batch Required.',
    //         'marks_type.required' => 'Marks Type Required.',
    //         'subject_id.required' => 'Subject Required.',
    //         'semester_id.required' => 'Semester Required.',
    //         'annual_semester_id.required' => 'Annual Semester Required.',
    //     ]);

    //     $studentId = $request->student_id;
    //     $mid_sem_exam_id = $request->mid_sem_exam_id;

    //     $authUser = auth('institute')->user();

    //     $instituteId = $authUser->account_type === 'institute'
    //         ? $authUser->id
    //         : $authUser->institute_id;

    //     $exam = MidSemExam::findOrFail($mid_sem_exam_id);

    //     $exam_id = $exam->exam_id;

    //     $subject = Subject::findOrFail($request->subject_id);

    //     /*
    //     |--------------------------------------------------------------------------
    //     | MAX MARKS (Semester + Annual)
    //     |--------------------------------------------------------------------------
    //     */

    //     if ($request->course_type === 'Semester Based') {

    //         $maxMarks = match ($request->marks_type) {
    //             'Internal' => $subject->internal_marks,
    //             default    => 0
    //         };

    //     } else {

    //         $maxMarks = match ($request->marks_type) {
    //             'Internal 1' => $subject->internal1,
    //             'Internal 2' => $subject->internal2,
    //             'Internal 3' => $subject->internal3,
    //             default      => 0
    //         };
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | MARKS MAXIMUM VALIDATION
    //     |--------------------------------------------------------------------------
    //     */

    //     if (
    //         $request->exam_status !== 'absent' &&
    //         $request->marks !== null &&
    //         (float) $request->marks > (float) $maxMarks
    //     ) {
    //         return response()->json([
    //             'message' => 'Marks cannot be greater than Maximum Marks (' . $maxMarks . ')',
    //             'errors' => [
    //                 'marks' => [
    //                     'Marks cannot be greater than Maximum Marks (' . $maxMarks . ')'
    //                 ]
    //             ]
    //         ], 422);
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | RECORD CHECK
    //     |--------------------------------------------------------------------------
    //     */

    //     $marksQuery = MidSemMark::where([
    //         'exam_id'         => $exam_id,
    //         'mid_sem_exam_id' => $request->mid_sem_exam_id,
    //         'student_id'      => $studentId,
    //         'subject_id'      => $request->subject_id,
    //         'institute_id'    => $instituteId,
    //         'course_type'     => $request->course_type,
    //         'course_id'       => $request->course_id,
    //         'scheme_id'       => $request->scheme_id,
    //         'branch_id'       => $request->branch_id,
    //         'batch_id'        => $request->batch_id,
    //         'marks_type'      => $request->marks_type,
    //     ]);

    //     if ($request->course_type === 'Semester Based') {

    //         $marksQuery->where('semester_id', $request->semester_id)
    //             ->whereNull('annual_semester_id');

    //     } else {

    //         $marksQuery->where('annual_semester_id', $request->annual_semester_id)
    //             ->whereNull('semester_id');
    //     }

    //     $record = $marksQuery->first();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | DATA PREPARE
    //     |--------------------------------------------------------------------------
    //     */

    //     $data = [
    //         'exam_id'            => $exam_id,
    //         'mid_sem_exam_id'    => $request->mid_sem_exam_id,
    //         'student_id'         => $studentId,
    //         'subject_id'         => $request->subject_id,
    //         'institute_id'       => $instituteId,
    //         'course_type'        => $request->course_type,
    //         'course_id'          => $request->course_id,
    //         'branch_id'          => $request->branch_id,
    //         'batch_id'           => $request->batch_id,
    //         'scheme_id'          => $request->scheme_id,

    //         'semester_id'        => $request->course_type === 'Semester Based'
    //                                 ? $request->semester_id
    //                                 : null,

    //         'annual_semester_id' => $request->course_type === 'Annual Based'
    //                                 ? $request->annual_semester_id
    //                                 : null,

    //         'marks_type'         => $request->marks_type,
    //         'max_marks'          => $maxMarks,

    //         'obtained_marks'     => $request->exam_status === 'absent'
    //                                 ? 0
    //                                 : min(
    //                                     (float) ($request->marks ?? 0),
    //                                     (float) $maxMarks
    //                                 ),

    //         'exam_status'        => $request->exam_status ?? 'present',
    //         'upload_by'          => $instituteId,
    //     ];

    //     /*
    //     |--------------------------------------------------------------------------
    //     | SAVE
    //     |--------------------------------------------------------------------------
    //     */

    //     if ($record) {
    //         $record->update($data);
    //     } else {
    //         $record = MidSemMark::create($data);
    //     }

    //     return response()->json([
    //         'status' => true,
    //         'id' => $record->id
    //     ]);
    // }



    // public function lockUnlock(Request $request)
    // {
    //     $request->validate([
    //         'ids' => 'required|array',
    //         'action' => 'required|in:lock,unlock,forward'
    //     ]);

    //     // Get only records that are not forwarded
    //     $records = MidSemMark::whereIn('id', $request->ids)
    //         ->where('university_forword', 0)
    //         ->get();

    //     if ($records->count() !== count($request->ids)) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Some selected records are already forwarded.'
    //         ]);
    //     }

    //     // EXTRA SECURITY FOR FORWARD
    //     if ($request->action === 'forward') {

    //         // Check if any record is unlocked
    //         $unlockedExists = $records->where('is_locked', 0)->count();

    //         if ($unlockedExists > 0) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Please lock all selected students marks before forwarding.'
    //             ]);
    //         }
    //     }

    //     foreach ($records as $record) {

    //         if ($request->action === 'lock') {
    //             $record->is_locked = 1;
    //         }

    //         if ($request->action === 'unlock') {
    //             $record->is_locked = 0;
    //         }

    //         if ($request->action === 'forward') {
    //             $record->university_forword = 1;
    //             $record->is_locked = 1; // force lock
    //         }

    //         $record->save();
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Updated Successfully'
    //     ]);
    // }


    public function lockUnlock(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array',
            'action' => 'required|in:lock,unlock,forward'
        ]);

        $instituteUser = auth('institute')->user();

        $instituteId = $instituteUser->account_type === 'institute'
            ? auth('institute')->id()
            : $instituteUser->institute_id;

        // Get records
        $records = MidSemMark::whereIn('id', $request->ids)->get();

        /*
        |--------------------------------------------------------------------------
        | FORWARD CHECK
        |--------------------------------------------------------------------------
        | Non-institute users cannot modify forwarded records
        */

        if ($instituteUser->account_type !== 'institute') {

            $forwardedExists = $records->where('university_forword', 1)->count();

            if ($forwardedExists > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some selected records are already forwarded.'
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | EXTRA SECURITY FOR FORWARD
        |--------------------------------------------------------------------------
        */

        if ($request->action === 'forward') {

            // Check unlocked records
            $unlockedExists = $records->where('is_locked', 0)->count();

            if ($unlockedExists > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please lock all selected students marks before forwarding.'
                ]);
            }
        }

        foreach ($records as $record) {

            /*
            |--------------------------------------------------------------------------
            | LOCK
            |--------------------------------------------------------------------------
            */

            if ($request->action === 'lock') {
                $record->is_locked = 1;
            }

            /*
            |--------------------------------------------------------------------------
            | UNLOCK
            |--------------------------------------------------------------------------
            | If account_type = institute
            | and record already forwarded,
            | then unlock + remove forward status
            |--------------------------------------------------------------------------
            */

            if ($request->action === 'unlock') {

                $record->is_locked = 0;

                if (
                    $instituteUser->account_type === 'institute' &&
                    $record->university_forword == 1
                ) {
                    $record->university_forword = 0;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | FORWARD
            |--------------------------------------------------------------------------
            */

            if ($request->action === 'forward') {
                $record->university_forword = 1;
                $record->is_locked = 1; // force lock
            }

            $record->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Updated Successfully'
        ]);
    }


    // public function import(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|file|mimes:csv,txt|max:4096',
    //     ]);

    //     try {

    //         $path = $request->file('file')->getRealPath();
    //         $rawData = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    //         if (!$rawData || count($rawData) <= 1) {
    //             return back()->with('error', 'CSV file is empty or invalid.');
    //         }

    //         $data = array_map('str_getcsv', $rawData);
    //         $header = array_map(fn($h) => strtolower(trim($h)), array_shift($data));

    //         $insertCount = 0;
    //         $skipCount   = 0;

    //         DB::beginTransaction();

    //         foreach ($data as $row) {



    //             if (count(array_filter($row)) === 0) continue;

    //             $row = array_combine($header, $row);
    //             if (!$row) continue;

    //             // ============================
    //             // 1️⃣ Basic Master Lookups
    //             // ============================

    //             $course   = Course::where('name', trim($row['course_name']))->first();
    //             $branch   = $course
    //                 ? Branch::where('course_id', $course->id)
    //                         ->where('name', trim($row['branch_name']))
    //                         ->first()
    //                 : null;

    //             $semester = Semester::where('name', trim($row['semester_name']))->first();
    //             $annual   = AnnualSemester::where('name', trim($row['annual_semester_name']))->first();

    //             $batch    = Batch::where('name', trim($row['batch_name']))->first();
    //             $institute= Institute::where('name', trim($row['institute_name']))->first();

    //             // ============================
    //             // 2️⃣ Scheme (Optional)
    //             // ============================

    //             $scheme = null;
    //             if (!empty(trim($row['scheme_name']))) {
    //                 $scheme = Scheme::where('name', trim($row['scheme_name']))->first();
    //             }

    //             // ============================
    //             // 3️⃣ Student Lookup
    //             // based on university_roll_no
    //             // ============================

    //             $studentQuery = Student::where('university_roll_no', trim($row['university_roll_no']))
    //                 ->where('course_type', trim($row['course_type']))
    //                 ->where('course_id', $course?->id)
    //                 ->where('branch_id', $branch?->id)
    //                 ->where('batch_id', $batch?->id)
    //                 ->where('institute_id', $institute?->id);

    //             // Course Type Based Condition
    //             // if (trim($row['course_type']) === 'Semester Based') {
    //             //     $studentQuery->where('semester_id', $semester?->id)
    //             //                 ->whereNull('annual_semester_id');
    //             // } else {
    //             //     $studentQuery->where('annual_semester_id', $annual?->id)
    //             //                 ->whereNull('semester_id');
    //             // }

    //             if ($scheme) {
    //                 $studentQuery->where('scheme_id', $scheme->id);
    //             }

    //             $student = $studentQuery->first();

    //             if (!$student) {
    //                 $skipCount++;
    //                 continue;
    //             }


    //             $subjectQuery = Subject::where('subject_code', trim($row['subject_code']))
    //                 ->where('name', trim($row['subject_name']))
    //                 ->where('course_type', trim($row['course_type']))
    //                 ->where('course_id', $course?->id)
    //                 ->where('branch_id', $branch?->id);

    //             if (trim($row['course_type']) === 'Semester Based') {
    //                 $subjectQuery->where('semester_id', $semester?->id)
    //                             ->whereNull('annual_semester_id');
    //             } else {
    //                 $subjectQuery->where('annual_semester_id', $annual?->id)
    //                             ->whereNull('semester_id');
    //             }

    //             if ($scheme) {
    //                 $subjectQuery->where('scheme_id', $scheme->id);
    //             }

    //             $subject = $subjectQuery->first();

    //             if (!$subject) {
    //                 $skipCount++;
    //                 continue;
    //             }


    //             // ============================
    //             // 5️⃣ Exam Lookup
    //             // Based only on MidSemExam table
    //             // ============================

    //             $midSemExam = MidSemExam::where('mid_sem_exam_name', trim($row['internal_exam_name']))
    //                 ->where('status', 1) // optional: only active exams
    //                 ->first();

    //             if (!$midSemExam) {
    //                 $skipCount++;
    //                 continue;
    //             }

    //             // exam_id directly from mid_sem_exam table
    //             $examId = $midSemExam->exam_id;



    //             // ============================
    //             // 6️⃣ Duplicate Check
    //             // ============================

    //             $courseType = strtolower(trim($row['course_type']));

    //             $existsQuery = MidSemMark::where('student_id', $student->id)
    //                 ->where('subject_id', $subject->id)
    //                 ->where('exam_id', $examId)
    //                 ->where('mid_sem_exam_id', $midSemExam->id)
    //                 ->where('course_type', $courseType)
    //                 ->where('course_id', $course?->id)
    //                 ->where('branch_id', $branch?->id)
    //                 ->where('batch_id', $batch?->id)
    //                 ->where('institute_id', $institute?->id)
    //                 ->where('marks_type', trim($row['marks_type']));

    //             if ($scheme) {
    //                 $existsQuery->where('scheme_id', $scheme->id);
    //             }

    //             if ($courseType === 'semester based') {
    //                 $existsQuery->where('semester_id', $semester?->id)
    //                             ->whereNull('annual_semester_id');
    //             } else {
    //                 $existsQuery->where('annual_semester_id', $annual?->id)
    //                             ->whereNull('semester_id');
    //             }

    //             if ($existsQuery->exists()) {
    //                 $skipCount++;
    //                 continue;
    //             }

    //             // ============================
    //             // 7️⃣ Exam Status Handling
    //             // ============================

    //             $examStatus = strtolower(trim($row['exam_status']));
    //             $obtainedMarks = ($examStatus === 'absent')
    //                 ? 0
    //                 : (float) $row['obtained_marks'];

    //             // ============================
    //             // 8️⃣ Insert Marks
    //             // ============================

    //             // $data = [
    //             //     'exam_id'              => $examId,
    //             //     'mid_sem_exam_id'      => $midSemExam->id,
    //             //     'student_id'           => $student->id,
    //             //     'subject_id'           => $subject->id,
    //             //     'institute_id'         => $institute?->id,
    //             //     'course_type'          => trim($row['course_type']),
    //             //     'course_id'            => $course?->id,
    //             //     'branch_id'            => $branch?->id,
    //             //     'batch_id'             => $batch?->id,
    //             //     'semester_id'          => $semester?->id ?? null,
    //             //     'annual_semester_id'   => $annual?->id ?? null,
    //             //     'scheme_id'            => $scheme?->id,
    //             //     'marks_type'           => trim($row['marks_type']),
    //             //     'max_marks'            => (float) $row['max_marks'],
    //             //     'obtained_marks'       => $obtainedMarks,
    //             //     'exam_status'          => $examStatus,
    //             //     'is_locked'            => 0,
    //             //     'university_forword'   => 0,
    //             // ];

    //             // echo "<pre>";
    //             // print_r($data);


    //             $courseType = ucwords(trim($row['course_type']));

    //             MidSemMark::create([
    //                 'exam_id'              => $examId,
    //                 'mid_sem_exam_id'      => $midSemExam->id,
    //                 'student_id'           => $student->id,
    //                 'subject_id'           => $subject->id,
    //                 'institute_id'         => $institute?->id,
    //                 'course_type'          => $courseType,
    //                 'course_id'            => $course?->id,
    //                 'branch_id'            => $branch?->id,
    //                 'batch_id'             => $batch?->id,
    //                 'semester_id'          => $semester?->id ?? null,
    //                 'annual_semester_id'   => $annual?->id ?? null,
    //                 'scheme_id'            => $scheme?->id,
    //                 'marks_type'           => trim($row['marks_type']),
    //                 'max_marks'            => (float) $row['max_marks'],
    //                 'obtained_marks'       => $obtainedMarks,
    //                 'exam_status'          => $examStatus,
    //                 'is_locked'            => 0,
    //                 'university_forword'   => 0,
    //             ]);

    //             $insertCount++;
    //         }

    //         DB::commit();

    //         return back()->with('success',
    //             "Inserted: {$insertCount} | Skipped (Already/Invalid): {$skipCount}"
    //         );

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->with('error', 'Import failed: ' . $e->getMessage());
    //     }
    // }


    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        try {

            $path = $request->file('file')->getRealPath();
            $rawData = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if (!$rawData || count($rawData) <= 1) {
                return back()->with('error', 'CSV file is empty or invalid.');
            }

            $data = array_map('str_getcsv', $rawData);
            $header = array_map(fn($h) => strtolower(trim($h)), array_shift($data));

            $insertCount = 0;
            $skipCount   = 0;

            DB::beginTransaction();

            foreach ($data as $row) {

                if (count(array_filter($row)) === 0) {
                    continue;
                }

                $row = array_combine($header, $row);

                if (!$row) {
                    continue;
                }

                // ============================
                // 1️⃣ Basic Master Lookups
                // ============================

                $course = Course::where('name', trim($row['course_name']))->first();

                $branch = $course
                    ? Branch::where('course_id', $course->id)
                        ->where('name', trim($row['branch_name']))
                        ->first()
                    : null;

                $semester = Semester::where('name', trim($row['semester_name']))->first();

                // Annual Semester Name column is not available in CSV
                // $annual = AnnualSemester::where('name', trim($row['annual_semester_name']))->first();

                $annual = null;

                $batch = Batch::where('name', trim($row['batch_name']))->first();

                $institute = Institute::where('name', trim($row['institute_name']))->first();


                // ============================
                // 2️⃣ Scheme (Optional)
                // ============================

                $scheme = null;

                if (!empty(trim($row['scheme_name']))) {
                    $scheme = Scheme::where('name', trim($row['scheme_name']))->first();
                }


                // ============================
                // 3️⃣ Student Lookup
                // Based on university_roll_no
                // ============================

                $studentQuery = Student::where('university_roll_no', trim($row['university_roll_no']))
                    ->where('course_type', trim($row['course_type']))
                    ->where('course_id', $course?->id)
                    ->where('branch_id', $branch?->id)
                    ->where('batch_id', $batch?->id)
                    ->where('institute_id', $institute?->id);

                // Course Type Based Condition
                // if (trim($row['course_type']) === 'Semester Based') {
                //     $studentQuery->where('semester_id', $semester?->id)
                //                  ->whereNull('annual_semester_id');
                // } else {
                //     $studentQuery->where('annual_semester_id', $annual?->id)
                //                  ->whereNull('semester_id');
                // }

                if ($scheme) {
                    $studentQuery->where('scheme_id', $scheme->id);
                }

                $student = $studentQuery->first();

                if (!$student) {
                    $skipCount++;
                    continue;
                }


                // ============================
                // 4️⃣ Annual Semester Lookup
                // From Student Record
                // ============================

                if (trim($row['course_type']) === 'Annual Based') {
                    $annual = AnnualSemester::find($student->annual_semester_id);
                }


                // ============================
                // 5️⃣ Subject Lookup
                // ============================

                $subjectQuery = Subject::where('subject_code', trim($row['subject_code']))
                    ->where('name', trim($row['subject_name']))
                    ->where('course_type', trim($row['course_type']))
                    ->where('course_id', $course?->id)
                    ->where('branch_id', $branch?->id);

                if (trim($row['course_type']) === 'Semester Based') {

                    $subjectQuery->where('semester_id', $semester?->id)
                        ->whereNull('annual_semester_id');

                } else {

                    $subjectQuery->where('annual_semester_id', $annual?->id)
                        ->whereNull('semester_id');

                }

                if ($scheme) {
                    $subjectQuery->where('scheme_id', $scheme->id);
                }

                $subject = $subjectQuery->first();

                if (!$subject) {
                    $skipCount++;
                    continue;
                }


                // ============================
                // 6️⃣ Exam Lookup
                // Based only on MidSemExam table
                // ============================

                $midSemExam = MidSemExam::where('mid_sem_exam_name', trim($row['internal_exam_name']))
                    ->where('status', 1)
                    ->first();

                if (!$midSemExam) {
                    $skipCount++;
                    continue;
                }

                // exam_id directly from mid_sem_exam table
                $examId = $midSemExam->exam_id;


                // ============================
                // 7️⃣ Duplicate Check
                // ============================

                $courseType = strtolower(trim($row['course_type']));

                $existsQuery = MidSemMark::where('student_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->where('exam_id', $examId)
                    ->where('mid_sem_exam_id', $midSemExam->id)
                    ->where('course_type', $courseType)
                    ->where('course_id', $course?->id)
                    ->where('branch_id', $branch?->id)
                    ->where('batch_id', $batch?->id)
                    ->where('institute_id', $institute?->id)
                    ->where('marks_type', trim($row['marks_type']));

                if ($scheme) {
                    $existsQuery->where('scheme_id', $scheme->id);
                }

                if ($courseType === 'semester based') {

                    $existsQuery->where('semester_id', $semester?->id)
                        ->whereNull('annual_semester_id');

                } else {

                    $existsQuery->where('annual_semester_id', $annual?->id)
                        ->whereNull('semester_id');

                }

                if ($existsQuery->exists()) {
                    $skipCount++;
                    continue;
                }


                // ============================
                // 8️⃣ Exam Status Handling
                // ============================

                $examStatus = strtolower(trim($row['exam_status']));

                $obtainedMarks = ($examStatus === 'absent')
                    ? 0
                    : (float) $row['obtained_marks'];


                // ============================
                // 9️⃣ Insert Marks
                // ============================

                $courseType = ucwords(trim($row['course_type']));

                MidSemMark::create([
                    'exam_id'              => $examId,
                    'mid_sem_exam_id'      => $midSemExam->id,
                    'student_id'           => $student->id,
                    'subject_id'           => $subject->id,
                    'institute_id'         => $institute?->id,
                    'course_type'          => $courseType,
                    'course_id'            => $course?->id,
                    'branch_id'            => $branch?->id,
                    'batch_id'             => $batch?->id,
                    'semester_id'          => $semester?->id ?? null,
                    'annual_semester_id'   => $annual?->id ?? null,
                    'scheme_id'            => $scheme?->id,
                    'marks_type'           => trim($row['marks_type']),
                    'max_marks'            => (float) $row['max_marks'],
                    'obtained_marks'       => $obtainedMarks,
                    'exam_status'          => $examStatus,
                    'is_locked'            => 0,
                    'university_forword'   => 0,
                ]);

                $insertCount++;
            }

            DB::commit();

            return back()->with(
                'success',
                "Inserted: {$insertCount} | Skipped (Already/Invalid): {$skipCount}"
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }


    public function export(Request $request)
    {
        /**
         * =========================
         * INSTITUTE
         * =========================
         */
        $authUser = auth('institute')->user();

        $institute_id = $authUser->account_type === 'institute'
            ? $authUser->id
            : $authUser->institute_id;


        /**
         * =========================
         * FILTER INPUTS
         * =========================
         */
        $course_type        = $request->course_type;
        $course_id          = $request->course_id;
        $scheme_id          = $request->scheme_id;
        $branch_id          = $request->branch_id;
        $semester_id        = $request->semester_id;
        $annual_semester_id = $request->annual_semester_id;
        $batch_id           = $request->batch_id;
        $mid_sem_exam_id    = $request->mid_sem_exam_id;
        $marks_type         = $request->marks_type;
        $subject_id         = $request->subject_id;


        /**
         * =========================
         * MASTER DATA
         * =========================
         */
        $course    = Course::find($course_id);
        $scheme    = Scheme::find($scheme_id);
        $branch    = Branch::find($branch_id);
        $semester  = Semester::find($semester_id);
        $annual    = AnnualSemester::find($annual_semester_id);
        $batch     = Batch::find($batch_id);
        $exam      = MidSemExam::find($mid_sem_exam_id);
        $institute = Institute::find($institute_id);
        $subject   = Subject::find($subject_id);


        /**
         * =========================
         * EXAM DETAILS
         * =========================
         * mid_sem_exams.exam_id
         *       ↓
         * exams.id
         */
        $examDetails = $exam
            ? \Illuminate\Support\Facades\DB::table('exams')
                ->where('id', $exam->exam_id)
                ->first()
            : null;


        /**
         * =========================
         * EXAMINATION FORM QUERY
         * =========================
         */
        $examinationForms = ExaminationForm::where('exam_type', 'Regular')
            ->where('course_id', $course_id)
            ->where('branch_id', $branch_id)
            ->where('batch_id', $batch_id)
            ->where('institute_id', $institute_id)
            ->where('payment_status', 1)

            ->when(
                $scheme_id,
                fn($q) => $q->where('scheme_id', $scheme_id)
            )

            ->when(
                $course_type === 'Semester Based',
                function ($q) use ($semester_id) {
                    $q->where('semester_id', $semester_id);
                },
                function ($q) use ($annual_semester_id) {
                    $q->where('annual_semester_id', $annual_semester_id);
                }
            )

            ->whereHas('choosedSubjects', function ($q) use ($subject_id) {
                $q->where('subject_id', $subject_id);
            });


        /**
         * =========================
         * ELIGIBLE STUDENT IDS
         * =========================
         */
        $studentSubjects = $examinationForms
            ->pluck('student_id')
            ->unique()
            ->values()
            ->toArray();


        /**
         * =========================
         * STUDENTS
         * =========================
         */
        $students = Student::whereIn('id', $studentSubjects)
            ->where('course_type', $course_type)
            ->where('course_id', $course_id)
            ->where('branch_id', $branch_id)
            ->where('institute_id', $institute_id)
            ->where('batch_id', $batch_id)

            ->when(
                $scheme_id,
                fn($q) => $q->where('scheme_id', $scheme_id)
            )

            ->when(
                $course_type === 'Semester Based',
                fn($q) => $q->where('semester_id', $semester_id),
                fn($q) => $q->where('annual_semester_id', $annual_semester_id)
            )

            ->orderByRaw("
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(university_roll_no, '-', 2), '-', -1) AS UNSIGNED) ASC,
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(university_roll_no, '-', 4), '-', -1) AS UNSIGNED) ASC
            ")
            ->get();


        /**
         * =========================
         * CSV FILE NAME
         * =========================
         */
        $subjectCode = $subject->subject_code ?? 'Subject';

        $examMonth = $examDetails->exam_month ?? '';
        $examYear  = $examDetails->exam_year ?? '';

        $fileName = 'Internal_Marks_' .
            $subjectCode . '_' .
            $examMonth . '_' .
            $examYear . '.csv';

        // Remove unwanted filename characters
        $fileName = preg_replace('/[^A-Za-z0-9_.-]/', '_', $fileName);


        /**
         * =========================
         * CSV HEADERS
         * =========================
         */
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$fileName\"",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];


        /**
         * =========================
         * CSV COLUMNS
         * =========================
         */
        $columns = [
            'id',
            'university_roll_no',
            'student_name',
            'internal_exam_name',
            'course_type',
            'course_name',
            'scheme_name',
            'branch_name',
            'semester_name',
            // 'annual_semester_name',
            'batch_name',
            'institute_name',
            'marks_type',
            'subject_name',
            'subject_code',
            'max_marks',
            'obtained_marks',
            'exam_status'
        ];


        /**
         * =========================
         * CSV CALLBACK
         * =========================
         */
        $callback = function () use (
            $students,
            $columns,
            $course,
            $scheme,
            $branch,
            $semester,
            $annual,
            $batch,
            $institute,
            $subject,
            $exam,
            $marks_type
        ) {

            $file = fopen('php://output', 'w');

            // CSV Header
            fputcsv($file, $columns);

            foreach ($students as $key => $student) {

                $row = [
                    $key + 1,
                    $student->university_roll_no,
                    $student->student_name,
                    $exam->mid_sem_exam_name ?? '',
                    $student->course_type,
                    $course->name ?? '',
                    $scheme->name ?? '',
                    $branch->name ?? '',
                    $semester->name ?? '',
                    // $annual->name ?? '',
                    $batch->name ?? '',
                    $institute->name ?? '',
                    $marks_type,
                    $subject->name ?? '',
                    $subject->subject_code ?? '',
                    '', // max_marks
                    '', // obtained_marks
                    'present'
                ];

                fputcsv($file, $row);
            }

            fclose($file);
        };


        return new StreamedResponse($callback, 200, $headers);
    }


    // public function export(Request $request)
    // {
    //     /**
    //      * =========================
    //      * INSTITUTE
    //      * =========================
    //      */
    //     $authUser = auth('institute')->user();

    //     $institute_id = $authUser->account_type === 'institute'
    //         ? $authUser->id
    //         : $authUser->institute_id;


    //     /**
    //      * =========================
    //      * FILTER INPUTS
    //      * =========================
    //      */
    //     $course_type        = $request->course_type;
    //     $course_id          = $request->course_id;
    //     $scheme_id          = $request->scheme_id;
    //     $branch_id          = $request->branch_id;
    //     $semester_id        = $request->semester_id;
    //     $annual_semester_id = $request->annual_semester_id;
    //     $batch_id           = $request->batch_id;
    //     $mid_sem_exam_id    = $request->mid_sem_exam_id;
    //     $marks_type         = $request->marks_type;
    //     $subject_id         = $request->subject_id;


    //     /**
    //      * =========================
    //      * MASTER DATA
    //      * =========================
    //      */
    //     $course    = Course::find($course_id);
    //     $scheme    = Scheme::find($scheme_id);
    //     $branch    = Branch::find($branch_id);
    //     $semester  = Semester::find($semester_id);
    //     $annual    = AnnualSemester::find($annual_semester_id);
    //     $batch     = Batch::find($batch_id);
    //     $exam      = MidSemExam::find($mid_sem_exam_id);
    //     $institute = Institute::find($institute_id);
    //     $subject   = Subject::find($subject_id);


    //     /**
    //      * =========================
    //      * EXAMINATION FORM QUERY
    //      * =========================
    //      * Only paid Regular examination forms
    //      * and students who selected the requested subject.
    //      */
    //     $examinationForms = ExaminationForm::where('exam_type', 'Regular')
    //         ->where('course_id', $course_id)
    //         ->where('branch_id', $branch_id)
    //         ->where('batch_id', $batch_id)
    //         ->where('institute_id', $institute_id)
    //         ->where('payment_status', 1)

    //         ->when(
    //             $scheme_id,
    //             fn($q) => $q->where('scheme_id', $scheme_id)
    //         )

    //         ->when(
    //             $course_type === 'Semester Based',
    //             function ($q) use ($semester_id) {
    //                 $q->where('semester_id', $semester_id);
    //             },
    //             function ($q) use ($annual_semester_id) {
    //                 $q->where('annual_semester_id', $annual_semester_id);
    //             }
    //         )

    //         // Student must have selected this subject
    //         ->whereHas('choosedSubjects', function ($q) use ($subject_id) {
    //             $q->where('subject_id', $subject_id);
    //         });


    //     /**
    //      * =========================
    //      * ELIGIBLE STUDENT IDS
    //      * =========================
    //      */
    //     $studentSubjects = $examinationForms
    //         ->pluck('student_id')
    //         ->unique()
    //         ->values()
    //         ->toArray();


    //     /**
    //      * =========================
    //      * STUDENTS
    //      * =========================
    //      * Only students having:
    //      * 1. Paid Regular examination form
    //      * 2. Selected requested subject
    //      */
    //     $students = Student::whereIn('id', $studentSubjects)
    //         ->where('course_type', $course_type)
    //         ->where('course_id', $course_id)
    //         ->where('branch_id', $branch_id)
    //         ->where('institute_id', $institute_id)
    //         ->where('batch_id', $batch_id)

    //         ->when(
    //             $scheme_id,
    //             fn($q) => $q->where('scheme_id', $scheme_id)
    //         )

    //         ->when(
    //             $course_type === 'Semester Based',
    //             fn($q) => $q->where('semester_id', $semester_id),
    //             fn($q) => $q->where('annual_semester_id', $annual_semester_id)
    //         )

    //         ->orderByRaw("
    //             CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(university_roll_no, '-', 2), '-', -1) AS UNSIGNED) ASC,
    //             CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(university_roll_no, '-', 4), '-', -1) AS UNSIGNED) ASC
    //         ")
    //         ->get();


    //     /**
    //      * =========================
    //      * CSV FILE NAME
    //      * =========================
    //      */
    //     $fileName = "mid_sem_marks_sample_" . time() . ".csv";


    //     /**
    //      * =========================
    //      * CSV HEADERS
    //      * =========================
    //      */
    //     $headers = [
    //         "Content-type"        => "text/csv",
    //         "Content-Disposition" => "attachment; filename=$fileName",
    //         "Pragma"              => "no-cache",
    //         "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
    //         "Expires"             => "0"
    //     ];


    //     /**
    //      * =========================
    //      * CSV COLUMNS
    //      * =========================
    //      */
    //     $columns = [
    //         'id',
    //         'university_roll_no',
    //         'student_name',
    //         'internal_exam_name',
    //         'course_type',
    //         'course_name',
    //         'scheme_name',
    //         'branch_name',
    //         'semester_name',
    //         // 'annual_semester_name',
    //         'batch_name',
    //         'institute_name',
    //         'marks_type',
    //         'subject_name',
    //         'subject_code',
    //         'max_marks',
    //         'obtained_marks',
    //         'exam_status'
    //     ];


    //     /**
    //      * =========================
    //      * CSV CALLBACK
    //      * =========================
    //      */
    //     $callback = function () use (
    //         $students,
    //         $columns,
    //         $course,
    //         $scheme,
    //         $branch,
    //         $semester,
    //         $annual,
    //         $batch,
    //         $institute,
    //         $subject,
    //         $exam,
    //         $marks_type
    //     ) {

    //         $file = fopen('php://output', 'w');

    //         // CSV Header
    //         fputcsv($file, $columns);

    //         foreach ($students as $key => $student) {

    //             $row = [
    //                 $key + 1,
    //                 $student->university_roll_no,
    //                 $student->student_name,
    //                 $exam->mid_sem_exam_name ?? '',
    //                 $student->course_type,
    //                 $course->name ?? '',
    //                 $scheme->name ?? '',
    //                 $branch->name ?? '',
    //                 $semester->name ?? '',
    //                 // $annual->name ?? '',
    //                 $batch->name ?? '',
    //                 $institute->name ?? '',
    //                 $marks_type,
    //                 $subject->name ?? '',
    //                 $subject->subject_code ?? '',
    //                 '', // max_marks
    //                 '', // obtained_marks
    //                 'present'
    //             ];

    //             fputcsv($file, $row);
    //         }

    //         fclose($file);
    //     };


    //     return new StreamedResponse($callback, 200, $headers);
    // }


}
