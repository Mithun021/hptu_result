<?php

namespace App\Http\Controllers;

use App\Models\MarksGrade;
use App\Models\Course;
use App\Models\Scheme;
use Illuminate\Http\Request;

class MarksGradeController extends Controller
{
    /**
     * Index page = create form + list
     */
    // public function index()
    // {
    //     return view('backend.marks_grades.index', [
    //         'marksGrades' => MarksGrade::with([
    //             'course:id,name',
    //             'scheme:id,name',
    //         ])
    //             ->orderBy('course_id')
    //             ->orderBy('scheme_id')
    //             ->get(),

    //         'courses' => Course::select('id', 'name')->get(),

    //         'schemes' => Scheme::select('id', 'name')->get(),
    //     ]);
    // }

    public function index()
    {
        return view('backend.marks_grades.index', [
            'marksGrades' => MarksGrade::with([
                'course:id,name',
                'scheme:id,name',
            ])
                ->join('courses', 'marks_grades.course_id', '=', 'courses.id')
                ->join('schemes', 'marks_grades.scheme_id', '=', 'schemes.id')
                ->select('marks_grades.*')
                ->orderBy('courses.name', 'asc')
                ->orderBy('schemes.name', 'asc')
                ->orderBy('marks_grades.percent_from', 'asc')
                ->get(),

            'courses' => Course::select('id', 'name')->get(),

            'schemes' => Scheme::select('id', 'name')->get(),
        ]);
    }

    /**
     * Store Marks Grade
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id'         => 'required|exists:courses,id',
            'scheme_id'         => 'required|exists:schemes,id',
            'grade_name'        => 'required|string|max:20',
            'percent_from'      => 'required|numeric|min:0|max:100',
            'percent_upto'      => 'required|numeric|min:0|max:100|gte:percent_from',
            'grade_point'       => 'required|numeric|min:0',
            'grade_description' => 'nullable|string|max:20',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Prevent Overlapping Ranges
        |--------------------------------------------------------------------------
        | Same Course + Same Scheme
        */

        $exists = MarksGrade::where('course_id', $request->course_id)
            ->where('scheme_id', $request->scheme_id)
            ->where(function ($q) use ($request) {

                $q->where('percent_from', '<=', $request->percent_upto)
                ->where('percent_upto', '>=', $request->percent_from);

            })
            ->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'percent_from' => 'This percentage range overlaps with an existing grade for the selected course and scheme.',
                ])
                ->withInput();
        }

        MarksGrade::create($validated);

        return redirect()
            ->route('marks-grades.index')
            ->with('success', 'Grade added successfully.');
    }


    /**
     * Edit Marks Grade
     */
    public function edit(string $id)
    {
        $marksGrade = MarksGrade::findOrFail($id);

        $courses = Course::select('id', 'name')->get();

        $schemes = Scheme::select('id', 'name')->get();

        return view('backend.marks_grades.edit', compact(
            'marksGrade',
            'courses',
            'schemes'
        ));
    }


    /**
     * Update Marks Grade
     */
    public function update(Request $request, string $id)
    {
        $marksGrade = MarksGrade::findOrFail($id);

        $validated = $request->validate([
            'course_id'         => 'required|exists:courses,id',
            'scheme_id'         => 'required|exists:schemes,id',
            'grade_name'        => 'required|string|max:20',
            'percent_from'      => 'required|numeric|min:0|max:100',
            'percent_upto'      => 'required|numeric|min:0|max:100|gte:percent_from',
            'grade_point'       => 'required|numeric|min:0',
            'grade_description' => 'nullable|string|max:20',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Prevent Overlapping Ranges
        |--------------------------------------------------------------------------
        | Same Course + Same Scheme
        | Exclude Current Record
        */

        $exists = MarksGrade::where('course_id', $request->course_id)
            ->where('scheme_id', $request->scheme_id)
            ->where('id', '!=', $marksGrade->id)
            ->where(function ($q) use ($request) {

                $q->where('percent_from', '<=', $request->percent_upto)
                ->where('percent_upto', '>=', $request->percent_from);

            })
            ->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'percent_from' => 'This percentage range overlaps with an existing grade for the selected course and scheme.',
                ])
                ->withInput();
        }

        $marksGrade->update($validated);

        return redirect()
            ->route('marks-grades.index')
            ->with('success', 'Grade updated successfully.');
    }

    /**
     * Delete
     */
    public function destroy(string $id)
    {
        MarksGrade::findOrFail($id)->delete();

        return redirect()
            ->route('marks-grades.index')
            ->with('success', 'Grade deleted successfully.');
    }
}
