<?php

namespace App\Http\Controllers\institute;

use App\Http\Controllers\Controller;
use App\Models\AnnualSemester;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Scheme;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentSubject;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class StudentSubjectController extends Controller
{
    public function index()
    {
        $user = auth('institute')->user();
        $institute_id = $user->account_type === 'institute'
                            ? $user->id
                            : $user->institute_id;
        $courses = Course::whereHas('institutes', function ($q) use ($institute_id) {
            $q->where('institutes.id', $institute_id);
        })->get();
        return view('institute.student-subject.index', compact('courses'));
    }

    public function create()
    {
        $user = auth('institute')->user();
        $institute_id = $user->account_type === 'institute'
                            ? $user->id
                            : $user->institute_id;
        $courses = Course::whereHas('institutes', function ($q) use ($institute_id) {
            $q->where('institutes.id', $institute_id);
        })->get();
        return view('institute.student-subject.create', compact('courses'));
    }

    // public function render(Request $request)
    // {
        
    //     $student = Student::where('university_roll_no', $request->roll_no)
    //         ->where('course_id', $request->course_id)
    //         ->where('branch_id', $request->branch_id)
    //         ->where('batch_id', $request->batch_id)
    //         ->first();

    //     if (!$student) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Student not found'
    //         ]);
    //     }

    //     $subjectsQuery = Subject::where([
    //         'course_id'  => $request->course_id,
    //         'branch_id'  => $request->branch_id,
    //     ]);

    //     if ($request->scheme_id) {
    //         $subjectsQuery->where('scheme_id', $request->scheme_id);
    //     }

    //     if ($request->course_type === 'Semester Based') {
    //         $subjectsQuery->where('semester_id', $request->semester_id)
    //                     ->whereNull('annual_semester_id');
    //     } else {
    //         $subjectsQuery->where('annual_semester_id', $request->annual_semester_id)
    //                     ->whereNull('semester_id');
    //     }

    //     $subjects = $subjectsQuery->get();

    //     $selectedSubjects = StudentSubject::where([
    //         'student_id' => $student->id,
    //         'course_id'  => $request->course_id,
    //         'branch_id'  => $request->branch_id,
    //     ])
    //     ->pluck('subject_id')
    //     ->toArray();

    //     $html = view('institute.student-subject.student', compact(
    //         'student',
    //         'subjects',
    //         'selectedSubjects',
    //         'request'
    //     ))->render();

    //     return response()->json([
    //         'status' => true,
    //         'html' => $html
    //     ]);
    // }
    
    public function render(Request $request)
    {
        $user = auth('institute')->user();
    
        $instituteId = $user->account_type === 'institute'
            ? $user->id
            : $user->institute_id;
    
        /*
        |--------------------------------------------------------------------------
        | STUDENTS
        |--------------------------------------------------------------------------
        */
    
        $studentsQuery = Student::where('course_type', $request->course_type)
            ->where('course_id', $request->course_id)
            ->where('branch_id', $request->branch_id)
            ->where('batch_id', $request->batch_id)
            ->where('institute_id', $instituteId)
            ->when($request->scheme_id, function ($q) use ($request) {
                $q->where('scheme_id', $request->scheme_id);
            });
    
        // Roll No supplied = Only one student
        // Roll No empty = All students
    
        if (!empty(trim($request->roll_no ?? ''))) {
            $studentsQuery->where(
                'university_roll_no',
                trim($request->roll_no)
            );
        }
    
        $students = $studentsQuery
            ->orderBy('university_roll_no')
            ->get();
    
        if ($students->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No students found.'
            ]);
        }
    
        /*
        |--------------------------------------------------------------------------
        | SUBJECTS
        |--------------------------------------------------------------------------
        */
    
        $subjectsQuery = Subject::where('course_id', $request->course_id)
            ->where('branch_id', $request->branch_id);
    
        if ($request->scheme_id) {
            $subjectsQuery->where('scheme_id', $request->scheme_id);
        }
    
        if ($request->course_type === 'Semester Based') {
    
            $subjectsQuery->where('semester_id', $request->semester_id)
                ->whereNull('annual_semester_id');
    
        } else {
    
            $subjectsQuery->where('annual_semester_id', $request->annual_semester_id)
                ->whereNull('semester_id');
        }
    
        $subjects = $subjectsQuery->get();
    
        /*
        |--------------------------------------------------------------------------
        | SELECTED SUBJECTS OF ALL STUDENTS
        |--------------------------------------------------------------------------
        */
    
        $selectedSubjects = StudentSubject::whereIn(
                'student_id',
                $students->pluck('id')
            )
            ->where('course_type', $request->course_type)
            ->where('course_id', $request->course_id)
            ->where('branch_id', $request->branch_id)
            ->when($request->scheme_id, function ($q) use ($request) {
                $q->where('scheme_id', $request->scheme_id);
            })
            ->when(
                $request->course_type === 'Semester Based',
                function ($q) use ($request) {
                    $q->where('semester_id', $request->semester_id)
                        ->whereNull('annual_semester_id');
                },
                function ($q) use ($request) {
                    $q->where('annual_semester_id', $request->annual_semester_id)
                        ->whereNull('semester_id');
                }
            )
            ->get()
            ->groupBy('student_id')
            ->map(function ($items) {
                return $items->pluck('subject_id')->toArray();
            });
    
        /*
        |--------------------------------------------------------------------------
        | RENDER BLADE
        |--------------------------------------------------------------------------
        */
    
        $html = view('institute.student-subject.student', compact(
            'students',
            'subjects',
            'selectedSubjects'
        ))->render();
    
        return response()->json([
            'status' => true,
            'html' => $html
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'course_type' => 'required',
            'course_id' => 'required',
            'branch_id' => 'required',
            'batch_id' => 'required',
            'student_subjects' => 'required|array',
        ]);
    
        $user = auth('institute')->user();
    
        $instituteId = $user->account_type === 'institute'
            ? $user->id
            : $user->institute_id;
    
        DB::beginTransaction();
    
        try {
    
            /*
            |--------------------------------------------------------------------------
            | FETCH STUDENTS
            |--------------------------------------------------------------------------
            */
    
            $studentsQuery = Student::where('course_type', $request->course_type)
                ->where('course_id', $request->course_id)
                ->where('branch_id', $request->branch_id)
                ->where('batch_id', $request->batch_id)
                ->where('institute_id', $instituteId)
                ->when($request->scheme_id, function ($q) use ($request) {
                    $q->where('scheme_id', $request->scheme_id);
                });
    
            if (!empty(trim($request->roll_no ?? ''))) {
                $studentsQuery->where(
                    'university_roll_no',
                    trim($request->roll_no)
                );
            }
    
            $students = $studentsQuery->get();
    
            if ($students->isEmpty()) {
                throw new \Exception('No students found.');
            }
    
            /*
            |--------------------------------------------------------------------------
            | VALIDATE AVAILABLE SUBJECTS
            |--------------------------------------------------------------------------
            */
    
            $subjectsQuery = Subject::where('course_id', $request->course_id)
                ->where('branch_id', $request->branch_id);
    
            if ($request->scheme_id) {
                $subjectsQuery->where('scheme_id', $request->scheme_id);
            }
    
            if ($request->course_type === 'Semester Based') {
    
                $subjectsQuery->where('semester_id', $request->semester_id)
                    ->whereNull('annual_semester_id');
    
            } else {
    
                $subjectsQuery->where('annual_semester_id', $request->annual_semester_id)
                    ->whereNull('semester_id');
            }
    
            $availableSubjectIds = $subjectsQuery->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
    
            /*
            |--------------------------------------------------------------------------
            | SAVE EACH STUDENT SUBJECT SELECTION
            |--------------------------------------------------------------------------
            */
    
            foreach ($students as $student) {
    
                $selectedSubjects = $request->input(
                    'student_subjects.' . $student->id,
                    []
                );
    
                if (!is_array($selectedSubjects)) {
                    throw new \Exception('Invalid subject selection.');
                }
    
                $selectedSubjects = array_unique(
                    array_map('strval', $selectedSubjects)
                );
    
                // Check submitted subjects belong to selected course/term
                if (!empty(array_diff($selectedSubjects, $availableSubjectIds))) {
                    throw new \Exception('Invalid subject selected for student.');
                }
    
                /*
                |--------------------------------------------------------------------------
                | EXISTING SUBJECTS
                |--------------------------------------------------------------------------
                */
    
                $existingQuery = StudentSubject::where([
                    'student_id' => $student->id,
                    'course_type' => $request->course_type,
                    'course_id' => $request->course_id,
                    'branch_id' => $request->branch_id,
                ])
                ->when($request->scheme_id, function ($q) use ($request) {
                    $q->where('scheme_id', $request->scheme_id);
                });
    
                if ($request->course_type === 'Semester Based') {
    
                    $existingQuery->where('semester_id', $request->semester_id)
                        ->whereNull('annual_semester_id');
    
                } else {
    
                    $existingQuery->where('annual_semester_id', $request->annual_semester_id)
                        ->whereNull('semester_id');
                }
    
                $existingSubjects = $existingQuery->pluck('subject_id')
                    ->map(fn($id) => (string) $id)
                    ->toArray();
    
                /*
                |--------------------------------------------------------------------------
                | INSERT NEW SUBJECTS
                |--------------------------------------------------------------------------
                */
    
                $toInsert = array_diff(
                    $selectedSubjects,
                    $existingSubjects
                );
    
                foreach ($toInsert as $subjectId) {
    
                    StudentSubject::create([
    
                        'student_id' => $student->id,
    
                        'subject_id' => $subjectId,
    
                        'course_type' => $request->course_type,
    
                        'course_id' => $request->course_id,
    
                        'branch_id' => $request->branch_id,
    
                        'scheme_id' => $request->scheme_id,
    
                        'semester_id' => $request->course_type === 'Semester Based'
                            ? $request->semester_id
                            : null,
    
                        'annual_semester_id' => $request->course_type === 'Annual Based'
                            ? $request->annual_semester_id
                            : null,
    
                    ]);
                }
    
                /*
                |--------------------------------------------------------------------------
                | DELETE UNSELECTED SUBJECTS
                |--------------------------------------------------------------------------
                */
    
                $toDelete = array_diff(
                    $existingSubjects,
                    $selectedSubjects
                );
    
                if (!empty($toDelete)) {
    
                    $deleteQuery = StudentSubject::where([
                        'student_id' => $student->id,
                        'course_type' => $request->course_type,
                        'course_id' => $request->course_id,
                        'branch_id' => $request->branch_id,
                    ])
                    ->whereIn('subject_id', $toDelete)
                    ->when($request->scheme_id, function ($q) use ($request) {
                        $q->where('scheme_id', $request->scheme_id);
                    });
    
                    if ($request->course_type === 'Semester Based') {
    
                        $deleteQuery->where('semester_id', $request->semester_id)
                            ->whereNull('annual_semester_id');
    
                    } else {
    
                        $deleteQuery->where('annual_semester_id', $request->annual_semester_id)
                            ->whereNull('semester_id');
                    }
    
                    $deleteQuery->delete();
                }
            }
    
            DB::commit();
    
            return response()->json([
                'status' => true,
                'message' => 'All students subjects updated successfully.'
            ]);
    
        } catch (\Throwable $e) {
    
            DB::rollBack();
    
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }


    // public function store(Request $request)
    // {
    //     DB::beginTransaction();

    //     try {

    //         $student = Student::where('university_roll_no', $request->roll_no)
    //             ->where('course_id', $request->course_id)
    //             ->where('branch_id', $request->branch_id)
    //             ->where('batch_id', $request->batch_id)
    //             ->first();

    //         if (!$student) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Student not found'
    //             ]);
    //         }

    //         $existingSubjects = StudentSubject::where([
    //             'student_id' => $student->id,
    //             'course_id'  => $request->course_id,
    //             'branch_id'  => $request->branch_id,
    //         ])
    //         ->when($request->scheme_id, fn($q) => $q->where('scheme_id', $request->scheme_id))
    //         ->when($request->course_type === 'Semester Based', function ($q) use ($request) {
    //             $q->where('semester_id', $request->semester_id)
    //             ->whereNull('annual_semester_id');
    //         })
    //         ->when($request->course_type === 'Annual Based', function ($q) use ($request) {
    //             $q->where('annual_semester_id', $request->annual_semester_id)
    //             ->whereNull('semester_id');
    //         })
    //         ->pluck('subject_id')
    //         ->toArray();

    //         $selectedSubjects = $request->subjects ?? [];

    //         $toInsert = array_diff($selectedSubjects, $existingSubjects);

    //         foreach ($toInsert as $subjectId) {

    //             StudentSubject::create([
    //                 'student_id'         => $student->id,
    //                 'subject_id'         => $subjectId,
    //                 'course_type'        => $request->course_type,
    //                 'course_id'          => $request->course_id,
    //                 'branch_id'          => $request->branch_id,
    //                 'scheme_id'          => $request->scheme_id,
    //                 'semester_id'        => $request->course_type === 'Semester Based'
    //                                             ? $request->semester_id
    //                                             : null,
    //                 'annual_semester_id' => $request->course_type === 'Annual Based'
    //                                             ? $request->annual_semester_id
    //                                             : null,
    //             ]);
    //         }

    //         $toDelete = array_diff($existingSubjects, $selectedSubjects);

    //         if (!empty($toDelete)) {
    //             StudentSubject::where([
    //                 'student_id' => $student->id,
    //                 'course_id'  => $request->course_id,
    //                 'branch_id'  => $request->branch_id,
    //             ])
    //             ->whereIn('subject_id', $toDelete)
    //             ->when($request->scheme_id, fn($q) => $q->where('scheme_id', $request->scheme_id))
    //             ->when($request->course_type === 'Semester Based', function ($q) use ($request) {
    //                 $q->where('semester_id', $request->semester_id)
    //                 ->whereNull('annual_semester_id');
    //             })
    //             ->when($request->course_type === 'Annual Based', function ($q) use ($request) {
    //                 $q->where('annual_semester_id', $request->annual_semester_id)
    //                 ->whereNull('semester_id');
    //             })
    //             ->delete();
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Subjects updated successfully'
    //         ]);

    //     } catch (\Exception $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ]);
    //     }
    // }

    public function edit($id)
    {
        return view('institute.student-subject.edit');
    }

    // public function list(Request $request)
    // {
    //     $data = DB::table('student_subjects as ss')
    //         ->join('students as s', 's.id', '=', 'ss.student_id')
    //         ->join('courses as c', 'c.id', '=', 'ss.course_id')
    //         ->join('branches as b', 'b.id', '=', 'ss.branch_id')
    //         ->leftJoin('semesters as sem', 'sem.id', '=', 'ss.semester_id')
    //         ->leftJoin('annual_semesters as asem', 'asem.id', '=', 'ss.annual_semester_id')
    //         ->join('subjects as sub', 'sub.id', '=', 'ss.subject_id')

    //         ->select(
    //             'ss.student_id',
    //             's.student_name',
    //             's.university_roll_no',
    //             's.father_name',
    //             'c.name as course_name',
    //             'b.name as branch_name',
    //             DB::raw("COALESCE(sem.name, asem.name) as semester_name"),
    //             DB::raw("
    //                 GROUP_CONCAT(
    //                     CONCAT(sub.name, ' (', sub.subject_code, ')')
    //                     SEPARATOR '||'
    //                 ) as subjects
    //             ")
    //         )

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 🔍 FILTERS
    //         |--------------------------------------------------------------------------
    //         */

    //         ->when($request->roll_no, function ($q) use ($request) {
    //             $q->where('s.university_roll_no', 'like', '%' . $request->roll_no . '%');
    //         })

    //         ->when($request->course_type, fn($q) => $q->where('ss.course_type', $request->course_type))

    //         ->when($request->course_id, fn($q) => $q->where('ss.course_id', $request->course_id))

    //         ->when($request->branch_id, fn($q) => $q->where('ss.branch_id', $request->branch_id))

    //         ->when($request->scheme_id, fn($q) => $q->where('ss.scheme_id', $request->scheme_id))

    //         ->when($request->batch_id, fn($q) => $q->where('s.batch_id', $request->batch_id))

    //         ->when($request->course_type === 'Semester Based' && $request->semester_id, function ($q) use ($request) {
    //             $q->where('ss.semester_id', $request->semester_id)
    //             ->whereNull('ss.annual_semester_id');
    //         })

    //         ->when($request->course_type === 'Annual Based' && $request->annual_semester_id, function ($q) use ($request) {
    //             $q->where('ss.annual_semester_id', $request->annual_semester_id)
    //             ->whereNull('ss.semester_id');
    //         })

    //         ->groupBy(
    //             'ss.student_id',
    //             's.student_name',
    //             's.university_roll_no',
    //             's.father_name',
    //             'c.name',
    //             'b.name',
    //             'semester_name'
    //         );

    //     return DataTables::of($data)
    //         ->addIndexColumn()
    //         ->make(true);
    // }

    public function list(Request $request)
    {
        $institute_id = auth('institute')->user()->account_type === 'institute'
            ? auth('institute')->id()
            : auth('institute')->user()->institute_id;

        $data = DB::table('student_subjects as ss')
            ->join('students as s', 's.id', '=', 'ss.student_id')
            ->join('courses as c', 'c.id', '=', 'ss.course_id')
            ->join('branches as b', 'b.id', '=', 'ss.branch_id')
            ->leftJoin('semesters as sem', 'sem.id', '=', 'ss.semester_id')
            ->leftJoin('annual_semesters as asem', 'asem.id', '=', 'ss.annual_semester_id')
            ->join('subjects as sub', 'sub.id', '=', 'ss.subject_id')

            ->select(
                'ss.student_id',
                's.student_name',
                's.university_roll_no',
                's.father_name',
                'c.name as course_name',
                'b.name as branch_name',
                DB::raw("COALESCE(sem.name, asem.name) as semester_name"),
                DB::raw("
                    GROUP_CONCAT(
                        CONCAT(sub.name, ' (', sub.subject_code, ')')
                        SEPARATOR '||'
                    ) as subjects
                ")
            )

            /*
            |--------------------------------------------------------------------------
            | 🔐 INSTITUTE FILTER (IMPORTANT)
            |--------------------------------------------------------------------------
            */
            ->where('s.institute_id', $institute_id)

            /*
            |--------------------------------------------------------------------------
            | 🔍 FILTERS
            |--------------------------------------------------------------------------
            */
            ->when($request->roll_no, function ($q) use ($request) {
                $q->where('s.university_roll_no', 'like', '%' . $request->roll_no . '%');
            })

            ->when($request->course_type, fn($q) => $q->where('ss.course_type', $request->course_type))

            ->when($request->course_id, fn($q) => $q->where('ss.course_id', $request->course_id))

            ->when($request->branch_id, fn($q) => $q->where('ss.branch_id', $request->branch_id))

            ->when($request->scheme_id, fn($q) => $q->where('ss.scheme_id', $request->scheme_id))

            ->when($request->batch_id, fn($q) => $q->where('s.batch_id', $request->batch_id))

            ->when($request->course_type === 'Semester Based' && $request->semester_id, function ($q) use ($request) {
                $q->where('ss.semester_id', $request->semester_id)
                ->whereNull('ss.annual_semester_id');
            })

            ->when($request->course_type === 'Annual Based' && $request->annual_semester_id, function ($q) use ($request) {
                $q->where('ss.annual_semester_id', $request->annual_semester_id)
                ->whereNull('ss.semester_id');
            })

            ->groupBy(
                'ss.student_id',
                's.student_name',
                's.university_roll_no',
                's.father_name',
                'c.name',
                'b.name',
                'semester_name'
            );

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        DB::beginTransaction();

        try {

            $file = $request->file('file');

            $handle = fopen($file->getRealPath(), 'r');

            if (!$handle) {
                return back()->with('error', 'Unable to read file');
            }

            $header = fgetcsv($handle); // skip header

            while (($row = fgetcsv($handle, 1000, ',')) !== false) {

                /*
                |--------------------------------------------------------------------------
                | 📥 Column Mapping
                |--------------------------------------------------------------------------
                */

                $studentRoll   = trim($row[1] ?? '');
                $subjectCode   = trim($row[3] ?? '');
                $courseType    = trim($row[4] ?? '');
                $courseName    = trim($row[5] ?? '');
                $schemeName    = trim($row[6] ?? '');
                $branchName    = trim($row[7] ?? '');
                $semesterName  = trim($row[8] ?? '');
                $annualName    = trim($row[9] ?? '');

                if (!$studentRoll || !$subjectCode || !$courseName || !$branchName) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | 🎓 Student
                |--------------------------------------------------------------------------
                */
                $student = Student::where('university_roll_no', $studentRoll)->first();
                if (!$student) continue;

                /*
                |--------------------------------------------------------------------------
                | 📚 Course
                |--------------------------------------------------------------------------
                */
                $course = Course::where('name', $courseName)->first();
                if (!$course) continue;

                /*
                |--------------------------------------------------------------------------
                | 🌿 Branch
                |--------------------------------------------------------------------------
                */
                $branch = Branch::where('name', $branchName)
                            ->where('course_id', $course->id)
                            ->first();
                if (!$branch) continue;

                /*
                |--------------------------------------------------------------------------
                | 📘 Scheme (Optional)
                |--------------------------------------------------------------------------
                */
                $schemeId = null;
                if ($schemeName) {
                    $scheme = Scheme::where('name', $schemeName)->first();
                    $schemeId = $scheme?->id;
                }

                /*
                |--------------------------------------------------------------------------
                | 🎯 Semester / Annual
                |--------------------------------------------------------------------------
                */
                $semesterId = null;
                $annualSemesterId = null;

                if ($courseType === 'Semester Based') {

                    $semester = Semester::where('name', $semesterName)->first();

                    if (!$semester) continue;

                    $semesterId = $semester->id;

                } else {

                    $annual = AnnualSemester::where('name', $annualName)->first();

                    if (!$annual) continue;

                    $annualSemesterId = $annual->id;
                }

                /*
                |--------------------------------------------------------------------------
                | 📖 Subject
                |--------------------------------------------------------------------------
                */
                $subject = Subject::where('subject_code', $subjectCode)
                    ->where('course_id', $course->id)
                    ->where('branch_id', $branch->id)
                    ->when($courseType === 'Semester Based', function ($q) use ($semesterId) {
                        $q->where('semester_id', $semesterId)
                        ->whereNull('annual_semester_id');
                    })
                    ->when($courseType === 'Annual Based', function ($q) use ($annualSemesterId) {
                        $q->where('annual_semester_id', $annualSemesterId)
                        ->whereNull('semester_id');
                    })
                    ->first();

                if (!$subject) continue;

                /*
                |--------------------------------------------------------------------------
                | 💾 Insert / Update
                |--------------------------------------------------------------------------
                */
                StudentSubject::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'course_id'  => $course->id,
                        'branch_id'  => $branch->id,
                        'semester_id' => $semesterId,
                        'annual_semester_id' => $annualSemesterId,
                    ],
                    [
                        'course_type' => $courseType,
                        'scheme_id'   => $schemeId,
                    ]
                );
            }

            fclose($handle);

            DB::commit();

            return back()->with('success', 'CSV imported successfully');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }
    
    
    public function exportSample(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'roll_no' => 'nullable|string',
            'course_type' => 'required|in:Semester Based,Annual Based',
            'course_id' => 'required|exists:courses,id',
            'scheme_id' => 'nullable|exists:schemes,id',
            'branch_id' => 'required|exists:branches,id',
            'batch_id' => 'required|exists:batches,id',
    
            'semester_id' => 'nullable|required_if:course_type,Semester Based|exists:semesters,id',
    
            'annual_semester_id' => 'nullable|required_if:course_type,Annual Based|exists:annual_semesters,id',
        ]);
    
        /*
        |--------------------------------------------------------------------------
        | Institute ID
        |--------------------------------------------------------------------------
        */
    
        $user = Auth::guard('institute')->user();
    
        $instituteId = $user->account_type === 'institute'
            ? $user->id
            : $user->institute_id;
    
    
        /*
        |--------------------------------------------------------------------------
        | Fetch Students
        |--------------------------------------------------------------------------
        */
    
        $studentsQuery = Student::query()
            ->where('institute_id', $instituteId)
            ->where('course_type', $request->course_type)
            ->where('course_id', $request->course_id)
            ->where('branch_id', $request->branch_id)
            ->where('batch_id', $request->batch_id);
    
        /*
        |--------------------------------------------------------------------------
        | Optional Roll Number Filter
        |--------------------------------------------------------------------------
        | If roll_no is provided:
        | First find student ID using university_roll_no.
        | Export only that student.
        |
        | If roll_no is empty:
        | Export all matching students.
        */
    
        if ($request->filled('roll_no')) {
    
            $studentId = Student::where('university_roll_no', trim($request->roll_no))
                ->where('institute_id', $instituteId)
                ->value('id');
    
            if (!$studentId) {
                $studentsQuery->whereRaw('1 = 0');
            } else {
                $studentsQuery->where('id', $studentId);
            }
        }
    
        if ($request->filled('scheme_id')) {
            $studentsQuery->where('scheme_id', $request->scheme_id);
        }
    
        if ($request->course_type === 'Semester Based') {
    
            $studentsQuery
                ->where('semester_id', $request->semester_id)
                ->whereNull('annual_semester_id');
    
        } else {
    
            $studentsQuery
                ->where('annual_semester_id', $request->annual_semester_id)
                ->whereNull('semester_id');
        }
    
        $students = $studentsQuery
            ->with([
                'course',
                'scheme',
                'branch',
                'semester',
                'annual_semester',
            ])
            ->orderBy('university_roll_no', 'asc')
            ->get();
    
    
        /*
        |--------------------------------------------------------------------------
        | Fetch All Subjects
        |--------------------------------------------------------------------------
        | Only subjects table is used.
        | No StudentSubject table.
        */
    
        $subjectsQuery = Subject::query()
            ->where('course_type', $request->course_type)
            ->where('course_id', $request->course_id)
            ->where('branch_id', $request->branch_id);
    
        if ($request->filled('scheme_id')) {
            $subjectsQuery->where('scheme_id', $request->scheme_id);
        }
    
        if ($request->course_type === 'Semester Based') {
    
            $subjectsQuery
                ->where('semester_id', $request->semester_id)
                ->whereNull('annual_semester_id');
    
        } else {
    
            $subjectsQuery
                ->where('annual_semester_id', $request->annual_semester_id)
                ->whereNull('semester_id');
        }
    
        $subjects = $subjectsQuery
            ->orderBy('orders', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    
    
        /*
        |--------------------------------------------------------------------------
        | CSV Download
        |--------------------------------------------------------------------------
        */
    
        $filename = 'student_assign_subjects_' . date('Ymd_His') . '.csv';
    
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache',
        ];
    
        return response()->streamDownload(function () use ($students, $subjects, $request) {
    
            $file = fopen('php://output', 'w');
    
            // UTF-8 BOM for Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
    
            // Exact column headings
            fputcsv($file, [
                'id',
                'student_roll_no',
                'subject_name',
                'subject_code',
                'course_type',
                'course_name',
                'scheme_name',
                'branch_name',
                'semester_name',
                'annual_year',
            ]);
    
            $srNo = 1;
    
            /*
            |--------------------------------------------------------------------------
            | Every Student × Every Subject
            |--------------------------------------------------------------------------
            */
    
            foreach ($students as $student) {
    
                foreach ($subjects as $subject) {
    
                    fputcsv($file, [
    
                        // Sequential Sr. No.
                        $srNo++,
    
                        // Student details
                        $student->university_roll_no,
    
                        // Subject details
                        $subject->name,
                        $subject->subject_code,
    
                        // Course details
                        $student->course_type,
                        optional($student->course)->name,
    
                        // Scheme and branch
                        optional($student->scheme)->name,
                        optional($student->branch)->name,
    
                        // Semester Based / Annual Based
                        $request->course_type === 'Semester Based'
                            ? optional($student->semester)->name
                            : '',
    
                        $request->course_type === 'Annual Based'
                            ? optional($student->annual_semester)->name
                            : '',
                    ]);
                }
            }
    
            fclose($file);
    
        }, $filename, $headers);
    }
    

}
