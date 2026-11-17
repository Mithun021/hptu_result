<?php

namespace App\Http\Controllers\institute;

use App\Http\Controllers\Controller;
use App\Models\AssignTeacherSubject;
use App\Models\Course;
use App\Models\Institute;
use App\Models\Subject;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignSubjectController extends Controller
{
    // public function assign_subject(){
    //     return view('institute.teacher.assign-subjects');
    // }

    // public function assign_subject()
    // {
    //     $authUser = auth('institute')->user();

    //     $institute_id = $authUser->account_type === 'institute'
    //                         ? $authUser->id
    //                         : $authUser->institute_id;

    //     $query = TeacherSubject::with([
    //         'teacher',
    //         'subject.course',
    //         'subject.branch',
    //         'subject.semester',
    //         'subject.annual_semester'
    //     ])
    //     ->where('institute_id', $institute_id);

    //     // If teacher login, show only his data
    //     if ($authUser->account_type !== 'institute') {
    //         $query->where('teacher_id', $authUser->id);
    //     }

    //     $assignments = $query->latest()->get();

    //     return view('institute.teacher.assign-subjects', compact('assignments'));
    // }


    public function assign_subject()
    {
        $authUser = auth('institute')->user();

        /* ================= INSTITUTE ID ================= */

        $institute_id = $authUser->account_type === 'institute'
            ? $authUser->id
            : $authUser->institute_id;

        /* ================= FETCH ASSIGNMENTS ================= */

        $query = AssignTeacherSubject::with([
            'teacher',
            'institute',
            'course',
            'scheme',
            'branch',
            'batch',
            'semester',
            'annualSemester',
            'subjects',
        ])
        ->where('institute_id', $institute_id);

        // Teacher login: only own assignments
        if ($authUser->account_type !== 'institute') {
            $query->where('teacher_id', $authUser->id);
        }

        $assignments = $query->latest()->get();

        return view(
            'institute.teacher.assign-subjects',
            compact('assignments')
        );
    }


    public function create_assign_subject()
    {
        $user = auth('institute')->user();

        $institute_id = $user->account_type === 'institute'
                            ? $user->id
                            : $user->institute_id;

        $teachers = Institute::where('account_type','teacher')->where('institute_id', $institute_id)->get();

        $courses = Course::whereHas('institutes', function ($q) use ($institute_id) {
            $q->where('institutes.id', $institute_id);
        })->get();

        return view('institute.teacher.create-assign-subjects', compact('courses', 'teachers'));
    }

    public function save_assign_subject(Request $request)
    {
        /* ================= VALIDATION ================= */

        $request->validate([
            'course_id'     => 'required|exists:courses,id',
            'scheme_id'     => 'required|exists:schemes,id',
            'branch_id'     => 'required|exists:branches,id',
            'semester_id'   => 'required|exists:semesters,id',
            'batch_id'      => 'required|exists:batches,id',
            'teacher_id'    => 'required|exists:institutes,id',
            'subject_ids'   => 'required|array|min:1',
            'subject_ids.*' => 'required|distinct|exists:subjects,id',
        ]);

        /* ================= AUTH USER ================= */

        $authUser = auth('institute')->user();

        $institute_id = $authUser->account_type === 'institute'
            ? $authUser->id
            : $authUser->institute_id;

        /* ================= VERIFY TEACHER ================= */

        $teacher = Institute::where('id', $request->teacher_id)
            ->where('account_type', 'teacher')
            ->where('institute_id', $institute_id)
            ->firstOrFail();

        /* ================= SAVE ASSIGNMENT ================= */

        DB::transaction(function () use ($request, $teacher, $institute_id) {

            // Parent table: assign_teacher_subjects
            $assignment = AssignTeacherSubject::create([
                'teacher_id'         => $teacher->id,
                'institute_id'       => $institute_id,
                'course_id'          => $request->course_id,
                'scheme_id'          => $request->scheme_id,
                'branch_id'          => $request->branch_id,
                'batch_id'           => $request->batch_id,
                'semester_id'        => $request->semester_id,
                'annual_semester_id' => null,
            ]);

            // Child table: assign_teach_subject_list
            $subjectData = [];

            foreach ($request->subject_ids as $subject_id) {
                $subjectData[] = [
                    'subject_id' => $subject_id,
                ];
            }

            // Using subjectList() relationship from AssignTeacherSubject model
            $assignment->subjectList()->createMany($subjectData);
        });

        /* ================= REDIRECT ================= */

        return redirect()
            ->route('assign.subject')
            ->with('success', 'Subjects Assigned Successfully');
    }


    public function edit_assign_subject($id)
    {
        $authUser = auth('institute')->user();

        $institute_id = $authUser->account_type === 'institute'
            ? $authUser->id
            : $authUser->institute_id;

        // Fetch assignment using assignment ID
        $query = AssignTeacherSubject::with([
            'teacher',
            'course',
            'scheme',
            'branch',
            'batch',
            'semester',
            'annualSemester',
            'subjects',
        ])->where('id', $id)
        ->where('institute_id', $institute_id);

        // Teacher login can access only own assignment
        if ($authUser->account_type !== 'institute') {
            $query->where('teacher_id', $authUser->id);
        }

        $assignment = $query->firstOrFail();

        // Fetch institute courses
        $courses = Course::whereHas('institutes', function ($q) use ($institute_id) {
            $q->where('institutes.id', $institute_id);
        })->get();

        // Existing assigned subject IDs
        $assignedSubjectIds = $assignment->subjects
            ->pluck('id')
            ->values();

        return view('institute.teacher.edit-assign-subjects', [
            'assignment'          => $assignment,
            'teacher'             => $assignment->teacher,
            'courses'             => $courses,
            'assignedSubjectIds'  => $assignedSubjectIds,

            'selectedCourse'      => $assignment->course_id,
            'selectedScheme'      => $assignment->scheme_id,
            'selectedBranch'      => $assignment->branch_id,
            'selectedBatch'       => $assignment->batch_id,
            'selectedSemester'    => $assignment->semester_id,
            'selectedAnnualSemester' => $assignment->annual_semester_id,
        ]);
    }

    public function update_assign_subject(Request $request, $id)
    {
        /* ================= VALIDATION ================= */

        $request->validate([
            'course_id'     => 'required|exists:courses,id',
            'scheme_id'     => 'required|exists:schemes,id',
            'branch_id'     => 'required|exists:branches,id',
            'semester_id'   => 'required|exists:semesters,id',
            'batch_id'      => 'required|exists:batches,id',
            'subject_ids'   => 'required|array|min:1',
            'subject_ids.*' => 'required|distinct|exists:subjects,id',
        ]);

        $authUser = auth('institute')->user();

        $institute_id = $authUser->account_type === 'institute'
            ? $authUser->id
            : $authUser->institute_id;

        /* ================= FETCH ASSIGNMENT ================= */

        $query = AssignTeacherSubject::where('id', $id)
            ->where('institute_id', $institute_id);

        if ($authUser->account_type !== 'institute') {
            $query->where('teacher_id', $authUser->id);
        }

        $assignment = $query->firstOrFail();

        /* ================= VERIFY SUBJECTS ================= */

        $subjectIds = array_map('intval', $request->subject_ids);

        $validSubjects = Subject::whereIn('id', $subjectIds)
            ->where('course_id', $request->course_id)
            ->where('scheme_id', $request->scheme_id)
            ->where('branch_id', $request->branch_id)
            ->where('semester_id', $request->semester_id)
            ->count();

        if ($validSubjects !== count($subjectIds)) {
            return back()
                ->withInput()
                ->withErrors([
                    'subject_ids' => 'One or more selected subjects do not match the selected academic details.',
                ]);
        }

        /* ================= UPDATE ASSIGNMENT ================= */

        DB::transaction(function () use ($request, $assignment, $subjectIds) {

            // Update parent assignment
            $assignment->update([
                'course_id'          => $request->course_id,
                'scheme_id'          => $request->scheme_id,
                'branch_id'          => $request->branch_id,
                'batch_id'           => $request->batch_id,
                'semester_id'        => $request->semester_id,
                'annual_semester_id' => null,
            ]);

            // Remove previous subject records
            $assignment->subjectList()->delete();

            // Insert updated subjects
            $subjectData = [];

            foreach ($subjectIds as $subjectId) {
                $subjectData[] = [
                    'subject_id' => $subjectId,
                ];
            }

            $assignment->subjectList()->createMany($subjectData);
        });

        return redirect()
            ->route('assign.subject')
            ->with('success', 'Subjects Updated Successfully');
    }

    public function delete_assign_subject($id)
    {
        $authUser = auth('institute')->user();

        // Determine Institute ID
        $institute_id = $authUser->account_type === 'institute'
            ? $authUser->id
            : $authUser->institute_id;

        // Fetch Assignment
        $query = AssignTeacherSubject::where('id', $id)
            ->where('institute_id', $institute_id);

        // Teacher can delete only own assignment
        if ($authUser->account_type !== 'institute') {
            $query->where('teacher_id', $authUser->id);
        }

        $assignment = $query->firstOrFail();

        // Delete assignment
        // Child subject records will automatically delete via cascadeOnDelete()
        $assignment->delete();

        return redirect()
            ->route('assign.subject')
            ->with('success', 'Assigned Subjects Deleted Successfully');
    }

}
