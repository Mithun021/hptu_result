<?php

namespace App\Http\Controllers;

use App\Models\AcademicAdjustment;
use App\Models\AnnualSemester;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\EndSemMark;
use App\Models\Exam;
use App\Models\ExaminationForm;
use App\Models\Institute;
use App\Models\MarksGrade;
use App\Models\MidSemExam;
use App\Models\MidSemMark;
use App\Models\PassPercentage;
use App\Models\ResultGpSheet;
use App\Models\Scheme;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentResultSummary;
use App\Models\StudentSubject;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResultController extends Controller
{
    public function internalMarks()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.internal-marks.index', compact('courses', 'exams'));
    }

    public function internalMarksSubjectWise()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.internal-marks.subject-wise.index', compact('courses', 'exams'));
    }

    public function fetchInternalMarks(Request $request)
    {
        $examId           = $request->exam_id;
        $courseType       = $request->course_type;
        $courseId         = $request->course_id;
        $branchId         = $request->branch_id;
        $batchId          = $request->batch_id;
        $schemeId         = $request->scheme_id;
        $semesterId       = $request->semester_id;
        $annualSemesterId = $request->annual_semester_id;
        $instituteId      = $request->institute_id;

        /*
        |--------------------------------------------------------------------------
        | 1. EXAM / COURSE / BRANCH
        |--------------------------------------------------------------------------
        */

        $exam   = Exam::find($examId);
        $course = Course::find($courseId);
        $branch = Branch::find($branchId);

        /*
        |--------------------------------------------------------------------------
        | 2. CLASS NAME
        |--------------------------------------------------------------------------
        */

        if ($courseType == 'Semester Based') {

            $semester = Semester::find($semesterId);

            $className = ($semester->name ?? '') . ' Semester';

        } else {

            $semester = AnnualSemester::find($annualSemesterId);

            $className = $semester->name ?? '';
        }

        /*
        |--------------------------------------------------------------------------
        | 3. EXAMINATION FORMS
        |--------------------------------------------------------------------------
        | Only students who have filled the selected regular examination form.
        |--------------------------------------------------------------------------
        */

        $examinationFormsQuery = ExaminationForm::with([
            'choosedSubjects' => function ($query) {
                $query->select(
                    'id',
                    'examination_form_id',
                    'subject_id'
                );
            }
        ])
            ->where('exam_id', $examId)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

            // Only Regular Examination Forms
            ->whereRaw('LOWER(exam_type) = ?', ['regular'])

            ->when($schemeId, function ($query) use ($schemeId) {
                $query->where('scheme_id', $schemeId);
            })

            ->when($instituteId, function ($query) use ($instituteId) {
                $query->where('institute_id', $instituteId);
            });

        /*
        |--------------------------------------------------------------------------
        | SEMESTER / ANNUAL FILTER
        |--------------------------------------------------------------------------
        | ExaminationForm model currently contains semester_id.
        | If annual_semester_id exists in the table, use that column.
        |--------------------------------------------------------------------------
        */

        if ($courseType == 'Semester Based') {

            $examinationFormsQuery->where('semester_id', $semesterId);

        } else {

            if (\Illuminate\Support\Facades\Schema::hasColumn(
                'examination_form',
                'annual_semester_id'
            )) {

                $examinationFormsQuery->where(
                    'annual_semester_id',
                    $annualSemesterId
                );

            } else {

                // Existing schema fallback
                $examinationFormsQuery->where(
                    'semester_id',
                    $annualSemesterId
                );
            }
        }

        $examinationForms = $examinationFormsQuery
            ->get([
                'id',
                'student_id'
            ]);

        /*
        |--------------------------------------------------------------------------
        | 4. STUDENT SUBJECT MAPPING
        |--------------------------------------------------------------------------
        | Source: examination_form_choosed_subjects
        |
        | Structure:
        | $studentSubjects[student_id] = [subject_id, subject_id]
        |--------------------------------------------------------------------------
        */

        $studentSubjects = $examinationForms
            ->groupBy('student_id')
            ->map(function ($forms) {

                return $forms
                    ->flatMap(function ($form) {

                        return $form->choosedSubjects
                            ->pluck('subject_id');

                    })
                    ->unique()
                    ->values()
                    ->toArray();

            })
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | 5. FORM FILLED STUDENT IDS
        |--------------------------------------------------------------------------
        */

        $studentIds = array_keys($studentSubjects);

        /*
        |--------------------------------------------------------------------------
        | 6. SELECTED SUBJECT IDS
        |--------------------------------------------------------------------------
        | Only subjects selected in examination forms.
        |--------------------------------------------------------------------------
        */

        $selectedSubjectIds = collect($studentSubjects)
            ->flatten()
            ->unique()
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | 7. SUBJECTS
        |--------------------------------------------------------------------------
        */

        $subjects = Subject::whereIn('id', $selectedSubjectIds)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)

            ->where(function ($query) {
                $query->where('is_nc', 0)
                    ->orWhereNull('is_nc');
            })

            ->when($schemeId, function ($query) use ($schemeId) {
                $query->where('scheme_id', $schemeId);
            })

            ->when($courseType == 'Semester Based', function ($query) use ($semesterId) {

                $query->where('semester_id', $semesterId)
                    ->whereNull('annual_semester_id');

            })

            ->when($courseType == 'Annual Based', function ($query) use ($annualSemesterId) {

                $query->where('annual_semester_id', $annualSemesterId)
                    ->whereNull('semester_id');

            })

            ->orderBy('type')
            ->orderBy('subject_code')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 8. STUDENTS
        |--------------------------------------------------------------------------
        | Only examination form filled students.
        | Institute relationship is eager loaded.
        |--------------------------------------------------------------------------
        */

        $students = Student::with([
            'institute:id,name,sort_name'
        ])
            ->whereIn('id', $studentIds)
            ->orderBy('institute_id')
            ->orderBy('university_roll_no')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 9. PRACTICAL SUBJECT IDS
        |--------------------------------------------------------------------------
        */

        $practicalSubjectIds = $subjects
            ->where('type', 'Practical')
            ->pluck('id')
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | 10. MID SEM MARKS (INTERNAL)
        |--------------------------------------------------------------------------
        */

        $midSemMarks = MidSemMark::where('exam_id', $examId)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->where('marks_type', 'internal')
            ->where('is_locked', 1)
            ->where('university_forword', 1)

            ->whereIn('student_id', $studentIds)
            ->whereIn('subject_id', $selectedSubjectIds)

            ->when($schemeId, function ($query) use ($schemeId) {
                $query->where('scheme_id', $schemeId);
            })

            ->when($courseType == 'Semester Based', function ($query) use ($semesterId) {

                $query->where('semester_id', $semesterId)
                    ->whereNull('annual_semester_id');

            })

            ->when($courseType == 'Annual Based', function ($query) use ($annualSemesterId) {

                $query->where('annual_semester_id', $annualSemesterId)
                    ->whereNull('semester_id');

            })

            ->get();

        /*
        |--------------------------------------------------------------------------
        | 11. END SEM MARKS (EXTERNAL)
        |--------------------------------------------------------------------------
        | Only Practical subjects.
        |--------------------------------------------------------------------------
        */

        $endSemMarks = collect();

        if (!empty($practicalSubjectIds) && !empty($studentIds)) {

            $endSemMarks = EndSemMark::where('exam_id', $examId)
                ->where('course_type', $courseType)
                ->where('course_id', $courseId)
                ->where('branch_id', $branchId)
                ->where('batch_id', $batchId)

                ->whereIn('student_id', $studentIds)
                ->whereIn('subject_id', $practicalSubjectIds)

                ->where('marks_type', 'external')
                ->where('is_locked', 1)

                ->when($schemeId, function ($query) use ($schemeId) {
                    $query->where('scheme_id', $schemeId);
                })

                ->when($courseType == 'Semester Based', function ($query) use ($semesterId) {

                    $query->where('semester_id', $semesterId)
                        ->whereNull('annual_semester_id');

                })

                ->when($courseType == 'Annual Based', function ($query) use ($annualSemesterId) {

                    $query->where('annual_semester_id', $annualSemesterId)
                        ->whereNull('semester_id');

                })

                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | 12. FORMAT MARKS ARRAY
        |--------------------------------------------------------------------------
        */

        $marks = [];

        // INTERNAL MARKS
        foreach ($midSemMarks as $mark) {

            if ($mark->exam_status == 'absent') {

                $value = '<span class="text-danger fw-bold">AB</span>';

            } else {

                $value = [
                    'obtained_marks' => $mark->obtained_marks ?? 0,
                    'grace_marks'    => $mark->grace_marks ?? 0,
                ];
            }

            $marks[$mark->student_id][$mark->subject_id]['internal'] = $value;
        }

        // EXTERNAL MARKS
        foreach ($endSemMarks as $mark) {

            $value = '-';

            if ($mark->exam_status == 'absent') {

                $value = '<span class="text-danger fw-bold">AB</span>';

            } else {

                $value = $mark->obtained_marks ?? '-';
            }

            $marks[$mark->student_id][$mark->subject_id]['external'] = $value;
        }

        /*
        |--------------------------------------------------------------------------
        | 13. EXTERNAL MAX MARKS
        |--------------------------------------------------------------------------
        */

        $externalMaxMarks = $endSemMarks
            ->groupBy('subject_id')
            ->map(function ($items) {

                return $items->first()->max_marks ?? 0;

            })
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | 14. VIEW
        |--------------------------------------------------------------------------
        */

        $view = $courseType === 'Annual Based'
            ? 'backend.result.internal-marks.annual-result'
            : 'backend.result.internal-marks.result';

        return view(
            $view,
            compact(
                'exam',
                'course',
                'branch',
                'className',
                'subjects',
                'students',
                'marks',
                'studentSubjects',
                'externalMaxMarks'
            )
        )->render();
    }

    public function fetchInternalMarksSubjectWise(Request $request)
    {
        $examId           = $request->exam_id;
        $midsemexamId     = $request->mid_sem_exam_id ?? $request->midsem_exam_id;
        $courseType       = $request->course_type;
        $courseId         = $request->course_id;
        $branchId         = $request->branch_id;
        $batchId          = $request->batch_id;
        $schemeId         = $request->scheme_id;
        $semesterId       = $request->semester_id;
        $annualSemesterId = $request->annual_semester_id;
        $instituteId      = $request->institute_id;
        $marksType        = $request->marks_type;
        $subjectId        = $request->subject_id;

        /*
        |--------------------------------------------------------------------------
        | EXAM
        |--------------------------------------------------------------------------
        */

        $exam = Exam::findOrFail($examId);

        $midsemexam = MidSemExam::find($midsemexamId);

        /*
        |--------------------------------------------------------------------------
        | COURSE / BRANCH / BATCH / SCHEME
        |--------------------------------------------------------------------------
        */

        $course = Course::findOrFail($courseId);
        $branch = Branch::findOrFail($branchId);
        $batch  = Batch::findOrFail($batchId);
        $scheme = Scheme::find($schemeId);

        /*
        |--------------------------------------------------------------------------
        | SEMESTER NAME
        |--------------------------------------------------------------------------
        */

        if ($courseType == 'Semester Based') {

            $semester = Semester::findOrFail($semesterId);

            $className = ' SEM - ' . $semester->name;

        } else {

            $semester = AnnualSemester::findOrFail($annualSemesterId);

            $className = $semester->name;
        }

        /*
        |--------------------------------------------------------------------------
        | SUBJECT
        |--------------------------------------------------------------------------
        */

        $subject = Subject::findOrFail($subjectId);

        /*
        |--------------------------------------------------------------------------
        | STUDENTS
        |--------------------------------------------------------------------------
        */

        $students = Student::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })

            ->when($instituteId, function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            })

            ->orderBy('institute_id')
            ->orderBy('university_roll_no')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | MARKS
        |--------------------------------------------------------------------------
        */

        $rawMarks = MidSemMark::where('exam_id', $examId)
            ->where('subject_id', $subjectId)
            ->where('marks_type', $marksType)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->where('university_forword', 1)

            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {

                $q->where('semester_id', $semesterId)
                    ->whereNull('annual_semester_id');

            })

            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {

                $q->where('annual_semester_id', $annualSemesterId)
                    ->whereNull('semester_id');

            })

            ->get();

        /*
        |--------------------------------------------------------------------------
        | FORMAT MARKS
        |--------------------------------------------------------------------------
        */

        $marks = [];

        foreach ($rawMarks as $mark) {

            $marks[$mark->student_id] = [

                'id'             => $mark->id,

                'obtained_marks' => $mark->obtained_marks,

                'exam_status'    => $mark->exam_status,

                'grace_marks'    => $mark->grace_marks ?? 0,

                'is_locked'      => $mark->is_locked ?? 0,

            ];
        }

        /*
        |--------------------------------------------------------------------------
        | STUDENT SUBJECTS
        |--------------------------------------------------------------------------
        | Only students having:
        | 1. Examination Form
        | 2. Successful payment
        | 3. Selected current subject
        |
        | Both Annual Based and Semester Based use semester_id.
        |--------------------------------------------------------------------------
        */

        $studentSubjects = ExaminationForm::where('exam_id', $examId)

            ->where('course_id', $courseId)

            ->where('branch_id', $branchId)

            ->where('batch_id', $batchId)

            ->where('payment_status', 1)

            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })

            ->when($instituteId, function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            })

            ->where('semester_id', $semesterIdForForm = (
                $courseType == 'Semester Based'
                    ? $semesterId
                    : $annualSemesterId
            ))

            ->whereHas('choosedSubjects', function ($q) use ($subjectId) {

                $q->where('subject_id', $subjectId);

            })

            ->pluck('student_id')

            ->unique()

            ->values()

            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'backend.result.internal-marks.subject-wise.result',
            compact(
                'exam',
                'midsemexam',
                'courseType',
                'course',
                'branch',
                'scheme',
                'className',
                'batch',
                'subject',
                'students',
                'marks',
                'marksType',
                'studentSubjects'
            )
        )->render();
    }

    public function saveInternalMarks(Request $request)
    {
        $request->validate([

            'student_id' => 'required|exists:students,id',

            'subject_id' => 'required|exists:subjects,id',

            'exam_id' => 'required|exists:exams,id',

            'midsem_exam_id' => 'required|exists:mid_sem_exams,id',

            'marks_type' => 'required',

            'course_type' => 'required|in:Semester Based,Annual Based',

            'course_id' => 'required|exists:courses,id',

            'branch_id' => 'required|exists:branches,id',

            'batch_id' => 'required|exists:batches,id',

            'scheme_id' => 'nullable|exists:schemes,id',

            'institute_id' => 'nullable|exists:institutes,id',

            'semester_id' => 'nullable',

            'annual_semester_id' => 'nullable',

            'exam_status' => 'required|in:present,absent',

            'obtained_marks' => 'nullable|numeric|min:0',

            'max_marks' => 'required|numeric|min:0',

        ]);

        try {

            $studentId = $request->student_id;

            $subjectId = $request->subject_id;

            $examId = $request->exam_id;

            $midsemexamId = $request->midsem_exam_id;

            $courseType = $request->course_type;

            $courseId = $request->course_id;

            $branchId = $request->branch_id;

            $batchId = $request->batch_id;

            $schemeId = $request->scheme_id;

            $semesterId = $request->semester_id;

            $annualSemesterId = $request->annual_semester_id;

            $marksType = $request->marks_type;

            $examStatus = $request->exam_status;

            $maxMarks = $request->max_marks;



            /*
            |--------------------------------------------------------------------------
            | GET INSTITUTE ID
            |--------------------------------------------------------------------------
            | First priority: Request institute_id
            | Second priority: Student profile institute_id
            |--------------------------------------------------------------------------
            */

            $student = Student::findOrFail($studentId);

            $instituteId = $request->filled('institute_id')
                ? $request->institute_id
                : $student->institute_id;


            if (!$instituteId) {

                return response()->json([

                    'status' => false,

                    'message' => 'Institute ID not found in request or student profile.'

                ], 422);

            }


            /*
            |--------------------------------------------------------------------------
            | ABSENT / PRESENT MARKS
            |--------------------------------------------------------------------------
            */

            $obtainedMarks = $examStatus == 'absent'
                ? 0
                : $request->obtained_marks;

            if ($examStatus == 'present' && $obtainedMarks === null) {

                return response()->json([

                    'status' => false,

                    'message' => 'Please enter obtained marks.'

                ], 422);

            }

            if ($examStatus == 'present' && $obtainedMarks > $maxMarks) {

                return response()->json([

                    'status' => false,

                    'message' => 'Obtained marks cannot exceed maximum marks (' . $maxMarks . ').'

                ], 422);

            }

            // Debug ---------------------------------------------------------------------

            // Log::info('INTERNAL MARKS DEBUG', [

            //     'student_id' => $studentId,
            //     'subject_id' => $subjectId,
            //     'exam_id' => $examId,
            //     'midsem_exam_id' => $midsemexamId,

            //     'institute_id' => $instituteId,

            //     'course_type' => $courseType,
            //     'course_id' => $courseId,
            //     'branch_id' => $branchId,
            //     'batch_id' => $batchId,

            //     'scheme_id' => $schemeId,
            //     'semester_id' => $semesterId,
            //     'annual_semester_id' => $annualSemesterId,

            //     'max_marks' => $maxMarks,
            //     'obtained_marks' => $obtainedMarks,

            // ]);

            /*
            |--------------------------------------------------------------------------
            | VERIFY STUDENT SUBJECT SELECTION
            |--------------------------------------------------------------------------
            | Payment must be successful and subject must be selected.
            |--------------------------------------------------------------------------
            */

            $formExists = ExaminationForm::where('exam_id', $examId)

                ->where('student_id', $studentId)

                ->where('course_id', $courseId)

                ->where('branch_id', $branchId)

                ->where('batch_id', $batchId)

                ->where('institute_id', $instituteId)

                ->where('payment_status', 1)

                ->when($schemeId, function ($q) use ($schemeId) {
                    $q->where('scheme_id', $schemeId);
                })

                ->where('semester_id', $courseType == 'Semester Based'
                    ? $semesterId
                    : $annualSemesterId
                )

                ->whereHas('choosedSubjects', function ($q) use ($subjectId) {

                    $q->where('subject_id', $subjectId);

                })

                ->exists();


            if (!$formExists) {

                return response()->json([

                    'status' => false,

                    'message' => 'Student has not selected this subject in a successfully paid examination form.'

                ], 422);

            }


            /*
            |--------------------------------------------------------------------------
            | FIND EXISTING MARKS
            |--------------------------------------------------------------------------
            */

            $markQuery = MidSemMark::where('exam_id', $examId)

                ->where('mid_sem_exam_id', $midsemexamId)

                ->where('student_id', $studentId)

                ->where('subject_id', $subjectId)

                ->where('marks_type', $marksType)

                ->where('course_type', $courseType)

                ->where('course_id', $courseId)

                ->where('branch_id', $branchId)

                ->where('batch_id', $batchId)

                ->where('institute_id', $instituteId);


            if ($courseType == 'Semester Based') {

                $markQuery->where('semester_id', $semesterId)
                    ->whereNull('annual_semester_id');

            } else {

                $markQuery->where('annual_semester_id', $annualSemesterId)
                    ->whereNull('semester_id');

            }


            if ($schemeId) {

                $markQuery->where('scheme_id', $schemeId);

            }


            $mark = $markQuery->first();


            /*
            |--------------------------------------------------------------------------
            | SAVE / UPDATE
            |--------------------------------------------------------------------------
            */

            if ($mark) {

                // Already existing record

                if (($mark->is_locked ?? 0) == 1) {

                    return response()->json([

                        'status' => false,

                        'message' => 'Marks are locked. You cannot update them.'

                    ], 422);

                }

            } else {

                // New record

                $mark = new MidSemMark();

                $mark->exam_id = $examId;

                $mark->mid_sem_exam_id = $midsemexamId;

                $mark->student_id = $studentId;

                $mark->subject_id = $subjectId;

                $mark->marks_type = $marksType;

                $mark->course_type = $courseType;

                $mark->course_id = $courseId;

                $mark->branch_id = $branchId;

                $mark->batch_id = $batchId;

                $mark->scheme_id = $schemeId;

                $mark->institute_id = $instituteId;


                if ($courseType == 'Semester Based') {

                    $mark->semester_id = $semesterId;

                    $mark->annual_semester_id = null;

                } else {

                    $mark->semester_id = null;

                    $mark->annual_semester_id = $annualSemesterId;

                }


                $mark->is_locked = 0;

                $mark->grace_marks = 0;

            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE MARKS
            |--------------------------------------------------------------------------
            */

            $mark->max_marks = $maxMarks;

            $mark->obtained_marks = $obtainedMarks;

            $mark->exam_status = $examStatus;

            $mark->university_forword = 1;

            $mark->is_locked = 0;

            $mark->save();


            return response()->json([

                'status' => true,

                'message' => 'Obtained marks saved successfully.',

                'data' => [

                    'student_id' => $studentId,

                    'subject_id' => $subjectId,

                    'mid_sem_exam_id' => $midsemexamId,

                    'institute_id' => $instituteId,

                    'obtained_marks' => $obtainedMarks,

                    'exam_status' => $examStatus,

                    'is_locked' => 0,

                ]

            ]);

        } catch (\Throwable $e) {

            Log::error('Internal Marks Save Error', [

                'message' => $e->getMessage(),

                'file' => $e->getFile(),

                'line' => $e->getLine(),

                'request_data' => $request->all(),

            ]);

            return response()->json([

                'status' => false,

                'message' => $e->getMessage(),

            ], 500);

        }
    }

    public function exportInternal(Request $request)
    {
        /**
         * =========================
         * FILTER INPUTS
         * =========================
         */
        $course_type        = $request->course_type;
        $course_id          = $request->course_id;
        $scheme_id          = $request->scheme_id;
        $branch_id          = $request->branch_id;
        $semester_id        = $request->semester_id;
        $annual_semester_id = $request->annual_semester_id;
        $batch_id           = $request->batch_id;
        $mid_sem_exam_id    = $request->mid_sem_exam_id;
        $marks_type         = $request->marks_type;
        $subject_id         = $request->subject_id;

        // Institute ID from modal (optional)
        $institute_id = $request->institute_id;


        /**
         * =========================
         * MASTER DATA
         * =========================
         */
        $course   = Course::find($course_id);
        $scheme   = Scheme::find($scheme_id);
        $branch   = Branch::find($branch_id);
        $semester = Semester::find($semester_id);
        $annual   = AnnualSemester::find($annual_semester_id);
        $batch    = Batch::find($batch_id);
        $exam     = MidSemExam::find($mid_sem_exam_id);
        $subject  = Subject::find($subject_id);


        /**
         * =========================
         * EXAM DETAILS
         * =========================
         */
        $examDetails = $exam
            ? \Illuminate\Support\Facades\DB::table('exams')
                ->where('id', $exam->exam_id)
                ->first()
            : null;


        /**
         * =========================
         * EXAMINATION FORM QUERY
         * =========================
         */
        $examinationForms = ExaminationForm::where('exam_type', 'Regular')
            ->where('course_id', $course_id)
            ->where('branch_id', $branch_id)
            ->where('batch_id', $batch_id)

            ->when($exam, function ($q) use ($exam) {
                $q->where('exam_id', $exam->exam_id);
            })

            ->when(
                $institute_id,
                fn($q) => $q->where('institute_id', $institute_id)
            )

            ->where('payment_status', 1)

            ->when(
                $scheme_id,
                fn($q) => $q->where('scheme_id', $scheme_id)
            )

            ->when(
                $course_type === 'Semester Based',
                function ($q) use ($semester_id) {
                    $q->where('semester_id', $semester_id);
                },
                function ($q) use ($annual_semester_id) {
                    $q->where('annual_semester_id', $annual_semester_id);
                }
            )

            ->whereHas('choosedSubjects', function ($q) use ($subject_id) {
                $q->where('subject_id', $subject_id);
            });

        /**
         * =========================
         * ELIGIBLE STUDENT IDS
         * =========================
         */
        $studentSubjects = $examinationForms
            ->pluck('student_id')
            ->unique()
            ->values()
            ->toArray();


        /**
         * =========================
         * STUDENTS
         * =========================
         */
        $studentsQuery = Student::whereIn('id', $studentSubjects)
            ->where('course_type', $course_type)
            ->where('course_id', $course_id)
            ->where('branch_id', $branch_id)
            ->where('batch_id', $batch_id)

            ->when(
                $institute_id,
                fn($q) => $q->where('institute_id', $institute_id)
            )

            ->when(
                $scheme_id,
                fn($q) => $q->where('scheme_id', $scheme_id)
            )

            // ->when(
            //     $course_type === 'Semester Based',
            //     fn($q) => $q->where('semester_id', $semester_id),
            //     fn($q) => $q->where('annual_semester_id', $annual_semester_id)
            // )

            ->orderByRaw("
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(university_roll_no, '-', 2), '-', -1) AS UNSIGNED) ASC,
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(university_roll_no, '-', 4), '-', -1) AS UNSIGNED) ASC
            ");

        $students = $studentsQuery->get();


        /**
         * =========================
         * INSTITUTE ID FALLBACK
         * =========================
         * If institute ID is not provided by modal,
         * take it from the selected students.
         */
        if (!$institute_id) {

            $institute_id = $students
                ->pluck('institute_id')
                ->filter()
                ->unique()
                ->first();

        }

        $institute = $institute_id
            ? Institute::find($institute_id)
            : null;


        /**
         * =========================
         * CSV FILE NAME
         * =========================
         */
        $subjectCode = $subject->subject_code ?? 'Subject';

        $examMonth = $examDetails->exam_month ?? '';
        $examYear  = $examDetails->exam_year ?? '';

        $fileName = 'Internal_Marks_' .
            $subjectCode . '_' .
            $examMonth . '_' .
            $examYear . '.csv';

        $fileName = preg_replace('/[^A-Za-z0-9_.-]/', '_', $fileName);


        /**
         * =========================
         * CSV HEADERS
         * =========================
         */
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$fileName\"",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];


        /**
         * =========================
         * CSV COLUMNS
         * =========================
         */
        $columns = [
            'id',
            'university_roll_no',
            'student_name',
            'internal_exam_name',
            'course_type',
            'course_name',
            'scheme_name',
            'branch_name',
            'semester_name',
            // 'annual_semester_name',
            'batch_name',
            'institute_name',
            'marks_type',
            'subject_name',
            'subject_code',
            'max_marks',
            'obtained_marks',
            'exam_status'
        ];


        /**
         * =========================
         * CSV CALLBACK
         * =========================
         */
        $callback = function () use (
            $students,
            $columns,
            $course,
            $scheme,
            $branch,
            $semester,
            $annual,
            $batch,
            $institute,
            $subject,
            $exam,
            $marks_type
        ) {

            $file = fopen('php://output', 'w');

            // CSV Header
            fputcsv($file, $columns);

            foreach ($students as $key => $student) {

                $row = [
                    $key + 1,
                    $student->university_roll_no,
                    $student->student_name,
                    $exam->mid_sem_exam_name ?? '',
                    $student->course_type,
                    $course->name ?? '',
                    $scheme->name ?? '',
                    $branch->name ?? '',
                    $semester->name ?? '',
                    // $annual->name ?? '',
                    $batch->name ?? '',
                    $institute->name ?? '',
                    $marks_type,
                    $subject->name ?? '',
                    $subject->subject_code ?? '',
                    '', // max_marks
                    '', // obtained_marks
                    'present'
                ];

                fputcsv($file, $row);
            }

            fclose($file);
        };


        return new StreamedResponse($callback, 200, $headers);
    }
    public function importInternal(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        try {

            $path = $request->file('file')->getRealPath();
            $rawData = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if (!$rawData || count($rawData) <= 1) {
                return back()->with('error', 'CSV file is empty or invalid.');
            }

            $data = array_map('str_getcsv', $rawData);
            $header = array_map(fn($h) => strtolower(trim($h)), array_shift($data));

            $insertCount = 0;
            $skipCount   = 0;

            DB::beginTransaction();

            foreach ($data as $row) {

                if (count(array_filter($row)) === 0) {
                    continue;
                }

                $row = array_combine($header, $row);

                if (!$row) {
                    continue;
                }

                // ============================
                // 1️⃣ Basic Master Lookups
                // ============================

                $course = Course::where('name', trim($row['course_name']))->first();

                $branch = $course
                    ? Branch::where('course_id', $course->id)
                        ->where('name', trim($row['branch_name']))
                        ->first()
                    : null;

                $semester = Semester::where('name', trim($row['semester_name']))->first();

                // Annual Semester Name column is not available in CSV
                // $annual = AnnualSemester::where('name', trim($row['annual_semester_name']))->first();

                $annual = null;

                $batch = Batch::where('name', trim($row['batch_name']))->first();

                $institute = Institute::where('name', trim($row['institute_name']))->first();


                // ============================
                // 2️⃣ Scheme (Optional)
                // ============================

                $scheme = null;

                if (!empty(trim($row['scheme_name']))) {
                    $scheme = Scheme::where('name', trim($row['scheme_name']))->first();
                }


                // ============================
                // 3️⃣ Student Lookup
                // Based on university_roll_no
                // ============================

                $studentQuery = Student::where('university_roll_no', trim($row['university_roll_no']))
                    ->where('course_type', trim($row['course_type']))
                    ->where('course_id', $course?->id)
                    ->where('branch_id', $branch?->id)
                    ->where('batch_id', $batch?->id)
                    ->where('institute_id', $institute?->id);

                // Course Type Based Condition
                // if (trim($row['course_type']) === 'Semester Based') {
                //     $studentQuery->where('semester_id', $semester?->id)
                //                  ->whereNull('annual_semester_id');
                // } else {
                //     $studentQuery->where('annual_semester_id', $annual?->id)
                //                  ->whereNull('semester_id');
                // }

                if ($scheme) {
                    $studentQuery->where('scheme_id', $scheme->id);
                }

                $student = $studentQuery->first();

                if (!$student) {
                    $skipCount++;
                    continue;
                }


                // ============================
                // 4️⃣ Annual Semester Lookup
                // From Student Record
                // ============================

                if (trim($row['course_type']) === 'Annual Based') {
                    $annual = AnnualSemester::find($student->annual_semester_id);
                }


                // ============================
                // 5️⃣ Subject Lookup
                // ============================

                $subjectQuery = Subject::where('subject_code', trim($row['subject_code']))
                    ->where('name', trim($row['subject_name']))
                    ->where('course_type', trim($row['course_type']))
                    ->where('course_id', $course?->id)
                    ->where('branch_id', $branch?->id);

                if (trim($row['course_type']) === 'Semester Based') {

                    $subjectQuery->where('semester_id', $semester?->id)
                        ->whereNull('annual_semester_id');

                } else {

                    $subjectQuery->where('annual_semester_id', $annual?->id)
                        ->whereNull('semester_id');

                }

                if ($scheme) {
                    $subjectQuery->where('scheme_id', $scheme->id);
                }

                $subject = $subjectQuery->first();

                if (!$subject) {
                    $skipCount++;
                    continue;
                }


                // ============================
                // 6️⃣ Exam Lookup
                // Based only on MidSemExam table
                // ============================

                $midSemExam = MidSemExam::where('mid_sem_exam_name', trim($row['internal_exam_name']))
                    ->where('status', 1)
                    ->first();

                if (!$midSemExam) {
                    $skipCount++;
                    continue;
                }

                // exam_id directly from mid_sem_exam table
                $examId = $midSemExam->exam_id;


                // ============================
                // 7️⃣ Duplicate Check
                // ============================

                $courseType = strtolower(trim($row['course_type']));

                $existsQuery = MidSemMark::where('student_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->where('exam_id', $examId)
                    ->where('mid_sem_exam_id', $midSemExam->id)
                    ->where('course_type', $courseType)
                    ->where('course_id', $course?->id)
                    ->where('branch_id', $branch?->id)
                    ->where('batch_id', $batch?->id)
                    ->where('institute_id', $institute?->id)
                    ->where('marks_type', trim($row['marks_type']));

                if ($scheme) {
                    $existsQuery->where('scheme_id', $scheme->id);
                }

                if ($courseType === 'semester based') {

                    $existsQuery->where('semester_id', $semester?->id)
                        ->whereNull('annual_semester_id');

                } else {

                    $existsQuery->where('annual_semester_id', $annual?->id)
                        ->whereNull('semester_id');

                }

                if ($existsQuery->exists()) {
                    $skipCount++;
                    continue;
                }


                // ============================
                // 8️⃣ Exam Status Handling
                // ============================

                $examStatus = strtolower(trim($row['exam_status']));

                $obtainedMarks = ($examStatus === 'absent')
                    ? 0
                    : (float) $row['obtained_marks'];


                // ============================
                // 9️⃣ Insert Marks
                // ============================

                $courseType = ucwords(trim($row['course_type']));

                MidSemMark::create([
                    'exam_id'              => $examId,
                    'mid_sem_exam_id'      => $midSemExam->id,
                    'student_id'           => $student->id,
                    'subject_id'           => $subject->id,
                    'institute_id'         => $institute?->id,
                    'course_type'          => $courseType,
                    'course_id'            => $course?->id,
                    'branch_id'            => $branch?->id,
                    'batch_id'             => $batch?->id,
                    'semester_id'          => $semester?->id ?? null,
                    'annual_semester_id'   => $annual?->id ?? null,
                    'scheme_id'            => $scheme?->id,
                    'marks_type'           => trim($row['marks_type']),
                    'max_marks'            => (float) $row['max_marks'],
                    'obtained_marks'       => $obtainedMarks,
                    'exam_status'          => $examStatus,
                    'is_locked'            => 0,
                    'university_forword'   => 1,
                ]);

                $insertCount++;
            }

            DB::commit();

            return back()->with(
                'success',
                "Inserted: {$insertCount} | Skipped (Already/Invalid): {$skipCount}"
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function updateInternalMarksLockStatus(Request $request)
    {
        $request->validate([
            'records' => 'required|array'
        ]);

        foreach ($request->records as $record) {

            MidSemMark::where([

                'course_type'        => $record['course_type'],
                'course_id'          => $record['course_id'],
                'branch_id'          => $record['branch_id'],
                'batch_id'           => $record['batch_id'],
                'marks_type'         => $record['marks_type'],
                'student_id'         => $record['student_id'],
                'subject_id'         => $record['subject_id'],
                'exam_id'            => $record['exam_id'],
                'mid_sem_exam_id'    => $record['mid_sem_exam_id'],

            ])

            ->when(!empty($record['scheme_id']), function ($q) use ($record) {

                $q->where('scheme_id', $record['scheme_id']);

            })

            ->when(

                $record['course_type'] == 'Semester Based',

                function ($q) use ($record) {

                    $q->where('semester_id', $record['semester_id'])
                    ->whereNull('annual_semester_id');

                },

                function ($q) use ($record) {

                    $q->where('annual_semester_id', $record['annual_semester_id'])
                    ->whereNull('semester_id');

                }

            )

            ->update([

                'is_locked' => $record['is_locked']

            ]);
        }

        return response()->json([

            'status' => true,
            'message' => 'Lock/Unlock updated successfully.'

        ]);
    }

    public function updateGraceMarks(Request $request)
    {
        $request->validate([
            'records' => 'required|array'
        ]);

        foreach ($request->records as $record) {

            $query = MidSemMark::where([

                'course_type'        => $record['course_type'],
                'course_id'          => $record['course_id'],
                'branch_id'          => $record['branch_id'],
                'batch_id'           => $record['batch_id'],
                'marks_type'         => $record['marks_type'],
                'student_id'         => $record['student_id'],
                'subject_id'         => $record['subject_id'],
                'exam_id'            => $record['exam_id'],
                'mid_sem_exam_id'    => $record['mid_sem_exam_id'],

            ])

            ->when(!empty($record['scheme_id']), function ($q) use ($record) {

                $q->where('scheme_id', $record['scheme_id']);

            })

            ->when(

                $record['course_type'] == 'Semester Based',

                function ($q) use ($record) {

                    $q->where('semester_id', $record['semester_id'])
                    ->whereNull('annual_semester_id');

                },

                function ($q) use ($record) {

                    $q->where('annual_semester_id', $record['annual_semester_id'])
                    ->whereNull('semester_id');

                }

            );

            /*
            =====================================
            GET CURRENT MARK RECORD
            =====================================
            */

            $markData = $query->first();

            if (!$markData) {
                continue;
            }

            $obtainedMarks = (float) $markData->obtained_marks;
            $maxMarks      = (float) $markData->max_marks;

            $requestedGrace = (float) $record['grace_marks'];

            /*
            =====================================
            CHECK MAX LIMIT
            =====================================

            Example:
            obtained = 9
            max = 10
            entered grace = 3

            allowed grace = 1
            */

            $allowedGrace = $maxMarks - $obtainedMarks;

            /*
            If obtained marks already equal/exceed max
            then grace should be 0
            */

            if ($allowedGrace < 0) {

                $allowedGrace = 0;

            }

            /*
            Final grace marks
            */

            $finalGraceMarks = min($requestedGrace, $allowedGrace);

            /*
            =====================================
            UPDATE
            =====================================
            */

            $query->update([

                'grace_marks' => $finalGraceMarks

            ]);
        }

        return response()->json([

            'status' => true,
            'message' => 'Grace marks updated successfully.'

        ]);
    }

    public function externalMarks()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.external-marks.index', compact('courses', 'exams'));
    }

    public function externalMarksSubjectWise()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.external-marks.subject-wise.index', compact('courses', 'exams'));
    }

    // public function fetchExternalMarksSubjectWise(Request $request){
    //     dd($request->all());
    // }

    public function fetchExternalMarksSubjectWise(Request $request)
    {
        $examId            = $request->exam_id;
        $courseType        = $request->course_type;
        $courseId          = $request->course_id;
        $branchId          = $request->branch_id;
        $batchId           = $request->batch_id;
        $schemeId          = $request->scheme_id;
        $semesterId        = $request->semester_id;
        $annualSemesterId  = $request->annual_semester_id;
        $instituteId       = $request->institute_id;
        $subjectId         = $request->subject_id;
        $marksType         = $request->marks_type;

        /*
        =====================================
        EXAM / COURSE / BRANCH / SUBJECT
        =====================================
        */

        $exam    = Exam::find($examId);
        $course  = Course::find($courseId);
        $branch  = Branch::find($branchId);
        $subject = Subject::find($subjectId);
        $scheme  = Scheme::find($schemeId);
        $batch   = Batch::find($batchId);

        /*
        =====================================
        CLASS NAME
        =====================================
        */

        if ($courseType == "Semester Based") {

            $semester   = Semester::find($semesterId);
            $className  = "Sem - ".$semester->name;

        } else {

            $semester   = AnnualSemester::find($annualSemesterId);
            $className  = $semester->name;
        }

        /*
        =====================================
        STUDENTS FROM EXAMINATION FORMS
        =====================================
        Only students who:
        1. Have filled examination form
        2. Have selected the current subject
        =====================================
        */

        $examFormStudentIds = ExaminationForm::where('exam_id', $examId)
            ->where('exam_type', "Regular")
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })

            ->when($instituteId, function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            })

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            })

            ->whereHas('choosedSubjects', function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId);
            })

            ->pluck('student_id')
            ->unique()
            ->values();

        /*
        =====================================
        ELIGIBLE STUDENTS
        =====================================
        */

        $students = Student::whereIn('id', $examFormStudentIds)

            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })

            ->when($instituteId, function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            })

            ->orderBy('institute_id')
            ->orderBy('university_roll_no')
            ->get();

        /*
        =====================================
        MARKS
        =====================================
        */

        $rawMarks = EndSemMark::where('exam_id', $examId)

            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->where('subject_id', $subjectId)
            ->where('marks_type', $marksType)

            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                    ->whereNull('annual_semester_id');
            })

            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                $q->where('annual_semester_id', $annualSemesterId)
                    ->whereNull('semester_id');
            })

            ->whereIn('student_id', $students->pluck('id'))

            ->get()
            ->keyBy('student_id');


        /*
        =====================================
        VIEW
        =====================================
        */

        $view = $courseType == 'Annual Based'
            ? 'backend.result.external-marks.subject-wise.annual-result'
            : 'backend.result.external-marks.subject-wise.result';

        return view(
            $view,
            compact(
                'exam',
                'course',
                'scheme',
                'schemeId',
                'annualSemesterId',
                'instituteId',
                'branchId',
                'batch',
                'batchId',
                'branch',
                'className',
                'subject',
                'students',
                'rawMarks',
                'courseType',
                'marksType'
            )
        )->render();
    }

    public function exportExternalMarks(Request $request)
    {
        $institute_id       = $request->export_institute_id;
        $exam_id            = $request->export_exam_id;
        $course_type        = $request->export_course_type;
        $course_id          = $request->export_course_id;
        $scheme_id          = $request->export_scheme_id;
        $branch_id          = $request->export_branch_id;
        $semester_id        = $request->export_semester_id;
        $annual_semester_id = $request->export_annual_semester_id;
        $batch_id           = $request->export_batch_id;
        $marks_type         = $request->export_marks_type;
        $subject_id         = $request->export_subject_id;

        /*
        |--------------------------------------------------------------------------
        | MASTER DATA
        |--------------------------------------------------------------------------
        */

        $course    = Course::find($course_id);
        $scheme    = $scheme_id ? Scheme::find($scheme_id) : null;
        $branch    = Branch::find($branch_id);
        $semester  = Semester::find($semester_id);
        $annual    = AnnualSemester::find($annual_semester_id);
        $batch     = Batch::find($batch_id);
        $subject   = Subject::find($subject_id);
        $exam      = Exam::find($exam_id);
        $institute = Institute::find($institute_id);

        /*
        |--------------------------------------------------------------------------
        | EXAMINATION FORMS
        |--------------------------------------------------------------------------
        | Fetch matching Regular Examination Forms with chosen subjects.
        |--------------------------------------------------------------------------
        */

        $examForms = ExaminationForm::where('exam_id', $exam_id)
            ->where('exam_type', 'Regular')
            ->where('course_id', $course_id)
            ->where('branch_id', $branch_id)
            ->where('batch_id', $batch_id)
            ->when($scheme_id, function ($q) use ($scheme_id) {
                $q->where('scheme_id', $scheme_id);
            })
            ->when($institute_id, function ($q) use ($institute_id) {
                $q->where('institute_id', $institute_id);
            })
            ->when($course_type == 'Semester Based', function ($q) use ($semester_id) {
                $q->where('semester_id', $semester_id);
            })
            ->with('choosedSubjects')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | FILTER STUDENTS BY CHOSEN SUBJECT
        |--------------------------------------------------------------------------
        | Student will be eligible only if the exported subject exists
        | in that student's Examination Form chosen subjects.
        |--------------------------------------------------------------------------
        */

        $eligibleStudentIds = [];

        foreach ($examForms as $form) {

            $hasSubject = $form->choosedSubjects
                ->contains(function ($chosenSubject) use ($subject_id) {
                    return (int) $chosenSubject->subject_id === (int) $subject_id;
                });

            if ($hasSubject) {
                $eligibleStudentIds[] = $form->student_id;
            }
        }

        $eligibleStudentIds = array_unique($eligibleStudentIds);

        /*
        |--------------------------------------------------------------------------
        | STUDENTS
        |--------------------------------------------------------------------------
        */

        $students = Student::whereIn('id', $eligibleStudentIds)
            ->where('course_type', $course_type)
            ->where('course_id', $course_id)
            ->where('branch_id', $branch_id)
            ->where('batch_id', $batch_id)
            ->when($scheme_id, function ($q) use ($scheme_id) {
                $q->where('scheme_id', $scheme_id);
            })
            ->when($institute_id, function ($q) use ($institute_id) {
                $q->where('institute_id', $institute_id);
            })
            // ->when($course_type == 'Semester Based', function ($q) use ($semester_id) {
            //     $q->where('semester_id', $semester_id);
            // })
            // ->when($course_type == 'Annual Based', function ($q) use ($annual_semester_id) {
            //     $q->where('annual_semester_id', $annual_semester_id);
            // })
            ->orderBy('student_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | DEBUG
        |--------------------------------------------------------------------------
        */

        /*
        dd([
            'total_exam_forms' => $examForms->count(),
            'eligible_student_ids' => count($eligibleStudentIds),
            'total_students' => $students->count(),
            'student_ids' => $students->pluck('id'),
        ]);
        */

        /*
        |--------------------------------------------------------------------------
        | CSV FILE
        |--------------------------------------------------------------------------
        */

        $subjectCode = $subject->subject_code ?? 'subject';

        $fileName = 'external_marks_' . $subjectCode . '_' . time() . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate',
            'Expires'             => '0',
        ];

        $callback = function () use (
            $students,
            $course,
            $scheme,
            $branch,
            $semester,
            $annual,
            $batch,
            $subject,
            $exam,
            $marks_type,
            $institute
        ) {

            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'sr_no',
                'university_roll_no',
                'student_name',
                'exam_name',
                'course_type',
                'course_name',
                'scheme_name',
                'branch_name',
                'semester_name',
                'annual_term',
                'batch_name',
                'institute_name',
                'marks_type',
                'subject_code',
                'subject_name',
                'max_marks',
                'obtained_marks',
                'exam_status'
            ]);

            foreach ($students as $key => $student) {

                fputcsv($file, [
                    $key + 1,
                    $student->university_roll_no,
                    $student->student_name,
                    $exam->name ?? '',
                    $student->course_type,
                    $course->name ?? '',
                    $scheme->name ?? '',
                    $branch->name ?? '',
                    $semester->name ?? '',
                    $annual->name ?? '',
                    $batch->name ?? '',
                    $institute->name ?? '',
                    $marks_type,
                    $subject->subject_code ?? '',
                    $subject->name ?? '',
                    '',
                    '',
                    'Present'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function importExternalMarks(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        try {

            $path = $request->file('import_file')->getRealPath();
            $rawData = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if (!$rawData || count($rawData) <= 1) {
                return back()->with('error', 'CSV file is empty or invalid.');
            }

            $data = array_map('str_getcsv', $rawData);

            // Header Normalize
            $header = array_map(fn($h) => strtolower(trim($h)), array_shift($data));

            $insertCount = 0;
            $skipCount   = 0;

            DB::beginTransaction();

            foreach ($data as $row) {

                if (count(array_filter($row)) === 0) {
                    continue;
                }

                $row = array_combine($header, $row);

                if (!$row) {
                    continue;
                }

                // ============================
                // 1️⃣ Basic Master Lookups
                // ============================

                $course = Course::where('name', trim($row['course_name']))->first();

                $branch = $course
                    ? Branch::where('course_id', $course->id)
                        ->where('name', trim($row['branch_name']))
                        ->first()
                    : null;

                $semester = Semester::where('name', trim($row['semester_name']))->first();

                $annual = AnnualSemester::where('name', trim($row['annual_term']))->first();

                $batch = Batch::where('name', trim($row['batch_name']))->first();

                $institute = Institute::where('name', trim($row['institute_name']))->first();

                // ============================
                // 2️⃣ Scheme (Optional)
                // ============================

                $scheme = null;

                if (!empty(trim($row['scheme_name']))) {
                    $scheme = Scheme::where('name', trim($row['scheme_name']))->first();
                }

                // ============================
                // 3️⃣ Student Lookup
                // ============================

                $studentQuery = Student::where('university_roll_no', trim($row['university_roll_no']))
                    ->where('course_type', trim($row['course_type']))
                    ->where('course_id', $course?->id)
                    ->where('branch_id', $branch?->id)
                    ->where('batch_id', $batch?->id)
                    ->where('institute_id', $institute?->id);

                // Semester condition intentionally removed

                if ($scheme) {
                    $studentQuery->where('scheme_id', $scheme->id);
                }

                $student = $studentQuery->first();

                if (!$student) {
                    $skipCount++;
                    continue;
                }

                // ============================
                // 4️⃣ Subject Lookup
                // ============================

                $subjectQuery = Subject::where('subject_code', trim($row['subject_code']))
                    ->where('name', trim($row['subject_name']))
                    ->where('course_type', trim($row['course_type']))
                    ->where('course_id', $course?->id)
                    ->where('branch_id', $branch?->id);

                if (trim($row['course_type']) === 'Semester Based') {

                    $subjectQuery->where('semester_id', $semester?->id)
                        ->whereNull('annual_semester_id');

                } else {

                    $subjectQuery->where('annual_semester_id', $annual?->id)
                        ->whereNull('semester_id');
                }

                if ($scheme) {
                    $subjectQuery->where('scheme_id', $scheme->id);
                }

                $subject = $subjectQuery->first();

                if (!$subject) {
                    $skipCount++;
                    continue;
                }

                // ============================
                // 5️⃣ Exam Lookup (External)
                // ============================

                $exam = Exam::where('name', trim($row['exam_name']))
                    ->first();

                if (!$exam) {
                    $skipCount++;
                    continue;
                }

                $examId = $exam->id;

                // ======= PART-2 =======
                            // ============================
                // 6️⃣ Duplicate Check
                // ============================

                $courseType = strtolower(trim($row['course_type']));

                $existsQuery = EndSemMark::where('student_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->where('exam_id', $examId)
                    ->where('course_type', $courseType)
                    ->where('course_id', $course?->id)
                    ->where('branch_id', $branch?->id)
                    ->where('batch_id', $batch?->id)
                    ->where('institute_id', $institute?->id)
                    ->where('marks_type', trim($row['marks_type']));

                if ($scheme) {
                    $existsQuery->where('scheme_id', $scheme->id);
                }

                if ($courseType === 'semester based') {

                    $existsQuery->where('semester_id', $semester?->id)
                        ->whereNull('annual_semester_id');

                } else {

                    $existsQuery->where('annual_semester_id', $annual?->id)
                        ->whereNull('semester_id');
                }

                if ($existsQuery->exists()) {
                    $skipCount++;
                    continue;
                }

                // ============================
                // 7️⃣ exam_status Handling
                // ============================

                $examStatus = strtolower(trim($row['exam_status']));

                $obtainedMarks = ($examStatus === 'absent')
                    ? 0
                    : (float) $row['obtained_marks'];

                // ============================
                // 8️⃣ Insert Marks
                // ============================

                $courseType = ucwords(trim($row['course_type']));

                EndSemMark::create([

                    'exam_id'              => $examId,
                    'student_id'           => $student->id,
                    'subject_id'           => $subject->id,

                    'institute_id'         => $institute?->id,
                    'course_type'          => $courseType,
                    'course_id'            => $course?->id,
                    'branch_id'            => $branch?->id,
                    'batch_id'             => $batch?->id,

                    'semester_id'          => $semester?->id ?? null,
                    'annual_semester_id'   => $annual?->id ?? null,
                    'scheme_id'            => $scheme?->id,

                    'marks_type'           => trim($row['marks_type']),
                    'max_marks'            => (float) $row['max_marks'],
                    'obtained_marks'       => $obtainedMarks,

                    'grace_marks'          => null,
                    'grade'                => null,

                    'exam_status'          => $examStatus,
                    'is_locked'            => 0,
                ]);

                $insertCount++;
            }

            DB::commit();

            return back()->with(
                'success',
                "Inserted: {$insertCount} | Skipped (Already/Invalid): {$skipCount}"
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(
                'error',
                'Import failed: ' . $e->getMessage()
            );
        }
    }

    public function saveAcademicAdjustment(Request $request)
    {
        try {

            /*
            =========================================
            CHANGED : DYNAMIC LIMITS
            =========================================
            */

            // Semester Based :
            // Per Subject = 2
            // Overall      = 4

            // Annual Based :
            // Per Subject = 3
            // Overall      = 5

            $perSubjectLimit = $request->course_type == 'Semester Based' ? 2 : 3;

            $overallLimit = $request->course_type == 'Semester Based' ? 4 : 5;

            /*
            =========================================
            VALIDATION
            =========================================
            */
            $request->validate([

                'exam_id'            => 'required',
                'student_id'         => 'required',
                'subject_id'         => 'required',

                'course_type'        => 'required',

                'course_id'          => 'required',
                'branch_id'          => 'required',
                'batch_id'           => 'required',

                /*
                =========================================
                CHANGED : DYNAMIC MAX VALIDATION
                =========================================
                */
                'adjustment_marks'   => 'required|numeric|min:0|max:'.$perSubjectLimit,

                'adjustment_type'    => 'required',
                'adjustment_scope'   => 'required',
            ]);

            /*
            =========================================
            CHANGED : PER SUBJECT VALIDATION
            =========================================
            */
            if($request->adjustment_marks > $perSubjectLimit){

                return response()->json([

                    'status'  => false,

                    'message' =>
                        $request->course_type == 'Semester Based'
                        ? 'Maximum 2 grace marks allowed per subject in Semester Based course'
                        : 'Maximum 3 grace marks allowed per subject in Annual Based course'

                ], 422);
            }

            /*
            =========================================
            CURRENT TOTAL USED GRACE
            =========================================
            */
            $currentGraceQuery = AcademicAdjustment::where('exam_id', $request->exam_id)

                ->where('student_id', $request->student_id)

                ->where('course_type', $request->course_type)

                ->where('course_id', $request->course_id)
                ->where('branch_id', $request->branch_id)
                ->where('batch_id', $request->batch_id)

                ->where('adjustment_scope', $request->adjustment_scope);

            /*
            =========================================
            SEMESTER / ANNUAL FILTER
            =========================================
            */
            if($request->course_type == 'Semester Based'){

                $currentGraceQuery
                    ->where('semester_id', $request->semester_id)
                    ->whereNull('annual_semester_id');

            }else{

                $currentGraceQuery
                    ->where('annual_semester_id', $request->annual_semester_id)
                    ->whereNull('semester_id');
            }

            /*
            =========================================
            EXCLUDE CURRENT RECORD
            =========================================
            */
            $currentGraceQuery->where(function ($q) use ($request) {

                $q->where('subject_id', '!=', $request->subject_id);

                if($request->course_type == 'Annual Based'){

                    $q->orWhere(function ($subQ) use ($request) {

                        $subQ->where('subject_id', $request->subject_id)
                            ->where('marks_type', '!=', $request->marks_type);
                    });
                }
            });

            $alreadyUsedGrace = $currentGraceQuery->sum('adjustment_marks');

            /*
            =========================================
            NEW TOTAL
            =========================================
            */
            $newTotalGrace =
                $alreadyUsedGrace + $request->adjustment_marks;

            /*
            =========================================
            CHANGED : OVERALL LIMIT VALIDATION
            =========================================
            */
            if($newTotalGrace > $overallLimit){

                return response()->json([

                    'status'  => false,

                    'message' =>
                        $request->course_type == 'Semester Based'
                        ? 'Maximum overall 4 grace marks allowed in Semester Based course'
                        : 'Maximum overall 5 grace marks allowed in Annual Based course'

                ], 422);
            }

            /*
            =========================================
            CHECK EXISTING
            =========================================
            */
            $adjustment = AcademicAdjustment::where('exam_id', $request->exam_id)

                ->where('student_id', $request->student_id)
                ->where('subject_id', $request->subject_id)

                ->where('adjustment_scope', $request->adjustment_scope)

                ->when($request->course_type == 'Semester Based', function ($q) use ($request) {

                    $q->where('semester_id', $request->semester_id)
                    ->whereNull('annual_semester_id');
                })

                ->when($request->course_type == 'Annual Based', function ($q) use ($request) {

                    $q->where('annual_semester_id', $request->annual_semester_id)
                    ->whereNull('semester_id')
                    ->where('marks_type', $request->marks_type);
                })

                ->first();

            /*
            =========================================
            UPDATE
            =========================================
            */
            if ($adjustment) {

                $adjustment->update([

                    'adjustment_marks' => $request->adjustment_marks,
                    'adjustment_type'  => $request->adjustment_type,
                    'marks_type'       => $request->marks_type,
                ]);

            } else {

                /*
                =========================================
                CREATE
                =========================================
                */
                AcademicAdjustment::create([

                    'exam_id'            => $request->exam_id,

                    'student_id'         => $request->student_id,
                    'subject_id'         => $request->subject_id,

                    'institute_id'       => $request->institute_id,

                    'course_type'        => $request->course_type,

                    'course_id'          => $request->course_id,
                    'branch_id'          => $request->branch_id,
                    'batch_id'           => $request->batch_id,

                    'semester_id'        => $request->semester_id,
                    'annual_semester_id' => $request->annual_semester_id,

                    'scheme_id'          => $request->scheme_id,

                    'adjustment_marks'   => $request->adjustment_marks,
                    'adjustment_type'    => $request->adjustment_type,
                    'adjustment_scope'   => $request->adjustment_scope,

                    'marks_type'         => $request->marks_type,

                    'is_approved'        => true,
                ]);
            }

            /*
            =========================================
            RESPONSE
            =========================================
            */
            return response()->json([

                'status'  => true,
                'message' => 'Adjustment saved successfully'
            ]);

        } catch (\Throwable $th) {

            return response()->json([

                'status'  => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    public function saveAnnualModeration(Request $request)
    {
        DB::beginTransaction();

        try {

            /*
            =========================================
            ANNUAL BASED LIMITS
            =========================================

            Per Subject = 3
            Overall Student = 5
            */

            $perSubjectLimit = 3;

            $overallLimit = 5;


            /*
            =========================================
            VALIDATION
            =========================================
            */

            $request->validate([

                'exam_id'             => 'required',

                'course_type'         => 'required|in:Annual Based',

                'course_id'           => 'required',

                'branch_id'           => 'required',

                'batch_id'            => 'required',

                'annual_semester_id'  => 'required',

                'subject_id'          => 'required',

                'moderation_students' => 'required',

                'moderation_action'   => 'required|in:apply,revert',

            ]);


            /*
            =========================================
            APPLY VALIDATION
            =========================================
            */

            if ($request->moderation_action == 'apply') {

                $request->validate([

                    'moderation_marks' =>
                        'required|numeric|min:0|max:3',

                ]);


                /*
                =========================================
                PER SUBJECT LIMIT
                =========================================
                */

                if ($request->moderation_marks > $perSubjectLimit) {

                    DB::rollBack();

                    return response()->json([

                        'status' => false,

                        'message' =>
                            'Maximum 3 moderation marks allowed per subject in Annual Based course'

                    ], 422);
                }
            }


            /*
            =========================================
            MODERATION STUDENTS JSON
            =========================================
            */

            $moderationStudents = json_decode(
                $request->moderation_students,
                true
            );


            if (!is_array($moderationStudents)) {

                DB::rollBack();

                return response()->json([

                    'status'  => false,

                    'message' => 'Invalid moderation students data'

                ], 422);
            }


            /*
            =========================================
            STUDENT IDS
            =========================================
            */

            $studentIds = collect($moderationStudents)

                ->filter(function ($studentId) {

                    return !empty($studentId);

                })

                ->map(function ($studentId) {

                    return (int) $studentId;

                })

                ->unique()

                ->values();


            if ($studentIds->isEmpty()) {

                DB::rollBack();

                return response()->json([

                    'status'  => false,

                    'message' => 'No students found for moderation'

                ], 422);
            }


            /*
            =========================================
            EXCLUDED STUDENT ROLL NUMBERS
            =========================================
            */

            $excludedRollNumbers = [];

            if (!empty($request->excluded_students)) {

                $excludedRollNumbers = collect(
                    explode(',', $request->excluded_students)
                )

                ->map(function ($rollNumber) {

                    return trim($rollNumber);

                })

                ->filter()

                ->unique()

                ->values()

                ->toArray();
            }


            /*
            =========================================
            FIND EXCLUDED STUDENT IDS
            =========================================
            */

            $excludedStudentIds = collect();

            if (!empty($excludedRollNumbers)) {

                $excludedStudentIds = Student::whereIn(
                    'university_roll_no',
                    $excludedRollNumbers
                )->pluck('id');
            }


            /*
            =========================================
            REMOVE EXCLUDED STUDENTS
            =========================================
            */

            $studentIds = $studentIds

                ->diff($excludedStudentIds)

                ->values();


            /*
            =========================================
            IF ALL STUDENTS EXCLUDED
            =========================================
            */

            if ($studentIds->isEmpty()) {

                DB::rollBack();

                return response()->json([

                    'status'  => false,

                    'message' => 'All students are excluded from moderation'

                ], 422);
            }


            /*
            =========================================================
            REVERT
            =========================================================
            */

            if ($request->moderation_action == 'revert') {

                $revertedStudents = [];


                /*
                =========================================
                STUDENT-WISE REVERT
                =========================================
                */

                foreach ($studentIds as $studentId) {

                    /*
                    =========================================
                    FIND CURRENT SUBJECT MODERATION
                    =========================================
                    */

                    $adjustmentQuery =
                        AcademicAdjustment::where(
                            'exam_id',
                            $request->exam_id
                        )

                        ->where(
                            'student_id',
                            $studentId
                        )

                        ->where(
                            'subject_id',
                            $request->subject_id
                        )

                        ->where(
                            'course_type',
                            'Annual Based'
                        )

                        ->where(
                            'course_id',
                            $request->course_id
                        )

                        ->where(
                            'branch_id',
                            $request->branch_id
                        )

                        ->where(
                            'batch_id',
                            $request->batch_id
                        )

                        ->where(
                            'annual_semester_id',
                            $request->annual_semester_id
                        )

                        ->whereNull('semester_id')

                        ->where(
                            'marks_type',
                            $request->marks_type
                        )

                        /*
                        =========================================
                        ONLY MODERATION RECORD
                        =========================================
                        */

                        ->where(
                            'adjustment_scope',
                            'external'
                        )

                        ->where(
                            'adjustment_type',
                            'moderation'
                        );


                    /*
                    =========================================
                    DELETE
                    =========================================
                    */

                    $deleted =
                        $adjustmentQuery->delete();


                    /*
                    =========================================
                    TRACK REVERTED
                    =========================================
                    */

                    if ($deleted > 0) {

                        $revertedStudents[] = $studentId;
                    }
                }


                /*
                =========================================
                COMMIT REVERT
                =========================================
                */

                DB::commit();


                /*
                =========================================
                RESPONSE
                =========================================
                */

                return response()->json([

                    'status' => true,

                    'message' =>
                        count($revertedStudents)
                        . ' student(s) moderation reverted successfully',

                    'reverted_count' =>
                        count($revertedStudents),

                ]);
            }


            /*
            =========================================================
            APPLY
            =========================================================
            */

            $appliedStudents = [];

            $skippedStudents = [];


            /*
            =========================================
            APPLY STUDENT-WISE
            =========================================
            */

            foreach ($studentIds as $studentId) {


                /*
                =========================================
                CURRENT TOTAL MODERATION
                =========================================

                Student ke Annual Semester ke
                saare subjects ka moderation total.

                Current subject + current marks_type
                ka existing record exclude hoga.
                */

                $currentModerationQuery =
                    AcademicAdjustment::where(
                        'exam_id',
                        $request->exam_id
                    )

                    ->where(
                        'student_id',
                        $studentId
                    )

                    ->where(
                        'course_type',
                        'Annual Based'
                    )

                    ->where(
                        'course_id',
                        $request->course_id
                    )

                    ->where(
                        'branch_id',
                        $request->branch_id
                    )

                    ->where(
                        'batch_id',
                        $request->batch_id
                    )

                    ->where(
                        'annual_semester_id',
                        $request->annual_semester_id
                    )

                    ->whereNull('semester_id')

                    /*
                    =========================================
                    ONLY MODERATION
                    =========================================
                    */

                    ->where(
                        'adjustment_scope',
                        'external'
                    )

                    ->where(
                        'adjustment_type',
                        'moderation'
                    );


                /*
                =========================================
                EXCLUDE CURRENT SUBJECT + MARKS TYPE
                =========================================
                */

                $currentModerationQuery->where(function ($q) use ($request) {

                    $q->where(
                        'subject_id',
                        '!=',
                        $request->subject_id
                    );

                    $q->orWhere(function ($subQ) use ($request) {

                        $subQ->where(
                            'subject_id',
                            $request->subject_id
                        )

                        ->where(
                            'marks_type',
                            '!=',
                            $request->marks_type
                        );

                    });

                });


                /*
                =========================================
                ALREADY USED MODERATION
                =========================================
                */

                $alreadyUsedModeration =
                    $currentModerationQuery->sum(
                        'adjustment_marks'
                    );


                /*
                =========================================
                NEW TOTAL
                =========================================
                */

                $newTotalModeration =
                    $alreadyUsedModeration
                    + $request->moderation_marks;


                /*
                =========================================
                OVERALL 5 MARKS VALIDATION
                =========================================
                */

                if ($newTotalModeration > $overallLimit) {

                    $student =
                        Student::find($studentId);


                    $skippedStudents[] = [

                        'student_id' =>
                            $studentId,

                        'university_roll_no' =>
                            $student->university_roll_no ?? '',

                        'reason' =>
                            'Maximum overall 5 moderation marks allowed per student'

                    ];

                    continue;
                }


                /*
                =========================================
                CHECK EXISTING MODERATION
                =========================================
                */

                $adjustment =
                    AcademicAdjustment::where(
                        'exam_id',
                        $request->exam_id
                    )

                    ->where(
                        'student_id',
                        $studentId
                    )

                    ->where(
                        'subject_id',
                        $request->subject_id
                    )

                    ->where(
                        'course_type',
                        'Annual Based'
                    )

                    ->where(
                        'course_id',
                        $request->course_id
                    )

                    ->where(
                        'branch_id',
                        $request->branch_id
                    )

                    ->where(
                        'batch_id',
                        $request->batch_id
                    )

                    ->where(
                        'annual_semester_id',
                        $request->annual_semester_id
                    )

                    ->whereNull('semester_id')

                    ->where(
                        'adjustment_scope',
                        'external'
                    )

                    ->where(
                        'adjustment_type',
                        'moderation'
                    )

                    ->where(
                        'marks_type',
                        $request->marks_type
                    )

                    ->first();


                /*
                =========================================
                UPDATE
                =========================================
                */

                if ($adjustment) {

                    $adjustment->update([

                        'adjustment_marks' =>
                            $request->moderation_marks,

                        'adjustment_type' =>
                            'moderation',

                        'marks_type' =>
                            $request->marks_type,

                        'is_approved' =>
                            true,

                    ]);

                }


                /*
                =========================================
                CREATE
                =========================================
                */

                else {

                    AcademicAdjustment::create([

                        'exam_id' =>
                            $request->exam_id,

                        'student_id' =>
                            $studentId,

                        'subject_id' =>
                            $request->subject_id,

                        'institute_id' =>
                            $request->institute_id,

                        'course_type' =>
                            'Annual Based',

                        'course_id' =>
                            $request->course_id,

                        'branch_id' =>
                            $request->branch_id,

                        'batch_id' =>
                            $request->batch_id,

                        'semester_id' =>
                            null,

                        'annual_semester_id' =>
                            $request->annual_semester_id,

                        'scheme_id' =>
                            $request->scheme_id,

                        'adjustment_marks' =>
                            $request->moderation_marks,

                        'adjustment_type' =>
                            'moderation',

                        'adjustment_scope' =>
                            'external',

                        'marks_type' =>
                            $request->marks_type,

                        'is_approved' =>
                            true,

                    ]);
                }


                /*
                =========================================
                APPLIED
                =========================================
                */

                $appliedStudents[] = $studentId;
            }


            /*
            =========================================
            COMMIT
            =========================================
            */

            DB::commit();


            /*
            =========================================
            RESPONSE MESSAGE
            =========================================
            */

            $message =
                count($appliedStudents)
                . ' student(s) moderation applied successfully';


            if (count($skippedStudents) > 0) {

                $message .=
                    ' and '
                    . count($skippedStudents)
                    . ' student(s) skipped because overall 5 moderation marks limit was exceeded.';
            }


            return response()->json([

                'status' =>
                    true,

                'message' =>
                    $message,

                'applied_count' =>
                    count($appliedStudents),

                'skipped_count' =>
                    count($skippedStudents),

                'skipped_students' =>
                    $skippedStudents,

            ]);


        } catch (\Throwable $th) {

            DB::rollBack();

            return response()->json([

                'status' =>
                    false,

                'message' =>
                    $th->getMessage()

            ], 500);
        }
    }

    public function fetchExternalMarks(Request $request)
    {
        $examId            = $request->exam_id;
        $courseType        = $request->course_type;
        $courseId          = $request->course_id;
        $branchId          = $request->branch_id;
        $batchId           = $request->batch_id;
        $schemeId          = $request->scheme_id;
        $semesterId        = $request->semester_id;
        $annualSemesterId  = $request->annual_semester_id;
        $instituteId       = $request->institute_id;

        /*
        |--------------------------------------------------------------------------
        | EXAM / COURSE / BRANCH / SCHEME / BATCH
        |--------------------------------------------------------------------------
        */

        $exam    = Exam::find($examId);
        $course  = Course::find($courseId);
        $branch  = Branch::find($branchId);
        $scheme  = Scheme::find($schemeId);
        $batch   = Batch::find($batchId);

        /*
        |--------------------------------------------------------------------------
        | CLASS NAME
        |--------------------------------------------------------------------------
        */

        if ($courseType == 'Semester Based') {

            $semester  = Semester::find($semesterId);
            $className = "SEM - ".$semester->name;

        } else {

            $semester  = AnnualSemester::find($annualSemesterId);
            $className = $semester->name;
        }

        /*
        |--------------------------------------------------------------------------
        | SUBJECTS
        |--------------------------------------------------------------------------
        */

        $subjects = Subject::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })
            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                    ->whereNull('annual_semester_id');
            })
            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                $q->where('annual_semester_id', $annualSemesterId)
                    ->whereNull('semester_id');
            })
            ->orderByRaw('CASE WHEN is_nc = 1 THEN 1 ELSE 0 END')
            ->orderBy('type')
            ->orderBy('subject_code')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | EXAMINATION FORMS
        |--------------------------------------------------------------------------
        */

        $examForms = ExaminationForm::where('exam_id', $examId)
            ->where('exam_type', 'Regular')
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })
            ->when($instituteId, function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            })
            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            })
            ->with('choosedSubjects')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | UNIQUE STUDENT IDS FROM EXAMINATION FORMS
        |--------------------------------------------------------------------------
        */

        $examFormStudentIds = $examForms
            ->pluck('student_id')
            ->unique()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | STUDENTS
        |--------------------------------------------------------------------------
        | Only students who have submitted the matching Regular Examination Form.
        |--------------------------------------------------------------------------
        */

        $students = Student::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->whereIn('id', $examFormStudentIds)
            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })
            ->when($instituteId, function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            })
            ->orderBy('institute_id')
            ->orderBy('university_roll_no')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | EXAMINATION FORM + CHOSEN SUBJECT MAPPING
        |--------------------------------------------------------------------------
        */

        $examFormSubjects = [];

        foreach ($examForms as $form) {

            foreach ($form->choosedSubjects as $chosenSubject) {

                $examFormSubjects[$form->student_id][] = $chosenSubject->subject_id;
            }
        }

        foreach ($examFormSubjects as $studentId => $subjectIds) {

            $examFormSubjects[$studentId] = array_unique($subjectIds);
        }

        /*
        |--------------------------------------------------------------------------
        | FETCH END SEMESTER MARKS
        |--------------------------------------------------------------------------
        */

        $rawMarks = EndSemMark::where('exam_id', $examId)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->where('is_locked', 1)
            ->when($schemeId, function ($q) use ($schemeId) {
                $q->where('scheme_id', $schemeId);
            })
            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                    ->whereNull('annual_semester_id');
            })
            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                $q->where('annual_semester_id', $annualSemesterId)
                    ->whereNull('semester_id');
            })
            ->whereIn('student_id', $students->pluck('id'))
            ->whereIn('subject_id', $subjects->pluck('id'))
            ->when(
                $courseType == 'Semester Based' &&
                $course->course != 'Diploma',
                function ($q) {
                    $q->where('marks_type', 'external');
                }
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | FORMAT MARKS
        |--------------------------------------------------------------------------
        */

        $marks = [];

        foreach ($rawMarks as $mark) {

            if (
                $courseType == 'Semester Based' &&
                $course->course != 'Diploma'
            ) {

                $marks[$mark->student_id][$mark->subject_id] = [
                    'obtained_marks' => $mark->obtained_marks,
                    'grace_marks'    => $mark->grace_marks,
                    'exam_status'    => $mark->exam_status,
                    'is_locked'      => $mark->is_locked,
                    'grade'          => $mark->grade,
                ];

            } else {

                $marks[$mark->student_id][$mark->subject_id][$mark->marks_type] = [
                    'obtained_marks' => $mark->obtained_marks,
                    'grace_marks'    => $mark->grace_marks,
                    'exam_status'    => $mark->exam_status,
                    'is_locked'      => $mark->is_locked,
                    'grade'          => $mark->grade,
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

        $view = $courseType == 'Annual Based'
            ? 'backend.result.external-marks.annual-result'
            : 'backend.result.external-marks.result';

        return view(
            $view,
            compact(
                'exam',
                'courseType',
                'course',
                'scheme',
                'batch',
                'branch',
                'className',
                'subjects',
                'students',
                'marks',
                'examFormSubjects'
            )
        )->render();
    }

    public function saveExternalMarks(Request $request)
    {
        // dd($request->all());
        $instituteId = $request->institute_id;

        if (empty($instituteId)) {
            $student = Student::find($request->student_id);
            $instituteId = $student->institute_id ?? null;
        }

        $isAnnualBased = $request->course_type == "Annual Based";

        /*
        |--------------------------------------------------------------------------
        | SEMESTER BASED
        | marks_type = external (FIXED)
        |--------------------------------------------------------------------------
        */
        if (!$isAnnualBased) {

            $subject = Subject::find($request->subject_id);
            $isNc    = $subject->is_nc ?? 0;

            EndSemMark::updateOrCreate(

                [
                    'exam_id'    => $request->exam_id,
                    'student_id' => $request->student_id,
                    'subject_id' => $request->subject_id,
                    'marks_type' => 'external', // âœ… FIXED TYPE
                ],

                [
                    'course_type'        => $request->course_type,
                    'course_id'          => $request->course_id,
                    'branch_id'          => $request->branch_id,
                    'batch_id'           => $request->batch_id,
                    'semester_id'        => $request->semester_id,
                    'annual_semester_id' => $request->annual_semester_id,
                    'scheme_id'          => $request->scheme_id,
                    'institute_id'       => $instituteId,

                    'marks_type'     => 'external', // âœ… FORCE SAME
                    'max_marks'      => $request->max_marks,
                    // 'obtained_marks' => $request->marks ?? 0,
                    // 'grade'          => null,

                    'obtained_marks' => $isNc ? 0 : ($request->marks ?? 0),
                    'grade'          => $isNc ? $request->marks : null,

                    'exam_status' => $request->status,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ANNUAL BASED
        | marks_type = theory_paper1 / practical_paper1 etc.
        |--------------------------------------------------------------------------
        */
        else {

            $subject = Subject::find($request->subject_id);
            $isNc    = $subject->is_nc ?? 0;

            $marksType = $isNc ? 'external' : $request->marks_type;

            $data = [

                'course_type'        => $request->course_type,
                'course_id'          => $request->course_id,
                'branch_id'          => $request->branch_id,
                'batch_id'           => $request->batch_id,
                'semester_id'        => $request->semester_id,
                'annual_semester_id' => $request->annual_semester_id,
                'scheme_id'          => $request->scheme_id,
                'institute_id'       => $instituteId,

                'marks_type' => $marksType,
                'max_marks'  => $request->max_marks,

                'exam_status' => $request->status,
            ];

            /*
            |--------------------------------------------------------------------------
            | NON CREDIT SUBJECT (GRADE BASED)
            |--------------------------------------------------------------------------
            */
            if ($isNc) {

                $data['grade'] = $request->marks; // Satisfactory / Unsatisfactory
                $data['obtained_marks'] = 0;

            } else {

                $data['grade'] = null;
                $data['obtained_marks'] = $request->marks ?? 0;
            }

            EndSemMark::updateOrCreate(

                [
                    'exam_id'    => $request->exam_id,
                    'student_id' => $request->student_id,
                    'subject_id' => $request->subject_id,
                    'marks_type' => $marksType, // âœ… IMPORTANT (paper wise row)
                ],

                $data
            );
        }

        return response()->json([
            'status' => 'success'
        ]);
    }


    public function updateExternalMarksLock(Request $request)
    {
        $examId            = $request->exam_id;
        $courseType        = $request->course_type;
        $courseId          = $request->course_id;
        $branchId          = $request->branch_id;
        $batchId           = $request->batch_id;
        $semesterId        = $request->semester_id;
        $annualSemesterId  = $request->annual_semester_id;
        $schemeId          = $request->scheme_id;
        $instituteId       = $request->institute_id;

        $records = $request->records ?? [];

        if(empty($records)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No records selected'
            ]);
        }

        foreach ($records as $record) {

            EndSemMark::where('exam_id', $examId)
                ->where('student_id', $record['student_id'])
                ->where('subject_id', $record['subject_id'])
                ->where('course_type', $courseType)
                ->where('course_id', $courseId)
                ->where('branch_id', $branchId)
                ->where('batch_id', $batchId)
                ->when($schemeId, function ($q) use ($schemeId) {
                    $q->where('scheme_id', $schemeId);
                })
                ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                    $q->where('semester_id', $semesterId)
                    ->whereNull('annual_semester_id');
                })
                ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                    $q->where('annual_semester_id', $annualSemesterId)
                    ->whereNull('semester_id');
                })
                ->when($instituteId, function ($q) use ($instituteId) {
                    $q->where('institute_id', $instituteId);
                })
                ->update([
                    'is_locked' => $request->status,
                    'university_forword' => 1,
                ]);
        }

        return response()->json([
            'status' => 'success'
        ]);
    }


    public function resultPrepare()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.result-prepare.index', compact('courses', 'exams'));
    }

    // public function fetchEndSemResult(Request $request)
    // {
    //     dd($request->all());
    // }


    public function fetchEndSemResult(Request $request)
    {
        $examId       = (int) $request->exam_id;
        $courseType   = $request->course_type;
        $courseId     = (int) $request->course_id;
        $schemeId     = $request->scheme_id ? (int) $request->scheme_id : null;
        $branchId     = (int) $request->branch_id;
        $semesterId   = (int) $request->semester_id;
        $batchId     = (int) $request->batch_id;
        $instituteId = $request->institute_id !== null
            && $request->institute_id !== ''
            ? (int) $request->institute_id
            : null;

        if ($courseType !== 'Semester Based') {
            return response('Only Semester Based result is supported.', 422);
        }

        /*
        |--------------------------------------------------------------------------
        | BASIC DATA
        |--------------------------------------------------------------------------
        */

        $exam   = \App\Models\Exam::findOrFail($examId);
        $course = \App\Models\Course::findOrFail($courseId);
        $branch = \App\Models\Branch::findOrFail($branchId);
        $scheme = \App\Models\Scheme::findOrFail($schemeId);
        $batch  = \App\Models\Batch::findOrFail($batchId);
        $semester = \App\Models\Semester::findOrFail($semesterId);

        $className = ($semester->name ?? '') . ' Semester';

        /*
        |--------------------------------------------------------------------------
        | PASS PERCENTAGE
        |--------------------------------------------------------------------------
        */

        $passPercentage = \App\Models\PassPercentage::where('course_id', $courseId)
            ->where('scheme_id', $schemeId)
            ->first();

        $overallPassPercent = (float) ($passPercentage->overall_pass_percentage ?? 40);
        $percentageType = $passPercentage->percentage_type ?? 'Separate';

        /*
        |--------------------------------------------------------------------------
        | SUBJECTS
        |--------------------------------------------------------------------------
        */

        $subjects = \App\Models\Subject::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('scheme_id', $schemeId)
            ->where('semester_id', $semesterId)
            ->whereNull('annual_semester_id')
            ->orderBy('orders')
            ->orderBy('id')
            ->get();

        $subjectIds = $subjects->pluck('id')->toArray();

        /*
        |--------------------------------------------------------------------------
        | GRADES
        |--------------------------------------------------------------------------
        */

        $grades = \App\Models\MarksGrade::where('course_id', $courseId)
            ->where('scheme_id', $schemeId)
            ->orderBy('percent_from')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | STUDENTS - REGULAR EXAM FORMS
        |--------------------------------------------------------------------------
        */

        $forms = \App\Models\ExaminationForm::with([
            'student',
            'choosedSubjects',
        ])
            ->where('exam_id', $examId)
            ->where('exam_type', 'Regular')
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('scheme_id', $schemeId)
            ->where('semester_id', $semesterId)
            ->where('batch_id', $batchId)
            ->where('payment_status', 1)
            ->when($instituteId !== null, function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            })
            ->get()
            ->filter(function ($form) use ($subjectIds) {
                return $form->student
                    && $form->choosedSubjects->pluck('subject_id')
                        ->intersect($subjectIds)
                        ->isNotEmpty();
            })
            ->unique('student_id')
            ->values();

        $students = $forms->pluck('student')
            ->filter()
            ->unique('id')
            ->sortBy('university_roll_no')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | INSTITUTES
        |--------------------------------------------------------------------------
        */

        $institutes = \App\Models\Institute::whereIn(
            'id',
            $students->pluck('institute_id')->filter()->unique()
        )->pluck('name', 'id');

        /*
        |--------------------------------------------------------------------------
        | CURRENT INTERNAL MARKS
        |--------------------------------------------------------------------------
        */

        $midSemMarks = \App\Models\MidSemMark::where('exam_id', $examId)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('scheme_id', $schemeId)
            ->where('semester_id', $semesterId)
            ->where('batch_id', $batchId)
            ->where('marks_type', 'internal')
            ->whereIn('student_id', $students->pluck('id'))
            ->whereIn('subject_id', $subjectIds)
            ->get()
            ->groupBy('student_id');

        /*
        |--------------------------------------------------------------------------
        | CURRENT EXTERNAL MARKS
        |--------------------------------------------------------------------------
        */

        $endSemMarks = \App\Models\EndSemMark::where('exam_id', $examId)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('scheme_id', $schemeId)
            ->where('semester_id', $semesterId)
            ->where('batch_id', $batchId)
            ->where('marks_type', 'external')
            ->whereIn('student_id', $students->pluck('id'))
            ->whereIn('subject_id', $subjectIds)
            ->get()
            ->groupBy('student_id');

        /*
        |--------------------------------------------------------------------------
        | PREVIOUS SEMESTERS - REGULAR + REAPPEAR
        |--------------------------------------------------------------------------
        */

        $previousGpSheets = collect();
        $previousReappearGpSheets = collect();

        $previousExamIds = getPreviousExamSessionIds($examId);

        // Reappear result can also be uploaded in the current exam session.
        $reappearExamIds = array_values(array_unique(
            array_merge($previousExamIds, [$examId])
        ));

        $previousSemesters = $semesterId > 1
            ? range(1, $semesterId - 1)
            : [];

        if (!empty($previousSemesters)) {

            $previousGpSheets = \App\Models\ResultGpSheet::where('course_type', $courseType)
                ->where('course_id', $courseId)
                ->where('branch_id', $branchId)
                ->where('scheme_id', $schemeId)
                ->where('batch_id', $batchId)
                ->whereIn('semester_id', $previousSemesters)
                ->whereIn('exam_id', $previousExamIds)
                ->when($instituteId !== null, function ($q) use ($instituteId) {
                    $q->where('institute_id', $instituteId);
                })
                ->get()
                ->groupBy('student_id');

            $previousReappearGpSheets = \App\Models\ReappearResultGpSheet::where('course_type', $courseType)
                ->where('course_id', $courseId)
                ->where('branch_id', $branchId)
                ->where('scheme_id', $schemeId)
                ->where('batch_id', $batchId)
                ->whereIn('semester_id', $previousSemesters)
                ->whereIn('exam_id', $reappearExamIds)
                ->when($instituteId !== null, function ($q) use ($instituteId) {
                    $q->where('institute_id', $instituteId);
                })
                ->get()
                ->groupBy('student_id');
        }

        /*
        |--------------------------------------------------------------------------
        | HELPER: GET GRADE BY PERCENTAGE
        |--------------------------------------------------------------------------
        */

        $getGrade = function ($percentage) use ($grades) {

            return $grades->first(function ($grade) use ($percentage) {
                return $percentage >= (float) $grade->percent_from
                    && $percentage <= (float) $grade->percent_upto;
            });
        };

        /*
        |--------------------------------------------------------------------------
        | HELPER: MARK VALUE
        |--------------------------------------------------------------------------
        */

        $markValue = function ($mark) {

            if (!$mark) {
                return null;
            }

            if (strtolower(trim($mark->exam_status ?? '')) !== 'present') {
                return 0;
            }

            return (float) ($mark->obtained_marks ?? 0)
                + (float) ($mark->grace_marks ?? 0);
        };

        /*
        |--------------------------------------------------------------------------
        | HELPER: SPECIAL STATUS
        |--------------------------------------------------------------------------
        */

        $specialStatus = function ($mark) {

            if (!$mark) {
                return '';
            }

            $status = strtoupper(trim($mark->exam_status ?? ''));

            if (in_array($status, ['AB', 'UMC', 'I/F'])) {
                return $status;
            }

            return '';
        };

        /*
        |--------------------------------------------------------------------------
        | HELPER: RETOTALING VALUE
        |--------------------------------------------------------------------------
        */

        $retotalValue = function ($normal, $retotal) {

            if ($retotal !== null && $retotal !== '' && (float) $retotal != 0) {
                return (float) $retotal;
            }

            return (float) ($normal ?? 0);
        };

        /*
        |--------------------------------------------------------------------------
        | PREPARE RESULT DATA
        |--------------------------------------------------------------------------
        */

        $marks = [];

        foreach ($students as $student) {

            $studentId = $student->id;

            $studentInternal = $midSemMarks->get($studentId, collect())
                ->keyBy('subject_id');

            $studentExternal = $endSemMarks->get($studentId, collect())
                ->keyBy('subject_id');

            $studentSubjects = [];

            $currentCreditPoint = 0;
            $currentGradePointEarn = 0;

            $failedSubjects = [];

            foreach ($subjects as $subject) {

                $internalRecord = $studentInternal->get($subject->id);
                $externalRecord = $studentExternal->get($subject->id);

                $hasInternal = !is_null($internalRecord);
                $hasExternal = !is_null($externalRecord);

                // Both missing -> leave blank, not included in credit calculation.
                if (!$hasInternal && !$hasExternal) {
                    continue;
                }

                $credits = (float) ($subject->credits ?? 0);

                $internalValue = $markValue($internalRecord);
                $externalValue = $markValue($externalRecord);

                $internalSpecial = $specialStatus($internalRecord);
                $externalSpecial = $specialStatus($externalRecord);

                $total = null;
                $percentage = 0;
                $grade = '';
                $gradePoint = 0;
                $creditScore = 0;
                $result = 'FAIL';

                /*
                |--------------------------------------------------------------------------
                | EXTERNAL SPECIAL STATUS - HIGHEST PRIORITY
                |--------------------------------------------------------------------------
                */

                if ($externalSpecial !== '') {

                    $total = $externalSpecial;
                    $grade = $externalSpecial;
                    $result = $externalSpecial;

                } elseif (!$hasInternal || !$hasExternal) {

                    // Only one mark component exists -> R.
                    $total = 0;
                    $grade = 'R';
                    $result = 'R';

                } elseif (
                    $internalSpecial !== ''
                    && $externalSpecial !== ''
                    && $internalSpecial === $externalSpecial
                ) {

                    $total = $externalSpecial;
                    $grade = $externalSpecial;
                    $result = $externalSpecial;

                } elseif ($percentageType === 'Aggregate') {

                    $total = (float) $internalValue + (float) $externalValue;

                    $maximum = (float) ($subject->internal_marks ?? 0)
                        + (float) ($subject->external_marks ?? 0);

                    $percentage = $maximum > 0
                        ? ($total * 100) / $maximum
                        : 0;

                    $matchedGrade = $getGrade($percentage);

                    if ($percentage < $overallPassPercent || !$matchedGrade) {
                        $grade = 'R';
                        $result = 'FAIL';
                    } else {
                        $grade = $matchedGrade->grade_name;
                        $gradePoint = (float) $matchedGrade->grade_point;
                        $result = 'PASS';
                    }

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | SEPARATE PASS
                    |--------------------------------------------------------------------------
                    */

                    $total = (float) $internalValue + (float) $externalValue;

                    $internalPass = (float) ($subject->internal_marks ?? 0)
                        * $overallPassPercent / 100;

                    $externalPass = (float) ($subject->external_marks ?? 0)
                        * $overallPassPercent / 100;

                    $internalPassed = $internalValue >= $internalPass;
                    $externalPassed = $externalValue >= $externalPass;

                    $maximum = (float) ($subject->internal_marks ?? 0)
                        + (float) ($subject->external_marks ?? 0);

                    $percentage = $maximum > 0
                        ? ($total * 100) / $maximum
                        : 0;

                    $matchedGrade = $getGrade($percentage);

                    if (!$externalPassed && !$internalPassed) {
                        $grade = 'I/F';
                        $result = 'FAIL';
                    } elseif (!$externalPassed && $internalPassed) {
                        $grade = 'R';
                        $result = 'FAIL';
                    } elseif ($externalPassed && !$internalPassed) {
                        $grade = 'I';
                        $result = 'FAIL';
                    } else {
                        $grade = $matchedGrade->grade_name ?? 'R';
                        $gradePoint = (float) ($matchedGrade->grade_point ?? 0);
                        $result = ($grade === 'R' || $grade === 'F')
                            ? 'FAIL'
                            : 'PASS';
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | SPECIAL INTERNAL STATUS FALLBACK
                |--------------------------------------------------------------------------
                */

                if ($externalSpecial === '' && $internalSpecial !== '' && $result !== 'PASS') {
                    $total = $internalSpecial;
                    $grade = $internalSpecial;
                    $result = $internalSpecial;
                    $gradePoint = 0;
                    $percentage = 0;
                }

                /*
                |--------------------------------------------------------------------------
                | R / F GRADE POINT
                |--------------------------------------------------------------------------
                */

                if (in_array($grade, ['R', 'F', 'AB', 'UMC', 'I/F', 'I'])) {
                    $gradePoint = 0;
                }

                if ($result !== 'PASS') {
                    $gradePoint = 0;
                }

                $creditScore = $gradePoint * $credits;

                // Any existing mark record counts credits, including failures.
                $currentCreditPoint += $credits;
                $currentGradePointEarn += $creditScore;

                if ($result !== 'PASS') {
                    $failedSubjects[] = $subject->subject_code;
                }

                $studentSubjects[$subject->id] = [
                    'internal_marks' => $internalSpecial ?: $internalValue,
                    'external_marks' => $externalSpecial ?: $externalValue,
                    'total' => $total,
                    'percentage' => $percentage,
                    'grade' => $grade,
                    'grade_point' => $gradePoint,
                    'credit_score' => $creditScore,
                    'result' => $result,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | SGPA
            |--------------------------------------------------------------------------
            */

            $sgpa = $currentCreditPoint > 0
                ? round($currentGradePointEarn / $currentCreditPoint, 2)
                : 0;

            /*
            |--------------------------------------------------------------------------
            | PREVIOUS SEMESTER CREDIT / EARN
            |--------------------------------------------------------------------------
            */

            $previousCreditPoint = 0;
            $previousGradePointEarn = 0;
            $previousResultStatus = 'N/A';

            if ($semesterId > 1) {

                $regularRows = $previousGpSheets->get($studentId, collect());
                $reappearRows = $previousReappearGpSheets->get($studentId, collect());

                // Resolve each previous semester + subject independently.
                foreach ($previousSemesters as $prevSem) {

                    $regularSemRows = $regularRows->where('semester_id', $prevSem)
                        ->keyBy('subject_id');

                    $reappearSemRows = $reappearRows->where('semester_id', $prevSem)
                        ->keyBy('subject_id');

                    $allPreviousSubjectIds = $regularSemRows->keys()
                        ->merge($reappearSemRows->keys())
                        ->unique();

                    foreach ($allPreviousSubjectIds as $prevSubjectId) {

                        // Reappear takes priority over regular result if present.
                        $row = $reappearSemRows->get($prevSubjectId)
                            ?? $regularSemRows->get($prevSubjectId);

                        if (!$row) {
                            continue;
                        }

                        $credits = $retotalValue(
                            $row->credits ?? 0,
                            $row->retotalling_credits ?? null
                        );

                        $gpEarn = $retotalValue(
                            $row->credit_score ?? 0,
                            $row->retotalling_credit_score ?? null
                        );

                        $previousCreditPoint += $credits;
                        $previousGradePointEarn += $gpEarn;
                    }
                }

                $previousResultStatus = 'PASS';

                foreach ($previousSemesters as $prevSem) {

                    $regularSemRows = $regularRows->where('semester_id', $prevSem)
                        ->keyBy('subject_id');

                    $reappearSemRows = $reappearRows->where('semester_id', $prevSem)
                        ->keyBy('subject_id');

                    $allPreviousSubjectIds = $regularSemRows->keys()
                        ->merge($reappearSemRows->keys())
                        ->unique();

                    foreach ($allPreviousSubjectIds as $prevSubjectId) {

                        $row = $reappearSemRows->get($prevSubjectId)
                            ?? $regularSemRows->get($prevSubjectId);

                        $resultValue = $row->retotalling_result
                            ?: $row->result;

                        if (
                            $resultValue !== 'PASS'
                            && !in_array($resultValue, ['AB', 'UMC', 'I/F'])
                        ) {
                            $previousResultStatus = 'FAIL';
                        }

                        if (in_array($resultValue, ['AB', 'UMC', 'I/F'])) {
                            $previousResultStatus = 'FAIL';
                        }
                    }
                }
            } else {

                // First semester: Previous = Current
                $previousCreditPoint = $currentCreditPoint;
                $previousGradePointEarn = $currentGradePointEarn;
                $previousResultStatus = empty($failedSubjects) ? 'PASS' : 'FAIL';
            }

            /*
            |--------------------------------------------------------------------------
            | CGPA
            |--------------------------------------------------------------------------
            */

            $cgpaDenominator = $currentCreditPoint + $previousCreditPoint;

            $cgpa = $cgpaDenominator > 0
                ? round(
                    ($currentGradePointEarn + $previousGradePointEarn)
                        / $cgpaDenominator,
                    2
                )
                : 0;

            /*
            |--------------------------------------------------------------------------
            | RESULT / REMARK
            |--------------------------------------------------------------------------
            */

            $overallResult = implode(', ', $failedSubjects);

            $remark = empty($failedSubjects) ? 'PASS' : 'FAIL';

            /*
            |--------------------------------------------------------------------------
            | STUDENT DATA
            |--------------------------------------------------------------------------
            */

            $marks[$studentId] = [
                'subjects' => $studentSubjects,
                'result' => $overallResult,
                'current_credit_point' => $currentCreditPoint,
                'current_grade_point_earn' => $currentGradePointEarn,
                'sgpa' => $sgpa,
                'previous_credit_point' => $previousCreditPoint,
                'previous_grade_point_earn' => $previousGradePointEarn,
                'cgpa' => $cgpa,
                'previous_result_status' => $previousResultStatus,
                'remark' => $remark,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'backend.result.result-prepare.result',
            compact(
                'exam',
                'course',
                'branch',
                'scheme',
                'batch',
                'semester',
                'className',
                'subjects',
                'students',
                'midSemMarks',
                'endSemMarks',
                'grades',
                'marks',
                'institutes',
                'overallPassPercent',
                'percentageType',
                'previousGpSheets',
                'previousReappearGpSheets',
                'previousSemesters',
                'instituteId'
            )
        )->render();
    }


    // public function endSemResultPrepare(){
    //     $courses = Course::all();
    //     $exams = Exam::latest()->get();
    //     return view('backend.result.result-prepare.endsem.index', compact('courses', 'exams'));
    // }

    // public function finalFetchEndSemResult(Request $request)
    // {
    //     $examId            = $request->exam_id;
    //     $courseType        = $request->course_type;
    //     $courseId          = $request->course_id;
    //     $branchId          = $request->branch_id;
    //     $batchId           = $request->batch_id;
    //     $schemeId          = $request->scheme_id;
    //     $semesterId        = $request->semester_id;
    //     $annualSemesterId  = $request->annual_semester_id;
    //     $instituteId       = $request->institute_id;

    //     /*
    //     =============================
    //     BASIC DATA
    //     =============================
    //     */
    //     $grades = MarksGrade::where('course_id', $courseId)->get();
    //     $exam   = Exam::find($examId);
    //     $course = Course::find($courseId);
    //     $branch = Branch::find($branchId);
    //     $batch  = Batch::find($batchId);

    //     /*
    //     =============================
    //     PASS %
    //     =============================
    //     */
    //     $passPercentage     = PassPercentage::where('course_id', $courseId)->first();
    //     $overallPassPercent = $passPercentage->overall_pass_percentage ?? 50;
    //     $theoryMinPercent   = $passPercentage->theory_min_percentage ?? 20;

    //     /*
    //     =============================
    //     SEM / ANNUAL NAME
    //     =============================
    //     */
    //     if ($courseType == "Semester Based") {
    //         $semester  = Semester::find($semesterId);
    //         $className = $semester->name . " Semester";
    //     } else {
    //         $semester  = AnnualSemester::find($annualSemesterId);
    //         $className = $semester->name;
    //     }

    //     /*
    //     =============================
    //     SUBJECTS
    //     =============================
    //     */
    //     $subjects = Subject::where('course_id', $courseId)
    //         ->where('branch_id', $branchId)
    //         // ->where('is_nc', false)

    //         ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

    //         ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
    //             $q->where('semester_id', $semesterId)
    //             ->whereNull('annual_semester_id');
    //         })

    //         ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
    //             $q->where('annual_semester_id', $annualSemesterId)
    //             ->whereNull('semester_id');
    //         })

    //         ->orderBy('subject_code')
    //         ->get();

    //     /*
    //     =============================
    //     STUDENTS
    //     =============================
    //     */
    //     $students = Student::where('course_type', $courseType)
    //         ->where('course_id', $courseId)
    //         ->where('branch_id', $branchId)
    //         ->where('batch_id', $batchId)

    //         ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

    //         // ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
    //         //     $q->where('semester_id', $semesterId)
    //         //     ->whereNull('annual_semester_id');
    //         // })

    //         // ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
    //         //     $q->where('annual_semester_id', $annualSemesterId)
    //         //     ->whereNull('semester_id');
    //         // })

    //         ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

    //         ->orderBy('institute_id')
    //         ->orderBy('university_roll_no')
    //         ->get();

    //     /*
    //     =============================
    //     MID SEM MARKS
    //     =============================
    //     */
    //     $midSemMarks = MidSemMark::where('exam_id', $examId)
    //         ->where('course_type', $courseType)
    //         ->where('course_id', $courseId)
    //         ->where('branch_id', $branchId)
    //         ->where('batch_id', $batchId)
    //         ->where('is_locked', 1)
    //         ->where('university_forword', 1)

    //         ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

    //         ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
    //             $q->where('semester_id', $semesterId)
    //             ->whereNull('annual_semester_id');
    //         })

    //         ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
    //             $q->where('annual_semester_id', $annualSemesterId)
    //             ->whereNull('semester_id');
    //         })

    //         ->get()
    //         ->groupBy(['student_id', 'subject_id', 'marks_type']);

    //     /*
    //     =============================
    //     END SEM MARKS (KEY FIX)
    //     =============================
    //     */
    //     if ($courseType == 'Annual Based') {

    //         $endSemMarks = EndSemMark::where('exam_id', $examId)
    //             ->where('is_locked', 1)

    //             ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

    //             ->where('annual_semester_id', $annualSemesterId)
    //             ->whereNull('semester_id')

    //             ->get()
    //             ->groupBy(['student_id', 'subject_id', 'marks_type']); // ðŸ”¥

    //     } else {

    //         // $endSemMarks = EndSemMark::where('exam_id', $examId)
    //         //     ->where('is_locked', 1)
    //         //     ->get()
    //         //     ->groupBy(['student_id', 'subject_id']);

    //         $query = EndSemMark::where('exam_id', $examId)
    //             ->where('is_locked', 1)

    //             ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

    //             ->where('semester_id', $semesterId)
    //             ->whereNull('annual_semester_id');

    //         if ($course->course == 'Diploma') {

    //             $endSemMarks = $query
    //                 ->get()
    //                 ->groupBy(['student_id', 'subject_id', 'marks_type']);

    //         } else {

    //             // Existing UG Logic
    //             $endSemMarks = $query
    //                 ->get()
    //                 ->groupBy(['student_id', 'subject_id']);
    //         }
    //     }

    //     /*
    //     =============================
    //     ACADEMIC ADJUSTMENTS
    //     =============================
    //     */
    //     $academicAdjustments = AcademicAdjustment::where('exam_id', $examId)

    //         ->where('adjustment_scope', 'external')

    //         ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

    //         ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {

    //             $q->where('semester_id', $semesterId)
    //             ->whereNull('annual_semester_id');

    //         })

    //         ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {

    //             $q->where('annual_semester_id', $annualSemesterId)
    //             ->whereNull('semester_id');

    //         })

    //         ->get()
    //         ->groupBy(['student_id', 'subject_id', 'marks_type']);

    //     /*
    //     =============================
    //     STUDENT SUBJECTS
    //     =============================
    //     */
    //     $studentSubjects = StudentSubject::where('course_type', $courseType)
    //         ->where('course_id', $courseId)
    //         ->where('branch_id', $branchId)

    //         ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

    //         ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
    //             $q->where('semester_id', $semesterId)
    //             ->whereNull('annual_semester_id');
    //         })

    //         ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
    //             $q->where('annual_semester_id', $annualSemesterId)
    //             ->whereNull('semester_id');
    //         })

    //         ->get()
    //         ->groupBy('student_id');

    //     /*
    //     =============================
    //     PREVIOUS SEMESTER GP SHEET
    //     =============================
    //     */

    //     // $previousGpSheets = collect();

    //     // if ($courseType == 'Semester Based' && $semesterId > 1) {

    //     //     $previousGpSheets = ResultGpSheet::where('course_type', $courseType)
    //     //         ->where('course_id', $courseId)
    //     //         ->where('branch_id', $branchId)
    //     //         ->where('batch_id', $batchId)

    //     //         ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))
    //     //         ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

    //     //         ->where('semester_id', '<', $semesterId)

    //     //         ->get()
    //     //         ->groupBy('student_id');
    //     // }

    //     /*
    //     =============================
    //     PREVIOUS SEMESTER GP SHEET
    //     =============================
    //     */

    //     $previousGpSheets = collect();

    //     $missingPublishedSemesters = [];

    //     if ($courseType == 'Semester Based' && $semesterId > 1) {

    //         // Current semester se pehle ke sab semester
    //         $requiredPreviousSemesters = range(1, $semesterId - 1);

    //         // Jitne semester ka result save/publish hua hai
    //         $publishedSemesters = ResultGpSheet::where('course_type', $courseType)
    //             ->where('course_id', $courseId)
    //             ->where('branch_id', $branchId)
    //             ->where('batch_id', $batchId)

    //             ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))
    //             ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

    //             ->whereIn('semester_id', $requiredPreviousSemesters)

    //             ->select('semester_id')
    //             ->distinct()
    //             ->pluck('semester_id')
    //             ->toArray();

    //         // Missing semester find karo
    //         $missingPublishedSemesters = array_values(array_diff(
    //             $requiredPreviousSemesters,
    //             $publishedSemesters
    //         ));

    //         // Previous GP Sheet
    //         $previousGpSheets = ResultGpSheet::with('subject')
    //             ->where('course_type', $courseType)
    //             ->where('course_id', $courseId)
    //             ->where('branch_id', $branchId)
    //             ->where('batch_id', $batchId)

    //             ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))
    //             ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

    //             ->whereIn('semester_id', $requiredPreviousSemesters)

    //             ->get()
    //             ->groupBy('student_id');
    //     }


    //     $view = $courseType == 'Annual Based'
    //         ? 'backend.result.result-prepare.endsem.annual-result'
    //         : 'backend.result.result-prepare.endsem.result';

    //     return view(
    //         $view,
    //         compact(
    //             'exam',
    //             'courseType',
    //             'course',
	// 			'schemeId',
    //             'branch',
    //             'batch',
    //             'semester',
	// 			'semesterId',
	// 			'annualSemesterId',
	// 			'instituteId',
    //             'subjects',
    //             'students',
    //             'midSemMarks',
    //             'endSemMarks',
    //             'overallPassPercent',
	// 			'theoryMinPercent',
    //             'className',
    //             'grades',
    //             'studentSubjects',
    //             'academicAdjustments',
    //             'previousGpSheets',
    //             'missingPublishedSemesters',
    //         )
    //     )->render();
    // }


    // Save Final Result Sheet ------------------
    public function saveResultSheet(Request $request)
    {
        DB::beginTransaction();

        try {

            $summaryData = $request->input('summary_data', []);
            $gpSheetData = $request->input('gp_sheet_data', []);

            /*
            |--------------------------------------------------------------------------
            | Student Result Summary
            |--------------------------------------------------------------------------
            */

            foreach ($summaryData as $row) {

                StudentResultSummary::updateOrCreate(

                    [
                        'student_id'          => $row['student_id'],
                        'exam_id'             => $row['exam_id'],
                        'course_type'         => $row['course_type'],
                        'course_id'           => $row['course_id'],
                        'scheme_id'           => $row['scheme_id'],
                        'branch_id'           => $row['branch_id'],
                        'semester_id'         => $row['semester_id'],
                        'annual_semester_id'  => $row['annual_semester_id'],
                        'batch_id'            => $row['batch_id'],
                        'institute_id'         => $row['institute_id'],
                    ],

                    [
                        'sgpa'           => $row['sgpa'] ?? 0,
                        'cgpa'           => $row['cgpa'] ?? 0,
                        'result_status'   => $row['result_status'] ?? 'FAIL',
                        'remarks'         => $row['remarks'] ?? null,

                        // Result will not be declared yet
                        'result_declare'  => 0,
                    ]
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Result GP Sheet
            |--------------------------------------------------------------------------
            */

            foreach ($gpSheetData as $row) {

                ResultGpSheet::updateOrCreate(

                    [
                        'student_id'          => $row['student_id'],
                        'subject_id'          => $row['subject_id'],
                        'exam_id'             => $row['exam_id'],
                        'course_type'          => $row['course_type'],
                        'course_id'            => $row['course_id'],
                        'scheme_id'            => $row['scheme_id'],
                        'branch_id'            => $row['branch_id'],
                        'semester_id'          => $row['semester_id'],
                        'annual_semester_id'   => $row['annual_semester_id'],
                        'batch_id'             => $row['batch_id'],
                        'institute_id'          => $row['institute_id'],
                    ],

                    [
                        'credits'               => $row['credits'] ?? 0,
                        'result'                 => $row['result'] ?? 'FAIL',
                        'earn_credit_point'      => $row['earn_credit_point'] ?? 0,
                        'grade'                 => $row['grade'] ?? null,
                        'gain_percentage'        => $row['gain_percentage'] ?? 0,
                        'credit_score'           => $row['credit_score'] ?? 0,
                    ]
                );
            }


            /*
            |--------------------------------------------------------------------------
            | COMMIT TRANSACTION
            |--------------------------------------------------------------------------
            */

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Result sheet saved successfully.'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine()
            ], 500);
        }
    }
    
    public function resultOverview(){
        $exams = Exam::orderBy('id', 'desc')->get();
        $courses = Course::all();
        return view('backend.result.result-overview.index', compact('exams', 'courses'));
    }

    public function fetchResultOverview(Request $request)
    {
        // dd($request->all());
        /*
        =============================
        GET REQUEST DATA
        =============================
        */
        $examId            = $request->exam_id;
        $courseType        = $request->course_type;
        $courseId          = $request->course_id;
        $branchId          = $request->branch_id;
        $batchId           = $request->batch_id;
        $schemeId          = $request->scheme_id;
        $semesterId        = $request->semester_id;
        $annualSemesterId  = $request->annual_semester_id;
        $instituteId       = $request->institute_id;
        $rollNo            = $request->student_roll_number;

        /*
        =============================
        STUDENT ID FROM ROLL NUMBER
        =============================
        */
        $studentId = null;

        if ($rollNo) {
            $student = Student::where('university_roll_no', $rollNo)
                ->first();

            if ($student) {
                $studentId = $student->id;
            }
        }

        // dd($studentId);

        /*
        =============================
        BASIC DATA
        =============================
        */
        $grades = MarksGrade::where('course_id', $courseId)->get();
        $exam    = Exam::find($examId);
        $course  = Course::find($courseId);
        $branch  = Branch::find($branchId);
        $batch   = Batch::find($batchId);
        $institute = Institute::find($instituteId);

        /*
        =============================
        SEMESTER / CLASS NAME
        =============================
        */
        if ($courseType == "Semester Based") {
            $semester  = Semester::find($semesterId);
            $className = $semester->name . " Semester";
        } else {
            $semester  = AnnualSemester::find($annualSemesterId);
            $className = $semester->name;
        }

        /*
        =============================
        FETCH RESULT SUMMARY
        =============================
        */

        $results = StudentResultSummary::with([
                'student',
                'course',
                'branch',
                'scheme',
                'batch',
                'institute'
            ])
            ->where('exam_id', $examId)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');
            })

            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                $q->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id');
            })

            ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

            ->when($studentId, fn($q) => $q->where('student_id', $studentId))

            ->orderBy('student_id')
            ->get();

        /*
        =============================
        RETURN VIEW
        =============================
        */
        // dd($results);

        return view(
            'backend.result.result-overview.result',
            compact(
                'results',
                'exam',
                'course',
                'branch',
                'batch',
                'schemeId',
                'institute',
                'className',
                'grades'
            )
        )->render();
    }

    public function declareResult(Request $request)
    {
        try {
            DB::beginTransaction();

            $ids = $request->ids;

            if (empty($ids)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No data selected'
                ], 400);
            }

            // StudentResultSummary::whereIn('id', $ids)
            //     ->update(['result_declare' => 1]);

            StudentResultSummary::whereIn('id', $ids)
            ->update([
                'result_declare' => $request->result_declare
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Result declared successfully!'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to declare result'
            ], 500);
        }
    }


    public function resultDmc(){
        $exams = Exam::orderBy('id', 'desc')->get();
        $courses = Course::all();
        return view('backend.result.dmc-sheet.index', compact('exams', 'courses'));
    }

    public function resultTranscript(){
        $exams = Exam::orderBy('id', 'desc')->get();
        $courses = Course::all();
        return view('backend.result.transcript.index', compact('exams', 'courses'));
    }

    public function fetchTranscript(Request $request)
    {
        $courseType   = $request->course_type;
        $courseId     = $request->course_id;
        $branchId     = $request->branch_id;
        $batchId      = $request->batch_id;
        $schemeId     = $request->scheme_id;
        $instituteId  = $request->institute_id;
        $rollNo       = $request->student_roll_number;

        $semesterIds = $request->semester_id ?? [];
        $annualIds   = $request->annual_semester_id ?? [];

        /*
        =============================
        STUDENT
        =============================
        */
        $student = Student::where('university_roll_no', $rollNo)->first();

        if (!$student) {
            return "<div class='alert alert-danger text-center'>Student not found</div>";
        }

        /*
        =============================
        RESULT GP SHEET DATA
        =============================
        */
        $query = ResultGpSheet::with('subject')
            ->where('student_id', $student->id)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))
            ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId));

        if ($courseType == "Semester Based") {
            $query->whereIn('semester_id', $semesterIds);
        } else {
            $query->whereIn('annual_semester_id', $annualIds);
        }

        $results = $query->get();

        /*
        =============================
        GROUP BY SEMESTER
        =============================
        */
        $grouped = $courseType == "Semester Based"
            ? $results->groupBy('semester_id')
            : $results->groupBy('annual_semester_id');

        /*
        =============================
        CALCULATIONS ðŸ”¥
        =============================
        */

        // ðŸ‘‰ Total Credits (ALL subjects)
        $totalCredits = $results->sum(function ($r) {
            return $r->subject->credits ?? 0;
        });

        // ðŸ‘‰ Total Credit Points (ONLY numeric GP will work automatically)
        // $totalPoints = $results->sum(function ($r) {
        //     $credit = $r->subject->credits ?? 0;
        //     $gp = is_numeric($r->result_gp) ? $r->result_gp : 0;
        //     return $gp * $credit;
        // });

        $totalPoints = $results->sum(function ($r) {

            $credit = $r->subject->credits ?? 0;

            // ðŸ‘‰ agar FAIL ya SUPPLE hai to GP = 0
            if (in_array(strtoupper($r->result), ['FAIL', 'SUPPLE'])) {
                $gp = 0;
            } else {
                $gp = is_numeric($r->result_gp) ? $r->result_gp : 0;
            }

            return $gp * $credit;
        });

        // ðŸ‘‰ CGPA (safe)
        $cgpa = $totalCredits > 0
            ? round($totalPoints / $totalCredits, 3)
            : 0;

        /*
        =============================
        PROFILE IMAGE
        =============================
        */
        $imagePath = $student->profile_image && file_exists(public_path($student->profile_image))
            ? asset($student->profile_image)
            : asset('assets/images/users/blank-profile-picture.webp');

        /*
        =============================
        RETURN VIEW
        =============================
        */
        return view('backend.result.transcript.result', compact(
            'student',
            'grouped',
            'courseType',
            'imagePath',
            'totalCredits',
            'totalPoints',
            'cgpa'
        ))->render();
    }

    public function saveGraceMarks(Request $request)
    {
        DB::beginTransaction();

        try {

            /*
            =====================================
            VALIDATION
            =====================================
            */

            $request->validate([

                'exam_id'     => 'required',
                'student_id'  => 'required',
                'subject_id'  => 'required',

                'course_type' => 'required',
                'course_id'   => 'required',
                'branch_id'   => 'required',
                'batch_id'    => 'required',

                'grace_marks' => 'required|numeric|min:0',

            ]);

            /*
            =====================================
            ANNUAL BASED
            =====================================
            */

            if($request->course_type == 'Annual Based'){

                return response()->json([

                    'status'  => false,

                    'message' => 'Working mode'

                ], 200);
            }

            /*
            =====================================
            CHECK RESULT EXIST
            =====================================
            */

            $mark = EndSemMark::where('exam_id', $request->exam_id)

                ->where('student_id', $request->student_id)
                ->where('subject_id', $request->subject_id)

                ->where('course_type', $request->course_type)
                ->where('course_id', $request->course_id)
                ->where('branch_id', $request->branch_id)
                ->where('batch_id', $request->batch_id)

                ->when($request->scheme_id, function ($q) use ($request) {

                    $q->where('scheme_id', $request->scheme_id);

                })

                /*
                =====================================
                SEMESTER BASED
                =====================================
                */
                ->when($request->course_type == 'Semester Based', function ($q) use ($request) {

                    $q->where('semester_id', $request->semester_id)
                        ->whereNull('annual_semester_id');

                })

                /*
                =====================================
                ANNUAL BASED
                =====================================
                */
                ->when($request->course_type == 'Annual Based', function ($q) use ($request) {

                    $q->where('annual_semester_id', $request->annual_semester_id)
                        ->whereNull('semester_id')
                        ->where('marks_type', $request->marks_type);

                })

                ->first();

            /*
            =====================================
            RESULT NOT EXIST
            =====================================
            */

            if (!$mark) {

                return response()->json([

                    'status'  => false,

                    'message' => 'Result not exist. Please save obtained marks first.'

                ], 404);
            }

            /*
            =====================================
            LOCK CHECK
            =====================================
            */

            if ($mark->is_locked) {

                return response()->json([

                    'status'  => false,

                    'message' => 'Result is locked.'

                ], 400);
            }

            /*
            =====================================
            UPDATE GRACE MARKS
            =====================================
            */

            /*
            =====================================
            MAX ALLOWED GRACE MARKS
            =====================================
            */

            $maxMarks      = (float) $mark->max_marks;

            $obtainedMarks = (float) $mark->obtained_marks;

            $enteredGrace  = (float) $request->grace_marks;

            /*
            =====================================
            REMAINING MARKS
            =====================================
            */

            $remainingMarks = $maxMarks - $obtainedMarks;

            /*
            =====================================
            IF ALREADY FULL MARKS
            =====================================
            */

            if($remainingMarks <= 0){

                $finalGraceMarks = 0;

            }else{

                /*
                =====================================
                STORE ONLY ALLOWED GRACE
                =====================================
                */

                $finalGraceMarks = min(
                    $enteredGrace,
                    $remainingMarks
                );
            }

            /*
            =====================================
            UPDATE
            =====================================
            */

            $mark->update([

                'grace_marks' => $finalGraceMarks

            ]);

            DB::commit();

            return response()->json([

                'status'  => true,

                'message' => 'Grace marks updated successfully',

                'stored_grace_marks' => $finalGraceMarks

            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'status'  => false,

                'message' => $e->getMessage()

            ], 500);
        }
    }

    // below for all subject grace marks,
    public function resultGrace(){
        $courses = Course::orderBy('name')->get();
        $exams = Exam::orderBy('id', 'desc')->get();
        return view('backend.result.grace-marks.index', compact('courses','exams'));
    }

    public function saveResultGrace(Request $request)
    {
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | VALIDATION
            |--------------------------------------------------------------------------
            */

            $request->validate([
                'exam_id'            => 'required',
                'course_type'        => 'required',
                'course_id'          => 'required',
                'branch_id'          => 'required',
                'batch_id'           => 'required',
                'university_roll'    => 'required',

                'semester_id'        => 'required_if:course_type,Semester Based',
                'annual_semester_id' => 'required_if:course_type,Annual Based',

                'grace_action'       => 'required|in:apply,revert',
                'grace_marks'        => 'required|numeric|min:0',
            ]);


            /*
            |--------------------------------------------------------------------------
            | STUDENT
            |--------------------------------------------------------------------------
            */

            $student = Student::where(
                'university_roll_no',
                trim($request->university_roll)
            )->first();

            if (!$student) {

                throw new \Exception(
                    'Student not found'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | FIND RELATED SUBJECTS
            |--------------------------------------------------------------------------
            |
            | Subject ID request se nahi liya ja raha.
            | Saare related subjects automatically fetch honge.
            |
            */

            $subjects = Subject::where(
                'course_type',
                $request->course_type
            )
            ->where(
                'course_id',
                $request->course_id
            )
            ->where(
                'branch_id',
                $request->branch_id
            )

            /*
            |--------------------------------------------------------------------------
            | SCHEME OPTIONAL
            |--------------------------------------------------------------------------
            */

            ->when($request->scheme_id, function ($q) use ($request) {

                $q->where(
                    'scheme_id',
                    $request->scheme_id
                );

            })

            /*
            |--------------------------------------------------------------------------
            | SEMESTER / ANNUAL SEMESTER
            |--------------------------------------------------------------------------
            */

            ->when(
                $request->course_type === 'Semester Based',
                function ($q) use ($request) {

                    $q->where(
                        'semester_id',
                        $request->semester_id
                    );

                }
            )

            ->when(
                $request->course_type === 'Annual Based',
                function ($q) use ($request) {

                    $q->where(
                        'annual_semester_id',
                        $request->annual_semester_id
                    );

                }
            )

            /*
            |--------------------------------------------------------------------------
            | NON NC SUBJECTS ONLY
            |--------------------------------------------------------------------------
            |
            | is_nc = 0 OR NULL
            |
            */

            ->where(function ($q) {

                $q->where('is_nc', 0)
                ->orWhereNull('is_nc');

            })

            ->get();


            /*
            |--------------------------------------------------------------------------
            | SUBJECT CHECK
            |--------------------------------------------------------------------------
            */

            if ($subjects->isEmpty()) {

                throw new \Exception(
                    'No related subjects found'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | SEMESTER BASED
            |--------------------------------------------------------------------------
            */

            if ($request->course_type == 'Semester Based') {

                /*
                |--------------------------------------------------------------------------
                | NOTE
                |--------------------------------------------------------------------------
                | Semester Based ka actual grace calculation abhi original
                | requirement mein define nahi hai.
                |
                */

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Semester Based subjects found successfully.'
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | ANNUAL BASED
            |--------------------------------------------------------------------------
            */

            if ($request->course_type == 'Annual Based') {

                /*
                |--------------------------------------------------------------------------
                | REQUIRED INTERNAL TYPES
                |--------------------------------------------------------------------------
                */

                $requiredTypes = [
                    'Internal 1',
                    'Internal 2',
                    'Internal 3'
                ];


                /*
                |--------------------------------------------------------------------------
                | PROCESS EACH SUBJECT
                |--------------------------------------------------------------------------
                */

                foreach ($subjects as $subject) {

                    /*
                    |--------------------------------------------------------------------------
                    | FETCH STUDENT SUBJECT MARKS
                    |--------------------------------------------------------------------------
                    */

                    $marks = MidSemMark::where(
                        'student_id',
                        $student->id
                    )
                    ->where(
                        'exam_id',
                        $request->exam_id
                    )
                    ->where(
                        'course_type',
                        $request->course_type
                    )
                    ->where(
                        'course_id',
                        $request->course_id
                    )
                    ->where(
                        'branch_id',
                        $request->branch_id
                    )
                    ->where(
                        'batch_id',
                        $request->batch_id
                    )
                    ->where(
                        'annual_semester_id',
                        $request->annual_semester_id
                    )
                    ->where(
                        'subject_id',
                        $subject->id
                    )

                    /*
                    |--------------------------------------------------------------------------
                    | SCHEME OPTIONAL
                    |--------------------------------------------------------------------------
                    */

                    ->when($request->scheme_id, function ($q) use ($request) {

                        $q->where(
                            'scheme_id',
                            $request->scheme_id
                        );

                    })

                    ->get();


                    /*
                    |--------------------------------------------------------------------------
                    | MARKS NOT FOUND
                    |--------------------------------------------------------------------------
                    */

                    if ($marks->isEmpty()) {

                        throw new \Exception(
                            'No marks data found for subject: ' .
                            $subject->name
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CHECK REQUIRED INTERNALS
                    |--------------------------------------------------------------------------
                    */

                    foreach ($requiredTypes as $type) {

                        $exists = $marks
                            ->where('marks_type', $type)
                            ->count();

                        if ($exists == 0) {

                            throw new \Exception(
                                "Missing {$type} marks for subject: " .
                                $subject->name
                            );
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | REVERT GRACE
                    |--------------------------------------------------------------------------
                    */

                    if ($request->grace_action == 'revert') {

                        MidSemMark::where(
                            'student_id',
                            $student->id
                        )
                        ->where(
                            'exam_id',
                            $request->exam_id
                        )
                        ->where(
                            'course_type',
                            $request->course_type
                        )
                        ->where(
                            'course_id',
                            $request->course_id
                        )
                        ->where(
                            'branch_id',
                            $request->branch_id
                        )
                        ->where(
                            'batch_id',
                            $request->batch_id
                        )
                        ->where(
                            'annual_semester_id',
                            $request->annual_semester_id
                        )
                        ->where(
                            'subject_id',
                            $subject->id
                        )

                        ->when($request->scheme_id, function ($q) use ($request) {

                            $q->where(
                                'scheme_id',
                                $request->scheme_id
                            );

                        })

                        ->update([
                            'grace_marks' => null
                        ]);

                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | ALREADY APPLIED CHECK
                    |--------------------------------------------------------------------------
                    |
                    | NULL = Not Applied
                    | 0    = Not Applied
                    | > 0  = Already Applied
                    |
                    */

                    $alreadyApplied = $marks->contains(function ($row) {

                        return $row->grace_marks !== null
                            && (float) $row->grace_marks > 0;

                    });


                    if ($alreadyApplied) {

                        throw new \Exception(
                            'Grace already applied for subject: ' .
                            $subject->name .
                            '. Please revert first.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | GRACE MARKS
                    |--------------------------------------------------------------------------
                    */

                    $remainingGrace = (float) $request->grace_marks;


                    /*
                    |--------------------------------------------------------------------------
                    | SORT ALL INTERNALS
                    |--------------------------------------------------------------------------
                    |
                    | Highest obtained marks first.
                    |
                    */

                    $sortedMarks = $marks
                        ->sortByDesc(function ($row) {

                            return (float) $row->obtained_marks;

                        })
                        ->values();


                    /*
                    |--------------------------------------------------------------------------
                    | BEST OF TWO
                    |--------------------------------------------------------------------------
                    |
                    | Highest obtained marks wale 2 internals select honge.
                    |
                    */

                    $topTwo = $sortedMarks
                        ->take(2)
                        ->values();


                    /*
                    |--------------------------------------------------------------------------
                    | THIRD INTERNAL
                    |--------------------------------------------------------------------------
                    |
                    | Best of two ke baad jo third internal bachta hai,
                    | usme remaining grace apply hoga.
                    |
                    */

                    $thirdInternal = $sortedMarks
                        ->skip(2)
                        ->first();


                    /*
                    |--------------------------------------------------------------------------
                    | APPLY GRACE ON BEST TWO
                    |--------------------------------------------------------------------------
                    */

                    foreach ($topTwo as $row) {

                        if ($remainingGrace <= 0) {
                            break;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | OBTAINED MARKS
                        |--------------------------------------------------------------------------
                        */

                        $obtainedMarks = (float) $row->obtained_marks;


                        /*
                        |--------------------------------------------------------------------------
                        | MAX MARKS
                        |--------------------------------------------------------------------------
                        */

                        $maxMarks = (float) $row->max_marks;


                        /*
                        |--------------------------------------------------------------------------
                        | AVAILABLE SPACE
                        |--------------------------------------------------------------------------
                        */

                        $space = $maxMarks - $obtainedMarks;


                        /*
                        |--------------------------------------------------------------------------
                        | NO SPACE
                        |--------------------------------------------------------------------------
                        */

                        if ($space <= 0) {
                            continue;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | APPLICABLE GRACE
                        |--------------------------------------------------------------------------
                        */

                        $applicableGrace = min(
                            $space,
                            $remainingGrace
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | UPDATE GRACE
                        |--------------------------------------------------------------------------
                        */

                        $row->update([
                            'grace_marks' => $applicableGrace
                        ]);


                        /*
                        |--------------------------------------------------------------------------
                        | REDUCE REMAINING GRACE
                        |--------------------------------------------------------------------------
                        */

                        $remainingGrace -= $applicableGrace;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | APPLY REMAINING GRACE ON THIRD INTERNAL
                    |--------------------------------------------------------------------------
                    |
                    | Agar best of two mein poora grace apply nahi hua,
                    | to remaining grace third internal mein jayega.
                    |
                    */

                    if (
                        $remainingGrace > 0 &&
                        $thirdInternal
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | THIRD INTERNAL OBTAINED MARKS
                        |--------------------------------------------------------------------------
                        */

                        $obtainedMarks = (float) $thirdInternal->obtained_marks;


                        /*
                        |--------------------------------------------------------------------------
                        | THIRD INTERNAL MAX MARKS
                        |--------------------------------------------------------------------------
                        */

                        $maxMarks = (float) $thirdInternal->max_marks;


                        /*
                        |--------------------------------------------------------------------------
                        | THIRD INTERNAL AVAILABLE SPACE
                        |--------------------------------------------------------------------------
                        */

                        $space = $maxMarks - $obtainedMarks;


                        /*
                        |--------------------------------------------------------------------------
                        | APPLY AVAILABLE SPACE ONLY
                        |--------------------------------------------------------------------------
                        */

                        if ($space > 0) {

                            $applicableGrace = min(
                                $space,
                                $remainingGrace
                            );


                            /*
                            |--------------------------------------------------------------------------
                            | UPDATE THIRD INTERNAL
                            |--------------------------------------------------------------------------
                            */

                            $thirdInternal->update([
                                'grace_marks' => $applicableGrace
                            ]);


                            /*
                            |--------------------------------------------------------------------------
                            | REDUCE REMAINING GRACE
                            |--------------------------------------------------------------------------
                            */

                            $remainingGrace -= $applicableGrace;
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | IMPORTANT
                    |--------------------------------------------------------------------------
                    |
                    | Agar teeno internals mein grace apply karne ke baad
                    | bhi remainingGrace bach jata hai to usko ignore/discard
                    | kar diya jayega.
                    |
                    | Koi error nahi aayega.
                    |
                    */
                }


                /*
                |--------------------------------------------------------------------------
                | COMMIT
                |--------------------------------------------------------------------------
                */

                DB::commit();


                /*
                |--------------------------------------------------------------------------
                | SUCCESS RESPONSE
                |--------------------------------------------------------------------------
                */

                if ($request->grace_action == 'revert') {

                    return response()->json([
                        'success' => true,
                        'message' =>
                            'Grace reverted successfully for all related subjects.'
                    ]);
                }


                return response()->json([
                    'success' => true,
                    'message' =>
                        'Grace marks processed successfully for all related subjects.'
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | INVALID COURSE TYPE
            |--------------------------------------------------------------------------
            */

            throw new \Exception(
                'Invalid course type'
            );


        } catch (ValidationException $e) {

            DB::rollBack();

            throw $e;


        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }



}
