<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\ResultGpSheet;
use App\Models\StudentResultSummary;
use Illuminate\Http\Request;

class FrontsheetController extends Controller
{
    public function ncCourseIndex()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.front-sheet.nc-course.index', compact('courses', 'exams'));
    }

    public function fetchNcCourseIndex(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. VALIDATION
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'exam_id' => ['required', 'integer'],
            'course_type' => ['required', 'string'],
            'course_id' => ['required', 'integer'],
            'branch_id' => ['required', 'integer'],
            'batch_id' => ['required', 'integer'],
            'institute_id' => ['required', 'integer'],
            'subject_id' => ['required', 'integer'],

            'semester_id' => [
                'nullable',
                'required_if:course_type,Semester Based',
                'integer',
            ],

            'annual_semester_id' => [
                'nullable',
                'required_if:course_type,Annual Based',
                'integer',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | 2. BASE QUERY
        |--------------------------------------------------------------------------
        */

        $query = ResultGpSheet::with([
            'student',
            'subject',
            'course',
            'branch',
            'batch',
            'institute',
            'exam',
            'semester',
            'annualSemester',
        ])
            ->where('exam_id', $validated['exam_id'])
            ->where('course_type', $validated['course_type'])
            ->where('course_id', $validated['course_id'])
            ->where('branch_id', $validated['branch_id'])
            ->where('batch_id', $validated['batch_id'])
            ->where('institute_id', $validated['institute_id'])
            ->where('subject_id', $validated['subject_id']);


        /*
        |--------------------------------------------------------------------------
        | 3. SEMESTER / ANNUAL FILTER
        |--------------------------------------------------------------------------
        */

        if ($validated['course_type'] === 'Semester Based') {

            $query->where('semester_id', $validated['semester_id'])
                ->whereNull('annual_semester_id');

        } elseif ($validated['course_type'] === 'Annual Based') {

            $query->where('annual_semester_id', $validated['annual_semester_id'])
                ->whereNull('semester_id');
        }


        /*
        |--------------------------------------------------------------------------
        | 4. FETCH ONLY NC RESULT
        |--------------------------------------------------------------------------
        |
        | ResultGpSheet itself does not have is_nc.
        | So is_nc should be checked from Subject.
        |
        */

        $resultSheets = $query
            ->whereHas('subject', function ($q) {
                $q->where('is_nc', 1);
            })
            ->orderBy('student_id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 5. RENDER RESULT SHEET
        |--------------------------------------------------------------------------
        */

        return view(
            'backend.result.front-sheet.nc-course.result',
            compact('resultSheets')
        );
    }
    
    public function endSemIndex()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.front-sheet.end-sem.index', compact('courses', 'exams'));
    }
    
    public function fetchEndSemCourseIndex(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. VALIDATION
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'exam_id' => [
                'required',
                'integer',
            ],

            'course_type' => [
                'required',
                'in:Annual Based',
            ],

            'course_id' => [
                'required',
                'integer',
            ],

            'scheme_id' => [
                'nullable',
                'integer',
            ],

            'branch_id' => [
                'required',
                'integer',
            ],

            'semester_id' => [
                'nullable',
                'integer',
            ],

            'annual_semester_id' => [
                'required',
                'integer',
            ],

            'batch_id' => [
                'required',
                'integer',
            ],

            'institute_id' => [
                'required',
                'integer',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | 2. FETCH ANNUAL BASED RESULT GP SHEETS
        |--------------------------------------------------------------------------
        */

        $resultSheets = ResultGpSheet::with([

            'student',
            'subject',
            'exam',
            'course',
            'scheme',
            'branch',
            'annualSemester',
            'batch',
            'institute',

        ])

            ->where('exam_id', $validated['exam_id'])

            /*
            |--------------------------------------------------------------------------
            | ONLY ANNUAL BASED
            |--------------------------------------------------------------------------
            */

            ->where('course_type', 'Annual Based')

            ->where('course_id', $validated['course_id'])

            ->where('branch_id', $validated['branch_id'])

            ->where(
                'annual_semester_id',
                $validated['annual_semester_id']
            )

            ->where('batch_id', $validated['batch_id'])

            ->where('institute_id', $validated['institute_id'])

            /*
            |--------------------------------------------------------------------------
            | SCHEME
            |--------------------------------------------------------------------------
            */

            ->when(
                !empty($validated['scheme_id']),
                function ($query) use ($validated) {

                    $query->where(
                        'scheme_id',
                        $validated['scheme_id']
                    );

                }
            )

            ->orderBy('student_id')

            ->orderBy('subject_id')

            ->get();


        /*
        |--------------------------------------------------------------------------
        | 3. NO RESULT FOUND
        |--------------------------------------------------------------------------
        */

        if ($resultSheets->isEmpty()) {

            return response()->json([

                'status' => false,

                'message' => 'No result found for the selected criteria.',

            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | 4. GET UNIQUE STUDENT IDS
        |--------------------------------------------------------------------------
        */

        $studentIds = $resultSheets
            ->pluck('student_id')
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | 5. FETCH STUDENT RESULT SUMMARY
        |--------------------------------------------------------------------------
        */

        $summaries = StudentResultSummary::query()

            ->whereIn(
                'student_id',
                $studentIds
            )

            ->where(
                'exam_id',
                $validated['exam_id']
            )

            ->where(
                'course_type',
                'Annual Based'
            )

            ->where(
                'course_id',
                $validated['course_id']
            )

            ->where(
                'branch_id',
                $validated['branch_id']
            )

            ->where(
                'annual_semester_id',
                $validated['annual_semester_id']
            )

            ->where(
                'batch_id',
                $validated['batch_id']
            )

            ->where(
                'institute_id',
                $validated['institute_id']
            )

            ->when(
                !empty($validated['scheme_id']),
                function ($query) use ($validated) {

                    $query->where(
                        'scheme_id',
                        $validated['scheme_id']
                    );

                }
            )

            ->get()

            ->keyBy('student_id');


        /*
        |--------------------------------------------------------------------------
        | 6. RENDER RESULT PAGE
        |--------------------------------------------------------------------------
        */

        return view(
            'backend.result.front-sheet.end-sem.result',
            compact(
                'resultSheets',
                'summaries'
            )
        );
    }
    
    
}
