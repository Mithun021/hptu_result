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
use App\Models\Student;
use App\Models\StudentSubject;
use App\Models\Subject;
use Illuminate\Http\Request;

class DmcSheetController extends Controller
{
    public function regularDmcIndex(){
        $exams = Exam::orderBy('id', 'desc')->get();
        $courses = Course::all();
        return view('backend.result.dmc-sheet.index', compact('exams', 'courses'));
    }

    // public function fetchRegularDmcSheet(Request $request){
    //     dd($request->all());
    // }

    public function fetchRegularDmcSheet(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | ONLY ANNUAL BASED
        |--------------------------------------------------------------------------
        */

        if ($request->course_type !== 'Annual Based') {
            abort(422, 'Only Annual Based DMC is supported.');
        }

        /*
        |--------------------------------------------------------------------------
        | REQUEST DATA
        |--------------------------------------------------------------------------
        */

        $printDate           = $request->print_date;
        $studentRollNumber = trim($request->student_roll_number ?? '');

        $examId           = $request->exam_id;
        $courseType       = $request->course_type;
        $courseId         = $request->course_id;
        $schemeId         = $request->scheme_id;
        $branchId         = $request->branch_id;
        $annualSemesterId = $request->annual_semester_id;
        $batchId          = $request->batch_id;
        $instituteId      = $request->institute_id;


        /*
        |--------------------------------------------------------------------------
        | BASIC DATA
        |--------------------------------------------------------------------------
        */

        $exam = Exam::findOrFail($examId);

        $course = Course::findOrFail($courseId);

        $branch = Branch::find($branchId);

        $batch = Batch::find($batchId);

        /*
        |--------------------------------------------------------------------------
        | INSTITUTE
        |--------------------------------------------------------------------------
        */

        $institute = null;

        if ($instituteId) {
            $institute = Institute::find($instituteId);
        }


        /*
        |--------------------------------------------------------------------------
        | ANNUAL SEMESTER
        |--------------------------------------------------------------------------
        */

        $annualSemester = AnnualSemester::findOrFail($annualSemesterId);

        $className = $annualSemester->name;


        /*
        |--------------------------------------------------------------------------
        | PASS PERCENTAGE
        |--------------------------------------------------------------------------
        */

        $passPercentage = PassPercentage::where('course_id', $courseId)
            ->first();

        $overallPassPercent = $passPercentage->overall_pass_percentage ?? 50;

        $theoryMinPercent = $passPercentage->theory_min_percentage ?? 20;


        /*
        |--------------------------------------------------------------------------
        | SUBJECTS
        |--------------------------------------------------------------------------
        | ONLY ANNUAL BASED SUBJECTS
        |--------------------------------------------------------------------------
        */

        $subjects = Subject::where('course_id', $courseId)
            ->where('branch_id', $branchId)

            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })

            ->where('annual_semester_id', $annualSemesterId)
            ->whereNull('semester_id')

            ->orderBy('orders')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | STUDENTS
        |--------------------------------------------------------------------------
        | If roll number supplied:
        |     only that student
        |
        | If roll number empty:
        |     all students
        |--------------------------------------------------------------------------
        */

        $studentsQuery = Student::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })

            ->when($instituteId, function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            });


        /*
        |--------------------------------------------------------------------------
        | STUDENT ROLL NUMBER FILTER
        |--------------------------------------------------------------------------
        */

        if ($studentRollNumber !== '') {

            $studentsQuery->where(
                'university_roll_no',
                $studentRollNumber
            );
        }
        
        $students = $studentsQuery
            ->orderByRaw("
                CAST(
                    SUBSTRING_INDEX(
                        SUBSTRING_INDEX(university_roll_no, '-', 2),
                        '-', -1
                    ) AS UNSIGNED
                ) ASC
            ")
            ->orderByRaw("
                CAST(
                    SUBSTRING_INDEX(
                        SUBSTRING_INDEX(university_roll_no, '-', 4),
                        '-', -1
                    ) AS UNSIGNED
                ) ASC
            ")
            ->get();


        // $students = $studentsQuery
        //     ->orderByRaw("
        //         CAST(
        //             SUBSTRING_INDEX(
        //                 SUBSTRING_INDEX(university_roll_no, '-', 4),
        //                 '-', -1
        //             ) AS UNSIGNED
        //         ) ASC
        //     ")
        //     ->get();


        // dd([
        //     'total_students' => $students->count(),
        //     'student_roll_numbers' => $students->pluck('university_roll_no')->toArray(),
        // ]);

        /*
        |--------------------------------------------------------------------------
        | MID SEM MARKS
        |--------------------------------------------------------------------------
        */

        $midSemMarks = MidSemMark::where('exam_id', $examId)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

            ->where('is_locked', 1)
            ->where('university_forword', 1)

            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })

            ->where('annual_semester_id', $annualSemesterId)
            ->whereNull('semester_id')

            ->get()

            ->groupBy([
                'student_id',
                'subject_id',
                'marks_type'
            ]);


        /*
        |--------------------------------------------------------------------------
        | END SEMESTER MARKS
        |--------------------------------------------------------------------------
        | Annual Based
        |--------------------------------------------------------------------------
        */

        $endSemMarks = EndSemMark::where('exam_id', $examId)
            ->where('is_locked', 1)

            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })

            ->where('annual_semester_id', $annualSemesterId)
            ->whereNull('semester_id')

            ->get()

            ->groupBy([
                'student_id',
                'subject_id',
                'marks_type'
            ]);


        /*
        |--------------------------------------------------------------------------
        | ACADEMIC ADJUSTMENT / MODERATION
        |--------------------------------------------------------------------------
        */

        $academicAdjustments = AcademicAdjustment::where('exam_id', $examId)

            ->where('adjustment_scope', 'external')

            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })

            ->where('annual_semester_id', $annualSemesterId)
            ->whereNull('semester_id')

            ->get()

            ->groupBy([
                'student_id',
                'subject_id',
                'marks_type'
            ]);


        /*
        |--------------------------------------------------------------------------
        | STUDENT SUBJECTS
        |--------------------------------------------------------------------------
        */

        $studentSubjects = StudentSubject::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)

            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })

            ->where('annual_semester_id', $annualSemesterId)
            ->whereNull('semester_id')

            ->get()

            ->groupBy('student_id');


        /*
        |--------------------------------------------------------------------------
        | GRADES
        |--------------------------------------------------------------------------
        */

        $grades = MarksGrade::where('course_id', $courseId)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'backend.result.dmc-sheet.result',
            compact(
                'printDate',
                'exam',
                'courseType',
                'courseId',
                'course',
                'schemeId',
                'branchId',
                'branch',
                'batchId',
                'batch',
                'instituteId',
                'institute',
                'schemeId',
                'annualSemester',
                'annualSemesterId',
                'students',
                'subjects',
                'midSemMarks',
                'endSemMarks',
                'academicAdjustments',
                'studentSubjects',
                'overallPassPercent',
                'theoryMinPercent',
                'grades',
                'studentRollNumber'
            )
        );
    }
}
