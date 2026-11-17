<?php

namespace App\Http\Controllers;

use App\Models\BlockStudent;
use App\Models\Exam;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BlockStudentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $exams = Exam::orderBy('id', 'desc')->get();

        $students = Student::orderBy('student_name', 'asc')->get();

        $semesters = Semester::orderBy('id', 'asc')->get();

        return view('backend.block-students.index', compact(
            'exams',
            'students',
            'semesters'
        ));
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'hold_type' => 'required|in:overall,sem-wise',
            'university_roll_no' => 'required|exists:students,university_roll_no',
            'semester_id' => 'nullable|required_if:hold_type,sem-wise|exists:semesters,id',
        ], [
            'exam_id.required' => 'Please select examination.',
            'university_roll_no.required' => 'Please enter university roll number.',
            'university_roll_no.exists' => 'Student not found with this university roll number.',
            'semester_id.required_if' => 'Please select semester for semester-wise block.',
        ]);

        $student = Student::where(
            'university_roll_no',
            $validated['university_roll_no']
        )->firstOrFail();

        // Overall block: semester will be NULL
        if ($validated['hold_type'] === 'overall') {
            $validated['semester_id'] = null;
        }

        // Check duplicate block
        $alreadyExists = BlockStudent::where('student_id', $student->id)
            ->where('exam_id', $validated['exam_id'])
            ->where('hold_type', $validated['hold_type'])
            ->when(
                $validated['hold_type'] === 'sem-wise',
                function ($query) use ($validated) {
                    $query->where('semester_id', $validated['semester_id']);
                },
                function ($query) {
                    $query->whereNull('semester_id');
                }
            )
            ->exists();

        if ($alreadyExists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'This student is already blocked for the selected examination and semester.');
        }

        BlockStudent::create([
            'exam_id' => $validated['exam_id'],
            'hold_type' => $validated['hold_type'],
            'student_id' => $student->id,
            'semester_id' => $validated['semester_id'] ?? null,
        ]);

        return redirect()
            ->route('block-students.index')
            ->with('success', 'Student blocked successfully.');
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        // dd($id);
        $blockStudent = BlockStudent::with('student')->findOrFail($id);

        $exams = Exam::orderBy('id', 'desc')->get();

        $semesters = Semester::orderBy('id', 'asc')->get();

        return view('backend.block-students.edit', compact(
            'blockStudent',
            'exams',
            'semesters'
        ));
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $blockStudent = BlockStudent::findOrFail($id);

        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'hold_type' => 'required|in:overall,sem-wise',
            'semester_id' => 'nullable|required_if:hold_type,sem-wise|exists:semesters,id',
        ], [
            'exam_id.required' => 'Please select examination.',
            'hold_type.required' => 'Please select hold type.',
            'semester_id.required_if' => 'Please select semester for semester-wise block.',
        ]);

        // Overall hold: semester_id will be NULL
        if ($validated['hold_type'] === 'overall') {
            $validated['semester_id'] = null;
        }

        // Update other fields only.
        // Existing student_id will remain unchanged.
        $blockStudent->update([
            'exam_id' => $validated['exam_id'],
            'hold_type' => $validated['hold_type'],
            'semester_id' => $validated['semester_id'] ?? null,
        ]);

        return redirect()
            ->route('block-students.index')
            ->with('success', 'Block student updated successfully.');
    }


    /*
    |--------------------------------------------------------------------------
    | DATATABLE DATA
    |--------------------------------------------------------------------------
    */

    public function getData()
    {
        $query = BlockStudent::select('block_students.*')
            ->with(['exam', 'student', 'semester'])
            ->orderBy('block_students.id', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('exam_name', function ($row) {
                return $row->exam->name ?? '-';
            })

            ->addColumn('student_details', function ($row) {
                $name = $row->student->student_name ?? '-';
                $rollNo = $row->student->university_roll_no ?? '-';

                return '<strong>' . e($name) . '</strong><br>
                        <small class="text-muted">Roll No: ' . e($rollNo) . '</small>';
            })

            ->addColumn('hold_type', function ($row) {
                return $row->hold_type === 'overall'
                    ? '<span class="badge bg-danger">Overall</span>'
                    : '<span class="badge bg-warning text-dark">Sem-Wise</span>';
            })

            ->addColumn('semester_name', function ($row) {
                return $row->hold_type === 'sem-wise'
                    ? ($row->semester->name ?? '-')
                    : 'All Semesters';
            })

            ->addColumn('action', function ($row) {

                // Explicitly get block_students primary key
                $blockStudentId = $row->id;

                $editUrl = route('block-students.edit', ['id' => $blockStudentId]);
                $deleteUrl = route('block-students.destroy', ['id' => $blockStudentId]);

                return '
                    <a href="' . $editUrl . '" class="btn btn-sm btn-primary">
                        <i class="fa fa-edit"></i> Edit
                    </a>

                    <form action="' . $deleteUrl . '" method="POST"
                        class="d-inline"
                        onsubmit="return confirm(\'Are you sure you want to delete this record?\')">

                        ' . csrf_field() . method_field('DELETE') . '

                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </form>
                ';
            })

            ->rawColumns(['student_details', 'hold_type', 'action'])
            ->make(true);
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $blockStudent = BlockStudent::findOrFail($id);

        $blockStudent->delete();

        return redirect()
            ->route('block-students.index')
            ->with('success', 'Block student deleted successfully.');
    }
}
