<?php

namespace App\Http\Controllers;

use App\Models\AcademicAdjustment;
use App\Models\AnnualSemester;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\EndSemMark;
use App\Models\Exam;
use App\Models\Institute;
use App\Models\MarksGrade;
use App\Models\MidSemMark;
use App\Models\PassPercentage;
use App\Models\ResultGpSheet;
use App\Models\ReTotallingStudent;
use App\Models\Scheme;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentSubject;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RetotallingResultController extends Controller
{
    // External Marks ----------------------------------------
    public function regularExternalMarks(){
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.retotalling-result.regular-result.external-marks.index', compact('courses', 'exams'));
    }
    public function fetchRegularExternalMarks(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Request Filters
        |--------------------------------------------------------------------------
        */

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
        | 2. Basic Details
        |--------------------------------------------------------------------------
        */

        $exam = Exam::findOrFail($examId);
        $course = Course::findOrFail($courseId);
        $branch = Branch::find($branchId);

        if ($courseType == 'Semester Based') {

            $semester = Semester::find($semesterId);

            $className = $semester
                ? $semester->name . ' Semester'
                : '';

        } else {

            $annualSemester = AnnualSemester::find($annualSemesterId);

            $className = $annualSemester
                ? $annualSemester->name
                : '';
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Fetch Subjects
        |--------------------------------------------------------------------------
        */

        $subjectsQuery = Subject::query()
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId);

        if (!empty($schemeId)) {
            $subjectsQuery->where('scheme_id', $schemeId);
        }

        if ($courseType == 'Semester Based') {

            $subjectsQuery
                ->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');

        } else {

            $subjectsQuery
                ->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id');
        }

        $subjects = $subjectsQuery
            ->orderBy('is_nc')
            ->orderBy('type')
            ->orderBy('subject_code')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 4. Fetch Retotalling Applications
        |--------------------------------------------------------------------------
        |
        | re_totalling_student table does not have course_type.
        | result_type is fixed as regular.
        |
        */

        $retotallingQuery = ReTotallingStudent::with([
            'student',
            'subjects'
        ])
            ->where('exam_id', $examId)
            ->where('result_type', 'regular')
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId);

        if (!empty($schemeId)) {
            $retotallingQuery->where('scheme_id', $schemeId);
        }

        if (!empty($instituteId)) {
            $retotallingQuery->where('institute_id', $instituteId);
        }

        if ($courseType == 'Semester Based') {

            $retotallingQuery
                ->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');

        } else {

            $retotallingQuery
                ->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id');
        }

        /*
        * course_type filter only on Student table.
        */

        $retotallingQuery->whereHas('student', function ($query) use (
            $courseType,
            $courseId,
            $branchId,
            $batchId,
            $schemeId,
            $instituteId
        ) {

            $query->where('course_type', $courseType)
                ->where('course_id', $courseId)
                ->where('branch_id', $branchId)
                ->where('batch_id', $batchId);

            if (!empty($schemeId)) {
                $query->where('scheme_id', $schemeId);
            }

            if (!empty($instituteId)) {
                $query->where('institute_id', $instituteId);
            }
        });

        $retotallingRecords = $retotallingQuery->get();

        /*
        |--------------------------------------------------------------------------
        | 5. Get Applied Students
        |--------------------------------------------------------------------------
        */

        $students = $retotallingRecords
            ->pluck('student')
            ->filter()
            ->unique('id')
            ->sortBy('university_roll_no')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | 6. Prepare Student-wise Retotalling Subject IDs
        |--------------------------------------------------------------------------
        */

        $retotallingSubjects = [];

        foreach ($retotallingRecords as $record) {

            if (!$record->student) {
                continue;
            }

            $studentId = $record->student->id;

            $subjectIds = $record->subjects
                ->pluck('subjects_id')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            if (!isset($retotallingSubjects[$studentId])) {
                $retotallingSubjects[$studentId] = [];
            }

            $retotallingSubjects[$studentId] = array_values(
                array_unique(
                    array_merge(
                        $retotallingSubjects[$studentId],
                        $subjectIds
                    )
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 7. Fetch All Marks of Applied Students
        |--------------------------------------------------------------------------
        */

        $studentIds = $students->pluck('id')->toArray();

        $marks = [];

        if (!empty($studentIds)) {

            $marksQuery = EndSemMark::query()
                ->where('exam_id', $examId)
                ->whereIn('student_id', $studentIds)
                ->where('course_type', $courseType)
                ->where('course_id', $courseId)
                ->where('branch_id', $branchId)
                ->where('batch_id', $batchId);

            if (!empty($schemeId)) {
                $marksQuery->where('scheme_id', $schemeId);
            }

            if ($courseType == 'Semester Based') {

                $marksQuery
                    ->where('semester_id', $semesterId)
                    ->whereNull('annual_semester_id');

                if ($course->course != 'Diploma') {
                    $marksQuery->where('marks_type', 'external');
                }

            } else {

                $marksQuery
                    ->where('annual_semester_id', $annualSemesterId)
                    ->whereNull('semester_id');
            }

            $marksRecords = $marksQuery->get();

            /*
            |--------------------------------------------------------------------------
            | 8. Prepare Marks Array
            |--------------------------------------------------------------------------
            */

            foreach ($marksRecords as $mark) {

                $studentId = $mark->student_id;
                $subjectId = $mark->subject_id;

                $markData = [
                    'obtained_marks' => $mark->obtained_marks,
                    'exam_status' => $mark->exam_status,
                    'is_locked' => $mark->is_locked,
                    'grade' => $mark->grade,

                    'retotalling_obtained_marks' => $mark->retotalling_obtained_marks,
                    'retotalling_marks_locked' => $mark->retotalling_marks_locked,
                ];

                if (
                    $courseType == 'Semester Based' &&
                    $course->course != 'Diploma'
                ) {

                    $marks[$studentId][$subjectId] = $markData;

                } else {

                    $marks[$studentId][$subjectId][$mark->marks_type] = $markData;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 9. Fetch Student Subject Enrollment
        |--------------------------------------------------------------------------
        */

        $studentSubjects = [];

        if (!empty($studentIds)) {

            $studentSubjectsQuery = StudentSubject::query()
                ->where('course_type', $courseType)
                ->where('course_id', $courseId)
                ->where('branch_id', $branchId)
                ->whereIn('student_id', $studentIds);

            if (!empty($schemeId)) {
                $studentSubjectsQuery->where('scheme_id', $schemeId);
            }

            if ($courseType == 'Semester Based') {

                $studentSubjectsQuery
                    ->where('semester_id', $semesterId)
                    ->whereNull('annual_semester_id');

            } else {

                $studentSubjectsQuery
                    ->where('annual_semester_id', $annualSemesterId)
                    ->whereNull('semester_id');
            }

            $studentSubjects = $studentSubjectsQuery
                ->get()
                ->groupBy('student_id')
                ->map(function ($items) {
                    return $items
                        ->pluck('subject_id')
                        ->unique()
                        ->values()
                        ->toArray();
                })
                ->toArray();
        }

        /*
        |--------------------------------------------------------------------------
        | 10. Render View
        |--------------------------------------------------------------------------
        */

        if ($courseType == 'Annual Based') {

            return view(
                'backend.retotalling-result.regular-result.external-marks.annual-result',
                compact(
                    'exam',
                    'course',
                    'branch',
                    'className',
                    'subjects',
                    'students',
                    'marks',
                    'studentSubjects',
                    'retotallingSubjects',
                    'courseType'
                )
            );
        }

        return view(
            'backend.retotalling-result.regular-result.external-marks.result',
            compact(
                'exam',
                'course',
                'branch',
                'className',
                'subjects',
                'students',
                'marks',
                'studentSubjects',
                'retotallingSubjects',
                'courseType'
            )
        );
    }
    public function regularExternalMarksSubjectWise(){
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.retotalling-result.regular-result.external-marks.subject-wise.index', compact('courses', 'exams'));
    }
    public function fetchRegularExternalMarksSubjectWise(Request $request)
    {
        $validated = $request->validate([
            'exam_id'              => 'required|exists:exams,id',
            'course_type'          => 'required|in:Semester Based,Annual Based',
            'course_id'            => 'required|exists:courses,id',
            'scheme_id'            => 'nullable|exists:schemes,id',
            'branch_id'            => 'required|exists:branches,id',
            'batch_id'             => 'required|exists:batches,id',
            'marks_type'           => 'required',
            'subject_id'           => 'required|exists:subjects,id',
            'result_type'          => 'required|in:regular,supplementary',
            'institute_id'         => 'nullable|exists:institutes,id',
            'semester_id'          => 'nullable|required_if:course_type,Semester Based|exists:semesters,id',
            'annual_semester_id'   => 'nullable|required_if:course_type,Annual Based|exists:annual_semesters,id',
        ]);

        $examId       = $validated['exam_id'];
        $courseType   = $validated['course_type'];
        $courseId     = $validated['course_id'];
        $schemeId     = $validated['scheme_id'] ?? null;
        $branchId     = $validated['branch_id'];
        $batchId      = $validated['batch_id'];
        $marksType    = $validated['marks_type'];
        $subjectId    = $validated['subject_id'];
        $resultType   = $validated['result_type'];
        $instituteId  = $validated['institute_id'] ?? null;
        $semesterId   = $validated['semester_id'] ?? null;
        $annualId     = $validated['annual_semester_id'] ?? null;

        $exam    = Exam::findOrFail($examId);
        $course  = Course::findOrFail($courseId);
        $branch  = Branch::findOrFail($branchId);
        $subject = Subject::findOrFail($subjectId);

        /*
        |--------------------------------------------------------------------------
        | Class / Semester Name
        |--------------------------------------------------------------------------
        */

        $className = '';

        if ($courseType === 'Semester Based' && $semesterId) {
            $className = Semester::find($semesterId)?->name ?? '';
        } elseif ($courseType === 'Annual Based' && $annualId) {
            $className = AnnualSemester::find($annualId)?->name ?? '';
        }

        /*
        |--------------------------------------------------------------------------
        | Fetch Only Mapped Students
        |--------------------------------------------------------------------------
        */

        $mappedStudentsQuery = ReTotallingStudent::query()
            ->with('student')
            ->where('exam_id', $examId)
            ->where('result_type', $resultType) // Regular / Supplementary filter
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

            // Selected subject must be mapped
            ->whereHas('subjects', function ($query) use ($subjectId) {
                $query->where('subjects_id', $subjectId);
            })

            // Student table academic filters
            ->whereHas('student', function ($query) use (
                $courseType,
                $courseId,
                $branchId,
                $batchId,
                $schemeId,
                $instituteId,
                $semesterId,
                $annualId
            ) {
                $query->where('course_type', $courseType)
                    ->where('course_id', $courseId)
                    ->where('branch_id', $branchId)
                    ->where('batch_id', $batchId);

                if ($schemeId) {
                    $query->where('scheme_id', $schemeId);
                }

                if ($instituteId) {
                    $query->where('institute_id', $instituteId);
                }

                if ($courseType === 'Semester Based') {
                    $query->where('semester_id', $semesterId)
                        ->whereNull('annual_semester_id');
                } else {
                    $query->where('annual_semester_id', $annualId)
                        ->whereNull('semester_id');
                }
            });

        $mappedRecords = $mappedStudentsQuery->get();

        $students = $mappedRecords
            ->pluck('student')
            ->filter()
            ->unique('id')
            ->sortBy('university_roll_no')
            ->values();

        $studentIds = $students->pluck('id')->toArray();

        /*
        |--------------------------------------------------------------------------
        | Fetch Existing End Semester Marks
        |--------------------------------------------------------------------------
        */

        $rawMarks = collect();

        if (!empty($studentIds)) {

            $marksQuery = EndSemMark::query()
                ->whereIn('student_id', $studentIds)
                ->where('exam_id', $examId)
                ->where('course_type', $courseType)
                ->where('course_id', $courseId)
                ->where('branch_id', $branchId)
                ->where('batch_id', $batchId)
                ->where('subject_id', $subjectId)
                ->where('marks_type', $marksType);

            if ($schemeId) {
                $marksQuery->where('scheme_id', $schemeId);
            }

            if ($instituteId) {
                $marksQuery->where('institute_id', $instituteId);
            }

            if ($courseType === 'Semester Based') {
                $marksQuery->where('semester_id', $semesterId)
                    ->whereNull('annual_semester_id');
            } else {
                $marksQuery->where('annual_semester_id', $annualId)
                    ->whereNull('semester_id');
            }

            $rawMarks = $marksQuery->get()->keyBy('student_id');
        }

        /*
        |--------------------------------------------------------------------------
        | Maximum Marks
        |--------------------------------------------------------------------------
        */

        // Keep your existing Diploma / non-Diploma max marks calculation here.
        $maxMarks = $subject->max_marks ?? 0;

        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view(
            'backend.retotalling-result.regular-result.external-marks.subject-wise.result',
            compact(
                'exam',
                'course',
                'branch',
                'subject',
                'className',
                'students',
                'rawMarks',
                'courseType',
                'marksType',
                'resultType',
                'schemeId',
                'semesterId',
                'annualId',
                'instituteId',
                'batchId',
                'maxMarks'
            )
        )->render();
    }
    public function saveRegularRetotallingExternalMarks(Request $request)
    {
        $validated = $request->validate([
            'student_id'       => 'required|exists:students,id',
            'exam_id'          => 'required|exists:exams,id',
            'subject_id'       => 'required|exists:subjects,id',
            'course_type'      => 'required|in:Semester Based,Annual Based',
            'course_id'        => 'required|exists:courses,id',
            'branch_id'        => 'required|exists:branches,id',
            'batch_id'         => 'required|exists:batches,id',
            'scheme_id'        => 'nullable|exists:schemes,id',
            'institute_id'     => 'nullable|exists:institutes,id',
            'semester_id'      => 'nullable|exists:semesters,id',
            'annual_semester_id' => 'nullable|exists:annual_semesters,id',
            'marks_type'       => 'required|in:external,theory_paper1,theory_paper2',
            'marks'            => 'required|numeric|min:0',
            'max_marks'        => 'required|numeric|min:0',
            'result_type'      => 'required|in:regular,supplementary',
        ]);

        try {

            $studentId  = $validated['student_id'];
            $examId     = $validated['exam_id'];
            $subjectId  = $validated['subject_id'];
            $courseType = $validated['course_type'];
            $courseId   = $validated['course_id'];
            $branchId   = $validated['branch_id'];
            $batchId    = $validated['batch_id'];
            $schemeId   = $validated['scheme_id'] ?? null;
            $instituteId = $validated['institute_id'] ?? null;
            $semesterId = $validated['semester_id'] ?? null;
            $annualId   = $validated['annual_semester_id'] ?? null;
            $marksType  = $validated['marks_type'];
            $marks      = $validated['marks'];

            /*
            |--------------------------------------------------------------------------
            | 1. FETCH SUBJECT AND VALIDATE MAXIMUM MARKS
            |--------------------------------------------------------------------------
            */

            $subject = Subject::findOrFail($subjectId);

            if ($marksType === 'external') {

                $maxMarks = $subject->external_marks ?? 0;

            } elseif ($marksType === 'theory_paper1') {

                $maxMarks = $subject->paper1 ?? 0;

            } elseif ($marksType === 'theory_paper2') {

                $maxMarks = $subject->paper2 ?? 0;

            } else {

                $maxMarks = 0;
            }

            if ((float) $marks > (float) $maxMarks) {

                return response()->json([
                    'status'  => 'error',
                    'message' => "Marks cannot be greater than maximum marks ($maxMarks).",
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | 2. VERIFY STUDENT SUBJECT MAPPING
            |--------------------------------------------------------------------------
            */

            $mappingQuery = ReTotallingStudent::query()
                ->where('student_id', $studentId)
                ->where('exam_id', $examId)
                ->where('course_id', $courseId)
                ->where('branch_id', $branchId)
                ->where('batch_id', $batchId)
                ->whereHas('subjects', function ($query) use ($subjectId) {
                    $query->where('subjects_id', $subjectId);
                })
                ->whereHas('student', function ($query) use (
                    $courseType,
                    $schemeId,
                    $instituteId,
                    $semesterId,
                    $annualId
                ) {
                    $query->where('course_type', $courseType);

                    if ($schemeId) {
                        $query->where('scheme_id', $schemeId);
                    }

                    if ($instituteId) {
                        $query->where('institute_id', $instituteId);
                    }

                    if ($courseType === 'Semester Based') {

                        $query->where('semester_id', $semesterId)
                            ->whereNull('annual_semester_id');

                    } else {

                        $query->where('annual_semester_id', $annualId)
                            ->whereNull('semester_id');
                    }
                });

            if ($instituteId) {
                $mappingQuery->where('institute_id', $instituteId);
            }

            if ($courseType === 'Semester Based') {

                $mappingQuery->where('semester_id', $semesterId)
                    ->whereNull('annual_semester_id');

            } else {

                $mappingQuery->where('annual_semester_id', $annualId)
                    ->whereNull('semester_id');
            }

            $mappingExists = $mappingQuery->exists();

            if (!$mappingExists) {

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Student subject mapping not found.',
                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | 3. FIND EXISTING END SEM MARK RECORD
            |--------------------------------------------------------------------------
            */

            $marksQuery = EndSemMark::query()
                ->where('student_id', $studentId)
                ->where('exam_id', $examId)
                ->where('subject_id', $subjectId)
                ->where('course_type', $courseType)
                ->where('course_id', $courseId)
                ->where('branch_id', $branchId)
                ->where('batch_id', $batchId)
                ->where('marks_type', $marksType);

            if ($schemeId) {
                $marksQuery->where('scheme_id', $schemeId);
            }

            if ($instituteId) {
                $marksQuery->where('institute_id', $instituteId);
            }

            if ($courseType === 'Semester Based') {

                $marksQuery->where('semester_id', $semesterId)
                    ->whereNull('annual_semester_id');

            } else {

                $marksQuery->where('annual_semester_id', $annualId)
                    ->whereNull('semester_id');
            }

            $endSemMark = $marksQuery->first();

            /*
            |--------------------------------------------------------------------------
            | 4. INSERT NOT ALLOWED - ONLY UPDATE
            |--------------------------------------------------------------------------
            */

            if (!$endSemMark) {

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Existing marks record not found. Retotalling marks cannot be inserted.',
                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | 5. CHECK LOCK STATUS
            |--------------------------------------------------------------------------
            */

            if ((int) $endSemMark->retotalling_marks_locked === 1) {

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Retotalling marks are locked. Please unlock before updating.',
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | 6. UPDATE ONLY RETOTALLING COLUMNS
            |--------------------------------------------------------------------------
            */

            $endSemMark->update([
                'retotalling_obtained_marks' => $marks,
                'retotalling_upload_by'      => Auth::id(),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Retotalling marks updated successfully.',
            ]);

        } catch (\Throwable $e) {

            Log::error('Retotalling Marks Update Error: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to update retotalling marks.',
            ], 500);
        }
    }
    public function updateRegularRetotallingExternalMarksLock(Request $request)
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
        // $marksType         = $request->marks_type;
        $records = $request->records ?? [];

        // dd($request->all());

        if (empty($records)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No records selected'
            ], 422);
        }

        if (!in_array((string) $request->status, ['0', '1'], true)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid lock status'
            ], 422);
        }

        foreach ($records as $record) {

            EndSemMark::where('exam_id', $record['exam_id'])
                ->where('student_id', $record['student_id'])
                ->where('subject_id', $record['subject_id'])
                ->where('course_type', $courseType)
                ->where('course_id', $courseId)
                ->where('branch_id', $branchId)
                ->where('batch_id', $batchId)
                ->where('marks_type', $record['marks_type'])

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
                    'retotalling_marks_locked' => $request->status
                ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => $request->status == 1
                ? 'Marks locked successfully'
                : 'Marks unlocked successfully'
        ]);
    }
    public function exportExternalMarks(Request $request)
    {
        // dd($request->all());

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
        $result_type        = $request->result_type;

        // Master Data
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
        | Student Query
        |--------------------------------------------------------------------------
        */

        $students = Student::query();

        if (!empty($institute_id)) {
            $students->where('students.institute_id', $institute_id);
        }

        $students->where('students.course_type', $course_type)
            ->where('students.course_id', $course_id)
            ->where('students.branch_id', $branch_id)
            ->where('students.batch_id', $batch_id);

        if (!empty($scheme_id)) {
            $students->where('students.scheme_id', $scheme_id);
        }

        if ($course_type == 'Semester Based') {

            $students->where('students.semester_id', $semester_id)
                ->whereNull('students.annual_semester_id');

        } elseif ($course_type == 'Annual Based') {

            $students->where('students.annual_semester_id', $annual_semester_id)
                ->whereNull('students.semester_id');
        }

        /*
        |--------------------------------------------------------------------------
        | Re-totaling Student + Result Type + Subject Mapping Check
        |--------------------------------------------------------------------------
        */

        $students->whereExists(function ($query) use (
            $exam_id,
            $subject_id,
            $result_type,
            $course_type,
            $course_id,
            $scheme_id,
            $branch_id,
            $batch_id,
            $semester_id,
            $annual_semester_id,
            $institute_id
        ) {

            $query->selectRaw('1')
                ->from('re_totalling_student as rts')
                ->join(
                    're_totalling_student_subjects as rtss',
                    'rtss.re_totalling_student_id',
                    '=',
                    'rts.id'
                )
                ->whereColumn('rts.student_id', 'students.id')
                ->where('rts.exam_id', $exam_id)
                ->where('rts.result_type', $result_type)
                ->where('rts.course_id', $course_id)
                ->where('rts.branch_id', $branch_id)
                ->where('rts.batch_id', $batch_id)
                ->where('rts.institute_id', $institute_id)
                ->where('rtss.subjects_id', $subject_id);

            // Scheme condition
            if (!empty($scheme_id)) {
                $query->where('rts.scheme_id', $scheme_id);
            }

            // Semester / Annual condition
            if ($course_type == 'Semester Based') {

                $query->where('rts.semester_id', $semester_id)
                    ->whereNull('rts.annual_semester_id');

            } elseif ($course_type == 'Annual Based') {

                $query->where('rts.annual_semester_id', $annual_semester_id)
                    ->whereNull('rts.semester_id');
            }
        });

        // Student Sorting
        $students = $students->orderBy('students.student_name')->get();

        /*
        |--------------------------------------------------------------------------
        | CSV Export
        |--------------------------------------------------------------------------
        */

        $subjectCode = $subject->subject_code ?? 'subject';

        $fileName = 'retotalling_external_marks_' . $subjectCode . '_' . time() . '.csv';

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
                'retotalling_obtained_marks'
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

            $updateCount = 0;
            $skipCount   = 0;

            DB::beginTransaction();

            foreach ($data as $row) {

                if (count(array_filter($row, fn($value) => trim($value) !== '')) === 0) {
                    continue;
                }

                // Validate CSV column count
                if (count($row) !== count($header)) {
                    $skipCount++;
                    continue;
                }

                $row = array_combine($header, $row);

                if (!$row) {
                    $skipCount++;
                    continue;
                }

                // Required CSV columns
                $requiredColumns = [
                    'university_roll_no',
                    'exam_name',
                    'course_type',
                    'course_name',
                    'branch_name',
                    'batch_name',
                    'institute_name',
                    'marks_type',
                    'subject_code',
                    'subject_name',
                    'retotalling_obtained_marks',
                ];

                $missingColumn = false;

                foreach ($requiredColumns as $column) {
                    if (!array_key_exists($column, $row)) {
                        $missingColumn = true;
                        break;
                    }
                }

                if ($missingColumn) {
                    throw new \Exception('Invalid CSV format. Required columns are missing.');
                }

                // ============================
                // 1. Basic Master Lookups
                // ============================

                $course = Course::where('name', trim($row['course_name']))->first();

                $branch = $course
                    ? Branch::where('course_id', $course->id)
                        ->where('name', trim($row['branch_name']))
                        ->first()
                    : null;

                $semester = !empty(trim($row['semester_name'] ?? ''))
                    ? Semester::where('name', trim($row['semester_name']))->first()
                    : null;

                $annual = !empty(trim($row['annual_term'] ?? ''))
                    ? AnnualSemester::where('name', trim($row['annual_term']))->first()
                    : null;

                $batch = Batch::where('name', trim($row['batch_name']))->first();

                $institute = Institute::where('name', trim($row['institute_name']))->first();

                // ============================
                // 2. Scheme Lookup
                // ============================

                $scheme = null;

                if (!empty(trim($row['scheme_name'] ?? ''))) {
                    $scheme = Scheme::where('name', trim($row['scheme_name']))->first();

                    if (!$scheme) {
                        $skipCount++;
                        continue;
                    }
                }

                // ============================
                // 3. Student Lookup
                // ============================

                if (!$course || !$branch || !$batch || !$institute) {
                    $skipCount++;
                    continue;
                }

                $courseType = trim($row['course_type']);

                $studentQuery = Student::where(
                        'university_roll_no',
                        trim($row['university_roll_no'])
                    )
                    ->where('course_type', $courseType)
                    ->where('course_id', $course->id)
                    ->where('branch_id', $branch->id)
                    ->where('batch_id', $batch->id)
                    ->where('institute_id', $institute->id);

                if ($scheme) {
                    $studentQuery->where('scheme_id', $scheme->id);
                }

                if ($courseType === 'Semester Based') {

                    if (!$semester) {
                        $skipCount++;
                        continue;
                    }

                    $studentQuery->where('semester_id', $semester->id)
                        ->whereNull('annual_semester_id');

                } elseif ($courseType === 'Annual Based') {

                    if (!$annual) {
                        $skipCount++;
                        continue;
                    }

                    $studentQuery->where('annual_semester_id', $annual->id)
                        ->whereNull('semester_id');

                } else {
                    $skipCount++;
                    continue;
                }

                $student = $studentQuery->first();

                if (!$student) {
                    $skipCount++;
                    continue;
                }

                // ============================
                // 4. Subject Lookup
                // ============================

                $subjectQuery = Subject::where('subject_code', trim($row['subject_code']))
                    ->where('name', trim($row['subject_name']))
                    ->where('type', 'Theory')
                    ->where(function ($query) {
                        $query->where('is_nc', 0)
                            ->orWhereNull('is_nc');
                    })
                    ->where('course_id', $course->id)
                    ->where('branch_id', $branch->id);

                if ($courseType === 'Semester Based') {

                    $subjectQuery->where('semester_id', $semester->id)
                        ->whereNull('annual_semester_id');

                } else {

                    $subjectQuery->where('annual_semester_id', $annual->id)
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
                // 5. Exam Lookup
                // ============================

                $exam = Exam::where('name', trim($row['exam_name']))->first();

                if (!$exam) {
                    $skipCount++;
                    continue;
                }

                // ============================
                // 6. Re-totaling Mapping Check
                // ============================

                $mappingQuery = ReTotallingStudent::where('student_id', $student->id)
                    ->where('exam_id', $exam->id)
                    ->where('course_id', $course->id)
                    ->where('branch_id', $branch->id)
                    ->where('batch_id', $batch->id)
                    ->where('institute_id', $institute->id)
                    ->whereHas('subjects', function ($query) use ($subject) {
                        $query->where('subjects_id', $subject->id);
                    });

                if ($scheme) {
                    $mappingQuery->where('scheme_id', $scheme->id);
                }

                if ($courseType === 'Semester Based') {

                    $mappingQuery->where('semester_id', $semester->id)
                        ->whereNull('annual_semester_id');

                } else {

                    $mappingQuery->where('annual_semester_id', $annual->id)
                        ->whereNull('semester_id');
                }

                // Match result_type if supplied in import request
                if ($request->filled('result_type')) {
                    $mappingQuery->where('result_type', $request->result_type);
                }

                if (!$mappingQuery->exists()) {
                    $skipCount++;
                    continue;
                }

                // ============================
                // 7. Revised Marks Validation
                // ============================

                $revisedMarksValue = trim($row['retotalling_obtained_marks']);

                // Blank marks are not imported
                if ($revisedMarksValue === '' || !is_numeric($revisedMarksValue)) {
                    $skipCount++;
                    continue;
                }

                $revisedMarks = (float) $revisedMarksValue;

                // ============================
                // 8. Existing EndSemMark Lookup
                // ============================

                $marksQuery = EndSemMark::where('student_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->where('exam_id', $exam->id)
                    ->where('course_type', $courseType)
                    ->where('course_id', $course->id)
                    ->where('branch_id', $branch->id)
                    ->where('batch_id', $batch->id)
                    ->where('institute_id', $institute->id)
                    ->where('marks_type', trim($row['marks_type']));

                if ($scheme) {
                    $marksQuery->where('scheme_id', $scheme->id);
                }

                if ($courseType === 'Semester Based') {

                    $marksQuery->where('semester_id', $semester->id)
                        ->whereNull('annual_semester_id');

                } else {

                    $marksQuery->where('annual_semester_id', $annual->id)
                        ->whereNull('semester_id');
                }

                $endSemMark = $marksQuery->first();

                if (!$endSemMark) {
                    $skipCount++;
                    continue;
                }

                // ============================
                // 9. Locked Record Check
                // ============================

                if ($endSemMark->retotalling_marks_locked == 1) {
                    $skipCount++;
                    continue;
                }

                // ============================
                // 10. Update Revised Marks
                // ============================

                $endSemMark->update([
                    'retotalling_obtained_marks' => $revisedMarks,
                    'retotalling_upload_by'      => Auth::id(),
                    'retotalling_marks_locked'   => 0,
                ]);

                $updateCount++;
            }

            DB::commit();

            return back()->with(
                'success',
                "Updated: {$updateCount} | Skipped (Invalid/Not Matched/Locked): {$skipCount}"
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(
                'error',
                'Import failed: ' . $e->getMessage()
            );
        }
    }

    // Result Preview -------------------------------------------
    public function regularResultPreviewSubjectWiseIndex(){
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.retotalling-result.regular-result.result-preview.subjectwise-result.index', compact('courses', 'exams'));
    }

    // public function fetchRegularResultPreviewSubjectWise(Request $request)
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
    //     $subjectId         = $request->subject_id;
    //     $studentId         = $request->student_id ?? null;

    //     /*
    //     =============================
    //     BASIC DATA
    //     =============================
    //     */

    //     $exam    = Exam::find($examId);
    //     $course  = Course::find($courseId);
    //     $branch  = Branch::find($branchId);
    //     $batch   = Batch::find($batchId);
    //     $subject = Subject::find($subjectId);

    //     /*
    //     =============================
    //     PASS PERCENTAGE
    //     =============================
    //     */

    //     $passPercentage = PassPercentage::where('course_id', $courseId)->first();

    //     /*
    //     =============================
    //     SEMESTER
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
    //     RETOTALLING STUDENT IDS
    //     =============================
    //     */

    //     $retotallingStudentsQuery = ReTotallingStudent::where('exam_id', $examId)
    //         ->where('result_type', 'regular')
    //         ->where('course_id', $courseId)
    //         ->where('branch_id', $branchId)
    //         ->where('batch_id', $batchId)

    //         ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

    //         ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

    //         ->when($studentId, fn($q) => $q->where('student_id', $studentId))

    //         ->whereHas('subjects', function ($q) use ($subjectId) {
    //             $q->where('subjects_id', $subjectId);
    //         });

    //     if ($courseType == 'Annual Based') {

    //         $retotallingStudentsQuery
    //             ->where('annual_semester_id', $annualSemesterId)
    //             ->whereNull('semester_id');

    //     } else {

    //         $retotallingStudentsQuery
    //             ->where('semester_id', $semesterId)
    //             ->whereNull('annual_semester_id');
    //     }

    //     $retotallingStudentIds = $retotallingStudentsQuery
    //         ->pluck('student_id')
    //         ->unique()
    //         ->values()
    //         ->toArray();

    //     /*
    //     =============================
    //     STUDENTS
    //     =============================
    //     */

    //     $students = Student::whereIn('id', $retotallingStudentIds)
    //         ->where('course_type', $courseType)
    //         ->where('course_id', $courseId)
    //         ->where('branch_id', $branchId)
    //         ->where('batch_id', $batchId)

    //         ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

    //         // ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
    //         //     $q->where('semester_id', $semesterId)
    //         //       ->whereNull('annual_semester_id');
    //         // })

    //         // ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
    //         //     $q->where('annual_semester_id', $annualSemesterId)
    //         //       ->whereNull('semester_id');
    //         // })

    //         ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

    //         ->when($studentId, fn($q) => $q->where('id', $studentId))

    //         ->orderBy('institute_id')
    //         ->orderBy('university_roll_no')
    //         ->get();

    //     /*
    //     =============================
    //     MID SEM MARKS
    //     =============================
    //     */

    //     $midSemMarks = MidSemMark::where('exam_id', $examId)
    //         ->where('subject_id', $subjectId)
    //         ->where('course_type', $courseType)
    //         ->where('course_id', $courseId)
    //         ->where('branch_id', $branchId)
    //         ->where('batch_id', $batchId)
    //         ->where('is_locked', 1)
    //         ->where('university_forword', 1)
    //         ->whereIn('student_id', $retotallingStudentIds)

    //         ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

    //         ->when($studentId, fn($q) => $q->where('student_id', $studentId))

    //         ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {

    //             $q->where('semester_id', $semesterId)
    //             ->whereNull('annual_semester_id');

    //         })

    //         ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {

    //             $q->where('annual_semester_id', $annualSemesterId)
    //             ->whereNull('semester_id');

    //         })

    //         ->get()
    //         ->groupBy(['student_id', 'marks_type']);

    //     /*
    //     =============================
    //     END SEM MARKS
    //     =============================
    //     */

    //     if ($courseType == 'Annual Based') {

    //         /*
    //         --------------------------------
    //         Annual : Multiple marks_type
    //         --------------------------------
    //         */

    //         $endSemMarks = EndSemMark::where('exam_id', $examId)
    //             ->where('subject_id', $subjectId)
    //             ->where('is_locked', 1)
    //             ->whereIn('student_id', $retotallingStudentIds)

    //             ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

    //             ->when($studentId, fn($q) => $q->where('student_id', $studentId))

    //             ->where('annual_semester_id', $annualSemesterId)
    //             ->whereNull('semester_id')

    //             ->get()
    //             ->groupBy(['student_id', 'marks_type']);

    //     } else {

    //         /*
    //         --------------------------------
    //         Semester : Existing Logic
    //         --------------------------------
    //         */

    //         $query = EndSemMark::where('exam_id', $examId)
    //             ->where('subject_id', $subjectId)
    //             ->where('is_locked', 1)
    //             ->whereIn('student_id', $retotallingStudentIds)

    //             ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

    //             ->when($studentId, fn($q) => $q->where('student_id', $studentId))

    //             ->where('semester_id', $semesterId)
    //             ->whereNull('annual_semester_id');

    //         if ($course->course == 'Diploma') {

    //             $endSemMarks = $query
    //                 ->get()
    //                 ->groupBy(['student_id', 'marks_type']);

    //         } else {

    //             $endSemMarks = $query
    //                 ->get()
    //                 ->keyBy('student_id');
    //         }
    //     }

    //     /*
    //     =============================
    //     ACADEMIC ADJUSTMENTS
    //     =============================
    //     */

    //     $academicAdjustments = AcademicAdjustment::where('exam_id', $examId)

    //         ->where('course_type', $courseType)

    //         ->where('course_id', $courseId)
    //         ->where('branch_id', $branchId)
    //         ->where('batch_id', $batchId)

    //         ->where('subject_id', $subjectId)

    //         ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

    //         ->when($studentId, fn($q) => $q->where('student_id', $studentId))

    //         ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {

    //             $q->where('semester_id', $semesterId)
    //             ->whereNull('annual_semester_id');

    //         })

    //         ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {

    //             $q->where('annual_semester_id', $annualSemesterId)
    //             ->whereNull('semester_id');

    //         })

    //         ->get();

    //     $adjustmentMarks = [];

    //     foreach ($academicAdjustments as $adjustment) {

    //         $adjustmentMarks
    //             [$adjustment->student_id]
    //             [$adjustment->marks_type]
    //             = $adjustment->adjustment_marks;
    //     }

    //     /*
    //     =============================
    //     STUDENT SUBJECTS
    //     =============================
    //     */

    //     $studentSubjects = StudentSubject::where([
    //         'subject_id'  => $subjectId,
    //         'course_type' => $courseType,
    //         'course_id'   => $courseId,
    //         'branch_id'   => $branchId,
    //     ])

    //     ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

    //     ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {

    //         $q->where('semester_id', $semesterId);

    //     }, function ($q) use ($annualSemesterId) {

    //         $q->where('annual_semester_id', $annualSemesterId);

    //     })

    //     ->pluck('student_id')
    //     ->toArray();

    //     /*
    //     =============================
    //     VIEW
    //     =============================
    //     */

    //     $view = $courseType == 'Annual Based'
    //         ? 'backend.retotalling-result.regular-result.result-preview.subjectwise-result.annual-result'
    //         : 'backend.retotalling-result.regular-result.result-preview.subjectwise-result.result';

    //     return view(
    //         $view,
    //         compact(
    //             'exam',
    //             'course',
    //             'branch',
    //             'batch',
    //             'subject',
    //             'students',
    //             'midSemMarks',
    //             'endSemMarks',
    //             'className',
    //             'passPercentage',
    //             'studentSubjects',
    //             'adjustmentMarks'
    //         )
    //     )->render();
    // }

    public function fetchRegularResultPreviewSubjectWise(Request $request)
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
        $studentId         = $request->student_id ?? null;

        /*
        =============================
        BASIC DATA
        =============================
        */

        $exam    = Exam::find($examId);
        $course  = Course::find($courseId);
        $branch  = Branch::find($branchId);
        $batch   = Batch::find($batchId);
        $subject = Subject::find($subjectId);

        /*
        =============================
        PASS PERCENTAGE
        =============================
        */

        $passPercentage = PassPercentage::where('course_id', $courseId)->first();

        /*
        =============================
        SEMESTER
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
        RETOTALLING STUDENT IDS
        =============================
        */

        $retotallingStudentsQuery = ReTotallingStudent::where('exam_id', $examId)
            ->where('result_type', 'regular')
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

            ->when($studentId, fn($q) => $q->where('student_id', $studentId))

            ->whereHas('subjects', function ($q) use ($subjectId) {
                $q->where('subjects_id', $subjectId);
            });

        if ($courseType == 'Annual Based') {

            $retotallingStudentsQuery
                ->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id');

        } else {

            $retotallingStudentsQuery
                ->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');
        }

        $retotallingStudentIds = $retotallingStudentsQuery
            ->pluck('student_id')
            ->unique()
            ->values()
            ->toArray();

        /*
        =============================
        STUDENTS
        =============================
        */

        $students = Student::whereIn('id', $retotallingStudentIds)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            // ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
            //     $q->where('semester_id', $semesterId)
            //       ->whereNull('annual_semester_id');
            // })

            // ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
            //     $q->where('annual_semester_id', $annualSemesterId)
            //       ->whereNull('semester_id');
            // })

            ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

            ->when($studentId, fn($q) => $q->where('id', $studentId))

            ->orderBy('institute_id')
            ->orderBy('university_roll_no')
            ->get();

        /*
        =============================
        MID SEM MARKS
        =============================
        */

        $midSemMarks = MidSemMark::where('exam_id', $examId)
            ->where('subject_id', $subjectId)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->where('is_locked', 1)
            ->where('university_forword', 1)

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($studentId, fn($q) => $q->where('student_id', $studentId))

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {

                $q->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');

            })

            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {

                $q->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id');

            })

            ->get()
            ->groupBy(['student_id', 'marks_type']);

        /*
        =============================
        END SEM MARKS
        =============================
        */

        if ($courseType == 'Annual Based') {

            /*
            --------------------------------
            Annual : Multiple marks_type
            --------------------------------
            */

            $endSemMarks = EndSemMark::where('exam_id', $examId)
                ->where('subject_id', $subjectId)
                ->where('is_locked', 1)

                ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

                ->when($studentId, fn($q) => $q->where('student_id', $studentId))

                ->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id')

                ->get()
                ->groupBy(['student_id', 'marks_type']);

        } else {

            /*
            --------------------------------
            Semester : Existing Logic
            --------------------------------
            */

            $query = EndSemMark::where('exam_id', $examId)
                ->where('subject_id', $subjectId)
                ->where('is_locked', 1)

                ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

                ->when($studentId, fn($q) => $q->where('student_id', $studentId))

                ->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');

            if ($course->course == 'Diploma') {

                $endSemMarks = $query
                    ->get()
                    ->groupBy(['student_id', 'marks_type']);

            } else {

                $endSemMarks = $query
                    ->get()
                    ->keyBy('student_id');
            }
        }

        /*
        =============================
        ACADEMIC ADJUSTMENTS
        =============================
        */

        $academicAdjustments = AcademicAdjustment::where('exam_id', $examId)

            ->where('course_type', $courseType)

            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

            ->where('subject_id', $subjectId)

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($studentId, fn($q) => $q->where('student_id', $studentId))

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {

                $q->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');

            })

            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {

                $q->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id');

            })

            ->get();

        $adjustmentMarks = [];

        foreach ($academicAdjustments as $adjustment) {

            $adjustmentMarks
                [$adjustment->student_id]
                [$adjustment->marks_type]
                = $adjustment->adjustment_marks;
        }

        /*
        =============================
        STUDENT SUBJECTS
        =============================
        */

        $studentSubjects = StudentSubject::where([
            'subject_id'  => $subjectId,
            'course_type' => $courseType,
            'course_id'   => $courseId,
            'branch_id'   => $branchId,
        ])

        ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

        ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {

            $q->where('semester_id', $semesterId);

        }, function ($q) use ($annualSemesterId) {

            $q->where('annual_semester_id', $annualSemesterId);

        })

        ->pluck('student_id')
        ->toArray();

        /*
        =============================
        VIEW
        =============================
        */

        $view = $courseType == 'Annual Based'
            ? 'backend.retotalling-result.regular-result.result-preview.subjectwise-result.annual-result'
            : 'backend.retotalling-result.regular-result.result-preview.subjectwise-result.result';

        return view(
            $view,
            compact(
                'exam',
                'course',
                'branch',
                'batch',
                'subject',
                'students',
                'midSemMarks',
                'endSemMarks',
                'className',
                'passPercentage',
                'studentSubjects',
                'adjustmentMarks'
            )
        )->render();
    }

    public function overallRegularResultPreviewIndex(){
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.retotalling-result.regular-result.result-preview.overall-result.index', compact('courses', 'exams'));
    }


    public function fetchOverallRegularResultPreview(Request $request)
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
        =============================
        BASIC DATA
        =============================
        */
        $grades = MarksGrade::where('course_id', $courseId)->get();
        $exam   = Exam::find($examId);
        $course = Course::find($courseId);
        $branch = Branch::find($branchId);
        $batch  = Batch::find($batchId);

        /*
        =============================
        PASS %
        =============================
        */
        $passPercentage     = PassPercentage::where('course_id', $courseId)->first();
        $overallPassPercent = $passPercentage->overall_pass_percentage ?? 50;
        $theoryMinPercent   = $passPercentage->theory_min_percentage ?? 20;

        /*
        =============================
        SEM / ANNUAL NAME
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
        SUBJECTS
        =============================
        */
        $subjects = Subject::where('course_id', $courseId)
            ->where('branch_id', $branchId)
            // ->where('is_nc', false)

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                    ->whereNull('annual_semester_id');
            })

            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                $q->where('annual_semester_id', $annualSemesterId)
                    ->whereNull('semester_id');
            })

            ->orderBy('subject_code')
            ->get();

        /*
        =============================
        MID SEM MARKS
        =============================
        */
        $midSemMarks = MidSemMark::where('exam_id', $examId)
            ->where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)
            ->where('is_locked', 1)
            ->where('university_forword', 1)

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                    ->whereNull('annual_semester_id');
            })

            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                $q->where('annual_semester_id', $annualSemesterId)
                    ->whereNull('semester_id');
            })

            ->get()
            ->groupBy(['student_id', 'subject_id', 'marks_type']);

        /*
        =============================
        END SEM MARKS (KEY FIX)
        =============================
        */
        if ($courseType == 'Annual Based') {

            $endSemMarks = EndSemMark::where('exam_id', $examId)
                ->where('is_locked', 1)

                ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

                ->where('annual_semester_id', $annualSemesterId)
                ->whereNull('semester_id')

                ->get()
                ->groupBy(['student_id', 'subject_id', 'marks_type']);

        } else {

            // $endSemMarks = EndSemMark::where('exam_id', $examId)
            //     ->where('is_locked', 1)
            //     ->get()
            //     ->groupBy(['student_id', 'subject_id']);

            $query = EndSemMark::where('exam_id', $examId)
                ->where('is_locked', 1)

                ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

                ->where('semester_id', $semesterId)
                ->whereNull('annual_semester_id');

            if ($course->course == 'Diploma') {

                $endSemMarks = $query
                    ->get()
                    ->groupBy(['student_id', 'subject_id', 'marks_type']);

            } else {

                // Existing UG Logic
                $endSemMarks = $query
                    ->get()
                    ->groupBy(['student_id', 'subject_id']);
            }
        }

        /*
        =============================
        ACADEMIC ADJUSTMENTS
        =============================
        */
        $academicAdjustments = AcademicAdjustment::where('exam_id', $examId)

            ->where('adjustment_scope', 'external')

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                    ->whereNull('annual_semester_id');
            })

            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                $q->where('annual_semester_id', $annualSemesterId)
                    ->whereNull('semester_id');
            })

            ->get()
            ->groupBy(['student_id', 'subject_id', 'marks_type']);

        /*
        =============================
        STUDENT SUBJECTS
        =============================
        */
        $studentSubjects = StudentSubject::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($courseType == 'Semester Based', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                    ->whereNull('annual_semester_id');
            })

            ->when($courseType == 'Annual Based', function ($q) use ($annualSemesterId) {
                $q->where('annual_semester_id', $annualSemesterId)
                    ->whereNull('semester_id');
            })

            ->get()
            ->groupBy('student_id');

        /*
        =============================
        RETOTALLING STUDENTS & SUBJECTS
        =============================
        */
        $retotallingStudents = ReTotallingStudent::with('subjects')

            ->where('exam_id', $examId)
            ->where('result_type', 'regular')
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

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
        =============================
        ONLY RETOTALLING APPLIED STUDENTS
        =============================
        */

        $appliedStudentIds = $retotallingStudents
            ->pluck('student_id')
            ->unique()
            ->values()
            ->toArray();

        $students = Student::where('course_type', $courseType)
            ->where('course_id', $courseId)
            ->where('branch_id', $branchId)
            ->where('batch_id', $batchId)

            ->whereIn('id', $appliedStudentIds)

            ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))

            ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

            ->orderBy('institute_id')
            ->orderBy('university_roll_no')
            ->get();

        /*
        =============================
        PREVIOUS SEMESTER GP SHEET
        =============================
        */

        $previousGpSheets = collect();

        $missingPublishedSemesters = [];

        if ($courseType == 'Semester Based' && $semesterId > 1) {

            // Current semester se pehle ke sab semester
            $requiredPreviousSemesters = range(1, $semesterId - 1);

            // Jitne semester ka result save/publish hua hai
            $publishedSemesters = ResultGpSheet::where('course_type', $courseType)
                ->where('course_id', $courseId)
                ->where('branch_id', $branchId)
                ->where('batch_id', $batchId)

                ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))
                ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

                ->whereIn('semester_id', $requiredPreviousSemesters)

                ->select('semester_id')
                ->distinct()
                ->pluck('semester_id')
                ->toArray();

            // Missing semester find karo
            $missingPublishedSemesters = array_values(array_diff(
                $requiredPreviousSemesters,
                $publishedSemesters
            ));

            // Previous GP Sheet
            $previousGpSheets = ResultGpSheet::with('subject')
                ->where('course_type', $courseType)
                ->where('course_id', $courseId)
                ->where('branch_id', $branchId)
                ->where('batch_id', $batchId)

                ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))
                ->when($instituteId, fn($q) => $q->where('institute_id', $instituteId))

                ->whereIn('semester_id', $requiredPreviousSemesters)

                ->get()
                ->groupBy('student_id');
        }

        /*
        =============================
        VIEW
        =============================
        */

        $view = $courseType == 'Annual Based'
            ? 'backend.retotalling-result.regular-result.result-preview.overall-result.annual-result'
            : 'backend.retotalling-result.regular-result.result-preview.overall-result.result';

        return view(
            $view,
            compact(
                'exam',
                'courseType',
                'course',
                'schemeId',
                'branch',
                'batch',
                'semester',
                'semesterId',
                'annualSemesterId',
                'instituteId',
                'subjects',
                'students',
                'midSemMarks',
                'endSemMarks',
                'overallPassPercent',
                'theoryMinPercent',
                'className',
                'grades',
                'studentSubjects',
                'academicAdjustments',
                'previousGpSheets',
                'missingPublishedSemesters',
                'retotallingStudents'
            )
        )->render();
    }




}
