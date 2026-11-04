<?php

namespace App\Http\Controllers\institute;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Institute;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;

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

        // Determine institute id
        $institute_id = $authUser->account_type === 'institute'
                            ? $authUser->id
                            : $authUser->institute_id;

        /*
        |--------------------------------------------------------------------------
        | Fetch Teachers With Subjects
        |--------------------------------------------------------------------------
        | - Institute login → all teachers
        | - Teacher login → only self
        */

        $query = Institute::with([
            'subjects.course',
            'subjects.branch',
            'subjects.semester',
            'subjects.annual_semester'
        ])
        ->whereHas('subjects', function ($q) use ($institute_id) {
            $q->where('teacher_subjects.institute_id', $institute_id);
        });

        if ($authUser->account_type !== 'institute') {
            $query->where('id', $authUser->id);
        }

        $teachers = $query->get();

        return view('institute.teacher.assign-subjects', compact('teachers'));
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
        $request->validate([
            'teacher_id'   => 'required|exists:institutes,id',
            'subject_ids'  => 'required|array',
            'subject_ids.*'=> 'exists:subjects,id',
        ]);

        $authUser = auth('institute')->user();

        $institute_id = $authUser->account_type === 'institute'
                            ? $authUser->id
                            : $authUser->institute_id;

        $teacher = Institute::findOrFail($request->teacher_id);

        $syncData = [];

        foreach ($request->subject_ids as $subject_id) {
            $syncData[$subject_id] = [
                'institute_id' => $institute_id
            ];
        }

        $teacher->subjects()->sync($syncData);

        return redirect()
                ->route('assign.subject')
                ->with('success', 'Subjects Assigned Successfully');
    }

    public function edit_assign_subject($teacherId)
    {
        $authUser = auth('institute')->user();

        $institute_id = $authUser->account_type === 'institute'
            ? $authUser->id
            : $authUser->institute_id;

        $teacher = Institute::where('id', $teacherId)
            ->where('account_type', 'teacher')
            ->firstOrFail();

        $assignedSubjects = $teacher->subjects()
            ->wherePivot('institute_id', $institute_id)
            ->with(['course','branch','semester','annual_semester'])
            ->get();

        if ($assignedSubjects->isEmpty()) {
            return redirect()->route('assign.subject')
                ->with('error','No subjects assigned to this teacher.');
        }

        $firstSubject = $assignedSubjects->first();

        $courses = Course::whereHas('institutes', function ($q) use ($institute_id) {
            $q->where('institutes.id', $institute_id);
        })->get();

        $teachers = Institute::where('account_type','teacher')
            ->where('institute_id',$institute_id)
            ->get();

        return view('institute.teacher.edit-assign-subjects', [
            'teacher'            => $teacher,
            'courses'            => $courses,
            'teachers'           => $teachers,
            'assignedSubjects'   => $assignedSubjects,
            'selectedCourseType' => $firstSubject->course_type,
            'selectedCourse'     => $firstSubject->course_id,
            'selectedBranch'     => $firstSubject->branch_id,
            'selectedSemester'   => $firstSubject->semester_id,
            'selectedAnnual'     => $firstSubject->annual_semester_id,
        ]);
    }

    public function update_assign_subject(Request $request, $teacherId)
    {
        $request->validate([
            'teacher_id'   => 'required|exists:institutes,id',
            'subject_ids'  => 'required|array',
            'subject_ids.*'=> 'exists:subjects,id',
        ]);

        $authUser = auth('institute')->user();

        $institute_id = $authUser->account_type === 'institute'
            ? $authUser->id
            : $authUser->institute_id;

        $teacher = Institute::findOrFail($request->teacher_id);

        $syncData = [];
        foreach ($request->subject_ids as $subject_id) {
            $syncData[$subject_id] = [
                'institute_id' => $institute_id
            ];
        }

        $teacher->subjects()->sync($syncData);

        return redirect()
            ->route('assign.subject')
            ->with('success','Subjects Updated Successfully');
    }

    public function delete_assign_subject($teacherId)
    {
        $authUser = auth('institute')->user();

        $institute_id = $authUser->account_type === 'institute'
            ? $authUser->id
            : $authUser->institute_id;

        TeacherSubject::where('teacher_id', $teacherId)
            ->where('institute_id', $institute_id)
            ->delete();

        return redirect()
            ->route('assign.subject')
            ->with('success','Assigned Subjects Deleted Successfully');
    }
}
