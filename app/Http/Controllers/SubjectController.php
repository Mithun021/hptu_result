<?php

namespace App\Http\Controllers;

use App\Models\AnnualSemester;
use App\Models\AssignTeacherSubject;
use App\Models\AssignTeachSubjectList;
use App\Models\Branch;
use Illuminate\Database\QueryException;
use App\Models\Course;
use App\Models\Scheme;
use App\Models\Semester;
use App\Models\StudentSubject;
use App\Models\Subject;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;
use Exception;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

use function Symfony\Component\Clock\now;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subjects = Subject::with(['course', 'branch', 'semester', 'annual_semester', 'scheme'])
            ->orderBy('course_id', 'asc')
            ->orderBy('branch_id', 'asc')
            ->orderBy('scheme_id', 'asc')
            ->orderBy('semester_id', 'asc')
            ->get();

        return view('backend.subjects.index', [
            'subjects' => $subjects,
            'courses' => Course::all()
        ]);
    }

    public function data(Request $request)
    {
        $subjects = Subject::with(['course', 'branch', 'semester', 'annual_semester', 'scheme'])
            ->select([
                'subjects.id',
                'subjects.course_type',
                'subjects.name',
                'subjects.subject_code',
                'subjects.type',
                'subjects.credits',
                'subjects.internal_marks',
                'subjects.external_marks',
                'subjects.course_id',
                'subjects.branch_id',
                'subjects.scheme_id',
                'subjects.semester_id',
                'subjects.annual_semester_id',
                'subjects.is_nc',
            ]);

        /* ============================
         MANDATORY FILTERS
        ============================*/
        if ($request->filled('course_type')) {
            $subjects->where('subjects.course_type', $request->course_type);
        }

        if ($request->filled('course_id')) {
            $subjects->where('subjects.course_id', $request->course_id);
        }

        /* ============================
         OPTIONAL FILTERS
        ============================*/
        if ($request->filled('scheme_id')) {
            $subjects->where('subjects.scheme_id', $request->scheme_id);
        }

        if ($request->filled('branch_id')) {
            $subjects->where('subjects.branch_id', $request->branch_id);
        }

        if ($request->filled('semester_id')) {
            $subjects->where('subjects.semester_id', $request->semester_id);
        }

        if ($request->filled('annual_semester_id')) {
            $subjects->where('subjects.annual_semester_id', $request->annual_semester_id);
        }

        return DataTables::of($subjects)
            ->addIndexColumn()

            ->addColumn('course', fn($row) => $row->course->name ?? '-')
            ->addColumn('scheme', fn($row) => $row->scheme->name ?? '-')
            ->addColumn('branch', fn($row) => $row->branch->name ?? '-')

            ->addColumn('semester', function ($row) {
                if ($row->course_type === 'Semester Based') {
                    return $row->semester->name ?? '-';
                }

                if ($row->course_type === 'Annual Based') {
                    return $row->annual_semester->name ?? '-';
                }

                return '-';
            })

            ->addColumn('is_nc', function ($row) {
                return (int) $row->is_nc === 1 ? 'Yes' : 'No';
            })

            ->addColumn('action', function ($row) {
                return '
                <div class="dropdown">
                    <button class="btn btn-sm btn-transparent dropdown-toggle" type="button"
                        id="actionDropdown' . $row->id . '" data-bs-toggle="dropdown">
                        <i class="mdi mdi-dots-vertical fs-16"></i>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-start">
                        <li>
                            <a class="dropdown-item" href="' . route('subjects.edit', $row->id) . '">
                                <i class="mdi mdi-pencil-outline me-2"></i> Edit
                            </a>
                        </li>
                        <li>
                            <form action="' . route('subjects.destroy', $row->id) . '" method="POST">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="dropdown-item"
                                    onclick="return confirm(\'Delete this subject?\')">
                                    <i class="mdi mdi-delete-outline me-2"></i> Delete
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>';
            })
            ->rawColumns(['action', 'is_nc'])
            ->make(true);
    }

    // Find Subject based on course type, course_id,scheme_id,branch_id,semester_id
    public function getSubjects(Request $request)
    {
        $query = Subject::query();

        // Required fields
        $query->where('course_id', $request->course_id)
            ->where('branch_id', $request->branch_id);

        // Optional scheme
        if (!empty($request->scheme_id)) {
            $query->where('scheme_id', $request->scheme_id);
        }

        // Based on Course Type
        if ($request->course_type == 'Semester Based') {
            $query->where('semester_id', $request->semester_id);
        }

        if ($request->course_type == 'Annual Based') {
            $query->where('annual_semester_id', $request->annual_semester_id);
        }

        // $subjects = $query->select('id', 'name', 'subject_code','type')->get();
        $subjects = $query
            ->select('id', 'name', 'subject_code', 'type')
            ->orderBy('subject_code', 'asc')
            ->get();

        return response()->json($subjects);
    }

    public function getSubjectsByInstitute(Request $request)
    {
        $user = auth('institute')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated user.',
                'data' => [],
            ], 401);
        }

        $institute_id = $user->account_type === 'institute'
            ? $user->id
            : $user->institute_id;

        // =========================
        // VALIDATION
        // =========================
        $rules = [
            'course_type' => 'required|in:Semester Based,Annual Based',
            'course_id'   => 'required|integer',
            'branch_id'   => 'required|integer',
        ];

        if ($request->course_type == 'Semester Based') {
            $rules['semester_id'] = 'required|integer';
        }

        if ($request->course_type == 'Annual Based') {
            $rules['annual_semester_id'] = 'required|integer';
        }

        // Teacher login requires batch
        if ($user->account_type === 'teacher') {
            $rules['batch_id'] = 'required|integer';
        }

        $validator = \Illuminate\Support\Facades\Validator::make(
            $request->all(),
            $rules
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please select all required fields.',
                'errors'  => $validator->errors(),
                'data'    => [],
            ], 422);
        }

        try {

            // =========================
            // TEACHER ASSIGNED SUBJECT IDS
            // =========================
            $assignedSubjectIds = collect();
            $assignmentCount = 0;
            $batchAssignmentCount = 0;

            if ($user->account_type === 'teacher') {

                $assignmentQuery = AssignTeacherSubject::query()
                    ->where('teacher_id', $user->id)
                    ->where('institute_id', $institute_id)
                    ->where('course_id', $request->course_id)
                    ->where('branch_id', $request->branch_id);

                // Scheme filter
                if ($request->filled('scheme_id')) {
                    $assignmentQuery->where('scheme_id', $request->scheme_id);
                } else {
                    $assignmentQuery->whereNull('scheme_id');
                }

                // Semester Based
                if ($request->course_type === 'Semester Based') {
                    $assignmentQuery->where('semester_id', $request->semester_id)
                        ->whereNull('annual_semester_id');
                }

                // Annual Based
                if ($request->course_type === 'Annual Based') {
                    $assignmentQuery->where('annual_semester_id', $request->annual_semester_id)
                        ->whereNull('semester_id');
                }

                $assignmentCount = (clone $assignmentQuery)->count();

                // Selected batch must be mapped to the assignment
                $batchAssignmentQuery = (clone $assignmentQuery)
                    ->whereHas('batchList', function ($q) use ($request) {
                        $q->where('batch_id', $request->batch_id);
                    });

                $batchAssignmentCount = (clone $batchAssignmentQuery)->count();

                // Get subjects assigned under matching assignment + batch
                $assignedSubjectIds = AssignTeachSubjectList::whereHas(
                    'assignTeacherSubject',
                    function ($q) use ($batchAssignmentQuery) {
                        $q->whereIn('id', $batchAssignmentQuery->select('id'));
                    }
                )->pluck('subject_id')->unique()->values();
            }

            // =========================
            // BASIC SUBJECT QUERY
            // =========================
            $query = Subject::query()
                ->where('course_id', $request->course_id)
                ->where('branch_id', $request->branch_id);

            // Scheme filter
            if ($request->filled('scheme_id')) {
                $query->where('scheme_id', $request->scheme_id);
            } else {
                $query->whereNull('scheme_id');
            }

            // Exclude NC subjects
            $query->where(function ($q) {
                $q->where('is_nc', 0)
                    ->orWhereNull('is_nc');
            });

            // Semester Based
            if ($request->course_type === 'Semester Based') {
                $query->where('semester_id', $request->semester_id)
                    ->whereNull('annual_semester_id');
            }

            // Annual Based
            if ($request->course_type === 'Annual Based') {
                $query->where('annual_semester_id', $request->annual_semester_id)
                    ->whereNull('semester_id');
            }

            $subjectsBeforeAssignmentFilter = (clone $query)->count();

            // Teacher sees only assigned subjects
            if ($user->account_type === 'teacher') {
                $query->whereIn('id', $assignedSubjectIds);
            }

            $subjects = $query
                ->select('id', 'name', 'subject_code')
                ->orderBy('name')
                ->get();

            // =========================
            // RESPONSE
            // =========================
            return response()->json([
                'success' => true,
                'message' => $subjects->isNotEmpty()
                    ? 'Subjects loaded successfully.'
                    : 'No subjects found for the selected filters.',

                'count' => $subjects->count(),
                'data' => $subjects,

                // Temporary debugging information
                'debug' => [
                    'login_type' => $user->account_type,
                    'institute_id' => $institute_id,
                    'teacher_id' => $user->account_type === 'teacher'
                        ? $user->id
                        : null,

                    'filters' => [
                        'course_type' => $request->course_type,
                        'course_id' => $request->course_id,
                        'scheme_id' => $request->scheme_id,
                        'branch_id' => $request->branch_id,
                        'batch_id' => $request->batch_id,
                        'semester_id' => $request->semester_id,
                        'annual_semester_id' => $request->annual_semester_id,
                    ],

                    'matching_assignments' => $assignmentCount,
                    'assignments_with_selected_batch' => $batchAssignmentCount,
                    'assigned_subject_ids' => $assignedSubjectIds->all(),
                    'subjects_before_teacher_assignment_filter' => $subjectsBeforeAssignmentFilter,
                    'final_subject_count' => $subjects->count(),
                ],
            ]);

        } catch (\Throwable $e) {

            \Illuminate\Support\Facades\Log::error(
                'Get Subjects By Institute Error',
                [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'request' => $request->all(),
                    'user_id' => $user->id,
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while loading subjects.',
                'error' => $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    public function getPracticalSubjectsByInstitute(Request $request)
    {
        $user = auth('institute')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated user.',
                'data' => [],
            ], 401);
        }

        $institute_id = $user->account_type === 'institute'
            ? $user->id
            : $user->institute_id;

        // =========================
        // VALIDATION
        // =========================
        $rules = [
            'course_type' => 'required|in:Semester Based,Annual Based',
            'course_id'   => 'required|integer',
            'branch_id'   => 'required|integer',
        ];

        if ($request->course_type == 'Semester Based') {
            $rules['semester_id'] = 'required|integer';
        }

        if ($request->course_type == 'Annual Based') {
            $rules['annual_semester_id'] = 'required|integer';
        }

        // Teacher login requires batch
        if ($user->account_type === 'teacher') {
            $rules['batch_id'] = 'required|integer';
        }

        $validator = \Illuminate\Support\Facades\Validator::make(
            $request->all(),
            $rules
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please select all required fields.',
                'errors'  => $validator->errors(),
                'data'    => [],
            ], 422);
        }

        try {

            // =========================
            // TEACHER ASSIGNED SUBJECT IDS
            // =========================
            $assignedSubjectIds = collect();
            $assignmentCount = 0;
            $batchAssignmentCount = 0;

            if ($user->account_type === 'teacher') {

                $assignmentQuery = AssignTeacherSubject::query()
                    ->where('teacher_id', $user->id)
                    ->where('institute_id', $institute_id)
                    ->where('course_id', $request->course_id)
                    ->where('branch_id', $request->branch_id);

                // Scheme filter
                if ($request->filled('scheme_id')) {
                    $assignmentQuery->where('scheme_id', $request->scheme_id);
                } else {
                    $assignmentQuery->whereNull('scheme_id');
                }

                // Semester Based
                if ($request->course_type === 'Semester Based') {
                    $assignmentQuery->where('semester_id', $request->semester_id)
                        ->whereNull('annual_semester_id');
                }

                // Annual Based
                if ($request->course_type === 'Annual Based') {
                    $assignmentQuery->where('annual_semester_id', $request->annual_semester_id)
                        ->whereNull('semester_id');
                }

                $assignmentCount = (clone $assignmentQuery)->count();

                // Selected batch must be mapped to the assignment
                $batchAssignmentQuery = (clone $assignmentQuery)
                    ->whereHas('batchList', function ($q) use ($request) {
                        $q->where('batch_id', $request->batch_id);
                    });

                $batchAssignmentCount = (clone $batchAssignmentQuery)->count();

                // Get subjects assigned under matching assignment + batch
                $assignedSubjectIds = AssignTeachSubjectList::whereHas(
                    'assignTeacherSubject',
                    function ($q) use ($batchAssignmentQuery) {
                        $q->whereIn('id', $batchAssignmentQuery->select('id'));
                    }
                )->pluck('subject_id')->unique()->values();
            }

            // =========================
            // BASIC SUBJECT QUERY
            // =========================
            $query = Subject::query()
                ->where('course_id', $request->course_id)
                ->where('branch_id', $request->branch_id)
                ->where('type', "Practical");

            // Scheme filter
            if ($request->filled('scheme_id')) {
                $query->where('scheme_id', $request->scheme_id);
            } else {
                $query->whereNull('scheme_id');
            }

            // Exclude NC subjects
            $query->where(function ($q) {
                $q->where('is_nc', 0)
                    ->orWhereNull('is_nc');
            });

            // Semester Based
            if ($request->course_type === 'Semester Based') {
                $query->where('semester_id', $request->semester_id)
                    ->whereNull('annual_semester_id');
            }

            // Annual Based
            if ($request->course_type === 'Annual Based') {
                $query->where('annual_semester_id', $request->annual_semester_id)
                    ->whereNull('semester_id');
            }

            $subjectsBeforeAssignmentFilter = (clone $query)->count();

            // Teacher sees only assigned subjects
            if ($user->account_type === 'teacher') {
                $query->whereIn('id', $assignedSubjectIds);
            }

            $subjects = $query
                ->select('id', 'name', 'subject_code')
                ->orderBy('name')
                ->get();

            // =========================
            // RESPONSE
            // =========================
            return response()->json([
                'success' => true,
                'message' => $subjects->isNotEmpty()
                    ? 'Subjects loaded successfully.'
                    : 'No subjects found for the selected filters.',

                'count' => $subjects->count(),
                'data' => $subjects,

                // Temporary debugging information
                'debug' => [
                    'login_type' => $user->account_type,
                    'institute_id' => $institute_id,
                    'teacher_id' => $user->account_type === 'teacher'
                        ? $user->id
                        : null,

                    'filters' => [
                        'course_type' => $request->course_type,
                        'course_id' => $request->course_id,
                        'scheme_id' => $request->scheme_id,
                        'branch_id' => $request->branch_id,
                        'batch_id' => $request->batch_id,
                        'semester_id' => $request->semester_id,
                        'annual_semester_id' => $request->annual_semester_id,
                    ],

                    'matching_assignments' => $assignmentCount,
                    'assignments_with_selected_batch' => $batchAssignmentCount,
                    'assigned_subject_ids' => $assignedSubjectIds->all(),
                    'subjects_before_teacher_assignment_filter' => $subjectsBeforeAssignmentFilter,
                    'final_subject_count' => $subjects->count(),
                ],
            ]);

        } catch (\Throwable $e) {

            \Illuminate\Support\Facades\Log::error(
                'Get Subjects By Institute Error',
                [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'request' => $request->all(),
                    'user_id' => $user->id,
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while loading subjects.',
                'error' => $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    public function getFilterSubjects(Request $request)
    {
        $subjects = Subject::query()

            ->where('course_id', $request->course_id)
            ->where('branch_id', $request->branch_id)
            ->where(function ($q) {
                $q->where('is_nc', 0)
                    ->orWhereNull('is_nc');
            })

            // scheme optional
            ->when($request->scheme_id, function ($q) use ($request) {
                $q->where('scheme_id', $request->scheme_id);
            })

            // course type condition
            ->when($request->course_type === 'Semester Based', function ($q) use ($request) {
                $q->where('semester_id', $request->semester_id)
                    ->whereNull('annual_semester_id');
            })

            ->when($request->course_type === 'Annual Based', function ($q) use ($request) {
                $q->where('annual_semester_id', $request->annual_semester_id)
                    ->whereNull('semester_id');
            })

            ->select('id', 'name', 'subject_code')
            ->orderBy('name')
            ->get();

        return response()->json($subjects);
    }

    public function getFilterNcSubjects(Request $request)
    {
        $subjects = Subject::query()

            ->where('course_id', $request->course_id)
            ->where('branch_id', $request->branch_id)
            ->where('is_nc', 1)
            // scheme optional
            ->when($request->scheme_id, function ($q) use ($request) {
                $q->where('scheme_id', $request->scheme_id);
            })

            // course type condition
            ->when($request->course_type === 'Semester Based', function ($q) use ($request) {
                $q->where('semester_id', $request->semester_id)
                    ->whereNull('annual_semester_id');
            })

            ->when($request->course_type === 'Annual Based', function ($q) use ($request) {
                $q->where('annual_semester_id', $request->annual_semester_id)
                    ->whereNull('semester_id');
            })

            ->select('id', 'name', 'subject_code')
            ->orderBy('name')
            ->get();

        return response()->json($subjects);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.subjects.create', [
            'courses' => Course::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // dd($request->all());
        $request->validate([
            'course_type' => 'required',
            'course_id' => 'required',
            'branch_id' => 'required',
            'semester_id' => 'required_if:course_type,Semester Based|nullable',
            'annual_semester_id' => 'required_if:course_type,Annual Based|nullable',
            'scheme_id' => 'nullable',
            'name' => 'required',
            'type' => 'required',
            'subject_code' => 'required',
            'credits' => 'required|numeric',
            // 'mid_sem_marks1' => 'required_if:course_type,Semester Based|nullable|numeric',
            // 'mid_sem_marks2' => 'nullable|numeric',
            // 'mid_sem_marks3' => 'nullable|numeric',

            /* ================= ANNUAL BASED ================= */
            // 'internal1' => 'required_if:course_type,Annual Based|nullable|numeric',
            // 'internal2' => 'required_if:course_type,Annual Based|nullable|numeric',
            // 'internal3' => 'required_if:course_type,Annual Based|nullable|numeric',

            // 'paper1' => 'required_if:course_type,Annual Based|nullable|numeric',
            // 'paper2' => 'required_if:course_type,Annual Based|nullable|numeric',

            /* ================= ANNUAL BASED (NOW OPTIONAL) ================= */
            // 'internal1' => 'nullable|numeric',
            // 'internal2' => 'nullable|numeric',
            // 'internal3' => 'nullable|numeric',

            // 'paper1' => 'nullable|numeric',
            // 'paper2' => 'nullable|numeric',

            /* ================= NEW OPTIONAL FIELDS ================= */
            // 'practical_paper1' => 'nullable|numeric',
            // 'practical_paper2' => 'nullable|numeric',

            'is_nc' => 'nullable|in:0,1',

            'internal_marks' => 'required|numeric',
            'external_marks' => 'required|numeric',
            'total' => 'required|numeric',
        ]);

        try {
            Subject::create([
                'course_type' => $request->course_type,
                'course_id' => $request->course_id,
                'branch_id' => $request->branch_id,
                'semester_id' => $request->semester_id,
                'annual_semester_id' => $request->annual_semester_id,
                'scheme_id' => $request->scheme_id,
                'name' => trim($request->name),
                'type' => trim($request->type),
                'subject_code' => trim($request->subject_code),
                'theory_credits' => $request->theory_credits,
                'practical_credits' => $request->practical_credits,
                'credits' => $request->credits,
                // 'mid_sem_marks1' => $request->mid_sem_marks1 ?? 0,
                // 'mid_sem_marks2' => $request->mid_sem_marks2 ?? 0,
                // 'mid_sem_marks3' => $request->mid_sem_marks3 ?? 0,

                /* ===== ANNUAL BASED ===== */
                // 'internal1' => $request->internal1 ?? 0,
                // 'internal2' => $request->internal2 ?? 0,
                // 'internal3' => $request->internal3 ?? 0,

                // 'paper1' => $request->paper1 ?? 0,
                // 'paper2' => $request->paper2 ?? 0,

                /* ===== PRACTICAL ===== */
                // 'practical_paper1' => $request->practical_paper1 ?? 0,
                // 'practical_paper2' => $request->practical_paper2 ?? 0,

                'internal_marks' => $request->internal_marks ?? 0,
                'external_marks' => $request->external_marks ?? 0,
                'total' => $request->total ?? 0,

                'is_nc' => $request->is_nc ?? 0,
            ]);

            return to_route('subjects.index')->with('success', 'Subject created successfully!');
        } catch (QueryException $e) {
            return back()->withInput()->with('error', 'Database error: ' . $e->getMessage());
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(String $id)
    {
        $subject = Subject::with(['course', 'branch', 'semester', 'scheme'])->findOrFail($id);
        return view('backend.subjects.edit', [
            'courses' => Course::all(),
            'subject' => $subject
        ]);
    }

    public function update(Request $request, String $id)
    {
        // dd($request->all());
        $request->validate([
            'course_type' => 'required',
            'course_id' => 'required',
            'branch_id' => 'required',

            'semester_id' => 'required_if:course_type,Semester Based|nullable',
            'annual_semester_id' => 'required_if:course_type,Annual Based|nullable',

            'scheme_id' => 'nullable',

            'name' => 'required',
            'type' => 'required',
            'subject_code' => 'required',

            'theory_credits' => 'nullable|numeric',
            'practical_credits' => 'nullable|numeric',
            'credits' => 'required|numeric',

            /* ===== SEMESTER ===== */
            // 'mid_sem_marks1' => 'required_if:course_type,Semester Based|nullable|numeric',
            // 'mid_sem_marks2' => 'nullable|numeric',
            // 'mid_sem_marks3' => 'nullable|numeric',

            /* ===== ANNUAL ===== */
            // 'internal1' => 'nullable|numeric',
            // 'internal2' => 'nullable|numeric',
            // 'internal3' => 'nullable|numeric',

            // 'paper1' => 'nullable|numeric',
            // 'paper2' => 'nullable|numeric',

            /* ===== PRACTICAL ===== */
            // 'practical_paper1' => 'nullable|numeric',
            // 'practical_paper2' => 'nullable|numeric',

            /* ===== FLAGS ===== */
            'is_nc' => 'nullable|in:0,1',

            'internal_marks' => 'required|numeric',
            'external_marks' => 'required|numeric',
            'total' => 'required|numeric',
        ]);

        try {
            $subject = Subject::findOrFail($id);

            $subject->update([
                'course_type' => $request->course_type,
                'course_id' => $request->course_id,
                'branch_id' => $request->branch_id,
                'semester_id' => $request->semester_id,
                'annual_semester_id' => $request->annual_semester_id,
                'scheme_id' => $request->scheme_id ?: null,

                'name' => trim($request->name),
                'type' => trim($request->type),
                'subject_code' => trim($request->subject_code),
                'theory_credits' => $request->theory_credits,
                'practical_credits' => $request->practical_credits,
                'credits' => $request->credits,

                /* ===== SEMESTER ===== */
                // 'mid_sem_marks1' => $request->mid_sem_marks1 ?? 0,
                // 'mid_sem_marks2' => $request->mid_sem_marks2 ?? 0,
                // 'mid_sem_marks3' => $request->mid_sem_marks3 ?? 0,

                /* ===== ANNUAL ===== */
                // 'internal1' => $request->internal1 ?? 0,
                // 'internal2' => $request->internal2 ?? 0,
                // 'internal3' => $request->internal3 ?? 0,

                // 'paper1' => $request->paper1 ?? 0,
                // 'paper2' => $request->paper2 ?? 0,

                /* ===== PRACTICAL ===== */
                // 'practical_paper1' => $request->practical_paper1 ?? 0,
                // 'practical_paper2' => $request->practical_paper2 ?? 0,

                /* ===== TOTAL ===== */
                'internal_marks' => $request->internal_marks ?? 0,
                'external_marks' => $request->external_marks ?? 0,
                'total' => $request->total ?? 0,

                /* ===== FLAG ===== */
                'is_nc' => $request->is_nc ?? 0,
            ]);

            return to_route('subjects.index')->with('success', 'Subject updated successfully!');
        } catch (QueryException $e) {
            return back()->withInput()->with('error', 'Database error: ' . $e->getMessage());
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $id)
    {
        try {
            $subject = Subject::findOrFail($id);
            if ($subject) {
                Subject::destroy($id);
                return to_route('subjects.index')->with('success', 'Subject Delete successfully!');
            } else {
                return to_route('subjects.index')->with('eroor', 'Semester not found!');
            }
        } catch (QueryException $e) {
            // Handle database errors
            return back()->withInput()->with('error', 'Database error: ' . $e->getMessage());
        } catch (Exception $e) {
            // Handle general errors
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }


    public function importSubject(Request $request)
    {
        // dd($request->all());
        // $request->validate([
        //     'file' => 'required|file|mimes:csv|max:2048',
        // ]);

        try {
            $path = $request->file('file')->getRealPath();

            // Read CSV lines safely
            $rawData = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if (!$rawData || count($rawData) <= 1) {
                return back()->with('error', 'CSV file is empty or invalid.');
            }

            // Convert CSV to array
            $data = array_map('str_getcsv', $rawData);

            // Normalize and extract headers
            $header = array_map(fn($h) => strtolower(trim($h)), array_shift($data));

            $insertCount = 0;

            DB::beginTransaction();

            foreach ($data as $row) {

                if (count(array_filter($row)) === 0) {
                    continue;
                }

                $row = array_combine($header, $row);

                if (!$row) {
                    continue;
                }

                $course = Course::where('name', trim($row['course_id'] ?? ''))->first();

                // dd([
                //     'excel_course_name' => $row['course_id'] ?? null,
                //     'trimmed_course_name' => trim($row['course_id'] ?? ''),
                //     'matched_course' => $course,
                //     'database_course_names' => Course::pluck('name')->toArray(),
                // ]);

                if ($course) {
                    $branch = Branch::where('course_id', $course->id)
                        ->where('name', trim($row['branch_id'] ?? ''))
                        ->first();
                } else {
                    $branch = null;
                }

                $semester = Semester::where('name', trim($row['semester_id'] ?? ''))->first();

                $annualsemester = AnnualSemester::where('name', trim($row['annual_semester_id'] ?? ''))->first();

                $schemeName = trim($row['scheme_id'] ?? '');

                $scheme = !empty($schemeName)
                    ? Scheme::where('name', $schemeName)->first()
                    : null;

                $exists = Subject::where('name', trim($row['name'] ?? ''))
                    ->where('subject_code', trim($row['subject_code'] ?? ''))
                    ->where('course_id', $course?->id)
                    ->where('branch_id', $branch?->id)
                    ->where('semester_id', $semester?->id)
                    ->where('scheme_id', $scheme?->id)
                    ->exists();

                if ($exists) {
                    continue; // Skip duplicate entries
                }

                // $subjectData = [
                //     'course_type'        => 'Semester Based',
                //     'course_id'          => $course?->id,
                //     'branch_id'          => $branch?->id,
                //     'semester_id'        => $semester?->id,
                //     'annual_semester_id' => $annualsemester?->id,
                //     'scheme_id'          => $scheme?->id,

                //     'name'               => trim($row['name'] ?? ''),
                //     'type'               => strtolower(trim($row['type'] ?? 'theory')),
                //     'subject_code'       => trim($row['subject_code'] ?? ''),

                //     'theory_credits'     => trim($row['theory_credits'] ?? ''),
                //     'practical_credits'  => trim($row['practical_credits'] ?? ''),
                //     'credits'            => trim($row['total_credits'] ?? ''),

                //     'internal_marks'     => trim($row['internal_marks'] ?? ''),
                //     'external_marks'     => trim($row['external_marks'] ?? ''),
                //     'total'              => trim($row['total'] ?? ''),

                //     'created_at'         => now(),
                //     'updated_at'         => now(),
                // ];

                // // Print all data before insert
                // echo "<pre>";
                // print_r($subjectData);

                // echo "</pre>";

                // Create new subject
                Subject::create([
                    'course_type'       => 'Semester Based',
                    'course_id'         => $course?->id,
                    'branch_id'         => $branch?->id,
                    'semester_id'       => $semester?->id,
                    'annual_semester_id' => $annualsemester?->id,
                    'scheme_id'         => $scheme?->id,
                    'name'              => trim($row['name'] ?? ''),
                    'type'              => strtolower(trim($row['type'] ?? 'theory')),
                    'subject_code'      => trim($row['subject_code'] ?? ''),
                    'theory_credits'    => trim($row['theory_credits'] ?? ''),
                    'practical_credits' => trim($row['practical_credits'] ?? ''),
                    'credits'           => trim($row['total_credits'] ?? ''),
                    // 'mid_sem_marks1'    => trim($row['mid_sem'] ?? ''),
                    // 'mid_sem_marks2'    => trim($row['quiz'] ?? ''),
                    // 'mid_sem_marks3'    => trim($row['practical'] ?? ''),

                    // 'internal1'         => trim($row['internal1'] ?? ''),
                    // 'internal2'         => trim($row['internal2'] ?? ''),
                    // 'internal3'         => trim($row['internal3'] ?? ''),
                    // 'paper1'            => trim($row['external_paper1'] ?? ''),
                    // 'paper2'            => trim($row['external_paper2'] ?? ''),
                    // 'practical_paper1'  => trim($row['practical_paper1'] ?? ''),
                    // 'practical_paper2'  => trim($row['practical_paper2'] ?? ''),

                    'internal_marks'    => trim($row['internal_marks'] ?? ''),
                    'external_marks'    => trim($row['external_marks'] ?? ''),
                    'total'             => trim($row['total'] ?? ''),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                $insertCount++;
            }

            DB::commit();

            return back()->with('success', "Successfully imported {$insertCount} subjects!");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }



    // public function importSubject(Request $request)
    // {
    //     // dd($request->all());
    //     // $request->validate([
    //     //     'file' => 'required|file|mimes:csv|max:2048',
    //     // ]);

    //     try {
    //         $path = $request->file('file')->getRealPath();

    //         // Read CSV lines safely
    //         $rawData = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    //         if (!$rawData || count($rawData) <= 1) {
    //             return back()->with('error', 'CSV file is empty or invalid.');
    //         }

    //         // Convert CSV to array
    //         $data = array_map('str_getcsv', $rawData);

    //         // Normalize and extract headers
    //         $header = array_map(fn($h) => strtolower(trim($h)), array_shift($data));

    //         $insertCount = 0;

    //         DB::beginTransaction();

    //         foreach ($data as $row) {

    //             if (count(array_filter($row)) === 0) continue;

    //             $row = array_combine($header, $row);
    //             if (!$row) continue;

    //             $course   = Course::where('name', trim($row['course_id'] ?? ''))->first();
    //             // $branch   = Branch::where('name', trim($row['branch_id'] ?? ''))->first();
    //             if ($course) {
    //                 $branch = Branch::where('course_id', $course->id)
    //                     ->where('name', trim($row['branch_id'] ?? ''))
    //                     ->first();
    //             } else {
    //                 $branch = null;
    //             }
    //             $semester = Semester::where('name', trim($row['semester_id'] ?? ''))->first();
    //             $annualsemester = AnnualSemester::where('name', trim($row['annual_semester_id'] ?? ''))->first();
    //             // $scheme   = Scheme::where('name', trim($row['scheme_id'] ?? ''))->first();
    //             $schemeName = trim($row['scheme_id'] ?? '');
    //             $scheme = !empty($schemeName)
    //                 ? Scheme::where('name', $schemeName)->first()
    //                 : null;


    //             $exists = Subject::where('name', trim($row['name'] ?? ''))
    //                 ->where('course_id', $course?->id)
    //                 ->where('semester_id', $semester?->id)
    //                 ->exists();

    //             if ($exists) {
    //                 continue; // skip duplicate entries
    //             }

    //             // Create new subject
    //             Subject::create([
    //                 'course_type'    => 'Semester Based', // Assuming course_type is always 'Semester Based' for import
    //                 'course_id'      => $course?->id,
    //                 'branch_id'      => $branch?->id,
    //                 'semester_id'    => $semester?->id,
    //                 'annual_semester_id'    => $annualsemester?->id,
    //                 'scheme_id'      => $scheme?->id,
    //                 'name'           => trim($row['name'] ?? ''),
    //                 'type'           => strtolower(trim($row['type'] ?? 'theory')),
    //                 'subject_code'   => trim($row['subject_code'] ?? ''),
    //                 'theory_credits' => trim($row['theory_credits'] ?? ''),
    //                 'practical_credits' => trim($row['practical_credits'] ?? ''),
    //                 'credits'        => trim($row['total_credits'] ?? ''),
    //                 'mid_sem_marks1' => trim($row['mid_sem'] ?? ''),
    //                 'mid_sem_marks2' => trim($row['quiz'] ?? ''),
    //                 'mid_sem_marks3' => trim($row['practical'] ?? ''),

    //                 'internal1' => trim($row['internal1'] ?? ''),
    //                 'internal2' => trim($row['internal2'] ?? ''),
    //                 'internal3' => trim($row['internal3'] ?? ''),
    //                 'paper1' => trim($row['external_paper1'] ?? ''),
    //                 'paper2' => trim($row['external_paper2'] ?? ''),
    //                 'practical_paper1' => trim($row['practical_paper1'] ?? ''),
    //                 'practical_paper2' => trim($row['practical_paper2'] ?? ''),

    //                 'internal_marks' => trim($row['internal_marks'] ?? ''),
    //                 'external_marks' => trim($row['external_marks'] ?? ''),
    //                 'total'          => trim($row['total'] ?? ''),
    //                 'created_at'     => now(),
    //                 'updated_at'     => now()
    //             ]);

    //             $insertCount++;
    //         }

    //         DB::commit();

    //         return back()->with('success', "Successfully imported {$insertCount} subjects!");
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->with('error', 'Import failed: ' . $e->getMessage());
    //     }
    // }


    public function fetchSubjects(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'course_id' => 'required|integer',
            'branch_id' => 'required|integer',
            'scheme_id' => 'nullable|integer',
            'semester_id' => 'required|integer',
        ]);

        $subjects = Subject::where('course_id', $request->course_id)
            ->where('branch_id', $request->branch_id)
            ->where('scheme_id', $request->scheme_id)
            ->where('semester_id', $request->semester_id)
            ->select('id', 'name', 'type', 'subject_code')
            ->get();

        return response()->json($subjects);
    }


    public function getFilterTheoryPaperSubjects(Request $request)
    {
        $subjects = Subject::query()

            ->where('course_id', $request->course_id)
            ->where('branch_id', $request->branch_id)

            // Only Theory Subjects
            ->where('type', 'Theory')

            // Exclude NC Subjects
            ->where(function ($q) {
                $q->where('is_nc', 0)
                    ->orWhereNull('is_nc');
            })

            // Scheme optional
            ->when($request->scheme_id, function ($q) use ($request) {
                $q->where('scheme_id', $request->scheme_id);
            })

            // Semester Based condition
            ->when($request->course_type === 'Semester Based', function ($q) use ($request) {
                $q->where('semester_id', $request->semester_id)
                    ->whereNull('annual_semester_id');
            })

            // Annual Based condition
            ->when($request->course_type === 'Annual Based', function ($q) use ($request) {
                $q->where('annual_semester_id', $request->annual_semester_id)
                    ->whereNull('semester_id');
            })

            ->select('id', 'name', 'subject_code')
            ->orderBy('name')
            ->get();

        return response()->json($subjects);
    }



    public function getStudentExamFormVerifiedSubjects(Request $request)
    {
        $student = auth('student')->user();

        if (!$student) {
            abort(403);
        }

        $query = StudentSubject::with([
            'subject:id,name,subject_code,type'
        ])
        ->where('student_id', $student->id)
        ->where('course_type', $student->course_type)
        ->where('course_id', $student->course_id)
        ->where('branch_id', $student->branch_id)
        ->where('scheme_id', $student->scheme_id);

        // Semester Based
        if ($student->course_type == 'Semester Based') {

            $request->validate([
                'semester_id' => 'required|exists:semesters,id',
            ]);

            $query->where('semester_id', $request->semester_id);
        }

        // Annual Based
        if ($student->course_type == 'Annual Based') {

            $request->validate([
                'annual_semester_id' => 'required|exists:annual_semesters,id',
            ]);

            $query->where('annual_semester_id', $request->annual_semester_id);
        }

        $subjects = $query
            ->get()
            ->pluck('subject')
            ->filter()
            ->sort(function ($a, $b) {

                $typeOrder = [
                    'Theory' => 1,
                    'Practical' => 2,
                ];

                $typeComparison = ($typeOrder[$a->type] ?? 3)
                    <=> ($typeOrder[$b->type] ?? 3);

                if ($typeComparison !== 0) {
                    return $typeComparison;
                }

                return strcmp($a->subject_code, $b->subject_code);
            })
            ->values();

        return response()->json($subjects);
    }


}
