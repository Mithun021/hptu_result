<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\ExaminationForm;
use App\Models\Institute;
use App\Models\Student;
use App\Models\StudentSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

    public function edit($id)
    {
        $examinationForm = ExaminationForm::with([
            'student',
            'exam',
            'course',
            'scheme',
            'branch',
            'semester',
            'batch',
            'institute',
            'examCenter',
            'choosedSubjects.subject',
        ])->findOrFail($id);

        // Only Regular examination form can be edited
        abort_if(
            strtolower($examinationForm->exam_type ?? '') !== 'regular',
            403,
            'Only Regular examination forms can be edited.'
        );

        // Existing selected subject IDs
        $selectedSubjectIds = $examinationForm->choosedSubjects
            ->pluck('subject_id')
            ->map(fn ($subjectId) => (int) $subjectId)
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Fetch Student Mapped Subjects
        |--------------------------------------------------------------------------
        */

        $mappedSubjectsQuery = StudentSubject::with('subject')
            ->where('student_id', $examinationForm->student_id)
            ->where('course_id', $examinationForm->course_id)
            ->where('scheme_id', $examinationForm->scheme_id)
            ->where('branch_id', $examinationForm->branch_id);

        if (!empty($examinationForm->semester_id)) {
            $mappedSubjectsQuery->where(
                'semester_id',
                $examinationForm->semester_id
            );
        } else {
            $mappedSubjectsQuery->whereNull('semester_id');
        }

        $mappedSubjects = $mappedSubjectsQuery
            ->get()
            ->filter(fn ($item) => $item->subject !== null)
            ->sortBy(fn ($item) => $item->subject->orders ?? 0)
            ->values();

        // Fetch available exam centers
        $examCenters = Institute::orderBy('name')->get();

        return view('backend.examination-form.edit', compact(
            'examinationForm',
            'mappedSubjects',
            'selectedSubjectIds',
            'examCenters'
        ));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'exam_center_id' => 'required|integer|exists:institutes,id',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'required|integer|distinct|exists:subjects,id',
        ]);

        $examinationForm = ExaminationForm::findOrFail($id);

        // Only Regular examination form can be updated
        abort_if(
            strtolower($examinationForm->exam_type ?? '') !== 'regular',
            403,
            'Only Regular examination forms can be updated.'
        );

        $selectedSubjectIds = collect($request->input('subject_ids', []))
            ->map(fn ($subjectId) => (int) $subjectId)
            ->unique()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Validate Student Mapped Subjects
        |--------------------------------------------------------------------------
        */

        $mappedSubjectsQuery = StudentSubject::where(
            'student_id',
            $examinationForm->student_id
        )
            ->where('course_id', $examinationForm->course_id)
            ->where('scheme_id', $examinationForm->scheme_id)
            ->where('branch_id', $examinationForm->branch_id);

        if (!empty($examinationForm->semester_id)) {
            $mappedSubjectsQuery->where(
                'semester_id',
                $examinationForm->semester_id
            );
        } else {
            $mappedSubjectsQuery->whereNull('semester_id');
        }

        $mappedSubjectIds = $mappedSubjectsQuery
            ->pluck('subject_id')
            ->map(fn ($subjectId) => (int) $subjectId);

        // Ensure submitted subjects are mapped to this student
        $invalidSubjectIds = $selectedSubjectIds->diff($mappedSubjectIds);

        if ($invalidSubjectIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'subject_ids' => 'One or more selected subjects are not mapped to this student.',
            ]);
        }

        DB::transaction(function () use (
            $examinationForm,
            $request,
            $selectedSubjectIds
        ) {
            /*
            |--------------------------------------------------------------------------
            | Update Exam Center Only If Changed
            |--------------------------------------------------------------------------
            */

            $newExamCenterId = (int) $request->exam_center_id;
            $existingExamCenterId = (int) $examinationForm->exam_center_id;

            if ($newExamCenterId !== $existingExamCenterId) {
                $examinationForm->update([
                    'exam_center_id' => $newExamCenterId,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Update Selected Subjects
            |--------------------------------------------------------------------------
            */

            $existingSubjectIds = $examinationForm->choosedSubjects()
                ->pluck('subject_id')
                ->map(fn ($subjectId) => (int) $subjectId);

            // Remove unchecked existing subjects
            $removeSubjectIds = $existingSubjectIds->diff($selectedSubjectIds);

            if ($removeSubjectIds->isNotEmpty()) {
                $examinationForm->choosedSubjects()
                    ->whereIn('subject_id', $removeSubjectIds)
                    ->delete();
            }

            // Insert only newly checked subjects
            $insertSubjectIds = $selectedSubjectIds->diff($existingSubjectIds);

            foreach ($insertSubjectIds as $subjectId) {
                $examinationForm->choosedSubjects()->create([
                    'subject_id' => $subjectId,
                ]);
            }
        });

        return redirect()
            ->route('admin.examination-form.edit', $examinationForm->id)
            ->with('success', 'Examination form updated successfully.');
    }

}
