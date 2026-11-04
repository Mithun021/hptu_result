<?php

namespace App\Http\Controllers\institute;

use App\Http\Controllers\Controller;
use App\Models\AnnualSemester;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
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
 
    // public function renderResult(Request $request)
    // {
    //     $students = Student::with([
    //             'course','branch','semester','annual_semester',
    //             'scheme','batch','institute'
    //         ])
    //         ->where('course_type', $request->course_type)
    //         ->where('course_id', $request->course_id)
    //         ->where('branch_id', $request->branch_id)
    //         ->where('batch_id', $request->batch_id);

    //     if ($request->scheme_id) {
    //         $students->where('scheme_id', $request->scheme_id);
    //     }

    //     if ($request->course_type === 'Semester Based') {
    //         $students->where('semester_id', $request->semester_id);
    //     } else {
    //         $students->where('annual_semester_id', $request->annual_semester_id);
    //     }

    //     $students = $students->get();

    //     $subject = Subject::findOrFail($request->subject_id);

    //     $maxMarks = match ($request->marks_type) {
    //         'Mid-Sem' => $subject->mid_sem_marks1,
    //         'Quiz' => $subject->mid_sem_marks2,
    //         'Practical' => $subject->mid_sem_marks3,
    //         default => 0
    //     };

    //     $instituteId = auth('institute')->user()->account_type === 'institute'
    //     ? auth('institute')->id()
    //     : auth('institute')->user()->institute_id;

    //     $marksQuery = MidSemMark::where([
    //         'mid_sem_exam_id' => $request->mid_sem_exam_id,
    //         'subject_id'      => $request->subject_id,
    //         'institute_id'    => auth('institute')->user()->account_type === 'institute'
    //                             ? auth('institute')->id()
    //                             : auth('institute')->user()->institute_id,
    //         'course_type'     => $request->course_type,
    //         'course_id'       => $request->course_id,
    //         'branch_id'       => $request->branch_id,
    //         'batch_id'        => $request->batch_id,
    //         'marks_type'      => $request->marks_type,
    //     ]);

    //     if ($request->scheme_id) {
    //         $marksQuery->where('scheme_id', $request->scheme_id);
    //     }

    //     if ($request->course_type === 'Semester Based') {
    //         $marksQuery->where('semester_id', $request->semester_id)
    //                 ->whereNull('annual_semester_id');
    //     } else {
    //         $marksQuery->where('annual_semester_id', $request->annual_semester_id)
    //                 ->whereNull('semester_id');
    //     }

    //     $allMarks = $marksQuery->get()->keyBy('student_id');

    //     $studentSubjects = StudentSubject::where([
    //         'subject_id'  => $request->subject_id,
    //         'course_type' => $request->course_type,
    //         'course_id'   => $request->course_id,
    //         'branch_id'   => $request->branch_id,
    //     ])
    //     ->when($request->scheme_id, function ($q) use ($request) {
    //         $q->where('scheme_id', $request->scheme_id);
    //     })
    //     ->when($request->course_type === 'Semester Based', function ($q) use ($request) {
    //         $q->where('semester_id', $request->semester_id);
    //     }, function ($q) use ($request) {
    //         $q->where('annual_semester_id', $request->annual_semester_id);
    //     })
    //     ->pluck('student_id')
    //     ->toArray();

    //     $exam = MidSemExam::find($request->mid_sem_exam_id);
    //     $batch = Batch::find($request->batch_id);

    //     $semester = null;
    //     $annualSemester = null;

    //     if ($request->course_type === 'Semester Based') {
    //         $semester = Semester::find($request->semester_id);
    //     } else {
    //         $annualSemester = AnnualSemester::find($request->annual_semester_id);
    //     }

    //     $exam = MidSemExam::find($request->mid_sem_exam_id);

    //     $batch = Batch::find($request->batch_id);

    //     $semester = null;
    //     $annualSemester = null;

    //     if ($request->course_type === 'Semester Based') {
    //         $semester = Semester::find($request->semester_id);
    //     } else {
    //         $annualSemester = AnnualSemester::find($request->annual_semester_id);
    //     }

    //     $institute = Institute::find($instituteId);

    //     return view('institute.mid-sem-marks.result', compact(
    //         'students',
    //         'allMarks',
    //         'maxMarks',
    //         'subject',
    //         'exam',
    //         'batch',
    //         'semester',
    //         'annualSemester',
    //         'instituteId',
    //         'institute',
    //         'studentSubjects'
    //     ));
    // }

    public function renderResult(Request $request)
    {
        /**
         * ✅ INSTITUTE
         */
        $instituteId = auth('institute')->user()->account_type === 'institute'
            ? auth('institute')->id()
            : auth('institute')->user()->institute_id;
            
        $students = Student::with([
                'course','branch','semester','annual_semester',
                'scheme','batch','institute'
            ])
            ->where('course_type', $request->course_type)
            ->where('course_id', $request->course_id)
            ->where('branch_id', $request->branch_id)
            ->where('institute_id' , $instituteId)
            ->where('batch_id', $request->batch_id);

        if ($request->scheme_id) {
            $students->where('scheme_id', $request->scheme_id);
        }

        // if ($request->course_type === 'Semester Based') {
        //     $students->where('semester_id', $request->semester_id);
        // } else {
        //     $students->where('annual_semester_id', $request->annual_semester_id);
        // }

        $students = $students->get();

        $subject = Subject::findOrFail($request->subject_id);

        /**
         * ✅ MAX MARKS LOGIC
         */
        if ($request->course_type === 'Semester Based') {

            $maxMarks = match ($request->marks_type) {
                'Mid-Sem' => $subject->mid_sem_marks1,
                'Quiz' => $subject->mid_sem_marks2,
                'Practical' => $subject->mid_sem_marks3,
                default => 0
            };

        } else {

            $maxMarks = match ($request->marks_type) {
                'Internal 1' => $subject->internal1,
                'Internal 2' => $subject->internal2,
                'Internal 3' => $subject->internal3,
                default => 0
            };
        }

        

        /**
         * ✅ MARKS QUERY
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
         * ✅ SUBJECT ENROLLMENT
         */
        $studentSubjects = StudentSubject::where([
                'subject_id'  => $request->subject_id,
                'course_type' => $request->course_type,
                'course_id'   => $request->course_id,
                'branch_id'   => $request->branch_id,
            ])
            ->when($request->scheme_id, fn($q) => $q->where('scheme_id', $request->scheme_id))
            ->when(
                $request->course_type === 'Semester Based',
                fn($q) => $q->where('semester_id', $request->semester_id),
                fn($q) => $q->where('annual_semester_id', $request->annual_semester_id)
            )
            ->pluck('student_id')
            ->toArray();

        /**
         * ✅ COMMON DATA
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
         * ✅ VIEW SWITCHING
         */
        $view = $request->course_type === 'Annual Based'
            ? 'institute.mid-sem-marks.annual-result'
            : 'institute.mid-sem-marks.result';

        return view($view, compact(
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
                'Mid-Sem'   => $subject->mid_sem_marks1,
                'Quiz'      => $subject->mid_sem_marks2,
                'Practical' => $subject->mid_sem_marks3,
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
        | 🔎 Record Check
        |--------------------------------------------------------------------------
        */

        $marksQuery = MidSemMark::where([
            'exam_id'         => $exam_id,
            'mid_sem_exam_id' => $request->mid_sem_exam_id,
            'student_id'      => $studentId,
            'subject_id'      => $request->subject_id,
            'institute_id'    => auth('institute')->user()->account_type === 'institute'
                                ? auth('institute')->id()
                                : auth('institute')->user()->institute_id,
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

        $record = $marksQuery->first();

        /*
        |--------------------------------------------------------------------------
        | 💾 Data Prepare
        |--------------------------------------------------------------------------
        */

        $data = [
            'exam_id'            => $exam_id,
            'mid_sem_exam_id'    => $request->mid_sem_exam_id,
            'student_id'         => $studentId,
            'subject_id'         => $request->subject_id,
            'institute_id'       => auth('institute')->user()->account_type === 'institute'
                                    ? auth('institute')->id()
                                    : auth('institute')->user()->institute_id,
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
                                    : min((float)$request->marks, $maxMarks),

            'exam_status'        => $request->exam_status ?? 'present',
            
            'upload_by'          => $instituteId,
        ];

        /**
         *  Save
         */
        // if ($record) {
        //     $record->update($data);
        // } else {
        //     MidSemMark::create($data);
        // }

        // return response()->json(['status' => true]);
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

                

                if (count(array_filter($row)) === 0) continue;

                $row = array_combine($header, $row);
                if (!$row) continue;

                // ============================
                // 1️⃣ Basic Master Lookups
                // ============================

                $course   = Course::where('name', trim($row['course_name']))->first();
                $branch   = $course
                    ? Branch::where('course_id', $course->id)
                            ->where('name', trim($row['branch_name']))
                            ->first()
                    : null;

                $semester = Semester::where('name', trim($row['semester_name']))->first();
                $annual   = AnnualSemester::where('name', trim($row['annual_semester_name']))->first();

                $batch    = Batch::where('name', trim($row['batch_name']))->first();
                $institute= Institute::where('name', trim($row['institute_name']))->first();

                // ============================
                // 2️⃣ Scheme (Optional)
                // ============================

                $scheme = null;
                if (!empty(trim($row['scheme_name']))) {
                    $scheme = Scheme::where('name', trim($row['scheme_name']))->first();
                }

                // ============================
                // 3️⃣ Student Lookup
                // based on university_roll_no
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
                //                 ->whereNull('annual_semester_id');
                // } else {
                //     $studentQuery->where('annual_semester_id', $annual?->id)
                //                 ->whereNull('semester_id');
                // }

                if ($scheme) {
                    $studentQuery->where('scheme_id', $scheme->id);
                }

                $student = $studentQuery->first();

                if (!$student) {
                    $skipCount++;
                    continue;
                }


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
                // 5️⃣ Exam Lookup
                // Based only on MidSemExam table
                // ============================

                $midSemExam = MidSemExam::where('mid_sem_exam_name', trim($row['internal_exam_name']))
                    ->where('status', 1) // optional: only active exams
                    ->first();

                if (!$midSemExam) {
                    $skipCount++;
                    continue;
                }

                // exam_id directly from mid_sem_exam table
                $examId = $midSemExam->exam_id;

                

                // ============================
                // 6️⃣ Duplicate Check
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
                // 7️⃣ Exam Status Handling
                // ============================

                $examStatus = strtolower(trim($row['exam_status']));
                $obtainedMarks = ($examStatus === 'absent')
                    ? 0
                    : (float) $row['obtained_marks'];

                // ============================
                // 8️⃣ Insert Marks
                // ============================

                // $data = [
                //     'exam_id'              => $examId,
                //     'mid_sem_exam_id'      => $midSemExam->id,
                //     'student_id'           => $student->id,
                //     'subject_id'           => $subject->id,
                //     'institute_id'         => $institute?->id,
                //     'course_type'          => trim($row['course_type']),
                //     'course_id'            => $course?->id,
                //     'branch_id'            => $branch?->id,
                //     'batch_id'             => $batch?->id,
                //     'semester_id'          => $semester?->id ?? null,
                //     'annual_semester_id'   => $annual?->id ?? null,
                //     'scheme_id'            => $scheme?->id,
                //     'marks_type'           => trim($row['marks_type']),
                //     'max_marks'            => (float) $row['max_marks'],
                //     'obtained_marks'       => $obtainedMarks,
                //     'exam_status'          => $examStatus,
                //     'is_locked'            => 0,
                //     'university_forword'   => 0,
                // ];

                // echo "<pre>";
                // print_r($data);

                
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

            return back()->with('success',
                "Inserted: {$insertCount} | Skipped (Already/Invalid): {$skipCount}"
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }


    // public function export(Request $request)
    // {
    //     // Get Institute ID (as per your condition)
    //     $institute_id = auth('institute')->user()->account_type === 'institute'
    //         ? auth('institute')->id()
    //         : auth('institute')->user()->institute_id;

    //     // Get filter inputs
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

    //     // Fetch master data names
    //     $course   = Course::find($course_id);
    //     $scheme   = Scheme::find($scheme_id);
    //     $branch   = Branch::find($branch_id);
    //     $semester = Semester::find($semester_id);
    //     $annual   = AnnualSemester::find($annual_semester_id);
    //     $batch    = Batch::find($batch_id);
    //     $exam     = MidSemExam::find($mid_sem_exam_id);
    //     $institute = Institute::find($institute_id);
    //     $subject   = Subject::find($subject_id);

    //     // Get Students based on filters
    //     $students = Student::where('institute_id', $institute_id)
    //         ->where('course_type', $course_type)
    //         ->where('course_id', $course_id)
    //         ->where('branch_id', $branch_id)
    //         ->where('batch_id', $batch_id)
    //         ->when($scheme_id, fn($q) => $q->where('scheme_id', $scheme_id))
    //         ->when($course_type == 'Semester Based', fn($q) => $q->where('semester_id', $semester_id))
    //         ->when($course_type == 'Annual Based', fn($q) => $q->where('annual_semester_id', $annual_semester_id))
    //         ->get();

    //     // CSV File Name
    //     $fileName = "mid_sem_marks_sample_" . time() . ".csv";

    //     // CSV Headers
    //     $headers = [
    //         "Content-type"        => "text/csv",
    //         "Content-Disposition" => "attachment; filename=$fileName",
    //         "Pragma"              => "no-cache",
    //         "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
    //         "Expires"             => "0"
    //     ];

    //     // CSV Columns
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
    //         'annual_semester_name',
    //         'batch_name',
    //         'institute_name',
    //         'marks_type',
    //         'subject_name',
    //         'subject_code',
    //         'max_marks',
    //         'obtained_marks',
    //         'exam_status'
    //     ];

    //     $callback = function () use (
    //         $students, $columns, $course, $scheme, $branch,
    //         $semester, $annual, $batch, $institute,
    //         $subject, $exam, $marks_type
    //     ) {
    //         $file = fopen('php://output', 'w');

    //         // Header Row
    //         fputcsv($file, $columns);

    //         foreach ($students as $key => $student) {

    //             $max_marks = null;
                

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
    //                 $annual->name ?? '',
    //                 $batch->name ?? '',
    //                 $institute->name ?? '',
    //                 $marks_type,
    //                 $subject->name ?? '',
    //                 $subject->subject_code ?? '',
    //                 '', // max_marks (blank)
    //                 '', // obtained_marks (blank)
    //                 'present' // default
    //             ];

    //             fputcsv($file, $row);
    //         }

    //         fclose($file);
    //     };

    //     return new StreamedResponse($callback, 200, $headers);
    // }

    public function export(Request $request)
    {
        // ✅ Get Institute ID
        $institute_id = auth('institute')->user()->account_type === 'institute'
            ? auth('institute')->id()
            : auth('institute')->user()->institute_id;

        // ✅ Get filter inputs
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

        // ✅ Fetch master data
        $course    = Course::find($course_id);
        $scheme    = Scheme::find($scheme_id);
        $branch    = Branch::find($branch_id);
        $semester  = Semester::find($semester_id);
        $annual    = AnnualSemester::find($annual_semester_id);
        $batch     = Batch::find($batch_id);
        $exam      = MidSemExam::find($mid_sem_exam_id);
        $institute = Institute::find($institute_id);
        $subject   = Subject::find($subject_id);

        // ✅ Students with subject mapping
        $students = Student::where('institute_id', $institute_id)
            ->where('course_type', $course_type)
            ->where('course_id', $course_id)
            ->where('branch_id', $branch_id)
            ->where('batch_id', $batch_id)

            // 🔥 IMPORTANT FILTER (student_subjects mapping)
            ->whereExists(function ($query) use (
                $subject_id, $scheme_id, $course_type, $semester_id, $annual_semester_id
            ) {
                $query->selectRaw('1')
                    ->from('student_subjects as ss')
                    ->whereColumn('ss.student_id', 'students.id')
                    ->where('ss.subject_id', $subject_id);

                if ($scheme_id) {
                    $query->where('ss.scheme_id', $scheme_id);
                }

                if ($course_type === 'Semester Based') {
                    $query->where('ss.semester_id', $semester_id)
                        ->whereNull('ss.annual_semester_id');
                } else {
                    $query->where('ss.annual_semester_id', $annual_semester_id)
                        ->whereNull('ss.semester_id');
                }
            })

            ->when($scheme_id, fn($q) => $q->where('scheme_id', $scheme_id))
            // ->when($course_type == 'Semester Based', fn($q) => $q->where('semester_id', $semester_id))
            // ->when($course_type == 'Annual Based', fn($q) => $q->where('annual_semester_id', $annual_semester_id))
            ->get();

        // ✅ CSV File Name
        $fileName = "mid_sem_marks_sample_" . time() . ".csv";

        // ✅ Headers
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // ✅ Columns
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
            'annual_semester_name',
            'batch_name',
            'institute_name',
            'marks_type',
            'subject_name',
            'subject_code',
            'max_marks',
            'obtained_marks',
            'exam_status'
        ];

        $callback = function () use (
            $students, $columns, $course, $scheme, $branch,
            $semester, $annual, $batch, $institute,
            $subject, $exam, $marks_type
        ) {
            $file = fopen('php://output', 'w');

            // Header
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
                    $annual->name ?? '',
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

}
