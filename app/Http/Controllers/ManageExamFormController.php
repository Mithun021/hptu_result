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

    public function downloadExamForm()
    {

        $exams = Exam::all();
        $courses = Course::all();

        return view('backend.examination-form.download-exam-form', compact(
            'exams',
            'courses'
        ));
    }

    public function downloadExamFormSheet(Request $request)
    {
        /* ================= VALIDATION ================= */

        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'student_roll_number' => 'nullable|string',

            'course_id' => 'nullable|integer',
            'scheme_id' => 'nullable|integer',
            'branch_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
            'batch_id' => 'nullable|integer',
            'institute_id' => 'nullable|integer',

            'form_type' => 'required|in:row-wise,column-wise',
        ]);

        /* ================= BASE QUERY ================= */

        $query = ExaminationForm::with([
            'student',
            'course',
            'branch',
            'scheme',
            'semester',
            'batch',
            'institute',
            'examCenter',
            'choosedSubjects.subject',
        ])->where('exam_id', $request->exam_id)
        ->where('payment_status', 1);

        /* ================= STUDENT FILTER ================= */

        if ($request->filled('student_roll_number')) {

            $rollNumber = trim($request->student_roll_number);

            $student = Student::where(
                'university_roll_no',
                $rollNumber
            )->first();

            if ($student) {
                $query->where('student_id', $student->id);
            } else {
                $query->whereRaw('1 = 0');
            }

        } else {

            /* ================= OTHER FILTERS ================= */

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

                    $query->where(
                        $column,
                        $request->input($column)
                    );
                }
            }
        }

        /* ================= FETCH FORMS ================= */

        $examinationForms = $query
            ->orderBy('id', 'asc')
            ->get();

        /* ================= FILE NAME ================= */

        $fileName = 'examination-form-' .
            $request->exam_id . '-' .
            $request->form_type . '-' .
            now()->format('YmdHis') . '.csv';

        /* ================= CSV HEADERS ================= */

        if ($request->form_type === 'row-wise') {

            $headers = [
                'sn',
                'student_id',
                'course_name',
                'branch_name',
                'scheme',
                'semester',
                'section',
                'student_institute',
                'exam_center_name',
                'exam_type',
                'exam_fee_amount',
                'code',
                'name',
                'type',
            ];

        } else {

            $headers = [
                'sn',
                'student_id',
                'course_name',
                'branch_name',
                'scheme',
                'semester',
                'section',
                'student_institute',
                'exam_center_name',
                'form_id',
                'exam_type',
                'exam_fee_amount',
                'concatenated_subject_codes',
                'last_appearning_session',
                'last_appearing_year',
            ];
        }

        /* ================= DOWNLOAD CSV ================= */

        return response()->streamDownload(function () use (
            $examinationForms,
            $headers,
            $request
        ) {

            $file = fopen('php://output', 'w');

            // Excel UTF-8 support
            fwrite($file, "\xEF\xBB\xBF");

            // CSV Header
            fputcsv($file, $headers);

            $sn = 1;

            foreach ($examinationForms as $form) {

                /* ================= COMMON DETAILS ================= */

                $studentId = $form->student->university_roll_no
                    ?? $form->student_id
                    ?? '';

                $courseName = $form->course->name ?? '';

                $branchName = $form->branch->name ?? '';

                $schemeName = $form->scheme->name ?? '';

                $semesterName = $form->semester->name ?? '';

                $section = $form->batch->name ?? '';

                $studentInstitute = $form->institute->name ?? '';

                $examCenterName = $form->examCenter->name ?? '';

                $examType = $form->exam_type ?? '';

                $examFeeAmount = $form->total_exam_fee ?? 0;

                /*
                * Selected subjects from:
                * examination_form_choosed_subjects
                *
                * Relationship:
                * choosedSubjects()
                *
                * Nested relationship:
                * choosedSubjects.subject
                */

                $chosenSubjects = $form->choosedSubjects;

                /* ==================================================
                ROW WISE EXPORT
                ================================================== */

                if ($request->form_type === 'row-wise') {

                    foreach ($chosenSubjects as $chosenSubject) {

                        $subject = $chosenSubject->subject;

                        if (!$subject) {
                            continue;
                        }

                        $row = [
                            $sn++,
                            $studentId,
                            $courseName,
                            $branchName,
                            $schemeName,
                            $semesterName,
                            $section,
                            $studentInstitute,
                            $examCenterName,
                            $examType,
                            $examFeeAmount,
                            $subject->subject_code ?? '',
                            $subject->name ?? '',
                            $subject->type ?? '',
                        ];

                        fputcsv($file, $row);
                    }
                }

                /* ==================================================
                COLUMN WISE EXPORT
                ================================================== */

                else {

                    $subjectCodes = $chosenSubjects
                        ->map(function ($chosenSubject) {

                            return $chosenSubject->subject->subject_code
                                ?? null;

                        })
                        ->filter()
                        ->implode(', ');

                    $row = [
                        $sn++,
                        $studentId,
                        $courseName,
                        $branchName,
                        $schemeName,
                        $semesterName,
                        $section,
                        $studentInstitute,
                        $examCenterName,
                        $form->form_id ?? '',
                        $examType,
                        $examFeeAmount,
                        $subjectCodes,
                        $form->last_appearning_session ?? '',
                        $form->last_appearing_year ?? '',
                    ];

                    fputcsv($file, $row);
                }
            }

            fclose($file);

        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

}
