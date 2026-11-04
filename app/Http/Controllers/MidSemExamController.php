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
            'exam_id' => 'required|exists:exams,id',
            'mid_sem_exam_name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('mid_sem_exams')
                    ->where(function ($query) use ($request) {
                        return $query->where('exam_id', $request->exam_id);
                    }),
            ],
            'status' => 'required'
        ], [
            'mid_sem_exam_name.unique' => 'This Mid-Sem Exam already exists for selected Exam.'
        ]);

        MidSemExam::create($request->all());

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
            'exam_id' => 'required|exists:exams,id',
            'mid_sem_exam_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('mid_sem_exams')
                    ->where(function ($query) use ($request) {
                        return $query->where('exam_id', $request->exam_id);
                    })
                    ->ignore($id) // ignore current record
            ],
            'status' => 'required'
        ], [
            'mid_sem_exam_name.unique' => 'This Mid-Sem Exam already exists for selected Exam.'
        ]);

        $midSemExam = MidSemExam::findOrFail($id);
        $midSemExam->update($request->all());

        return redirect()->route('mid-sem-exams.index')->with('success', 'Updated Successfully');
    }

    public function destroy($id)
    {
        MidSemExam::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Deleted Successfully');
    }
}