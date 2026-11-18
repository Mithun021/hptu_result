<?php

namespace App\Http\Controllers;

use App\Models\MidSemExam;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MidSemExamController extends Controller
{
    public function index()
    {
        $midSemExams = MidSemExam::with('exam')->latest()->get();
        $exams = Exam::all();

        return view('backend.mid_sem_exams.index', compact('midSemExams', 'exams'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_type' => 'required|in:Semester Based,Annual Based',
            'exam_id' => 'required|exists:exams,id',

            'mid_sem_exam_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('mid_sem_exams')
                    ->where(function ($query) use ($request) {
                        return $query->where('exam_id', $request->exam_id)
                            ->where('course_type', $request->course_type);
                    }),
            ],

            'status' => 'required|in:0,1'
        ], [
            'mid_sem_exam_name.unique' => 'This Mid-Sem Exam already exists for selected Exam and Course Type.'
        ]);

        MidSemExam::create([
            'course_type' => $request->course_type,
            'exam_id' => $request->exam_id,
            'mid_sem_exam_name' => $request->mid_sem_exam_name,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Mid-Sem Exam Created Successfully');
    }

    public function edit($id)
    {
        $midSemExam = MidSemExam::findOrFail($id);
        $exams = Exam::all();

        return view('backend.mid_sem_exams.edit', compact('midSemExam', 'exams'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'course_type' => 'required|in:Semester Based,Annual Based',
            'exam_id' => 'required|exists:exams,id',

            'mid_sem_exam_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('mid_sem_exams')
                    ->where(function ($query) use ($request) {
                        return $query->where('exam_id', $request->exam_id)
                            ->where('course_type', $request->course_type);
                    })
                    ->ignore($id)
            ],

            'status' => 'required|in:0,1'
        ], [
            'mid_sem_exam_name.unique' => 'This Mid-Sem Exam already exists for selected Exam and Course Type.'
        ]);

        $midSemExam = MidSemExam::findOrFail($id);

        $midSemExam->update([
            'course_type' => $request->course_type,
            'exam_id' => $request->exam_id,
            'mid_sem_exam_name' => $request->mid_sem_exam_name,
            'status' => $request->status,
        ]);

        return redirect()->route('mid-sem-exams.index')->with('success', 'Updated Successfully');
    }

    public function destroy($id)
    {
        MidSemExam::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Deleted Successfully');
    }

    public function getMidSemExamsCourseBased(Request $request)
    {
        $request->validate([
            'course_type' => 'required|in:Annual Based,Semester Based',
        ]);

        $midSemExams = MidSemExam::where('course_type', $request->course_type)
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->get([
                'id',
                'mid_sem_exam_name'
            ]);

        return response()->json([
            'status' => true,
            'data' => $midSemExams
        ]);
    }
}
