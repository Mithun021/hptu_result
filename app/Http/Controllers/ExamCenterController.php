<?php

namespace App\Http\Controllers;

use App\Models\ExamCenter;
use App\Models\Course;
use App\Models\Institute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamCenterController extends Controller
{
    public function index()
    {
        $examCenters = ExamCenter::with([
            'institute',
            'course',
            'branches',
            'semesters',
            'batches',
            'schemes',
            'colleges',
        ])->latest()->get();

        return view('backend.exam_center.index', compact('examCenters'));
    }

    public function create()
    {
        $courses = Course::orderBy('name', 'asc')->get();
        $institutes = Institute::orderBy('name', 'asc')->get();

        return view('backend.exam_center.create', compact(
            'courses',
            'institutes'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());

        DB::transaction(function () use ($validated) {

            $mappings = [
                'branches'  => $validated['branches'] ?? [],
                'semesters' => $validated['semesters'] ?? [],
                'batches'   => $validated['batches'] ?? [],
                'schemes'   => $validated['schemes'] ?? [],
                'colleges'  => $validated['colleges'] ?? [],
            ];

            unset(
                $validated['branches'],
                $validated['semesters'],
                $validated['batches'],
                $validated['schemes'],
                $validated['colleges']
            );

            $examCenter = ExamCenter::create($validated);

            $examCenter->branches()->sync($mappings['branches']);
            $examCenter->semesters()->sync($mappings['semesters']);
            $examCenter->batches()->sync($mappings['batches']);
            $examCenter->schemes()->sync($mappings['schemes']);
            $examCenter->colleges()->sync($mappings['colleges']);
        });

        return redirect()
            ->route('exam-centers.index')
            ->with('success', 'Exam center allocated successfully.');
    }

    public function show($id)
    {
        $examCenter = ExamCenter::with([
            'institute',
            'course',
            'branches',
            'semesters',
            'batches',
            'schemes',
            'colleges',
        ])->findOrFail($id);

        return view('backend.exam_center.show', compact('examCenter'));
    }

    public function edit($id)
    {
        $examCenter = ExamCenter::with([
            'institute',
            'course',
            'branches',
            'semesters',
            'batches',
            'schemes',
            'colleges',
        ])->findOrFail($id);

        $courses = Course::orderBy('name', 'asc')->get();

        // All institutes for the Exam Centers checkbox list.
        // Institute dropdown itself will be filtered through Course AJAX.
        $institutes = Institute::orderBy('name', 'asc')->get();

        return view('backend.exam_center.edit', compact(
            'examCenter',
            'courses',
            'institutes'
        ));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate($this->validationRules());

        $examCenter = ExamCenter::findOrFail($id);

        DB::transaction(function () use ($validated, $examCenter) {

            $mappings = [
                'branches'  => $validated['branches'] ?? [],
                'semesters' => $validated['semesters'] ?? [],
                'batches'   => $validated['batches'] ?? [],
                'schemes'   => $validated['schemes'] ?? [],
                'colleges'  => $validated['colleges'] ?? [],
            ];

            unset(
                $validated['branches'],
                $validated['semesters'],
                $validated['batches'],
                $validated['schemes'],
                $validated['colleges']
            );

            $examCenter->update($validated);

            $examCenter->branches()->sync($mappings['branches']);
            $examCenter->semesters()->sync($mappings['semesters']);
            $examCenter->batches()->sync($mappings['batches']);
            $examCenter->schemes()->sync($mappings['schemes']);
            $examCenter->colleges()->sync($mappings['colleges']);
        });

        return redirect()
            ->route('exam-centers.index')
            ->with('success', 'Exam center updated successfully.');
    }

    public function destroy($id)
    {
        $examCenter = ExamCenter::findOrFail($id);

        $examCenter->delete();

        return redirect()
            ->route('exam-centers.index')
            ->with('success', 'Exam center deleted successfully.');
    }

    private function validationRules(): array
    {
        return [
            'student_type' => 'required|string',

            'institute_id' => [
                'nullable',
                'required_if:student_type,Regular',
                'exists:institutes,id',
            ],

            'course_id' => 'required|exists:courses,id',

            'branches' => 'required|array|min:1',
            'branches.*' => 'required|exists:branches,id',

            'semesters' => 'required|array|min:1',
            'semesters.*' => 'required|exists:semesters,id',

            'batches' => 'required|array|min:1',
            'batches.*' => 'required|exists:batches,id',

            'schemes' => 'required|array|min:1',
            'schemes.*' => 'required|exists:schemes,id',

            'colleges' => 'required|array|min:1',
            'colleges.*' => 'required|exists:institutes,id',
        ];
    }
}
