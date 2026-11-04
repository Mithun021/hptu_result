<?php

namespace App\Http\Controllers;

use App\Models\MarksGrade;
use App\Models\Course;
use Illuminate\Http\Request;

class MarksGradeController extends Controller
{
    /**
     * Index page = create form + list
     */
    public function index()
    {
        return view('backend.marks_grades.index', [
            'marksGrades' => MarksGrade::with('course:id,name')->orderBy('course_id')->get(),
            'courses'     => Course::select('id', 'name')->get(),
        ]);
    }

    /**
     * Store new grade
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id'         => 'required|exists:courses,id',
            'grade_name'        => 'required|string|max:20',
            'percent_from'      => 'required|numeric|min:0|max:100',
            'percent_upto'      => 'required|numeric|min:0|max:100|gte:percent_from',
            // 'grade_point'       => 'nullable|numeric|min:0|max:10',
            'grade_description' => 'nullable|string|max:20',
        ]);

        // ❌ Prevent overlapping ranges for same course
        // $exists = MarksGrade::where('course_id', $request->course_id)
        //     ->where(function ($q) use ($request) {
        //         $q->whereBetween('percent_from', [$request->percent_from, $request->percent_upto])
        //           ->orWhereBetween('percent_upto', [$request->percent_from, $request->percent_upto])
        //           ->orWhere(function ($q2) use ($request) {
        //               $q2->where('percent_from', '<=', $request->percent_from)
        //                  ->where('percent_upto', '>=', $request->percent_upto);
        //           });
        //     })
        //     ->exists();

        // if ($exists) {
        //     return back()
        //         ->withErrors(['percent_from' => 'This percentage range overlaps with an existing grade for this course.'])
        //         ->withInput();
        // }

        MarksGrade::create($validated);

        return redirect()
            ->route('marks-grades.index')
            ->with('success', 'Grade added successfully.');
    }

    /**
     * Edit page
     */
    public function edit(string $id)
    {
        return view('backend.marks_grades.edit', [
            'marksGrade' => MarksGrade::findOrFail($id),
            'courses'    => Course::select('id', 'name')->get(),
        ]);
    }

    /**
     * Update grade
     */
    public function update(Request $request, string $id)
    {
        $marksGrade = MarksGrade::findOrFail($id);

        $validated = $request->validate([
            'course_id'         => 'required|exists:courses,id',
            'grade_name'        => 'required|string|max:20',
            'percent_from'      => 'required|numeric|min:0|max:100',
            'percent_upto'      => 'required|numeric|min:0|max:100|gte:percent_from',
            // 'grade_point'       => 'nullable|numeric|min:0|max:10',
            'grade_description' => 'nullable|string|max:20',
        ]);

        // ❌ Prevent overlapping ranges (exclude current record)
        // $exists = MarksGrade::where('course_id', $request->course_id)
        //     ->where('id', '!=', $marksGrade->id)
        //     ->where(function ($q) use ($request) {
        //         $q->whereBetween('percent_from', [$request->percent_from, $request->percent_upto])
        //           ->orWhereBetween('percent_upto', [$request->percent_from, $request->percent_upto])
        //           ->orWhere(function ($q2) use ($request) {
        //               $q2->where('percent_from', '<=', $request->percent_from)
        //                  ->where('percent_upto', '>=', $request->percent_upto);
        //           });
        //     })
        //     ->exists();

        // if ($exists) {
        //     return back()
        //         ->withErrors(['percent_from' => 'This percentage range overlaps with an existing grade for this course.'])
        //         ->withInput();
        // }

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
