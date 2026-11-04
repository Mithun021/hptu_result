<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\MidSemExam;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $exams = Exam::latest()->get();
        return view('backend.exams.index', compact('exams'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.exams.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sort_name' => 'nullable|string|max:50',
            'admit_card_name' => 'nullable|string|max:255',
            'exam_resultsheet_name' => 'nullable|string|max:255',
            'dmc_name' => 'nullable|string|max:255',
            'supple_result_sheet' => 'nullable|string|max:500',
            'reconsile_result_sheet' => 'nullable|string|max:500',
            'exam_month' => 'nullable|string|max:255',
            'exam_year' => 'nullable|integer|digits:4',
            'is_active' => 'required|boolean',
            'show_admitcard' => 'required|boolean',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validated['is_active'] == true) {
            Exam::where('is_active', true)->update([
                'is_active' => false
            ]);
        }

        Exam::create($validated);

        return redirect()->route('exams.index')
            ->with('success', 'Exam created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(String $id)
    {
        $exam = Exam::findOrFail($id);

        return view('backend.exams.edit', compact('exam'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id)
    {
        $exam = Exam::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sort_name' => 'nullable|string|max:50',
            'admit_card_name' => 'nullable|string|max:255',
            'exam_resultsheet_name' => 'nullable|string|max:255',
            'dmc_name' => 'nullable|string|max:255',
            'supple_result_sheet' => 'nullable|string|max:500',
            'reconsile_result_sheet' => 'nullable|string|max:500',
            'exam_month' => 'nullable|string|max:255',
            'exam_year' => 'nullable|integer|digits:4',
            'is_active' => 'required|boolean',
            'show_admitcard' => 'required|boolean',
            'description' => 'nullable|string|max:255',
        ]);

        // ✅ If current exam is being set to active (yes),
        // then make all other exams inactive (no)
        if ($validated['is_active'] == true) {
            Exam::where('id', '!=', $exam->id)->update([
                'is_active' => false
            ]);
        }

        $exam->update($validated);

        return redirect()->route('exams.index')
            ->with('success', 'Exam updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */ 
    public function destroy(String $id)
    {
        $exam = Exam::findOrFail($id);
        $exam->delete();

        return redirect()->route('exams.index')
            ->with('success', 'Exam deleted successfully.');
    }

    public function getMidSemExams($exam_id)
    {
        $midSemExams = MidSemExam::where('exam_id', $exam_id)
                        ->where('status',1)
                        ->select('id','mid_sem_exam_name')
                        ->get();

        return response()->json($midSemExams);
    }
}
