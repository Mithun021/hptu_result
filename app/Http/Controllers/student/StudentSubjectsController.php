<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Subject;
use App\Models\StudentSubject;
use App\Models\Semester;
use App\Models\AnnualSemester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentSubjectsController extends Controller
{
    public function index()
    {
        $student = auth('student')->user();

        if (!$student) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | CURRENT STUDENT PROFILE
        |--------------------------------------------------------------------------
        */

        $profile = [
            'id'                 => $student->id,
            'institute_id'       => $student->institute_id,
            'course_type'        => $student->course_type,
            'course_id'          => $student->course_id,
            'branch_id'          => $student->branch_id,
            'semester_id'        => $student->semester_id,
            'annual_semester_id' => $student->annual_semester_id,
            'scheme_id'          => $student->scheme_id,
            'batch_id'           => $student->batch_id,
        ];

        /*
        |--------------------------------------------------------------------------
        | AVAILABLE SUBJECTS
        |--------------------------------------------------------------------------
        */

        $subjectsQuery = Subject::where('course_type', $student->course_type)
            ->where('course_id', $student->course_id)
            ->where('branch_id', $student->branch_id);

        if ($student->scheme_id) {
            $subjectsQuery->where('scheme_id', $student->scheme_id);
        } else {
            $subjectsQuery->whereNull('scheme_id');
        }

        if ($student->course_type === 'Semester Based') {

            $subjectsQuery->where('semester_id', $student->semester_id)
                ->whereNull('annual_semester_id');

        } else {

            $subjectsQuery->where('annual_semester_id', $student->annual_semester_id)
                ->whereNull('semester_id');
        }

        $subjects = $subjectsQuery
            ->orderBy('orders')
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | CHECK WHETHER STUDENT HAS ALREADY SAVED SUBJECTS
        |--------------------------------------------------------------------------
        */

        $currentSubjectsQuery = StudentSubject::where('student_id', $student->id)
            ->where('course_type', $student->course_type)
            ->where('course_id', $student->course_id)
            ->where('branch_id', $student->branch_id);

        if ($student->scheme_id) {
            $currentSubjectsQuery->where('scheme_id', $student->scheme_id);
        } else {
            $currentSubjectsQuery->whereNull('scheme_id');
        }

        if ($student->course_type === 'Semester Based') {

            $currentSubjectsQuery->where('semester_id', $student->semester_id)
                ->whereNull('annual_semester_id');

        } else {

            $currentSubjectsQuery->where('annual_semester_id', $student->annual_semester_id)
                ->whereNull('semester_id');
        }

        $currentSubjects = $currentSubjectsQuery->get();

        $isLocked = $currentSubjects->isNotEmpty();

        $selectedSubjectIds = $currentSubjects
            ->pluck('subject_id')
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | ALL PREVIOUSLY SAVED SUBJECTS
        |--------------------------------------------------------------------------
        */

        $allSubjectsQuery = StudentSubject::with([
                'subject.semester',
                'subject.annual_semester'
            ])
            ->where('student_id', $student->id)
            ->where('course_type', $student->course_type)
            ->where('course_id', $student->course_id)
            ->where('branch_id', $student->branch_id);

        if ($student->scheme_id) {
            $allSubjectsQuery->where('scheme_id', $student->scheme_id);
        } else {
            $allSubjectsQuery->whereNull('scheme_id');
        }

        $allStudentSubjects = $allSubjectsQuery
            ->get()
            ->filter(function ($item) {
                return $item->subject !== null;
            });

        /*
        |--------------------------------------------------------------------------
        | GROUP SUBJECTS SEMESTER / ANNUAL TERM WISE
        |--------------------------------------------------------------------------
        */

        if ($student->course_type === 'Semester Based') {

            $groupedSubjects = $allStudentSubjects
                ->sortBy('semester_id')
                ->groupBy('semester_id');

            $semesterIds = $groupedSubjects->keys();

            $termNames = Semester::whereIn('id', $semesterIds)
                ->get()
                ->keyBy('id');

        } else {

            $groupedSubjects = $allStudentSubjects
                ->sortBy('annual_semester_id')
                ->groupBy('annual_semester_id');

            $semesterIds = $groupedSubjects->keys();

            $termNames = AnnualSemester::whereIn('id', $semesterIds)
                ->get()
                ->keyBy('id');
        }

        return view('student.verify-subjects.index', compact(
            'student',
            'profile',
            'subjects',
            'isLocked',
            'selectedSubjectIds',
            'groupedSubjects',
            'termNames'
        ));
    }

    /**
     * Save Student Subjects - Only Once
     */
    public function store(Request $request)
    {
        $student = auth('student')->user();

        if (!$student) {
            abort(403);
        }

        $request->validate([
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'required|integer|distinct|exists:subjects,id',
        ], [
            'subject_ids.required' => 'Please select at least one subject.',
            'subject_ids.min' => 'Please select at least one subject.',
        ]);

        DB::transaction(function () use ($student, $request) {

            /*
            |--------------------------------------------------------------------------
            | LOCK STUDENT RECORD TO PREVENT SIMULTANEOUS SUBMISSIONS
            |--------------------------------------------------------------------------
            */

            $lockedStudent = Student::where('id', $student->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | CHECK EXISTING SUBMISSION
            |--------------------------------------------------------------------------
            */

            $existingQuery = StudentSubject::where(
                    'student_id',
                    $lockedStudent->id
                )
                ->where('course_type', $lockedStudent->course_type)
                ->where('course_id', $lockedStudent->course_id)
                ->where('branch_id', $lockedStudent->branch_id);

            if ($lockedStudent->scheme_id) {
                $existingQuery->where('scheme_id', $lockedStudent->scheme_id);
            } else {
                $existingQuery->whereNull('scheme_id');
            }

            if ($lockedStudent->course_type === 'Semester Based') {

                $existingQuery->where('semester_id', $lockedStudent->semester_id)
                    ->whereNull('annual_semester_id');

            } else {

                $existingQuery->where('annual_semester_id', $lockedStudent->annual_semester_id)
                    ->whereNull('semester_id');
            }

            if ($existingQuery->exists()) {
                abort(403, 'You have already submitted your subjects. Contact your institute for any correction.');
            }

            /*
            |--------------------------------------------------------------------------
            | VALIDATE SUBJECTS AGAINST STUDENT PROFILE
            |--------------------------------------------------------------------------
            */

            $validSubjectsQuery = Subject::where('course_type', $lockedStudent->course_type)
                ->where('course_id', $lockedStudent->course_id)
                ->where('branch_id', $lockedStudent->branch_id);

            if ($lockedStudent->scheme_id) {
                $validSubjectsQuery->where('scheme_id', $lockedStudent->scheme_id);
            } else {
                $validSubjectsQuery->whereNull('scheme_id');
            }

            if ($lockedStudent->course_type === 'Semester Based') {

                $validSubjectsQuery->where('semester_id', $lockedStudent->semester_id)
                    ->whereNull('annual_semester_id');

            } else {

                $validSubjectsQuery->where('annual_semester_id', $lockedStudent->annual_semester_id)
                    ->whereNull('semester_id');
            }

            $validSubjectIds = $validSubjectsQuery
                ->whereIn('id', $request->subject_ids)
                ->pluck('id')
                ->toArray();

            if (count($validSubjectIds) !== count($request->subject_ids)) {
                abort(422, 'Invalid subject selection. Please refresh the page.');
            }

            /*
            |--------------------------------------------------------------------------
            | SAVE SUBJECTS
            |--------------------------------------------------------------------------
            */

            foreach ($validSubjectIds as $subjectId) {

                StudentSubject::create([
                    'student_id'         => $lockedStudent->id,
                    'subject_id'         => $subjectId,
                    'course_type'        => $lockedStudent->course_type,
                    'course_id'          => $lockedStudent->course_id,
                    'branch_id'          => $lockedStudent->branch_id,
                    'scheme_id'          => $lockedStudent->scheme_id,
                    'semester_id'        => $lockedStudent->course_type === 'Semester Based'
                        ? $lockedStudent->semester_id
                        : null,
                    'annual_semester_id' => $lockedStudent->course_type === 'Annual Based'
                        ? $lockedStudent->annual_semester_id
                        : null,
                ]);
            }
        });

        return redirect()
            ->route('student.verify-subjects.index')
            ->with('success', 'Your subjects have been submitted successfully. Subject selection is now locked.');
    }
}
