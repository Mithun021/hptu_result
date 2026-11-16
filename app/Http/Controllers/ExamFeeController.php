<?php

namespace App\Http\Controllers;

use App\Models\ExamFee;
use App\Models\Course;
use App\Models\Scheme;
use App\Models\Branch;
use App\Models\Semester;
use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamFeeController extends Controller
{
    // Exam Fees Listing
    public function index()
    {
        $examFees = ExamFee::with([
            'course',
            'schemes',
            'branches',
            'semesters',
            'batches',
        ])->latest()->get();

        return view('backend.exam_fees.index', compact('examFees'));
    }


    // Create Form
    public function create()
    {
        $courses = Course::orderBy('name', 'asc')->get();

        return view('backend.exam_fees.create', compact('courses'));
    }


    // Store Exam Fees
    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());

        DB::transaction(function () use ($validated) {

            // Mapping data
            $mappings = [
                'schemes'   => $validated['schemes'] ?? [],
                'branches'  => $validated['branches'] ?? [],
                'semesters' => $validated['semesters'] ?? [],
                'batches'   => $validated['batches'] ?? [],
            ];

            // Remove mapping fields and non-database field
            unset(
                $validated['schemes'],
                $validated['branches'],
                $validated['semesters'],
                $validated['batches'],
                $validated['course_type']
            );

            // Create Exam Fee
            $examFee = ExamFee::create($validated);

            // Save mappings
            $examFee->schemes()->sync($mappings['schemes']);
            $examFee->branches()->sync($mappings['branches']);
            $examFee->semesters()->sync($mappings['semesters']);
            $examFee->batches()->sync($mappings['batches']);
        });

        return redirect()
            ->route('exam-fees.index')
            ->with('success', 'Exam fees created successfully.');
    }



    // Show Exam Fees Details
    public function show($id)
    {
        $examFee = ExamFee::with([
            'course',
            'schemes',
            'branches',
            'semesters',
            'batches',
        ])->findOrFail($id);

        return view('backend.exam_fees.show', compact('examFee'));
    }


    // Edit Form
    public function edit($id)
    {
        $examFee = ExamFee::with([
            'course',
            'schemes',
            'branches',
            'semesters',
            'batches',
        ])->findOrFail($id);

        $courses = Course::orderBy('name', 'asc')->get();

        return view('backend.exam_fees.edit', compact('examFee', 'courses'));
    }


    // Update Exam Fees
    public function update(Request $request, $id)
    {
        $validated = $request->validate($this->validationRules());

        $examFee = ExamFee::findOrFail($id);

        DB::transaction(function () use ($validated, $examFee) {

            // Mapping data
            $mappings = [
                'schemes'   => $validated['schemes'] ?? [],
                'branches'  => $validated['branches'] ?? [],
                'semesters' => $validated['semesters'] ?? [],
                'batches'   => $validated['batches'] ?? [],
            ];

            // Remove mapping fields and non-database field
            unset(
                $validated['schemes'],
                $validated['branches'],
                $validated['semesters'],
                $validated['batches'],
                $validated['course_type']
            );

            // Update Exam Fee
            $examFee->update($validated);

            // Update mappings
            $examFee->schemes()->sync($mappings['schemes']);
            $examFee->branches()->sync($mappings['branches']);
            $examFee->semesters()->sync($mappings['semesters']);
            $examFee->batches()->sync($mappings['batches']);
        });

        return redirect()
            ->route('exam-fees.index')
            ->with('success', 'Exam fees updated successfully.');
    }


    // Delete Exam Fees
    public function destroy($id)
    {
        $examFee = ExamFee::findOrFail($id);

        $examFee->delete();

        return redirect()
            ->route('exam-fees.index')
            ->with('success', 'Exam fees deleted successfully.');
    }


    // Validation Rules
    private function validationRules(): array
    {
        return [

            // Course
            'course_type' => 'required|in:Semester Based,Annual Based',
            'course_id' => 'required|exists:courses,id',

            // Schemes
            'schemes' => 'required|array|min:1',
            'schemes.*' => 'required|exists:schemes,id',

            // Branches
            'branches' => 'required|array|min:1',
            'branches.*' => 'required|exists:branches,id',

            // Semesters
            'semesters' => 'required_if:course_type,Semester Based|array|min:1',
            'semesters.*' => 'required|exists:semesters,id',

            // Batches
            'batches' => 'required|array|min:1',
            'batches.*' => 'required|exists:batches,id',

            // Regular Exam Fee
            'regular_exam_fee' => 'required|numeric|min:0',

            // Reappear Exam Fee
            'reappear_theory_exam_fee' => 'required|numeric|min:0',
            'reappear_theory_max_exam_fee' => 'required|numeric|min:0',
            'reappear_practical_exam_fee' => 'required|numeric|min:0',
            'reappear_practical_max_exam_fee' => 'required|numeric|min:0',

            // Special Chance Exam Fee
            'special_chance_theory_exam_fee' => 'required|numeric|min:0',
            'special_chance_theory_max_exam_fee' => 'required|numeric|min:0',
            'special_chance_practical_exam_fee' => 'required|numeric|min:0',
            'special_chance_practical_max_exam_fee' => 'required|numeric|min:0',

            // Exam Dates
            'exam_start_date' => 'required|date',
            'exam_end_date' => 'required|date|after_or_equal:exam_start_date',

            // Late Fee 1
            'late_fee_start1' => 'nullable|date',
            'late_fee_end1' => 'nullable|date|after_or_equal:late_fee_start1',
            'late_fee_amount1' => 'nullable|numeric|min:0',

            // Late Fee 2
            'late_fee_start2' => 'nullable|date',
            'late_fee_end2' => 'nullable|date|after_or_equal:late_fee_start2',
            'late_fee_amount2' => 'nullable|numeric|min:0',

            // Late Fee 3
            'late_fee_start3' => 'nullable|date',
            'late_fee_end3' => 'nullable|date|after_or_equal:late_fee_start3',
            'late_fee_amount3' => 'nullable|numeric|min:0',
        ];
    }

}
