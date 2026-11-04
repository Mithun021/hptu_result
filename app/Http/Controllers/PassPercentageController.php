<?php

namespace App\Http\Controllers;

use App\Models\PassPercentage;
use App\Models\Course;
use Illuminate\Http\Request;

class PassPercentageController extends Controller
{
    /**
     * Display a listing of the resource + create form
     */
    public function index()
    {
        return view('backend.pass-percentages.index', [
            'passPercentages' => PassPercentage::with('course:id,name')->latest()->get(),
            'courses' => Course::select('id', 'name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id'       => 'required|exists:courses,id|unique:pass_percentages,course_id',
            'overall_pass_percentage' => 'required|numeric|min:0|max:100',
            'theory_min_percentage' => 'required|numeric|min:0|max:100',
        ]);

        PassPercentage::create($validated);

        return redirect()
            ->route('pass-percentages.index')
            ->with('success', 'Pass percentage added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $passPercentage = PassPercentage::findOrFail($id);

        return view('backend.pass-percentages.edit', [
            'passPercentage' => $passPercentage,
            'courses' => Course::select('id', 'name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $passPercentage = PassPercentage::findOrFail($id);

        $validated = $request->validate([
            'course_id'       => 'required|exists:courses,id|unique:pass_percentages,course_id,' . $passPercentage->id,
            'pass_percentage' => 'required|numeric|min:0|max:100',
            'theory_min_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $passPercentage->update($validated);

        return redirect()
            ->route('pass-percentages.index')
            ->with('success', 'Pass percentage updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $passPercentage = PassPercentage::findOrFail($id);
        $passPercentage->delete();

        return redirect()
            ->route('pass-percentages.index')
            ->with('success', 'Pass percentage deleted successfully.');
    }
}
