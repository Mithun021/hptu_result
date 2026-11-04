<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\ReTotallingStudent;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RetotallingMappedSubjectController extends Controller
{
    public function index()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.retotalling-result.mapped-subjects.index', compact('courses', 'exams'));
    }

    public function fetchStudentsMappedSubjectList(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'result_type' => 'required|in:regular,supplementary',
            'course_type' => 'required|in:Annual Based,Semester Based',
            'course_id' => 'required|exists:courses,id',
            'scheme_id' => 'nullable|exists:schemes,id',
            'branch_id' => 'required|exists:branches,id',
            'batch_id' => 'required|exists:batches,id',
            'institute_id' => 'required|exists:institutes,id',
            'semester_id' => 'required_if:course_type,Semester Based|nullable|exists:semesters,id',
            'annual_semester_id' => 'required_if:course_type,Annual Based|nullable|exists:annual_semesters,id',
            'student_roll_no' => 'nullable|string|max:100',
        ]);

        $query = ReTotallingStudent::with([
            'student',
            'subjects.subject'
        ])
            ->where('re_totalling_student.exam_id', $request->exam_id)
            ->where('re_totalling_student.result_type', $request->result_type)
            ->where('re_totalling_student.course_id', $request->course_id)
            ->where('re_totalling_student.branch_id', $request->branch_id)
            ->where('re_totalling_student.batch_id', $request->batch_id)
            ->where('re_totalling_student.institute_id', $request->institute_id);

        // Scheme filter
       if ($request->filled('scheme_id')) {
            $query->where('re_totalling_student.scheme_id', $request->scheme_id);
        }

        // Semester / Annual filter
        if ($request->course_type == 'Semester Based') {
            $query->where('re_totalling_student.semester_id', $request->semester_id)
                ->whereNull('re_totalling_student.annual_semester_id');
        } else {
            $query->where('re_totalling_student.annual_semester_id', $request->annual_semester_id)
                ->whereNull('re_totalling_student.semester_id');
        }

        // Student Roll Number filter
        if ($request->filled('student_roll_no')) {
            $rollNo = $request->student_roll_no;

            $query->whereHas('student', function ($q) use ($rollNo) {
                $q->where('university_roll_no', $rollNo);
            });
        }

        // Fetch records with numeric roll number sorting
        $students = $query
            ->join('students', 'students.id', '=', 're_totalling_student.student_id')
            ->select('re_totalling_student.*')
            ->orderByRaw("
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(students.university_roll_no, '-', 2), '-', -1) AS UNSIGNED) ASC
            ")
            ->orderByRaw("
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(students.university_roll_no, '-', 4), '-', -1) AS UNSIGNED) ASC
            ")
            ->orderBy('re_totalling_student.id', 'asc')
            ->get();

        return view(
            'backend.retotalling-result.mapped-subjects.student-subject-list',
            compact('students', 'validated')
        );
    }

    public function create()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.retotalling-result.mapped-subjects.create', compact('courses', 'exams'));
    }

    public function fetchStudents(Request $request)
    {
        $validated = $request->validate([
            'student_roll_no' => 'nullable|string|max:100',
            'exam_id' => 'required|exists:exams,id',
            'course_type' => 'required|in:Annual Based,Semester Based',
            'course_id' => 'required|exists:courses,id',
            'scheme_id' => 'nullable|exists:schemes,id',
            'branch_id' => 'required|exists:branches,id',
            'batch_id' => 'required|exists:batches,id',
            'institute_id' => 'required|exists:institutes,id',

            'semester_id' => 'required_if:course_type,Semester Based|nullable|exists:semesters,id',

            'annual_semester_id' => 'required_if:course_type,Annual Based|nullable|exists:annual_semesters,id',

            'result_type' => 'required|in:regular,supplementary',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 1. Fetch Students
        |--------------------------------------------------------------------------
        */

        $studentsQuery = Student::with([
            'course',
            'batch',
            'branch',
            'semester',
            'annual_semester',
        ])
        ->where('course_type', $validated['course_type'])
        ->where('course_id', $validated['course_id'])
        ->where('branch_id', $validated['branch_id'])
        ->where('batch_id', $validated['batch_id'])
        ->where('institute_id', $validated['institute_id']);

        // Optional Scheme Filter
        if (!empty($validated['scheme_id'])) {
            $studentsQuery->where('scheme_id', $validated['scheme_id']);
        }

        // Optional Student Roll Number
        if (!empty($validated['student_roll_no'])) {
            $studentsQuery->where(
                'university_roll_no',
                trim($validated['student_roll_no'])
            );
        }

        $students = $studentsQuery
            ->orderBy('university_roll_no')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 2. Fetch Theory Subjects
        |--------------------------------------------------------------------------
        */

        $subjectsQuery = Subject::where('course_type', $validated['course_type'])
            ->where('course_id', $validated['course_id'])
            ->where('branch_id', $validated['branch_id'])
            ->where('type', 'Theory')
            ->where(function ($query) {
                $query->where('is_nc', 0)
                    ->orWhereNull('is_nc');
            });

        // Optional Scheme Filter
        if (!empty($validated['scheme_id'])) {
            $subjectsQuery->where('scheme_id', $validated['scheme_id']);
        }

        // Course Type Based Semester Filter
        if ($validated['course_type'] === 'Semester Based') {

            $subjectsQuery->where('semester_id', $validated['semester_id'])
                ->whereNull('annual_semester_id');

        } else {

            $subjectsQuery->where('annual_semester_id', $validated['annual_semester_id'])
                ->whereNull('semester_id');
        }

        $subjects = $subjectsQuery
            ->orderBy('orders')
            ->orderBy('id')
            ->get([
                'id',
                'name',
                'subject_code',
            ]);

        /*
        |--------------------------------------------------------------------------
        | 3. Fetch Previously Mapped Subjects
        |--------------------------------------------------------------------------
        */

        $studentIds = $students->pluck('id');

        $mappedRecords = ReTotallingStudent::with('subjects')
            ->whereIn('student_id', $studentIds)
            ->where('exam_id', $validated['exam_id'])
            ->where('result_type', $validated['result_type'])
            ->where('course_id', $validated['course_id'])
            ->where('branch_id', $validated['branch_id'])
            ->where('batch_id', $validated['batch_id'])
            ->where('institute_id', $validated['institute_id'])
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 4. Assign Mapped Subject IDs Student Wise
        |--------------------------------------------------------------------------
        */

        foreach ($students as $student) {

            $mappedRecord = $mappedRecords->first(function ($record) use ($student, $validated) {

                return $record->student_id == $student->id
                    && $record->scheme_id == $student->scheme_id
                    && $record->semester_id == (
                        $validated['course_type'] === 'Semester Based'
                            ? $validated['semester_id']
                            : null
                    )
                    && $record->annual_semester_id == (
                        $validated['course_type'] === 'Annual Based'
                            ? $validated['annual_semester_id']
                            : null
                    );
            });

            // Previously Selected Subject IDs
            $student->mapped_subject_ids = $mappedRecord
                ? $mappedRecord->subjects->pluck('subjects_id')->toArray()
                : [];
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Return Render View
        |--------------------------------------------------------------------------
        */

        return view(
            'backend.retotalling-result.mapped-subjects.students-data',
            compact('students', 'subjects', 'validated')
        );
    }

    public function saveRetotallingSubjects(Request $request)
    {
        $validated = $request->validate([

            'exam_id' => 'required|exists:exams,id',
            'result_type' => 'required|in:regular,supplementary',
            'course_type' => 'required|in:Annual Based,Semester Based',
            'course_id' => 'required|exists:courses,id',
            'scheme_id' => 'nullable|exists:schemes,id',
            'branch_id' => 'required|exists:branches,id',
            'batch_id' => 'required|exists:batches,id',
            'institute_id' => 'required|exists:institutes,id',

            'semester_id' => 'required_if:course_type,Semester Based|nullable|exists:semesters,id',

            'annual_semester_id' => 'required_if:course_type,Annual Based|nullable|exists:annual_semesters,id',

            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|integer|distinct|exists:students,id',

            'students' => 'nullable|array',
            'students.*.subjects' => 'nullable|array',

            // IMPORTANT: Removed distinct
            'students.*.subjects.*' => 'required|integer|exists:subjects,id',

        ]);

        try {

            DB::transaction(function () use ($validated, $request) {

                /*
                |--------------------------------------------------------------------------
                | 1. Fetch Students
                |--------------------------------------------------------------------------
                */

                $students = Student::whereIn('id', $validated['student_ids'])
                    ->where('course_type', $validated['course_type'])
                    ->where('course_id', $validated['course_id'])
                    ->where('branch_id', $validated['branch_id'])
                    ->where('batch_id', $validated['batch_id'])
                    ->where('institute_id', $validated['institute_id'])
                    ->when(
                        !empty($validated['scheme_id']),
                        fn($query) => $query->where(
                            'scheme_id',
                            $validated['scheme_id']
                        )
                    )
                    ->get();

                if ($students->count() !== count($validated['student_ids'])) {

                    throw ValidationException::withMessages([
                        'student_ids' => 'Invalid student selection.',
                    ]);

                }

                /*
                |--------------------------------------------------------------------------
                | 2. Student Wise Subject Sync
                |--------------------------------------------------------------------------
                */

                foreach ($students as $student) {

                    $selectedSubjectIds = $request->input(
                        "students.{$student->id}.subjects",
                        []
                    );

                    $selectedSubjectIds = array_values(
                        array_unique(
                            array_map('intval', $selectedSubjectIds)
                        )
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | 3. Validate Student Wise Subjects
                    |--------------------------------------------------------------------------
                    */

                    $subjectsQuery = Subject::where('course_type', $validated['course_type'])
                        ->where('course_id', $validated['course_id'])
                        ->where('branch_id', $validated['branch_id'])
                        ->where('type', 'Theory')
                        ->where(function ($query) {
                            $query->where('is_nc', 0)
                                ->orWhereNull('is_nc');
                        });

                    // Student's Own Scheme
                    if ($student->scheme_id) {

                        $subjectsQuery->where(
                            'scheme_id',
                            $student->scheme_id
                        );

                    } else {

                        $subjectsQuery->whereNull('scheme_id');

                    }

                    // Semester Filter
                    if ($validated['course_type'] === 'Semester Based') {

                        $subjectsQuery->where(
                            'semester_id',
                            $validated['semester_id']
                        )->whereNull('annual_semester_id');

                    } else {

                        $subjectsQuery->where(
                            'annual_semester_id',
                            $validated['annual_semester_id']
                        )->whereNull('semester_id');

                    }

                    $allowedSubjectIds = $subjectsQuery->pluck('id')->toArray();

                    if (!empty(array_diff(
                        $selectedSubjectIds,
                        $allowedSubjectIds
                    ))) {

                        throw ValidationException::withMessages([
                            'students' => 'Invalid subject selected for student: '
                                . $student->university_roll_no,
                        ]);

                    }

                    /*
                    |--------------------------------------------------------------------------
                    | 4. Find Existing Parent Record
                    |--------------------------------------------------------------------------
                    */

                    $recordQuery = ReTotallingStudent::where(
                        'student_id',
                        $student->id
                    )
                        ->where('exam_id', $validated['exam_id'])
                        ->where('result_type', $validated['result_type'])
                        ->where('course_id', $validated['course_id'])
                        ->where('branch_id', $validated['branch_id'])
                        ->where('batch_id', $validated['batch_id'])
                        ->where('institute_id', $validated['institute_id']);

                    if ($student->scheme_id) {

                        $recordQuery->where(
                            'scheme_id',
                            $student->scheme_id
                        );

                    } else {

                        $recordQuery->whereNull('scheme_id');

                    }

                    if ($validated['course_type'] === 'Semester Based') {

                        $recordQuery->where(
                            'semester_id',
                            $validated['semester_id']
                        )->whereNull('annual_semester_id');

                    } else {

                        $recordQuery->where(
                            'annual_semester_id',
                            $validated['annual_semester_id']
                        )->whereNull('semester_id');

                    }

                    $record = $recordQuery->first();

                    /*
                    |--------------------------------------------------------------------------
                    | 5. No Subjects Selected - Delete Record
                    |--------------------------------------------------------------------------
                    */

                    if (empty($selectedSubjectIds)) {

                        if ($record) {

                            $record->subjects()->delete();

                            $record->delete();

                        }

                        continue;

                    }

                    /*
                    |--------------------------------------------------------------------------
                    | 6. Create Parent If Not Exists
                    |--------------------------------------------------------------------------
                    */

                    if (!$record) {

                        $record = ReTotallingStudent::create([

                            'student_id' => $student->id,
                            'exam_id' => $validated['exam_id'],
                            'result_type' => $validated['result_type'],
                            'course_id' => $validated['course_id'],
                            'scheme_id' => $student->scheme_id,
                            'branch_id' => $validated['branch_id'],
                            'batch_id' => $validated['batch_id'],

                            'semester_id' =>
                                $validated['course_type'] === 'Semester Based'
                                    ? $validated['semester_id']
                                    : null,

                            'annual_semester_id' =>
                                $validated['course_type'] === 'Annual Based'
                                    ? $validated['annual_semester_id']
                                    : null,

                            'institute_id' => $validated['institute_id'],

                            'upload_by' => Auth::id(),

                        ]);

                    }

                    /*
                    |--------------------------------------------------------------------------
                    | 7. Sync Child Subjects
                    |--------------------------------------------------------------------------
                    */

                    // Remove unchecked subjects
                    $record->subjects()
                        ->whereNotIn('subjects_id', $selectedSubjectIds)
                        ->delete();

                    // Get already existing subject IDs
                    $existingSubjectIds = $record->subjects()
                        ->pluck('subjects_id')
                        ->toArray();

                    // Insert only newly selected subjects
                    $newSubjectIds = array_diff(
                        $selectedSubjectIds,
                        $existingSubjectIds
                    );

                    foreach ($newSubjectIds as $subjectId) {

                        $record->subjects()->create([

                            'subjects_id' => $subjectId,

                        ]);

                    }

                }

            });

            return response()->json([

                'status' => true,
                'message' => 'Re-totaling subjects saved successfully!',

            ]);

        } catch (ValidationException $e) {

            throw $e;

        } catch (\Throwable $e) {

            report($e);

            return response()->json([

                'status' => false,
                'message' => 'Unable to save re-totaling subjects. Please try again.',

            ], 500);

        }
    }

    public function deleteMappedSubject($id)
    {
        try {

            DB::transaction(function () use ($id) {

                $record = ReTotallingStudent::findOrFail($id);

                // Delete mapped subjects first
                $record->subjects()->delete();

                // Delete parent record
                $record->delete();

            });

            return response()->json([

                'status' => true,

                'message' => 'Student mapped subjects deleted successfully!',

            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([

                'status' => false,

                'message' => 'Mapped student record not found.',

            ], 404);

        } catch (\Throwable $e) {

            report($e);

            return response()->json([

                'status' => false,

                'message' => 'Unable to delete mapped subjects. Please try again.',

            ], 500);

        }
    }

}
