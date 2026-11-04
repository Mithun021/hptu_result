<?php

namespace App\Http\Controllers;

use App\Models\GraceMarks;
use Illuminate\Http\Request;

class GraceMarksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $graceMarks = GraceMarks::latest()->get();
        return view('backend.grace-marks.index', compact('graceMarks'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_type'       => 'required',
            'addon_marks_type' => 'required',
            'addon_marks_on'   => 'required',
            'max_allowed'      => 'required|integer|min:1',
            'max_marks'        => 'required|integer|min:1',
        ]);

        GraceMarks::create($request->all());

        return redirect()->back()->with('success', 'Grace Marks Added Successfully');
    }

    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(String $id)
    {
        $graceMark = GraceMarks::findOrFail($id);
        return view('backend.grace-marks.edit', compact('graceMark'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id)
    {
        $request->validate([
            'course_type'       => 'required',
            'addon_marks_type' => 'required',
            'addon_marks_on'   => 'required',
            'max_allowed'      => 'required|integer|min:1',
            'max_marks'        => 'required|integer|min:1',
        ]);

        $graceMark = GraceMarks::findOrFail($id);
        $graceMark->update($request->all());

        return redirect()->route('grace-marks.index')->with('success', 'Grace Marks Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $id)
    {
        $graceMark = GraceMarks::findOrFail($id);
        $graceMark->delete();

        return redirect()->route('grace-marks.index')->with('success', 'Grace Marks Deleted Successfully');
    }
}
