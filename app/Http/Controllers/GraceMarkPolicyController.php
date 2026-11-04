<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\GraceMarkPolicy;
use Illuminate\Http\Request;

class GraceMarkPolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $courses = Course::orderBy('name')->get();
        $policies = GraceMarkPolicy::with([
            'course',
            'branch',
            'batch',
            'scheme'
        ])
        ->latest()
        ->get();

        return view('backend.grace-marks-policy.index', compact(
            'courses',
            'policies'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_type'      => 'nullable|string|max:50',
            'course_id'        => 'required|exists:courses,id',
            'branch_id'        => 'required|exists:branches,id',
            'batch_id'         => 'required|exists:batches,id',
            'scheme_id'        => 'nullable|exists:schemes,id',
            'academic_year'    => 'required|integer|min:1|max:10',
            'max_grace_marks'  => 'required|numeric|min:0|max:99.99',
        ]);

        $exists = GraceMarkPolicy::where([
            'course_type'   => $request->course_type,
            'course_id'     => $request->course_id,
            'branch_id'     => $request->branch_id,
            'batch_id'      => $request->batch_id,
            'scheme_id'     => $request->scheme_id,
            'academic_year' => $request->academic_year,
        ])->exists();

        if ($exists) {
            return back()->with('error', 'Policy already exists.');
        }

        GraceMarkPolicy::create([
            'course_type'      => $request->course_type,
            'course_id'        => $request->course_id,
            'branch_id'        => $request->branch_id,
            'batch_id'         => $request->batch_id,
            'scheme_id'        => $request->scheme_id,
            'academic_year'    => $request->academic_year,
            'max_grace_marks'  => $request->max_grace_marks,
            'status'           => true,
        ]);

        return back()->with('success', 'Grace mark policy created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $policy = GraceMarkPolicy::findOrFail($id);

        $courses = Course::orderBy('name')->get();

        return view('backend.grace-marks-policy.edit', compact(
            'policy',
            'courses'
        ));
    }

    public function update(Request $request, $id)
    {
        $policy = GraceMarkPolicy::findOrFail($id);

        $request->validate([
            'course_type'      => 'nullable|string|max:50',
            'course_id'        => 'required|exists:courses,id',
            'branch_id'        => 'required|exists:branches,id',
            'batch_id'         => 'required|exists:batches,id',
            'scheme_id'        => 'nullable|exists:schemes,id',
            'academic_year'    => 'required|integer|min:1|max:10',
            'max_grace_marks'  => 'required|numeric|min:0|max:99.99',
            'status'           => 'required|boolean',
        ]);

        $exists = GraceMarkPolicy::where([
            'course_type'   => $request->course_type,
            'course_id'     => $request->course_id,
            'branch_id'     => $request->branch_id,
            'batch_id'      => $request->batch_id,
            'scheme_id'     => $request->scheme_id,
            'academic_year' => $request->academic_year,
        ])
        ->where('id', '!=', $policy->id)
        ->exists();

        if ($exists) {
            return back()->withInput()
                        ->with('error', 'Policy already exists.');
        }

        $policy->update([
            'course_type'      => $request->course_type,
            'course_id'        => $request->course_id,
            'branch_id'        => $request->branch_id,
            'batch_id'         => $request->batch_id,
            'scheme_id'        => $request->scheme_id,
            'academic_year'    => $request->academic_year,
            'max_grace_marks'  => $request->max_grace_marks,
            'status'           => $request->status,
        ]);

        return redirect()
            ->route('grace-mark-policies.index')
            ->with('success', 'Grace mark policy updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $policy = GraceMarkPolicy::findOrFail($id);
        $policy->delete();
        return back()->with('success', 'Policy deleted successfully.');
    }
}
