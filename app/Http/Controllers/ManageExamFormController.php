<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExaminationForm;
use App\Models\Institute;
use App\Models\Scheme;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentSubject;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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

    public function uploadExamForm()
    {
        return view('backend.examination-form.upload-exam-from');
    }

    public function importExamForm(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:20480',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return back()->with('error', 'Unable to read CSV file.');
        }

        $successCount = 0;
        $errorRows = [];
        $rowNumber = 1;

        try {

            /*
            |--------------------------------------------------------------------------
            | READ AND NORMALIZE CSV HEADERS
            |--------------------------------------------------------------------------
            */

            $headers = fgetcsv($handle);

            if (!$headers) {
                fclose($handle);

                return back()->with('error', 'CSV file is empty.');
            }

            $headers = array_map(function ($header) {

                $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);

                return strtolower(
                    preg_replace('/[^a-z0-9]/', '', trim($header))
                );

            }, $headers);

            $requiredHeaders = [
                'examname',
                'studentuniversityrollno',
                'coursename',
                'branchname',
                'scheme',
                'semester',
                'batch',
                'studentinstitute',
                'examcentername',
                'examtype',
                'examfeeamount',
                'latefee',
                'totalfee',
                'lastappearningsession',
                'lastappearingyear',
                'concatenatedsubjectcodes',
            ];

            $missingHeaders = array_diff($requiredHeaders, $headers);

            if (!empty($missingHeaders)) {

                fclose($handle);

                return back()->with(
                    'error',
                    'Missing CSV columns: ' . implode(', ', $missingHeaders)
                );
            }

            /*
            |--------------------------------------------------------------------------
            | PROCESS CSV ROWS
            |--------------------------------------------------------------------------
            */

            while (($row = fgetcsv($handle)) !== false) {

                $rowNumber++;

                // Skip empty rows
                if (count(array_filter($row, function ($value) {
                    return trim((string) $value) !== '';
                })) === 0) {
                    continue;
                }

                if (count($row) !== count($headers)) {

                    $errorRows[] = "Row {$rowNumber}: Invalid number of columns.";

                    continue;
                }

                $data = array_combine($headers, $row);

                $data = array_map(function ($value) {
                    return trim((string) $value);
                }, $data);

                try {

                    DB::transaction(function () use ($data) {

                        /*
                        |--------------------------------------------------------------------------
                        | 1. FIND EXAM
                        |--------------------------------------------------------------------------
                        */

                        $exam = Exam::where('name', $data['examname'])
                            ->first();

                        if (!$exam) {
                            throw new \Exception(
                                "Exam not found: {$data['examname']}"
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | 2. FIND STUDENT USING UNIVERSITY ROLL NUMBER
                        |--------------------------------------------------------------------------
                        */

                        $student = Student::where(
                            'university_roll_no',
                            $data['studentuniversityrollno']
                        )->first();

                        if (!$student) {
                            throw new \Exception(
                                "Student not found with University Roll No: " .
                                $data['studentuniversityrollno']
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | 3. FIND COURSE
                        |--------------------------------------------------------------------------
                        */

                        $course = Course::where('name', $data['coursename'])
                            ->first();

                        if (!$course) {
                            throw new \Exception(
                                "Course not found: {$data['coursename']}"
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | 4. FIND BRANCH
                        |--------------------------------------------------------------------------
                        */

                        $branch = Branch::where('course_id', $course->id)
                            ->where('name', $data['branchname'])
                            ->first();

                        if (!$branch) {
                            throw new \Exception(
                                "Branch not found: {$data['branchname']}"
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | 5. FIND SCHEME
                        |--------------------------------------------------------------------------
                        */

                        $scheme = Scheme::where('name', $data['scheme'])
                            ->first();

                        if (!$scheme) {
                            throw new \Exception(
                                "Scheme not found: {$data['scheme']}"
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | 6. FIND SEMESTER / ANNUAL SEMESTER
                        |--------------------------------------------------------------------------
                        */

                        if ($course->course_type === 'Annual Based') {

                            $annualSemester = DB::table('annual_semesters')
                                ->where('name', $data['semester'])
                                ->first();

                            if (!$annualSemester) {
                                throw new \Exception(
                                    "Annual semester not found: {$data['semester']}"
                                );
                            }

                            $semesterId = $annualSemester->id;

                        } else {

                            $semester = Semester::where(
                                'name',
                                $data['semester']
                            )->first();

                            if (!$semester) {
                                throw new \Exception(
                                    "Semester not found: {$data['semester']}"
                                );
                            }

                            $semesterId = $semester->id;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | 7. FIND BATCH
                        |--------------------------------------------------------------------------
                        */

                        $batch = Batch::where('name', $data['batch'])
                            ->first();

                        if (!$batch) {
                            throw new \Exception(
                                "Batch not found: {$data['batch']}"
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | 8. FIND STUDENT INSTITUTE
                        |--------------------------------------------------------------------------
                        */

                        $institute = Institute::where(
                            'name',
                            $data['studentinstitute']
                        )->first();

                        if (!$institute) {
                            throw new \Exception(
                                "Student institute not found: {$data['studentinstitute']}"
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | 9. FIND EXAM CENTER
                        |--------------------------------------------------------------------------
                        */

                        $examCenter = Institute::where(
                            'name',
                            $data['examcentername']
                        )->first();

                        if (!$examCenter) {
                            throw new \Exception(
                                "Exam center not found: {$data['examcentername']}"
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | 10. FIND SUBJECTS
                        |--------------------------------------------------------------------------
                        */

                        $subjectCodes = array_filter(
                            array_map(
                                'trim',
                                explode(',', $data['concatenatedsubjectcodes'])
                            )
                        );

                        if (empty($subjectCodes)) {
                            throw new \Exception(
                                'No subject codes provided.'
                            );
                        }

                        $subjectIds = [];

                        foreach ($subjectCodes as $subjectCode) {

                            $subjectQuery = Subject::where(
                                'course_id',
                                $course->id
                            )
                                ->where('branch_id', $branch->id)
                                ->where('scheme_id', $scheme->id)
                                ->where('subject_code', $subjectCode);

                            if ($course->course_type === 'Annual Based') {

                                $subjectQuery->where(
                                    'annual_semester_id',
                                    $semesterId
                                );

                            } else {

                                $subjectQuery->where(
                                    'semester_id',
                                    $semesterId
                                );
                            }

                            $subject = $subjectQuery->first();

                            if (!$subject) {
                                throw new \Exception(
                                    "Subject not found: {$subjectCode}"
                                );
                            }

                            $subjectIds[] = $subject->id;
                        }

                        $subjectIds = array_unique($subjectIds);

                        /*
                        |--------------------------------------------------------------------------
                        | 11. VALIDATE FEES AND EXAM TYPE
                        |--------------------------------------------------------------------------
                        */

                        $validator = Validator::make($data, [
                            'examtype' => 'required|in:Regular,Reappear,Special Chance',
                            'examfeeamount' => 'required|numeric|min:0',
                            'latefee' => 'required|numeric|min:0',
                            'totalfee' => 'required|numeric|min:0',
                        ]);

                        if ($validator->fails()) {
                            throw new \Exception(
                                implode(', ', $validator->errors()->all())
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | 12. FIND EXISTING EXAMINATION FORM
                        |--------------------------------------------------------------------------
                        */

                        $existingForm = ExaminationForm::where(
                            'exam_id',
                            $exam->id
                        )
                            ->where('student_id', $student->id)
                            ->where('semester_id', $semesterId)
                            ->where('exam_type', $data['examtype'])
                            ->lockForUpdate()
                            ->first();

                        /*
                        |--------------------------------------------------------------------------
                        | 13. GENERATE FORM ID AND TRANSACTION ID
                        |--------------------------------------------------------------------------
                        */

                        if ($existingForm) {

                            // Preserve existing IDs
                            $formId = $existingForm->form_id;
                            $transactionId = $existingForm->transaction_id;

                        } else {

                            // Lock exam row before generating serial
                            $lockedExam = Exam::where('id', $exam->id)
                                ->lockForUpdate()
                                ->first();

                            $examMonth = (string) $lockedExam->exam_month;
                            $examYear  = (string) $lockedExam->exam_year;

                            $prefix = $examMonth . $examYear;

                            // Last four digits of university roll number
                            $rollNumber = (string) $student->university_roll_no;

                            $lastFourDigits = substr($rollNumber, -4);

                            $existingForms = ExaminationForm::where(
                                'exam_id',
                                $exam->id
                            )
                                ->where('form_id', 'like', $prefix . '%')
                                ->lockForUpdate()
                                ->get(['form_id']);

                            $lastSerial = 0;

                            foreach ($existingForms as $existing) {

                                $existingId = (string) $existing->form_id;

                                $serialPart = substr(
                                    $existingId,
                                    strlen($prefix),
                                    -4
                                );

                                if (ctype_digit($serialPart)) {
                                    $lastSerial = max(
                                        $lastSerial,
                                        (int) $serialPart
                                    );
                                }
                            }

                            $nextSerial = $lastSerial + 1;

                            $serial = str_pad(
                                (string) $nextSerial,
                                5,
                                '0',
                                STR_PAD_LEFT
                            );

                            $formId = $prefix . $serial . $lastFourDigits;

                            $transactionId = 'HPTUEXAM' .
                                $prefix .
                                $serial .
                                $lastFourDigits;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | 14. SAVE EXAMINATION FORM
                        |--------------------------------------------------------------------------
                        */

                        $formData = [

                            'exam_id' => $exam->id,

                            'exam_type' => $data['examtype'],

                            'student_id' => $student->id,

                            'course_id' => $course->id,

                            'scheme_id' => $scheme->id,

                            'branch_id' => $branch->id,

                            'semester_id' => $semesterId,

                            'batch_id' => $batch->id,

                            'institute_id' => $institute->id,

                            'exam_center_id' => $examCenter->id,

                            'exam_fee' => $data['examfeeamount'],

                            'late_fee' => $data['latefee'],

                            'total_exam_fee' => $data['totalfee'],

                            'last_appearning_session' =>
                                $data['lastappearningsession'] ?: null,

                            'last_appearing_year' =>
                                $data['lastappearingyear'] ?: null,

                            'form_id' => $formId,

                            'transaction_id' => $transactionId,
                        ];

                        if ($existingForm) {

                            $existingForm->update($formData);

                            $form = $existingForm;

                        } else {

                            $formData['transaction_date'] = null;
                            $formData['payment_status'] = 1;

                            $form = ExaminationForm::create($formData);
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | 15. UPDATE SELECTED SUBJECTS
                        |--------------------------------------------------------------------------
                        */

                        $form->choosedSubjects()->delete();

                        $subjectData = [];

                        foreach ($subjectIds as $subjectId) {

                            $subjectData[] = [
                                'subject_id' => $subjectId,
                            ];
                        }

                        $form->choosedSubjects()->createMany($subjectData);

                    }, 5);

                    $successCount++;

                } catch (\Throwable $e) {

                    $errorRows[] = "Row {$rowNumber}: " . $e->getMessage();
                }
            }

            fclose($handle);

            return redirect()
                ->route('admin.examination-form.upload')
                ->with(
                    'success',
                    "{$successCount} examination form(s) imported/updated successfully."
                )
                ->with('import_errors', $errorRows);

        } catch (\Throwable $e) {

            if (is_resource($handle)) {
                fclose($handle);
            }

            report($e);

            return back()->with(
                'error',
                'CSV import failed: ' . $e->getMessage()
            );
        }
    }

}
