<?php

namespace App\Http\Controllers;

use App\Models\AcademicAdjustment;
use App\Models\AnnualSemester;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\EndSemMark;
use App\Models\Exam;
use App\Models\GraceMarkPolicy;
use App\Models\Institute;
use App\Models\MarksGrade;
use App\Models\MidSemExam;
use App\Models\MidSemMark;
use App\Models\PassPercentage;
use App\Models\ResultGpSheet;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentResultSummary;
use App\Models\StudentSubject;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResultController extends Controller
{
    public function internalMarks()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.internal-marks.index', compact('courses', 'exams'));
    }

    public function internalMarksSubjectWise()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.internal-marks.subject-wise.index', compact('courses', 'exams'));
    }


    public function fetchInternalMarks(Request $request)
    {
        $examId = $request->exam_id;
        $courseType = $request->course_type;
        $courseId = $request->course_id;
        $branchId = $request->branch_id;
        $batchId = $request->batch_id;
        $schemeId = $request->scheme_id;
        $semesterId = $request->semester_id;
        $annualSemesterId = $request->annual_semester_id;
        $instituteId = $request->institute_id;

        /*
        ==============================
        EXAM
        ==============================
        */

        $exam = Exam::find($examId);

        /*
        ==============================
        COURSE / BRANCH
        ==============================
        */

        $course = Course::find($courseId);
        $branch = Branch::find($branchId);

        /*
        ==============================
        CLASS NAME
        ==============================
        */

        if ($courseType == "Semester Based") {

            $semester = Semester::find($semesterId);
            $className = $semester->name . " Semester";

        } else {

            $semester = AnnualSemester::find($annualSemesterId);
            $className = $semester->name;

        }

        /*
        ==============================
        SUBJECTS
        ==============================
        */

        $subjects = Subject::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where(function ($q) {
                $q->where('is_nc', 0)
                    ->orWhereNull('is_nc');
            })
            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })
            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');
            })
            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                $q->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id');
            })
            ->orderBy('type')
            ->orderBy('subject_code')
            ->get();

        /*
        ==============================
        STUDENTS
        ==============================
        */

        $students = Student::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })
            // ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
            //     $q->where('semester_id', $semesterId)
            //     ->whereNull('annual_semester_id');
            // })
            // ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
            //     $q->where('annual_semester_id', $annualSemesterId)
            //     ->whereNull('semester_id');
            // })
            ->when($instituteId, function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            })
            ->orderBy('institute_id')          // first sort
            ->orderBy('university_roll_no')    // second sort
            ->get();

        /*
        ==============================
        MARKS
        ==============================
        */

        $rawMarks = MidSemMark::where('exam_id', $examId)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->where('is_locked', 1)
            ->where('university_forword', 1)
            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })
            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');
            })
            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                $q->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id');
            })
            ->get();

        /*
        ==============================
        FORMAT MARKS ARRAY
        ==============================
        */

        $marks = [];

        foreach ($rawMarks as $mark) {

            $value = '-';

            if ($mark->exam_status == 'absent') {
                $value = '<span class="text-danger">AB</span>';
            } else {
                $value = $mark->obtained_marks;
            }

            $marks[$mark->student_id][$mark->subject_id][$mark->marks_type] = $value;
        }

        $studentSubjects = StudentSubject::where('course_type', $courseType)
        ->where('course_id', $courseId)
        ->where('branch_id', $branchId)
        ->when($schemeId, function ($q) use ($schemeId) {
            $q->where('scheme_id', $schemeId);
        })
        ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
            $q->where('semester_id', $semesterId);
        }, function ($q) use ($annualSemesterId) {
            $q->where('annual_semester_id', $annualSemesterId);
        })
        ->get()
        ->groupBy('student_id')
        ->map(function ($q) {
            return $q->pluck('subject_id')->toArray();
        })
        ->toArray();


        $view = $courseType === 'Annual Based'
        ? 'backend.result.internal-marks.annual-result'
        : 'backend.result.internal-marks.result';

        return view(
            $view,
            compact(
                'exam',
                'course',
                'branch',
                'className',
                'subjects',
                'students',
                'marks',
                'studentSubjects'
            )
        )->render();
    }



    public function fetchInternalMarksSubjectWise(Request $request)
    {
        $examId            = $request->exam_id;
        $midsemexamId      = $request->midsem_exam_id;
        $courseType        = $request->course_type;
        $courseId          = $request->course_id;
        $branchId          = $request->branch_id;
        $batchId           = $request->batch_id;
        $schemeId          = $request->scheme_id;
        $semesterId        = $request->semester_id;
        $annualSemesterId  = $request->annual_semester_id;
        $instituteId       = $request->institute_id;
        $marksType         = $request->marks_type;
        $subjectId         = $request->subject_id;

        /*
        =============================
        EXAM
        =============================
        */

        $exam = Exam::find($examId);

        $midsemexam = MidSemExam::find($midsemexamId);

        /*
        =============================
        COURSE / BRANCH
        =============================
        */

        $course = Course::find($courseId);
        $branch = Branch::find($branchId);
        $batch = Batch::find($batchId);

        /*
        =============================
        SEMESTER NAME
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
        SUBJECT
        =============================
        */

        $subject = Subject::find($subjectId);

        /*
        =============================
        STUDENTS
        =============================
        */

        $students = Student::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })

            // ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
            //     $q->where('semester_id', $semesterId)
            //     ->whereNull('annual_semester_id');
            // })

            // ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
            //     $q->where('annual_semester_id', $annualSemesterId)
            //     ->whereNull('semester_id');
            // })

            ->when($instituteId, function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            })

            ->orderBy('institute_id')
            ->orderBy('university_roll_no')
            ->get();

        /*
        =============================
        MARKS
        =============================
        */

        $rawMarks = MidSemMark::where('exam_id', $examId)
            ->where('subject_id', $subjectId)
            ->where('marks_type', $marksType)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            // ->where('is_locked', 1)
            ->where('university_forword', 1)

            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');
            })

            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                $q->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id');
            })

            ->get();

        /*
        =============================
        FORMAT MARKS
        =============================
        */

        $marks = [];

        // foreach ($rawMarks as $mark) {

        //     if ($mark->exam_status == 'absent') {

        //         $marks[$mark->student_id] = 'AB';

        //     } else {

        //         $marks[$mark->student_id] = $mark->obtained_marks;
        //     }
        // }

        foreach ($rawMarks as $mark) {

            $marks[$mark->student_id] = [

                'obtained_marks' => $mark->obtained_marks,

                'exam_status' => $mark->exam_status,

                'grace_marks' => $mark->grace_marks ?? 0,

                'is_locked' => $mark->is_locked ?? 0,
            ];
        }



        $studentSubjects = StudentSubject::where([
            'subject_id'  => $subjectId,
            'course_type' => $courseType,
            'course_id'   => $courseId,
            'branch_id'   => $branchId,
        ])
        ->when($schemeId, function ($q) use ($schemeId) {
            $q->where('scheme_id', $schemeId);
        })
        ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
            $q->where('semester_id', $semesterId);
        }, function ($q) use ($annualSemesterId) {
            $q->where('annual_semester_id', $annualSemesterId);
        })
        ->pluck('student_id')
        ->toArray();

        /*
        =====================================
        ACADEMIC ADJUSTMENTS
        =====================================
        */
        $academicAdjustments = AcademicAdjustment::where('exam_id', $examId)

            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->where('subject_id', $subjectId)

            ->when($schemeId, function ($q) use ($schemeId) {

                $q->where('scheme_id', $schemeId);
            })

            /*
            =====================================
            SEMESTER BASED
            =====================================
            */
            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {

                $q->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');
            })

            /*
            =====================================
            ANNUAL BASED
            =====================================
            */
            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId, $marksType) {

                $q->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id')
                ->where('marks_type', $marksType);
            })

            ->get()
            ->keyBy('student_id');

        return view(
            'backend.result.internal-marks.subject-wise.result',
            compact(
                'exam',
                'midsemexam',
                'courseType',
                'course',
                'branch',
                'className',
                'batch',
                'subject',
                'students',
                'marks',
                'marksType',
                'studentSubjects'
            )
        )->render();
    }


    public function updateInternalMarksLockStatus(Request $request)
    {
        $request->validate([
            'records' => 'required|array'
        ]);

        foreach ($request->records as $record) {

            MidSemMark::where([

                'course_type'        => $record['course_type'],
                'course_id'          => $record['course_id'],
                'branch_id'          => $record['branch_id'],
                'batch_id'           => $record['batch_id'],
                'marks_type'         => $record['marks_type'],
                'student_id'         => $record['student_id'],
                'subject_id'         => $record['subject_id'],
                'exam_id'            => $record['exam_id'],
                'mid_sem_exam_id'    => $record['mid_sem_exam_id'],

            ])

            ->when(!empty($record['scheme_id']), function ($q) use ($record) {

                $q->where('scheme_id', $record['scheme_id']);

            })

            ->when(

                $record['course_type'] == 'Semester Based',

                function ($q) use ($record) {

                    $q->where('semester_id', $record['semester_id'])
                    ->whereNull('annual_semester_id');

                },

                function ($q) use ($record) {

                    $q->where('annual_semester_id', $record['annual_semester_id'])
                    ->whereNull('semester_id');

                }

            )

            ->update([

                'is_locked' => $record['is_locked']

            ]);
        }

        return response()->json([

            'status' => true,
            'message' => 'Lock/Unlock updated successfully.'

        ]);
    }

    public function updateGraceMarks(Request $request)
    {
        $request->validate([
            'records' => 'required|array'
        ]);

        foreach ($request->records as $record) {

            $query = MidSemMark::where([

                'course_type'        => $record['course_type'],
                'course_id'          => $record['course_id'],
                'branch_id'          => $record['branch_id'],
                'batch_id'           => $record['batch_id'],
                'marks_type'         => $record['marks_type'],
                'student_id'         => $record['student_id'],
                'subject_id'         => $record['subject_id'],
                'exam_id'            => $record['exam_id'],
                'mid_sem_exam_id'    => $record['mid_sem_exam_id'],

            ])

            ->when(!empty($record['scheme_id']), function ($q) use ($record) {

                $q->where('scheme_id', $record['scheme_id']);

            })

            ->when(

                $record['course_type'] == 'Semester Based',

                function ($q) use ($record) {

                    $q->where('semester_id', $record['semester_id'])
                    ->whereNull('annual_semester_id');

                },

                function ($q) use ($record) {

                    $q->where('annual_semester_id', $record['annual_semester_id'])
                    ->whereNull('semester_id');

                }

            );

            /*
            =====================================
            GET CURRENT MARK RECORD
            =====================================
            */

            $markData = $query->first();

            if (!$markData) {
                continue;
            }

            $obtainedMarks = (float) $markData->obtained_marks;
            $maxMarks      = (float) $markData->max_marks;

            $requestedGrace = (float) $record['grace_marks'];

            /*
            =====================================
            CHECK MAX LIMIT
            =====================================

            Example:
            obtained = 9
            max = 10
            entered grace = 3

            allowed grace = 1
            */

            $allowedGrace = $maxMarks - $obtainedMarks;

            /*
            If obtained marks already equal/exceed max
            then grace should be 0
            */

            if ($allowedGrace < 0) {

                $allowedGrace = 0;

            }

            /*
            Final grace marks
            */

            $finalGraceMarks = min($requestedGrace, $allowedGrace);

            /*
            =====================================
            UPDATE
            =====================================
            */

            $query->update([

                'grace_marks' => $finalGraceMarks

            ]);
        }

        return response()->json([

            'status' => true,
            'message' => 'Grace marks updated successfully.'

        ]);
    }


    public function externalMarks()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.external-marks.index', compact('courses', 'exams'));
    }

    public function externalMarksSubjectWise()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.external-marks.subject-wise.index', compact('courses', 'exams'));
    }

    // public function fetchExternalMarksSubjectWise(Request $request){
    //     dd($request->all());
    // }

    public function fetchExternalMarksSubjectWise(Request $request)
    {
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
        $marksType         = $request->marks_type;

        /*
        =====================================
        EXAM / COURSE / BRANCH / SUBJECT
        =====================================
        */
        $exam       = Exam::find($examId);
        $course     = Course::find($courseId);
        $branch     = Branch::find($branchId);
        $subject    = Subject::find($subjectId);

        /*
        =====================================
        CLASS NAME
        =====================================
        */
        if ($courseType == "Semester Based") {

            $semester  = Semester::find($semesterId);
            $className = $semester->name . " Semester";

        } else {

            $semester  = AnnualSemester::find($annualSemesterId);
            $className = $semester->name;
        }

        /*
        =====================================
        STUDENTS
        =====================================
        */
        $students = Student::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })

            // ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {

            //     $q->where('semester_id', $semesterId)
            //         ->whereNull('annual_semester_id');

            // })

            // ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {

            //     $q->where('annual_semester_id', $annualSemesterId)
            //         ->whereNull('semester_id');

            // })

            ->when($instituteId, function ($q) use ($instituteId) {

                $q->where('institute_id', $instituteId);

            })

            ->orderBy('institute_id')
            ->orderBy('university_roll_no')
            ->get();

        /*
        =====================================
        MARKS
        =====================================
        */
        $rawMarks = EndSemMark::where('exam_id', $examId)

            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->where('subject_id', $subjectId)
            ->where('marks_type', $marksType)

            ->when($schemeId, function ($q) use ($schemeId) {

                $q->where('scheme_id', $schemeId);

            })

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {

                $q->where('semester_id', $semesterId)
                    ->whereNull('annual_semester_id');

            })

            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {

                $q->where('annual_semester_id', $annualSemesterId)
                    ->whereNull('semester_id');

            })

            // ->when($courseType == 'Annual Based', function ($q) use ($marksType) {

            //     $q->where('marks_type', $marksType);

            // })

            ->get()
            ->keyBy('student_id');


        /*
        =====================================
        ACADEMIC ADJUSTMENTS
        =====================================
        */
        $academicAdjustments = AcademicAdjustment::where('exam_id', $examId)

            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->where('subject_id', $subjectId)

            ->when($schemeId, function ($q) use ($schemeId) {

                $q->where('scheme_id', $schemeId);
            })

            /*
            =====================================
            SEMESTER BASED
            =====================================
            */
            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {

                $q->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');
            })

            /*
            =====================================
            ANNUAL BASED
            =====================================
            */
            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId, $marksType) {

                $q->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id')
                ->where('marks_type', $marksType);
            })

            ->get()
            ->keyBy('student_id');


        $moderationStudents = [];

        foreach ($students as $student) {

            $moderationStudents[] = $student->id;
            // $moderationStudents[] = [
            //     'student_id'         => $student->id,
            //     'admission_no'       => $student->admission_no,
            //     'university_roll_no' => $student->university_roll_no,
            //     'student_name'       => $student->student_name,
            // ];

        }

        /*
        =====================================
        VIEW
        =====================================
        */
        $view = $courseType == 'Annual Based'
            ? 'backend.result.external-marks.subject-wise.annual-result'
            : 'backend.result.external-marks.subject-wise.result';

        return view(
            $view,
            compact(
                'exam',
                'course',
                'schemeId',
                'annualSemesterId',
                'instituteId',
                'branchId',
                'batchId',
                'branch',
                'className',
                'subject',
                'students',
                'rawMarks',
                'courseType',
                'marksType',
                'academicAdjustments',
                'moderationStudents'
            )
        )->render();
    }


    public function exportExternalMarks(Request $request)
    {
        // dd($request->all());
        $institute_id       = $request->export_institute_id;
        $exam_id            = $request->export_exam_id;
        $course_type        = $request->export_course_type;
        $course_id          = $request->export_course_id;
        $scheme_id          = $request->export_scheme_id;
        $branch_id          = $request->export_branch_id;
        $semester_id        = $request->export_semester_id;
        $annual_semester_id = $request->export_annual_semester_id;
        $batch_id           = $request->export_batch_id;
        $marks_type         = $request->export_marks_type;
        $subject_id         = $request->export_subject_id;

        // Master Data
        $course   = Course::find($course_id);
        $scheme   = $scheme_id ? Scheme::find($scheme_id) : null;
        $branch   = Branch::find($branch_id);
        $semester = Semester::find($semester_id);
        $annual   = AnnualSemester::find($annual_semester_id);
        $batch    = Batch::find($batch_id);
        $subject  = Subject::find($subject_id);
        $exam     = Exam::find($exam_id);
        $institute = Institute::find($institute_id);

        // Student Query
        $students = Student::query();

        if (!empty($institute_id)) {
            $students->where('institute_id', $institute_id);
        }

        $students->where('course_type', $course_type)
            ->where('course_id', $course_id)
            ->where('branch_id', $branch_id)
            ->where('batch_id', $batch_id);

        if (!empty($scheme_id)) {
            $students->where('scheme_id', $scheme_id);
        }

        if ($course_type == 'Semester Based') {

            $students->where('semester_id', $semester_id);

        } elseif ($course_type == 'Annual Based') {

            $students->where('annual_semester_id', $annual_semester_id);
        }

        $students->whereExists(function ($query) use (
            $subject_id,
            $scheme_id,
            $course_type,
            $semester_id,
            $annual_semester_id
        ) {

            $query->selectRaw('1')
                ->from('student_subjects as ss')
                ->whereColumn('ss.student_id', 'students.id')
                ->where('ss.subject_id', $subject_id);

            if (!empty($scheme_id)) {
                $query->where('ss.scheme_id', $scheme_id);
            }

            if ($course_type == 'Semester Based') {

                $query->where('ss.semester_id', $semester_id);

            } else {

                $query->where('ss.annual_semester_id', $annual_semester_id);
            }
        });

        $students = $students->orderBy('student_name')->get();

        // DEBUG
        /*
        dd([
            'total_students' => $students->count(),
            'student_ids' => $students->pluck('id')
        ]);
        */

        $subjectCode = $subject->subject_code ?? 'subject';
        $fileName = 'external_marks_' . $subjectCode . '_' . time() . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate',
            'Expires'             => '0',
        ];

        $callback = function () use (
            $students,
            $course,
            $scheme,
            $branch,
            $semester,
            $annual,
            $batch,
            $subject,
            $exam,
            $marks_type,
            $institute
        ) {

            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'sr_no',
                'university_roll_no',
                'student_name',
                'exam_name',
                'course_type',
                'course_name',
                'scheme_name',
                'branch_name',
                'semester_name',
                'annual_term',
                'batch_name',
                'institute_name',
                'marks_type',
                'subject_code',
                'subject_name',
                'max_marks',
                'obtained_marks',
                'exam_status'
            ]);

            foreach ($students as $key => $student) {

                fputcsv($file, [
                    $key + 1,
                    $student->university_roll_no,
                    $student->student_name,
                    $exam->name ?? '',
                    $student->course_type,
                    $course->name ?? '',
                    $scheme->name ?? '',
                    $branch->name ?? '',
                    $semester->name ?? '',
                    $annual->name ?? '',
                    $batch->name ?? '',
                    $institute->name ?? '',
                    $marks_type,
                    $subject->subject_code ?? '',
                    $subject->name ?? '',
                    '',
                    '',
                    'Present'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importExternalMarks(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        try {

            $path = $request->file('import_file')->getRealPath();
            $rawData = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if (!$rawData || count($rawData) <= 1) {
                return back()->with('error', 'CSV file is empty or invalid.');
            }

            $data = array_map('str_getcsv', $rawData);

            // Header Normalize
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

                $annual = AnnualSemester::where('name', trim($row['annual_term']))->first();

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
                // ============================

                $studentQuery = Student::where('university_roll_no', trim($row['university_roll_no']))
                    ->where('course_type', trim($row['course_type']))
                    ->where('course_id', $course?->id)
                    ->where('branch_id', $branch?->id)
                    ->where('batch_id', $batch?->id)
                    ->where('institute_id', $institute?->id);

                // Semester condition intentionally removed

                if ($scheme) {
                    $studentQuery->where('scheme_id', $scheme->id);
                }

                $student = $studentQuery->first();

                if (!$student) {
                    $skipCount++;
                    continue;
                }

                // ============================
                // 4️⃣ Subject Lookup
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
                // 5️⃣ Exam Lookup (External)
                // ============================

                $exam = Exam::where('name', trim($row['exam_name']))
                    ->first();

                if (!$exam) {
                    $skipCount++;
                    continue;
                }

                $examId = $exam->id;

                // ======= PART-2 =======
                            // ============================
                // 6️⃣ Duplicate Check
                // ============================

                $courseType = strtolower(trim($row['course_type']));

                $existsQuery = EndSemMark::where('student_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->where('exam_id', $examId)
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
                // 7️⃣ exam_status Handling
                // ============================

                $examStatus = strtolower(trim($row['exam_status']));

                $obtainedMarks = ($examStatus === 'absent')
                    ? 0
                    : (float) $row['obtained_marks'];

                // ============================
                // 8️⃣ Insert Marks
                // ============================

                $courseType = ucwords(trim($row['course_type']));

                EndSemMark::create([

                    'exam_id'              => $examId,
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

                    'grace_marks'          => null,
                    'grade'                => null,

                    'exam_status'          => $examStatus,
                    'is_locked'            => 0,
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

            return back()->with(
                'error',
                'Import failed: ' . $e->getMessage()
            );
        }
    }



    public function saveAcademicAdjustment(Request $request)
    {
        try {

            /*
            =========================================
            CHANGED : DYNAMIC LIMITS
            =========================================
            */

            // Semester Based :
            // Per Subject = 2
            // Overall      = 4

            // Annual Based :
            // Per Subject = 3
            // Overall      = 5

            $perSubjectLimit = $request->course_type == 'Semester Based' ? 2 : 3;

            $overallLimit = $request->course_type == 'Semester Based' ? 4 : 5;

            /*
            =========================================
            VALIDATION
            =========================================
            */
            $request->validate([

                'exam_id'            => 'required',
                'student_id'         => 'required',
                'subject_id'         => 'required',

                'course_type'        => 'required',

                'course_id'          => 'required',
                'branch_id'          => 'required',
                'batch_id'           => 'required',

                /*
                =========================================
                CHANGED : DYNAMIC MAX VALIDATION
                =========================================
                */
                'adjustment_marks'   => 'required|numeric|min:0|max:'.$perSubjectLimit,

                'adjustment_type'    => 'required',
                'adjustment_scope'   => 'required',
            ]);

            /*
            =========================================
            CHANGED : PER SUBJECT VALIDATION
            =========================================
            */
            if($request->adjustment_marks > $perSubjectLimit){

                return response()->json([

                    'status'  => false,

                    'message' =>
                        $request->course_type == 'Semester Based'
                        ? 'Maximum 2 grace marks allowed per subject in Semester Based course'
                        : 'Maximum 3 grace marks allowed per subject in Annual Based course'

                ], 422);
            }

            /*
            =========================================
            CURRENT TOTAL USED GRACE
            =========================================
            */
            $currentGraceQuery = AcademicAdjustment::where('exam_id', $request->exam_id)

                ->where('student_id', $request->student_id)

                ->where('course_type', $request->course_type)

                ->where('course_id', $request->course_id)
                ->where('branch_id', $request->branch_id)
                ->where('batch_id', $request->batch_id)

                ->where('adjustment_scope', $request->adjustment_scope);

            /*
            =========================================
            SEMESTER / ANNUAL FILTER
            =========================================
            */
            if($request->course_type == 'Semester Based'){

                $currentGraceQuery
                    ->where('semester_id', $request->semester_id)
                    ->whereNull('annual_semester_id');

            }else{

                $currentGraceQuery
                    ->where('annual_semester_id', $request->annual_semester_id)
                    ->whereNull('semester_id');
            }

            /*
            =========================================
            EXCLUDE CURRENT RECORD
            =========================================
            */
            $currentGraceQuery->where(function ($q) use ($request) {

                $q->where('subject_id', '!=', $request->subject_id);

                if($request->course_type == 'Annual Based'){

                    $q->orWhere(function ($subQ) use ($request) {

                        $subQ->where('subject_id', $request->subject_id)
                            ->where('marks_type', '!=', $request->marks_type);
                    });
                }
            });

            $alreadyUsedGrace = $currentGraceQuery->sum('adjustment_marks');

            /*
            =========================================
            NEW TOTAL
            =========================================
            */
            $newTotalGrace =
                $alreadyUsedGrace + $request->adjustment_marks;

            /*
            =========================================
            CHANGED : OVERALL LIMIT VALIDATION
            =========================================
            */
            if($newTotalGrace > $overallLimit){

                return response()->json([

                    'status'  => false,

                    'message' =>
                        $request->course_type == 'Semester Based'
                        ? 'Maximum overall 4 grace marks allowed in Semester Based course'
                        : 'Maximum overall 5 grace marks allowed in Annual Based course'

                ], 422);
            }

            /*
            =========================================
            CHECK EXISTING
            =========================================
            */
            $adjustment = AcademicAdjustment::where('exam_id', $request->exam_id)

                ->where('student_id', $request->student_id)
                ->where('subject_id', $request->subject_id)

                ->where('adjustment_scope', $request->adjustment_scope)

                ->when($request->course_type == 'Semester Based', function ($q) use ($request) {

                    $q->where('semester_id', $request->semester_id)
                    ->whereNull('annual_semester_id');
                })

                ->when($request->course_type == 'Annual Based', function ($q) use ($request) {

                    $q->where('annual_semester_id', $request->annual_semester_id)
                    ->whereNull('semester_id')
                    ->where('marks_type', $request->marks_type);
                })

                ->first();

            /*
            =========================================
            UPDATE
            =========================================
            */
            if ($adjustment) {

                $adjustment->update([

                    'adjustment_marks' => $request->adjustment_marks,
                    'adjustment_type'  => $request->adjustment_type,
                    'marks_type'       => $request->marks_type,
                ]);

            } else {

                /*
                =========================================
                CREATE
                =========================================
                */
                AcademicAdjustment::create([

                    'exam_id'            => $request->exam_id,

                    'student_id'         => $request->student_id,
                    'subject_id'         => $request->subject_id,

                    'institute_id'       => $request->institute_id,

                    'course_type'        => $request->course_type,

                    'course_id'          => $request->course_id,
                    'branch_id'          => $request->branch_id,
                    'batch_id'           => $request->batch_id,

                    'semester_id'        => $request->semester_id,
                    'annual_semester_id' => $request->annual_semester_id,

                    'scheme_id'          => $request->scheme_id,

                    'adjustment_marks'   => $request->adjustment_marks,
                    'adjustment_type'    => $request->adjustment_type,
                    'adjustment_scope'   => $request->adjustment_scope,

                    'marks_type'         => $request->marks_type,

                    'is_approved'        => true,
                ]);
            }

            /*
            =========================================
            RESPONSE
            =========================================
            */
            return response()->json([

                'status'  => true,
                'message' => 'Adjustment saved successfully'
            ]);

        } catch (\Throwable $th) {

            return response()->json([

                'status'  => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    public function saveAnnualModeration(Request $request)
    {
        DB::beginTransaction();

        try {

            /*
            =========================================
            ANNUAL BASED LIMITS
            =========================================

            Per Subject = 3
            Overall Student = 5
            */

            $perSubjectLimit = 3;

            $overallLimit = 5;


            /*
            =========================================
            VALIDATION
            =========================================
            */

            $request->validate([

                'exam_id'             => 'required',

                'course_type'         => 'required|in:Annual Based',

                'course_id'           => 'required',

                'branch_id'           => 'required',

                'batch_id'            => 'required',

                'annual_semester_id'  => 'required',

                'subject_id'          => 'required',

                'moderation_students' => 'required',

                'moderation_action'   => 'required|in:apply,revert',

            ]);


            /*
            =========================================
            APPLY VALIDATION
            =========================================
            */

            if ($request->moderation_action == 'apply') {

                $request->validate([

                    'moderation_marks' =>
                        'required|numeric|min:0|max:3',

                ]);


                /*
                =========================================
                PER SUBJECT LIMIT
                =========================================
                */

                if ($request->moderation_marks > $perSubjectLimit) {

                    DB::rollBack();

                    return response()->json([

                        'status' => false,

                        'message' =>
                            'Maximum 3 moderation marks allowed per subject in Annual Based course'

                    ], 422);
                }
            }


            /*
            =========================================
            MODERATION STUDENTS JSON
            =========================================
            */

            $moderationStudents = json_decode(
                $request->moderation_students,
                true
            );


            if (!is_array($moderationStudents)) {

                DB::rollBack();

                return response()->json([

                    'status'  => false,

                    'message' => 'Invalid moderation students data'

                ], 422);
            }


            /*
            =========================================
            STUDENT IDS
            =========================================
            */

            $studentIds = collect($moderationStudents)

                ->filter(function ($studentId) {

                    return !empty($studentId);

                })

                ->map(function ($studentId) {

                    return (int) $studentId;

                })

                ->unique()

                ->values();


            if ($studentIds->isEmpty()) {

                DB::rollBack();

                return response()->json([

                    'status'  => false,

                    'message' => 'No students found for moderation'

                ], 422);
            }


            /*
            =========================================
            EXCLUDED STUDENT ROLL NUMBERS
            =========================================
            */

            $excludedRollNumbers = [];

            if (!empty($request->excluded_students)) {

                $excludedRollNumbers = collect(
                    explode(',', $request->excluded_students)
                )

                ->map(function ($rollNumber) {

                    return trim($rollNumber);

                })

                ->filter()

                ->unique()

                ->values()

                ->toArray();
            }


            /*
            =========================================
            FIND EXCLUDED STUDENT IDS
            =========================================
            */

            $excludedStudentIds = collect();

            if (!empty($excludedRollNumbers)) {

                $excludedStudentIds = Student::whereIn(
                    'university_roll_no',
                    $excludedRollNumbers
                )->pluck('id');
            }


            /*
            =========================================
            REMOVE EXCLUDED STUDENTS
            =========================================
            */

            $studentIds = $studentIds

                ->diff($excludedStudentIds)

                ->values();


            /*
            =========================================
            IF ALL STUDENTS EXCLUDED
            =========================================
            */

            if ($studentIds->isEmpty()) {

                DB::rollBack();

                return response()->json([

                    'status'  => false,

                    'message' => 'All students are excluded from moderation'

                ], 422);
            }


            /*
            =========================================================
            REVERT
            =========================================================
            */

            if ($request->moderation_action == 'revert') {

                $revertedStudents = [];


                /*
                =========================================
                STUDENT-WISE REVERT
                =========================================
                */

                foreach ($studentIds as $studentId) {

                    /*
                    =========================================
                    FIND CURRENT SUBJECT MODERATION
                    =========================================
                    */

                    $adjustmentQuery =
                        AcademicAdjustment::where(
                            'exam_id',
                            $request->exam_id
                        )

                        ->where(
                            'student_id',
                            $studentId
                        )

                        ->where(
                            'subject_id',
                            $request->subject_id
                        )

                        ->where(
                            'course_type',
                            'Annual Based'
                        )

                        ->where(
                            'course_id',
                            $request->course_id
                        )

                        ->where(
                            'branch_id',
                            $request->branch_id
                        )

                        ->where(
                            'batch_id',
                            $request->batch_id
                        )

                        ->where(
                            'annual_semester_id',
                            $request->annual_semester_id
                        )

                        ->whereNull('semester_id')

                        ->where(
                            'marks_type',
                            $request->marks_type
                        )

                        /*
                        =========================================
                        ONLY MODERATION RECORD
                        =========================================
                        */

                        ->where(
                            'adjustment_scope',
                            'external'
                        )

                        ->where(
                            'adjustment_type',
                            'moderation'
                        );


                    /*
                    =========================================
                    DELETE
                    =========================================
                    */

                    $deleted =
                        $adjustmentQuery->delete();


                    /*
                    =========================================
                    TRACK REVERTED
                    =========================================
                    */

                    if ($deleted > 0) {

                        $revertedStudents[] = $studentId;
                    }
                }


                /*
                =========================================
                COMMIT REVERT
                =========================================
                */

                DB::commit();


                /*
                =========================================
                RESPONSE
                =========================================
                */

                return response()->json([

                    'status' => true,

                    'message' =>
                        count($revertedStudents)
                        . ' student(s) moderation reverted successfully',

                    'reverted_count' =>
                        count($revertedStudents),

                ]);
            }


            /*
            =========================================================
            APPLY
            =========================================================
            */

            $appliedStudents = [];

            $skippedStudents = [];


            /*
            =========================================
            APPLY STUDENT-WISE
            =========================================
            */

            foreach ($studentIds as $studentId) {


                /*
                =========================================
                CURRENT TOTAL MODERATION
                =========================================

                Student ke Annual Semester ke
                saare subjects ka moderation total.

                Current subject + current marks_type
                ka existing record exclude hoga.
                */

                $currentModerationQuery =
                    AcademicAdjustment::where(
                        'exam_id',
                        $request->exam_id
                    )

                    ->where(
                        'student_id',
                        $studentId
                    )

                    ->where(
                        'course_type',
                        'Annual Based'
                    )

                    ->where(
                        'course_id',
                        $request->course_id
                    )

                    ->where(
                        'branch_id',
                        $request->branch_id
                    )

                    ->where(
                        'batch_id',
                        $request->batch_id
                    )

                    ->where(
                        'annual_semester_id',
                        $request->annual_semester_id
                    )

                    ->whereNull('semester_id')

                    /*
                    =========================================
                    ONLY MODERATION
                    =========================================
                    */

                    ->where(
                        'adjustment_scope',
                        'external'
                    )

                    ->where(
                        'adjustment_type',
                        'moderation'
                    );


                /*
                =========================================
                EXCLUDE CURRENT SUBJECT + MARKS TYPE
                =========================================
                */

                $currentModerationQuery->where(function ($q) use ($request) {

                    $q->where(
                        'subject_id',
                        '!=',
                        $request->subject_id
                    );

                    $q->orWhere(function ($subQ) use ($request) {

                        $subQ->where(
                            'subject_id',
                            $request->subject_id
                        )

                        ->where(
                            'marks_type',
                            '!=',
                            $request->marks_type
                        );

                    });

                });


                /*
                =========================================
                ALREADY USED MODERATION
                =========================================
                */

                $alreadyUsedModeration =
                    $currentModerationQuery->sum(
                        'adjustment_marks'
                    );


                /*
                =========================================
                NEW TOTAL
                =========================================
                */

                $newTotalModeration =
                    $alreadyUsedModeration
                    + $request->moderation_marks;


                /*
                =========================================
                OVERALL 5 MARKS VALIDATION
                =========================================
                */

                if ($newTotalModeration > $overallLimit) {

                    $student =
                        Student::find($studentId);


                    $skippedStudents[] = [

                        'student_id' =>
                            $studentId,

                        'university_roll_no' =>
                            $student->university_roll_no ?? '',

                        'reason' =>
                            'Maximum overall 5 moderation marks allowed per student'

                    ];

                    continue;
                }


                /*
                =========================================
                CHECK EXISTING MODERATION
                =========================================
                */

                $adjustment =
                    AcademicAdjustment::where(
                        'exam_id',
                        $request->exam_id
                    )

                    ->where(
                        'student_id',
                        $studentId
                    )

                    ->where(
                        'subject_id',
                        $request->subject_id
                    )

                    ->where(
                        'course_type',
                        'Annual Based'
                    )

                    ->where(
                        'course_id',
                        $request->course_id
                    )

                    ->where(
                        'branch_id',
                        $request->branch_id
                    )

                    ->where(
                        'batch_id',
                        $request->batch_id
                    )

                    ->where(
                        'annual_semester_id',
                        $request->annual_semester_id
                    )

                    ->whereNull('semester_id')

                    ->where(
                        'adjustment_scope',
                        'external'
                    )

                    ->where(
                        'adjustment_type',
                        'moderation'
                    )

                    ->where(
                        'marks_type',
                        $request->marks_type
                    )

                    ->first();


                /*
                =========================================
                UPDATE
                =========================================
                */

                if ($adjustment) {

                    $adjustment->update([

                        'adjustment_marks' =>
                            $request->moderation_marks,

                        'adjustment_type' =>
                            'moderation',

                        'marks_type' =>
                            $request->marks_type,

                        'is_approved' =>
                            true,

                    ]);

                }


                /*
                =========================================
                CREATE
                =========================================
                */

                else {

                    AcademicAdjustment::create([

                        'exam_id' =>
                            $request->exam_id,

                        'student_id' =>
                            $studentId,

                        'subject_id' =>
                            $request->subject_id,

                        'institute_id' =>
                            $request->institute_id,

                        'course_type' =>
                            'Annual Based',

                        'course_id' =>
                            $request->course_id,

                        'branch_id' =>
                            $request->branch_id,

                        'batch_id' =>
                            $request->batch_id,

                        'semester_id' =>
                            null,

                        'annual_semester_id' =>
                            $request->annual_semester_id,

                        'scheme_id' =>
                            $request->scheme_id,

                        'adjustment_marks' =>
                            $request->moderation_marks,

                        'adjustment_type' =>
                            'moderation',

                        'adjustment_scope' =>
                            'external',

                        'marks_type' =>
                            $request->marks_type,

                        'is_approved' =>
                            true,

                    ]);
                }


                /*
                =========================================
                APPLIED
                =========================================
                */

                $appliedStudents[] = $studentId;
            }


            /*
            =========================================
            COMMIT
            =========================================
            */

            DB::commit();


            /*
            =========================================
            RESPONSE MESSAGE
            =========================================
            */

            $message =
                count($appliedStudents)
                . ' student(s) moderation applied successfully';


            if (count($skippedStudents) > 0) {

                $message .=
                    ' and '
                    . count($skippedStudents)
                    . ' student(s) skipped because overall 5 moderation marks limit was exceeded.';
            }


            return response()->json([

                'status' =>
                    true,

                'message' =>
                    $message,

                'applied_count' =>
                    count($appliedStudents),

                'skipped_count' =>
                    count($skippedStudents),

                'skipped_students' =>
                    $skippedStudents,

            ]);


        } catch (\Throwable $th) {

            DB::rollBack();

            return response()->json([

                'status' =>
                    false,

                'message' =>
                    $th->getMessage()

            ], 500);
        }
    }

    public function fetchExternalMarks(Request $request)
    {
        $examId            = $request->exam_id;
        $courseType        = $request->course_type;
        $courseId          = $request->course_id;
        $branchId          = $request->branch_id;
        $batchId           = $request->batch_id;
        $schemeId          = $request->scheme_id;
        $semesterId        = $request->semester_id;
        $annualSemesterId  = $request->annual_semester_id;
        $instituteId       = $request->institute_id;

        /*
        ==============================
        EXAM / COURSE / BRANCH
        ==============================
        */
        $exam   = Exam::find($examId);
        $course = Course::find($courseId);
        $branch = Branch::find($branchId);

        /*
        ==============================
        CLASS NAME
        ==============================
        */
        if ($courseType == "Semester Based") {

            $semester  = Semester::find($semesterId);
            $className = $semester->name . " Semester";

        } else {

            $semester  = AnnualSemester::find($annualSemesterId);
            $className = $semester->name;
        }

        /*
        ==============================
        SUBJECTS
        ==============================
        */
        $subjects = Subject::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })
            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');
            })
            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                $q->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id');
            })
            ->orderByRaw('CASE WHEN is_nc = 1 THEN 1 ELSE 0 END')
            ->orderBy('type')
            ->orderBy('subject_code')
            ->get();

        /*
        ==============================
        STUDENTS
        ==============================
        */
        $students = Student::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })
            // ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
            //     $q->where('semester_id', $semesterId)
            //     ->whereNull('annual_semester_id');
            // })
            // ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
            //     $q->where('annual_semester_id', $annualSemesterId)
            //     ->whereNull('semester_id');
            // })
            ->when($instituteId, function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            })
            ->orderBy('institute_id')
            ->orderBy('university_roll_no')
            ->get();

        /*
        ==============================
        MARKS FETCH (ðŸ”¥ FIXED)
        ==============================
        */
        $rawMarks = EndSemMark::where('exam_id', $examId)

            ->when(
                $courseType == 'Semester Based' &&
                $course->course != 'Diploma',
                function ($q) {
                    $q->where('marks_type', 'external');
                }
            )

            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');
            })

            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                $q->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id');
            })

            ->get();

        /*
        ==============================
        FORMAT MARKS (ðŸ”¥ FIXED)
        ==============================
        */
        $marks = [];

        foreach ($rawMarks as $mark) {


            if ($courseType == 'Semester Based' && $course->course != 'Diploma') {

                $marks[$mark->student_id][$mark->subject_id] = [
                    'obtained_marks' => $mark->obtained_marks,
                    'exam_status'    => $mark->exam_status,
                    'is_locked'      => $mark->is_locked,
                    'grade'          => $mark->grade
                ];

            } else {


                $marks[$mark->student_id][$mark->subject_id][$mark->marks_type] = [
                    'obtained_marks' => $mark->obtained_marks,
                    'exam_status'    => $mark->exam_status,
                    'is_locked'      => $mark->is_locked,
                    'grade'          => $mark->grade
                ];
            }
        }

        /*
        ==============================
        STUDENT SUBJECT MAPPING
        ==============================
        */
        $studentSubjects = StudentSubject::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })
            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            }, function ($q) use ($annualSemesterId) {
                $q->where('annual_semester_id', $annualSemesterId);
            })
            ->get()
            ->groupBy('student_id')
            ->map(function ($q) {
                return $q->pluck('subject_id')->toArray();
            })
            ->toArray();

        /*
        ==============================
        VIEW
        ==============================
        */


        $view = $courseType == 'Annual Based'
            ? 'backend.result.external-marks.annual-result'
            : 'backend.result.external-marks.result';

        return view(
            $view,
            compact(
                'exam',
                'courseType',
                'course',
                'branch',
                'className',
                'subjects',
                'students',
                'marks',
                'studentSubjects'
            )
        )->render();
    }


    public function saveExternalMarks(Request $request)
    {
        // dd($request->all());
        $instituteId = $request->institute_id;

        if (empty($instituteId)) {
            $student = Student::find($request->student_id);
            $instituteId = $student->institute_id ?? null;
        }

        $isAnnualBased = $request->course_type == "Annual Based";

        /*
        |--------------------------------------------------------------------------
        | SEMESTER BASED
        | marks_type = external (FIXED)
        |--------------------------------------------------------------------------
        */
        if (!$isAnnualBased) {

            $subject = Subject::find($request->subject_id);
            $isNc    = $subject->is_nc ?? 0;

            EndSemMark::updateOrCreate(

                [
                    'exam_id'    => $request->exam_id,
                    'student_id' => $request->student_id,
                    'subject_id' => $request->subject_id,
                    'marks_type' => 'external', // âœ… FIXED TYPE
                ],

                [
                    'course_type'        => $request->course_type,
                    'course_id'          => $request->course_id,
                    'branch_id'          => $request->branch_id,
                    'batch_id'           => $request->batch_id,
                    'semester_id'        => $request->semester_id,
                    'annual_semester_id' => $request->annual_semester_id,
                    'scheme_id'          => $request->scheme_id,
                    'institute_id'       => $instituteId,

                    'marks_type'     => 'external', // âœ… FORCE SAME
                    'max_marks'      => $request->max_marks,
                    // 'obtained_marks' => $request->marks ?? 0,
                    // 'grade'          => null,

                    'obtained_marks' => $isNc ? 0 : ($request->marks ?? 0),
                    'grade'          => $isNc ? $request->marks : null,

                    'exam_status' => $request->status,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ANNUAL BASED
        | marks_type = theory_paper1 / practical_paper1 etc.
        |--------------------------------------------------------------------------
        */
        else {

            $subject = Subject::find($request->subject_id);
            $isNc    = $subject->is_nc ?? 0;

            $marksType = $isNc ? 'external' : $request->marks_type;

            $data = [

                'course_type'        => $request->course_type,
                'course_id'          => $request->course_id,
                'branch_id'          => $request->branch_id,
                'batch_id'           => $request->batch_id,
                'semester_id'        => $request->semester_id,
                'annual_semester_id' => $request->annual_semester_id,
                'scheme_id'          => $request->scheme_id,
                'institute_id'       => $instituteId,

                'marks_type' => $marksType,
                'max_marks'  => $request->max_marks,

                'exam_status' => $request->status,
            ];

            /*
            |--------------------------------------------------------------------------
            | NON CREDIT SUBJECT (GRADE BASED)
            |--------------------------------------------------------------------------
            */
            if ($isNc) {

                $data['grade'] = $request->marks; // Satisfactory / Unsatisfactory
                $data['obtained_marks'] = 0;

            } else {

                $data['grade'] = null;
                $data['obtained_marks'] = $request->marks ?? 0;
            }

            EndSemMark::updateOrCreate(

                [
                    'exam_id'    => $request->exam_id,
                    'student_id' => $request->student_id,
                    'subject_id' => $request->subject_id,
                    'marks_type' => $marksType, // âœ… IMPORTANT (paper wise row)
                ],

                $data
            );
        }

        return response()->json([
            'status' => 'success'
        ]);
    }


    public function updateExternalMarksLock(Request $request)
    {
        $examId            = $request->exam_id;
        $courseType        = $request->course_type;
        $courseId          = $request->course_id;
        $branchId          = $request->branch_id;
        $batchId           = $request->batch_id;
        $semesterId        = $request->semester_id;
        $annualSemesterId  = $request->annual_semester_id;
        $schemeId          = $request->scheme_id;
        $instituteId       = $request->institute_id;

        $records = $request->records ?? [];

        if(empty($records)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No records selected'
            ]);
        }

        foreach ($records as $record) {

            EndSemMark::where('exam_id', $examId)
                ->where('student_id', $record['student_id'])
                ->where('subject_id', $record['subject_id'])
                ->where('course_type', $courseType)
                ->where('course_id', $courseId)
                ->where('branch_id', $branchId)
                ->where('batch_id', $batchId)
                ->when($schemeId, function ($q) use ($schemeId) {
                    $q->where('scheme_id', $schemeId);
                })
                ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                    $q->where('semester_id', $semesterId)
                    ->whereNull('annual_semester_id');
                })
                ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                    $q->where('annual_semester_id', $annualSemesterId)
                    ->whereNull('semester_id');
                })
                ->when($instituteId, function ($q) use ($instituteId) {
                    $q->where('institute_id', $instituteId);
                })
                ->update([
                    'is_locked' => $request->status
                ]);
        }

        return response()->json([
            'status' => 'success'
        ]);
    }


    public function resultPrepare()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.result-prepare.index', compact('courses', 'exams'));
    }

    public function fetchEndSemResult(Request $request)
    {
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
        STUDENTS
        =============================
        */

        $students = Student::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

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
            ? 'backend.result.result-prepare.annual-result'
            : 'backend.result.result-prepare.result';

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
                'adjustmentMarks'
            )
        )->render();
    }

    public function endSemResultPrepare(){
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.result-prepare.endsem.index', compact('courses', 'exams'));
    }

    public function finalFetchEndSemResult(Request $request)
    {
        $examId            = $request->exam_id;
        $courseType        = $request->course_type;
        $courseId          = $request->course_id;
        $branchId          = $request->branch_id;
        $batchId           = $request->batch_id;
        $schemeId          = $request->scheme_id;
        $semesterId        = $request->semester_id;
        $annualSemesterId  = $request->annual_semester_id;
        $instituteId       = $request->institute_id;

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
        $theoryMinPercent   = $passPercentage->theory_min_percentage ?? 20;

        /*
        =============================
        SEM / ANNUAL NAME
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
        SUBJECTS
        =============================
        */
        $subjects = Subject::where('course_id', $courseId)
            ->where('branch_id', $branchId)
            // ->where('is_nc', false)

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');
            })

            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                $q->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id');
            })

            ->orderBy('subject_code')
            ->get();

        /*
        =============================
        STUDENTS
        =============================
        */
        $students = Student::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

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

            ->orderBy('institute_id')
            ->orderBy('university_roll_no')
            ->get();

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
        END SEM MARKS (KEY FIX)
        =============================
        */
        if ($courseType == 'Annual Based') {

            $endSemMarks = EndSemMark::where('exam_id', $examId)
                ->where('is_locked', 1)

                ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

                ->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id')

                ->get()
                ->groupBy(['student_id', 'subject_id', 'marks_type']); // ðŸ”¥

        } else {

            // $endSemMarks = EndSemMark::where('exam_id', $examId)
            //     ->where('is_locked', 1)
            //     ->get()
            //     ->groupBy(['student_id', 'subject_id']);

            $query = EndSemMark::where('exam_id', $examId)
                ->where('is_locked', 1)

                ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

                ->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');

            if ($course->course == 'Diploma') {

                $endSemMarks = $query
                    ->get()
                    ->groupBy(['student_id', 'subject_id', 'marks_type']);

            } else {

                // Existing UG Logic
                $endSemMarks = $query
                    ->get()
                    ->groupBy(['student_id', 'subject_id']);
            }
        }

        /*
        =============================
        ACADEMIC ADJUSTMENTS
        =============================
        */
        $academicAdjustments = AcademicAdjustment::where('exam_id', $examId)

            ->where('adjustment_scope', 'external')

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
        PREVIOUS SEMESTER GP SHEET
        =============================
        */

        // $previousGpSheets = collect();

        // if ($courseType == 'Semester Based' && $semesterId > 1) {

        //     $previousGpSheets = ResultGpSheet::where('course_type', $courseType)
        //         ->where('course_id', $courseId)
        //         ->where('branch_id', $branchId)
        //         ->where('batch_id', $batchId)

        //         ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))
        //         ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

        //         ->where('semester_id', '<', $semesterId)

        //         ->get()
        //         ->groupBy('student_id');
        // }

        /*
        =============================
        PREVIOUS SEMESTER GP SHEET
        =============================
        */

        $previousGpSheets = collect();

        $missingPublishedSemesters = [];

        if ($courseType == 'Semester Based' && $semesterId > 1) {

            // Current semester se pehle ke sab semester
            $requiredPreviousSemesters = range(1, $semesterId - 1);

            // Jitne semester ka result save/publish hua hai
            $publishedSemesters = ResultGpSheet::where('course_type', $courseType)
                ->where('course_id', $courseId)
                ->where('branch_id', $branchId)
                ->where('batch_id', $batchId)

                ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))
                ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

                ->whereIn('semester_id', $requiredPreviousSemesters)

                ->select('semester_id')
                ->distinct()
                ->pluck('semester_id')
                ->toArray();

            // Missing semester find karo
            $missingPublishedSemesters = array_values(array_diff(
                $requiredPreviousSemesters,
                $publishedSemesters
            ));

            // Previous GP Sheet
            $previousGpSheets = ResultGpSheet::with('subject')
                ->where('course_type', $courseType)
                ->where('course_id', $courseId)
                ->where('branch_id', $branchId)
                ->where('batch_id', $batchId)

                ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))
                ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

                ->whereIn('semester_id', $requiredPreviousSemesters)

                ->get()
                ->groupBy('student_id');
        }


        $view = $courseType == 'Annual Based'
            ? 'backend.result.result-prepare.endsem.annual-result'
            : 'backend.result.result-prepare.endsem.result';

        return view(
            $view,
            compact(
                'exam',
                'courseType',
                'course',
				'schemeId',
                'branch',
                'batch',
                'semester',
				'semesterId',
				'annualSemesterId',
				'instituteId',
                'subjects',
                'students',
                'midSemMarks',
                'endSemMarks',
                'overallPassPercent',
				'theoryMinPercent',
                'className',
                'grades',
                'studentSubjects',
                'academicAdjustments',
                'previousGpSheets',
                'missingPublishedSemesters',
            )
        )->render();
    }


    // Save Final Result Sheet ------------------


    public function saveResultSheet(Request $request)
    {
        DB::beginTransaction();

        try {

            $summaryData = $request->input('summary_data', []);
            $gpSheetData = $request->input('gp_sheet_data', []);

            // dd($gpSheetData);

            /*
            |--------------------------------------------------------------------------
            | Student Result Summary
            |--------------------------------------------------------------------------
            */

            foreach ($summaryData as $row) {

                StudentResultSummary::updateOrCreate(

                    [
                        'student_id'          => $row['student_id'],
                        'exam_id'             => $row['exam_id'],
                        'course_type'         => $row['course_type'],
                        'course_id'           => $row['course_id'],
                        'scheme_id'           => $row['scheme_id'],
                        'branch_id'           => $row['branch_id'],
                        'semester_id'         => $row['semester_id'],
                        'annual_semester_id'  => $row['annual_semester_id'],
                        'batch_id'            => $row['batch_id'],
                        'institute_id'        => $row['institute_id'],
                    ],

                    [
                        'gpa'             => $row['gpa'],
                        'cgpa'            => $row['cgpa'],
                        'result_status'   => $row['result_status'],
                        'grade_status'    => $row['grade_status'],
                        'remarks'         => $row['remarks'] ?? null,
                        // 'result_declare'  => $row['result_declare'] ?? 1,
                        'result_declare'  => 0,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Result GP Sheet
            |--------------------------------------------------------------------------
            */

            $subjectCredits = Subject::pluck('credits', 'id')->toArray();

            foreach ($gpSheetData as $row) {

                ResultGpSheet::updateOrCreate(

                    [
                        'student_id'          => $row['student_id'],
                        'subject_id'          => $row['subject_id'],
                        'exam_id'             => $row['exam_id'],
                        'course_type'         => $row['course_type'],
                        'course_id'           => $row['course_id'],
                        'scheme_id'           => $row['scheme_id'],
                        'branch_id'           => $row['branch_id'],
                        'semester_id'         => $row['semester_id'],
                        'annual_semester_id'  => $row['annual_semester_id'],
                        'batch_id'            => $row['batch_id'],
                        'institute_id'        => $row['institute_id'],
                    ],

                    [
                        'result'     => $row['result'],
                        'result_gp'  => $row['result_gp'],
                        'grade'      => $row['grade'] ?? null,
                        'credits'    => $subjectCredits[$row['subject_id']] ?? 0,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Result sheet saved successfully.'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function resultOverview(){
        $exams = Exam::orderBy('id', 'desc')->get();
        $courses = Course::all();
        return view('backend.result.result-overview.index', compact('exams', 'courses'));
    }

    public function fetchResultOverview(Request $request)
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

        // dd($studentId);

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
        =============================
        FETCH RESULT SUMMARY
        =============================
        */

        $results = StudentResultSummary::with([
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

        /*
        =============================
        RETURN VIEW
        =============================
        */
        // dd($results);

        return view(
            'backend.result.result-overview.result',
            compact(
                'results',
                'exam',
                'course',
                'branch',
                'batch',
                'schemeId',
                'institute',
                'className',
                'grades'
            )
        )->render();
    }

    public function declareResult(Request $request)
    {
        try {
            DB::beginTransaction();

            $ids = $request->ids;

            if (empty($ids)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No data selected'
                ], 400);
            }

            // StudentResultSummary::whereIn('id', $ids)
            //     ->update(['result_declare' => 1]);

            StudentResultSummary::whereIn('id', $ids)
            ->update([
                'result_declare' => $request->result_declare
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Result declared successfully!'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to declare result'
            ], 500);
        }
    }


    public function resultDmc(){
        $exams = Exam::orderBy('id', 'desc')->get();
        $courses = Course::all();
        return view('backend.result.dmc-sheet.index', compact('exams', 'courses'));
    }

    public function resultTranscript(){
        $exams = Exam::orderBy('id', 'desc')->get();
        $courses = Course::all();
        return view('backend.result.transcript.index', compact('exams', 'courses'));
    }

    public function fetchTranscript(Request $request)
    {
        $courseType   = $request->course_type;
        $courseId     = $request->course_id;
        $branchId     = $request->branch_id;
        $batchId      = $request->batch_id;
        $schemeId     = $request->scheme_id;
        $instituteId  = $request->institute_id;
        $rollNo       = $request->student_roll_number;

        $semesterIds = $request->semester_id ?? [];
        $annualIds   = $request->annual_semester_id ?? [];

        /*
        =============================
        STUDENT
        =============================
        */
        $student = Student::where('university_roll_no', $rollNo)->first();

        if (!$student) {
            return "<div class='alert alert-danger text-center'>Student not found</div>";
        }

        /*
        =============================
        RESULT GP SHEET DATA
        =============================
        */
        $query = ResultGpSheet::with('subject')
            ->where('student_id', $student->id)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))
            ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId));

        if ($courseType == "Semester Based") {
            $query->whereIn('semester_id', $semesterIds);
        } else {
            $query->whereIn('annual_semester_id', $annualIds);
        }

        $results = $query->get();

        /*
        =============================
        GROUP BY SEMESTER
        =============================
        */
        $grouped = $courseType == "Semester Based"
            ? $results->groupBy('semester_id')
            : $results->groupBy('annual_semester_id');

        /*
        =============================
        CALCULATIONS ðŸ”¥
        =============================
        */

        // ðŸ‘‰ Total Credits (ALL subjects)
        $totalCredits = $results->sum(function ($r) {
            return $r->subject->credits ?? 0;
        });

        // ðŸ‘‰ Total Credit Points (ONLY numeric GP will work automatically)
        // $totalPoints = $results->sum(function ($r) {
        //     $credit = $r->subject->credits ?? 0;
        //     $gp = is_numeric($r->result_gp) ? $r->result_gp : 0;
        //     return $gp * $credit;
        // });

        $totalPoints = $results->sum(function ($r) {

            $credit = $r->subject->credits ?? 0;

            // ðŸ‘‰ agar FAIL ya SUPPLE hai to GP = 0
            if (in_array(strtoupper($r->result), ['FAIL', 'SUPPLE'])) {
                $gp = 0;
            } else {
                $gp = is_numeric($r->result_gp) ? $r->result_gp : 0;
            }

            return $gp * $credit;
        });

        // ðŸ‘‰ CGPA (safe)
        $cgpa = $totalCredits > 0
            ? round($totalPoints / $totalCredits, 3)
            : 0;

        /*
        =============================
        PROFILE IMAGE
        =============================
        */
        $imagePath = $student->profile_image && file_exists(public_path($student->profile_image))
            ? asset($student->profile_image)
            : asset('assets/images/users/blank-profile-picture.webp');

        /*
        =============================
        RETURN VIEW
        =============================
        */
        return view('backend.result.transcript.result', compact(
            'student',
            'grouped',
            'courseType',
            'imagePath',
            'totalCredits',
            'totalPoints',
            'cgpa'
        ))->render();
    }

    public function saveGraceMarks(Request $request)
    {
        DB::beginTransaction();

        try {

            /*
            =====================================
            VALIDATION
            =====================================
            */

            $request->validate([

                'exam_id'     => 'required',
                'student_id'  => 'required',
                'subject_id'  => 'required',

                'course_type' => 'required',
                'course_id'   => 'required',
                'branch_id'   => 'required',
                'batch_id'    => 'required',

                'grace_marks' => 'required|numeric|min:0',

            ]);

            /*
            =====================================
            ANNUAL BASED
            =====================================
            */

            if($request->course_type == 'Annual Based'){

                return response()->json([

                    'status'  => false,

                    'message' => 'Working mode'

                ], 200);
            }

            /*
            =====================================
            CHECK RESULT EXIST
            =====================================
            */

            $mark = EndSemMark::where('exam_id', $request->exam_id)

                ->where('student_id', $request->student_id)
                ->where('subject_id', $request->subject_id)

                ->where('course_type', $request->course_type)
                ->where('course_id', $request->course_id)
                ->where('branch_id', $request->branch_id)
                ->where('batch_id', $request->batch_id)

                ->when($request->scheme_id, function ($q) use ($request) {

                    $q->where('scheme_id', $request->scheme_id);

                })

                /*
                =====================================
                SEMESTER BASED
                =====================================
                */
                ->when($request->course_type == 'Semester Based', function ($q) use ($request) {

                    $q->where('semester_id', $request->semester_id)
                        ->whereNull('annual_semester_id');

                })

                /*
                =====================================
                ANNUAL BASED
                =====================================
                */
                ->when($request->course_type == 'Annual Based', function ($q) use ($request) {

                    $q->where('annual_semester_id', $request->annual_semester_id)
                        ->whereNull('semester_id')
                        ->where('marks_type', $request->marks_type);

                })

                ->first();

            /*
            =====================================
            RESULT NOT EXIST
            =====================================
            */

            if (!$mark) {

                return response()->json([

                    'status'  => false,

                    'message' => 'Result not exist. Please save obtained marks first.'

                ], 404);
            }

            /*
            =====================================
            LOCK CHECK
            =====================================
            */

            if ($mark->is_locked) {

                return response()->json([

                    'status'  => false,

                    'message' => 'Result is locked.'

                ], 400);
            }

            /*
            =====================================
            UPDATE GRACE MARKS
            =====================================
            */

            /*
            =====================================
            MAX ALLOWED GRACE MARKS
            =====================================
            */

            $maxMarks      = (float) $mark->max_marks;

            $obtainedMarks = (float) $mark->obtained_marks;

            $enteredGrace  = (float) $request->grace_marks;

            /*
            =====================================
            REMAINING MARKS
            =====================================
            */

            $remainingMarks = $maxMarks - $obtainedMarks;

            /*
            =====================================
            IF ALREADY FULL MARKS
            =====================================
            */

            if($remainingMarks <= 0){

                $finalGraceMarks = 0;

            }else{

                /*
                =====================================
                STORE ONLY ALLOWED GRACE
                =====================================
                */

                $finalGraceMarks = min(
                    $enteredGrace,
                    $remainingMarks
                );
            }

            /*
            =====================================
            UPDATE
            =====================================
            */

            $mark->update([

                'grace_marks' => $finalGraceMarks

            ]);

            DB::commit();

            return response()->json([

                'status'  => true,

                'message' => 'Grace marks updated successfully',

                'stored_grace_marks' => $finalGraceMarks

            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'status'  => false,

                'message' => $e->getMessage()

            ], 500);
        }
    }

    // below for all subject grace marks,
    public function resultGrace(){
        $courses = Course::orderBy('name')->get();
        $exams = Exam::orderBy('id', 'desc')->get();
        return view('backend.result.grace-marks.index', compact('courses','exams'));
    }

    public function saveResultGrace(Request $request)
    {
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | VALIDATION
            |--------------------------------------------------------------------------
            */

            $request->validate([
                'exam_id'            => 'required',
                'course_type'        => 'required',
                'course_id'          => 'required',
                'branch_id'          => 'required',
                'batch_id'           => 'required',
                'university_roll'    => 'required',

                'semester_id'        => 'required_if:course_type,Semester Based',
                'annual_semester_id' => 'required_if:course_type,Annual Based',

                'grace_action'       => 'required|in:apply,revert',
                'grace_marks'        => 'required|numeric|min:0',
            ]);


            /*
            |--------------------------------------------------------------------------
            | STUDENT
            |--------------------------------------------------------------------------
            */

            $student = Student::where(
                'university_roll_no',
                trim($request->university_roll)
            )->first();

            if (!$student) {

                throw new \Exception(
                    'Student not found'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | FIND RELATED SUBJECTS
            |--------------------------------------------------------------------------
            |
            | Subject ID request se nahi liya ja raha.
            | Saare related subjects automatically fetch honge.
            |
            */

            $subjects = Subject::where(
                'course_type',
                $request->course_type
            )
            ->where(
                'course_id',
                $request->course_id
            )
            ->where(
                'branch_id',
                $request->branch_id
            )

            /*
            |--------------------------------------------------------------------------
            | SCHEME OPTIONAL
            |--------------------------------------------------------------------------
            */

            ->when($request->scheme_id, function ($q) use ($request) {

                $q->where(
                    'scheme_id',
                    $request->scheme_id
                );

            })

            /*
            |--------------------------------------------------------------------------
            | SEMESTER / ANNUAL SEMESTER
            |--------------------------------------------------------------------------
            */

            ->when(
                $request->course_type === 'Semester Based',
                function ($q) use ($request) {

                    $q->where(
                        'semester_id',
                        $request->semester_id
                    );

                }
            )

            ->when(
                $request->course_type === 'Annual Based',
                function ($q) use ($request) {

                    $q->where(
                        'annual_semester_id',
                        $request->annual_semester_id
                    );

                }
            )

            /*
            |--------------------------------------------------------------------------
            | NON NC SUBJECTS ONLY
            |--------------------------------------------------------------------------
            |
            | is_nc = 0 OR NULL
            |
            */

            ->where(function ($q) {

                $q->where('is_nc', 0)
                ->orWhereNull('is_nc');

            })

            ->get();


            /*
            |--------------------------------------------------------------------------
            | SUBJECT CHECK
            |--------------------------------------------------------------------------
            */

            if ($subjects->isEmpty()) {

                throw new \Exception(
                    'No related subjects found'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | SEMESTER BASED
            |--------------------------------------------------------------------------
            */

            if ($request->course_type == 'Semester Based') {

                /*
                |--------------------------------------------------------------------------
                | NOTE
                |--------------------------------------------------------------------------
                | Semester Based ka actual grace calculation abhi original
                | requirement mein define nahi hai.
                |
                */

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Semester Based subjects found successfully.'
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | ANNUAL BASED
            |--------------------------------------------------------------------------
            */

            if ($request->course_type == 'Annual Based') {

                /*
                |--------------------------------------------------------------------------
                | REQUIRED INTERNAL TYPES
                |--------------------------------------------------------------------------
                */

                $requiredTypes = [
                    'Internal 1',
                    'Internal 2',
                    'Internal 3'
                ];


                /*
                |--------------------------------------------------------------------------
                | PROCESS EACH SUBJECT
                |--------------------------------------------------------------------------
                */

                foreach ($subjects as $subject) {

                    /*
                    |--------------------------------------------------------------------------
                    | FETCH STUDENT SUBJECT MARKS
                    |--------------------------------------------------------------------------
                    */

                    $marks = MidSemMark::where(
                        'student_id',
                        $student->id
                    )
                    ->where(
                        'exam_id',
                        $request->exam_id
                    )
                    ->where(
                        'course_type',
                        $request->course_type
                    )
                    ->where(
                        'course_id',
                        $request->course_id
                    )
                    ->where(
                        'branch_id',
                        $request->branch_id
                    )
                    ->where(
                        'batch_id',
                        $request->batch_id
                    )
                    ->where(
                        'annual_semester_id',
                        $request->annual_semester_id
                    )
                    ->where(
                        'subject_id',
                        $subject->id
                    )

                    /*
                    |--------------------------------------------------------------------------
                    | SCHEME OPTIONAL
                    |--------------------------------------------------------------------------
                    */

                    ->when($request->scheme_id, function ($q) use ($request) {

                        $q->where(
                            'scheme_id',
                            $request->scheme_id
                        );

                    })

                    ->get();


                    /*
                    |--------------------------------------------------------------------------
                    | MARKS NOT FOUND
                    |--------------------------------------------------------------------------
                    */

                    if ($marks->isEmpty()) {

                        throw new \Exception(
                            'No marks data found for subject: ' .
                            $subject->name
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CHECK REQUIRED INTERNALS
                    |--------------------------------------------------------------------------
                    */

                    foreach ($requiredTypes as $type) {

                        $exists = $marks
                            ->where('marks_type', $type)
                            ->count();

                        if ($exists == 0) {

                            throw new \Exception(
                                "Missing {$type} marks for subject: " .
                                $subject->name
                            );
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | REVERT GRACE
                    |--------------------------------------------------------------------------
                    */

                    if ($request->grace_action == 'revert') {

                        MidSemMark::where(
                            'student_id',
                            $student->id
                        )
                        ->where(
                            'exam_id',
                            $request->exam_id
                        )
                        ->where(
                            'course_type',
                            $request->course_type
                        )
                        ->where(
                            'course_id',
                            $request->course_id
                        )
                        ->where(
                            'branch_id',
                            $request->branch_id
                        )
                        ->where(
                            'batch_id',
                            $request->batch_id
                        )
                        ->where(
                            'annual_semester_id',
                            $request->annual_semester_id
                        )
                        ->where(
                            'subject_id',
                            $subject->id
                        )

                        ->when($request->scheme_id, function ($q) use ($request) {

                            $q->where(
                                'scheme_id',
                                $request->scheme_id
                            );

                        })

                        ->update([
                            'grace_marks' => null
                        ]);

                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | ALREADY APPLIED CHECK
                    |--------------------------------------------------------------------------
                    |
                    | NULL = Not Applied
                    | 0    = Not Applied
                    | > 0  = Already Applied
                    |
                    */

                    $alreadyApplied = $marks->contains(function ($row) {

                        return $row->grace_marks !== null
                            && (float) $row->grace_marks > 0;

                    });


                    if ($alreadyApplied) {

                        throw new \Exception(
                            'Grace already applied for subject: ' .
                            $subject->name .
                            '. Please revert first.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | GRACE MARKS
                    |--------------------------------------------------------------------------
                    */

                    $remainingGrace = (float) $request->grace_marks;


                    /*
                    |--------------------------------------------------------------------------
                    | SORT ALL INTERNALS
                    |--------------------------------------------------------------------------
                    |
                    | Highest obtained marks first.
                    |
                    */

                    $sortedMarks = $marks
                        ->sortByDesc(function ($row) {

                            return (float) $row->obtained_marks;

                        })
                        ->values();


                    /*
                    |--------------------------------------------------------------------------
                    | BEST OF TWO
                    |--------------------------------------------------------------------------
                    |
                    | Highest obtained marks wale 2 internals select honge.
                    |
                    */

                    $topTwo = $sortedMarks
                        ->take(2)
                        ->values();


                    /*
                    |--------------------------------------------------------------------------
                    | THIRD INTERNAL
                    |--------------------------------------------------------------------------
                    |
                    | Best of two ke baad jo third internal bachta hai,
                    | usme remaining grace apply hoga.
                    |
                    */

                    $thirdInternal = $sortedMarks
                        ->skip(2)
                        ->first();


                    /*
                    |--------------------------------------------------------------------------
                    | APPLY GRACE ON BEST TWO
                    |--------------------------------------------------------------------------
                    */

                    foreach ($topTwo as $row) {

                        if ($remainingGrace <= 0) {
                            break;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | OBTAINED MARKS
                        |--------------------------------------------------------------------------
                        */

                        $obtainedMarks = (float) $row->obtained_marks;


                        /*
                        |--------------------------------------------------------------------------
                        | MAX MARKS
                        |--------------------------------------------------------------------------
                        */

                        $maxMarks = (float) $row->max_marks;


                        /*
                        |--------------------------------------------------------------------------
                        | AVAILABLE SPACE
                        |--------------------------------------------------------------------------
                        */

                        $space = $maxMarks - $obtainedMarks;


                        /*
                        |--------------------------------------------------------------------------
                        | NO SPACE
                        |--------------------------------------------------------------------------
                        */

                        if ($space <= 0) {
                            continue;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | APPLICABLE GRACE
                        |--------------------------------------------------------------------------
                        */

                        $applicableGrace = min(
                            $space,
                            $remainingGrace
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | UPDATE GRACE
                        |--------------------------------------------------------------------------
                        */

                        $row->update([
                            'grace_marks' => $applicableGrace
                        ]);


                        /*
                        |--------------------------------------------------------------------------
                        | REDUCE REMAINING GRACE
                        |--------------------------------------------------------------------------
                        */

                        $remainingGrace -= $applicableGrace;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | APPLY REMAINING GRACE ON THIRD INTERNAL
                    |--------------------------------------------------------------------------
                    |
                    | Agar best of two mein poora grace apply nahi hua,
                    | to remaining grace third internal mein jayega.
                    |
                    */

                    if (
                        $remainingGrace > 0 &&
                        $thirdInternal
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | THIRD INTERNAL OBTAINED MARKS
                        |--------------------------------------------------------------------------
                        */

                        $obtainedMarks = (float) $thirdInternal->obtained_marks;


                        /*
                        |--------------------------------------------------------------------------
                        | THIRD INTERNAL MAX MARKS
                        |--------------------------------------------------------------------------
                        */

                        $maxMarks = (float) $thirdInternal->max_marks;


                        /*
                        |--------------------------------------------------------------------------
                        | THIRD INTERNAL AVAILABLE SPACE
                        |--------------------------------------------------------------------------
                        */

                        $space = $maxMarks - $obtainedMarks;


                        /*
                        |--------------------------------------------------------------------------
                        | APPLY AVAILABLE SPACE ONLY
                        |--------------------------------------------------------------------------
                        */

                        if ($space > 0) {

                            $applicableGrace = min(
                                $space,
                                $remainingGrace
                            );


                            /*
                            |--------------------------------------------------------------------------
                            | UPDATE THIRD INTERNAL
                            |--------------------------------------------------------------------------
                            */

                            $thirdInternal->update([
                                'grace_marks' => $applicableGrace
                            ]);


                            /*
                            |--------------------------------------------------------------------------
                            | REDUCE REMAINING GRACE
                            |--------------------------------------------------------------------------
                            */

                            $remainingGrace -= $applicableGrace;
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | IMPORTANT
                    |--------------------------------------------------------------------------
                    |
                    | Agar teeno internals mein grace apply karne ke baad
                    | bhi remainingGrace bach jata hai to usko ignore/discard
                    | kar diya jayega.
                    |
                    | Koi error nahi aayega.
                    |
                    */
                }


                /*
                |--------------------------------------------------------------------------
                | COMMIT
                |--------------------------------------------------------------------------
                */

                DB::commit();


                /*
                |--------------------------------------------------------------------------
                | SUCCESS RESPONSE
                |--------------------------------------------------------------------------
                */

                if ($request->grace_action == 'revert') {

                    return response()->json([
                        'success' => true,
                        'message' =>
                            'Grace reverted successfully for all related subjects.'
                    ]);
                }


                return response()->json([
                    'success' => true,
                    'message' =>
                        'Grace marks processed successfully for all related subjects.'
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | INVALID COURSE TYPE
            |--------------------------------------------------------------------------
            */

            throw new \Exception(
                'Invalid course type'
            );


        } catch (ValidationException $e) {

            DB::rollBack();

            throw $e;


        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }



}
