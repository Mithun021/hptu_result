<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\ExaminationForm;
use App\Models\Student;
use Illuminate\Http\Request;

class ManageExamFormController extends Controller
{
    public function index()
    {

        $exams = Exam::all();
        $courses = Course::all();

        return view('backend.examination-form.index', compact(
            'exams',
            'courses'
        ));
    }
    public function fetchExamForms(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Base Query - Only Paid Examination Forms
        |--------------------------------------------------------------------------
        */

        $query = ExaminationForm::with([
            'student',
            'exam',
            'course',
            'scheme',
            'branch',
            'semester',
            'batch',
            'institute',
            'examCenter',
        ])
        ->where('payment_status', 1)
        ->where('exam_id', $request->exam_id);

        /*
        |--------------------------------------------------------------------------
        | Student Roll Number Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('student_roll_number')) {

            $rollNumber = trim($request->student_roll_number);

            $student = Student::where(
                'university_roll_no',
                $rollNumber
            )->first();

            if ($student) {

                $query->where('student_id', $student->id);

            } else {

                // Roll number does not exist
                $query->where('student_id', 0);

            }
        }

        /*
        |--------------------------------------------------------------------------
        | Optional Filters
        |--------------------------------------------------------------------------
        */

        $filters = [
            'course_id',
            'scheme_id',
            'branch_id',
            'semester_id',
            'batch_id',
            'institute_id',
        ];

        foreach ($filters as $column) {

            if ($request->filled($column)) {

                $query->where($column, $request->input($column));

            }
        }

        /*
        |--------------------------------------------------------------------------
        | Fetch Data
        |--------------------------------------------------------------------------
        */

        $examinationForms = $query
            ->orderByDesc('id')
            ->get();

        return view(
            'backend.examination-form.exam-sheet-data',
            compact('examinationForms')
        );
    }



}
