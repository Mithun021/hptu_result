<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use App\Models\BlockStudent;
use App\Models\Exam;
use App\Models\ExamCenter;
use App\Models\ExamFee;
use App\Models\ExaminationForm;
use App\Models\StudentSubject;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ExaminationFormController extends Controller
{
    // public function index()
    // {
    //     $student = auth('student')->user();

    //     if (!$student) {
    //         abort(403);
    //     }

    //     return view('student.examination-form.index', compact('student'));
    // }



// public function index()
// {
//     $student = auth('student')->user();

//     if (!$student) {
//         abort(403);
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | STUDENT PROFILE DATA
//     |--------------------------------------------------------------------------
//     */

//     $courseId    = $student->course_id;
//     $branchId    = $student->branch_id;
//     $schemeId    = $student->scheme_id;
//     $batchId     = $student->batch_id;
//     $instituteId = $student->institute_id;
//     $semesterId  = $student->semester_id;

//     $currentSemester = $student->semester;

//     /*
//     |--------------------------------------------------------------------------
//     | FIND MATCHING EXAM CENTER ALLOCATION
//     |--------------------------------------------------------------------------
//     */

//     $examCenterQuery = ExamCenter::with([
//         'institute',
//         'course',
//         'branches',
//         'semesters',
//         'batches',
//         'schemes',
//         'colleges',
//     ])
//     ->where('course_id', $courseId)

//     // Mandatory: Match student's branch (NA is also a valid branch)
//     ->whereHas('branches', function ($query) use ($branchId) {
//         $query->where('branches.id', $branchId);
//     })

//     // Mandatory: Match student's batch/session
//     ->whereHas('batches', function ($query) use ($batchId) {
//         $query->where('batches.id', $batchId);
//     })

//     // Mandatory: Match student's current semester
//     ->whereHas('semesters', function ($query) use ($semesterId) {
//         $query->where('semesters.id', $semesterId);
//     })

//     // Mandatory: Match student's scheme
//     ->whereHas('schemes', function ($query) use ($schemeId) {
//         $query->where('schemes.id', $schemeId);
//     });

//     /*
//     |--------------------------------------------------------------------------
//     | MATCH INSTITUTE BASED ON STUDENT TYPE
//     |--------------------------------------------------------------------------
//     */

//     $examCenterQuery->where(function ($query) use ($instituteId) {

//         // Regular: Student institute ID must match
//         $query->where(function ($q) use ($instituteId) {
//             $q->where('student_type', 'Regular')
//               ->where('institute_id', $instituteId);
//         })

//         // Reappear: Institute ID must be NULL
//         ->orWhere(function ($q) {
//             $q->where('student_type', 'Reappear')
//               ->whereNull('institute_id');
//         })

//         // Special Chance: Institute ID must be NULL
//         ->orWhere(function ($q) {
//             $q->where('student_type', 'Special Chance')
//               ->whereNull('institute_id');
//         });

//     });

//     /*
//     |--------------------------------------------------------------------------
//     | GET MATCHED EXAM CENTER
//     |--------------------------------------------------------------------------
//     */

//     $examCenter = $examCenterQuery->first();

//     if (!$examCenter) {
//         return view('student.examination-form.index', compact(
//             'student',
//             'currentSemester',
//             'examCenter'
//         ))->with([
//             'studentType' => null,
//             'studentSemesters' => collect(),
//         ]);
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | STUDENT TYPE
//     |--------------------------------------------------------------------------
//     */

//     $studentType = $examCenter->student_type;

//     /*
//     |--------------------------------------------------------------------------
//     | FILTER MAPPED SEMESTERS UP TO CURRENT SEMESTER
//     |--------------------------------------------------------------------------
//     */

//     $mappedSemesters = $examCenter->semesters
//         ->sortBy('id')
//         ->values();

//     $currentSemesterIndex = $mappedSemesters->search(function ($semester) use ($semesterId) {
//         return (int) $semester->id === (int) $semesterId;
//     });

//     if ($currentSemesterIndex === false) {

//         $studentSemesters = collect();

//     } else {

//         $studentSemesters = $mappedSemesters
//             ->take($currentSemesterIndex + 1)
//             ->values()
//             ->map(function ($semester) use ($studentType, $semesterId) {

//                 /*
//                 |----------------------------------------------------------
//                 | REGULAR STUDENT
//                 |----------------------------------------------------------
//                 | Previous semesters = Reappear
//                 | Current semester   = Regular
//                 */

//                 if ($studentType === 'Regular') {

//                     $semester->exam_type =
//                         ((int) $semester->id === (int) $semesterId)
//                             ? 'Regular'
//                             : 'Reappear';

//                 }

//                 /*
//                 |----------------------------------------------------------
//                 | REAPPEAR STUDENT
//                 |----------------------------------------------------------
//                 */

//                 elseif ($studentType === 'Reappear') {

//                     $semester->exam_type = 'Reappear';

//                 }

//                 /*
//                 |----------------------------------------------------------
//                 | SPECIAL CHANCE STUDENT
//                 |----------------------------------------------------------
//                 */

//                 elseif ($studentType === 'Special Chance') {

//                     $semester->exam_type = 'Special Chance';

//                 }

//                 return $semester;
//             });
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | RETURN VIEW
//     |--------------------------------------------------------------------------
//     */

//     return view('student.examination-form.index', compact(
//         'student',
//         'currentSemester',
//         'examCenter',
//         'studentType',
//         'studentSemesters'
//     ));
// }

    public function index()
    {
        $student = auth('student')->user();

        if (!$student) {
            abort(403);
        }

        // Check Active Examination
        $activeExam = Exam::where('is_active', 1)->first();

        if (!$activeExam) {
            return view('student.examination-form.index', [
                'activeExam' => null,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | STUDENT PROFILE DATA
        |--------------------------------------------------------------------------
        */

        $courseId    = $student->course_id;
        $branchId    = $student->branch_id;
        $schemeId    = $student->scheme_id;
        $batchId     = $student->batch_id;
        $instituteId = $student->institute_id;
        $semesterId  = $student->semester_id;

        $currentSemester = $student->semester;

        /*
        |--------------------------------------------------------------------------
        | FIND MATCHING EXAM CENTER ALLOCATION
        |--------------------------------------------------------------------------
        */

        $examCenterQuery = ExamCenter::with([
            'institute',
            'course',
            'branches',
            'semesters',
            'batches',
            'schemes',
            'colleges',
        ])
        ->where('course_id', $courseId)

        // Mandatory: Branch
        ->whereHas('branches', function ($query) use ($branchId) {
            $query->where('branches.id', $branchId);
        })

        // Mandatory: Batch
        ->whereHas('batches', function ($query) use ($batchId) {
            $query->where('batches.id', $batchId);
        })

        // Mandatory: Current Semester
        ->whereHas('semesters', function ($query) use ($semesterId) {
            $query->where('semesters.id', $semesterId);
        })

        // Mandatory: Scheme
        ->whereHas('schemes', function ($query) use ($schemeId) {
            $query->where('schemes.id', $schemeId);
        });

        /*
        |--------------------------------------------------------------------------
        | MATCH INSTITUTE BASED ON STUDENT TYPE
        |--------------------------------------------------------------------------
        */

        $examCenterQuery->where(function ($query) use ($instituteId) {

            // Regular
            $query->where(function ($q) use ($instituteId) {
                $q->where('student_type', 'Regular')
                ->where('institute_id', $instituteId);
            })

            // Reappear
            ->orWhere(function ($q) {
                $q->where('student_type', 'Reappear')
                ->whereNull('institute_id');
            })

            // Special Chance
            ->orWhere(function ($q) {
                $q->where('student_type', 'Special Chance')
                ->whereNull('institute_id');
            });

        });

        /*
        |--------------------------------------------------------------------------
        | GET MATCHED EXAM CENTER
        |--------------------------------------------------------------------------
        */

        $examCenter = $examCenterQuery->first();

        // Default empty collection
        $examCenters = collect();

        if (!$examCenter) {

            return view('student.examination-form.index', compact(
                'student',
                'currentSemester',
                'examCenter',
                'examCenters'
            ))->with([
                'studentType' => null,
                'studentSemesters' => collect(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | STUDENT TYPE
        |--------------------------------------------------------------------------
        */

        $studentType = $examCenter->student_type;

        /*
        |--------------------------------------------------------------------------
        | FETCH EXAM CENTERS
        |--------------------------------------------------------------------------
        |
        | Exam Center names are stored in institutes table.
        | Fetch institutes mapped in exam_center_colleges.
        |
        */

        $examCenters = $examCenter->colleges;

        /*
        |--------------------------------------------------------------------------
        | FILTER MAPPED SEMESTERS UP TO CURRENT SEMESTER
        |--------------------------------------------------------------------------
        */

        $mappedSemesters = $examCenter->semesters
            ->sortBy('id')
            ->values();

        $currentSemesterIndex = $mappedSemesters->search(function ($semester) use ($semesterId) {
            return (int) $semester->id === (int) $semesterId;
        });

        if ($currentSemesterIndex === false) {

            $studentSemesters = collect();

        } else {

            $studentSemesters = $mappedSemesters
                ->take($currentSemesterIndex + 1)
                ->values()
                ->map(function ($semester) use ($studentType, $semesterId) {

                    if ($studentType === 'Regular') {

                        $semester->exam_type =
                            ((int) $semester->id === (int) $semesterId)
                                ? 'Regular'
                                : 'Reappear';

                    } elseif ($studentType === 'Reappear') {

                        $semester->exam_type = 'Reappear';

                    } elseif ($studentType === 'Special Chance') {

                        $semester->exam_type = 'Special Chance';

                    }

                    return $semester;
                });
        }

        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view('student.examination-form.index', compact(
            'student',
            'currentSemester',
            'examCenter',
            'examCenters',
            'studentType',
            'studentSemesters',
            'activeExam'
        ));
    }


    public function calculateExamFee(Request $request)
    {
        $student = auth('student')->user();

        if (!$student) {
            abort(403);
        }

        $request->validate([
            'exam_type' => 'required|in:Regular,Reappear,Special Chance',
            'semester_id' => 'nullable|integer',
            'annual_semester_id' => 'nullable|integer',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'integer',
        ]);

        $examType = $request->exam_type;

        $today = Carbon::today();

        /*
        |--------------------------------------------------------------------------
        | FIND MATCHING EXAM FEE
        |--------------------------------------------------------------------------
        */

        $examFeeQuery = ExamFee::where('course_id', $student->course_id)

            ->whereHas('schemes', function ($query) use ($student) {
                $query->where('schemes.id', $student->scheme_id);
            })

            ->whereHas('branches', function ($query) use ($student) {
                $query->where('branches.id', $student->branch_id);
            })

            ->whereHas('batches', function ($query) use ($student) {
                $query->where('batches.id', $student->batch_id);
            });

        // Semester Based fee mapping
        if ($student->course_type === 'Semester Based') {

            if (!$request->semester_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please select semester.',
                ], 422);
            }

            $examFeeQuery->whereHas('semesters', function ($query) use ($request) {
                $query->where('semesters.id', $request->semester_id);
            });
        }

        $fee = $examFeeQuery->first();

        if (!$fee) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Examination fee details not found.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | EXAM DATE VALIDATION
        |--------------------------------------------------------------------------
        */

        $examStartDate = $fee->exam_start_date;
        $examEndDate   = $fee->exam_end_date;

        if (
            !$examStartDate ||
            !$examEndDate ||
            !$today->between(
                $examStartDate->copy()->startOfDay(),
                $examEndDate->copy()->endOfDay()
            )
        ) {
            return response()->json([
                'status' => 'closed',
                'message' => 'Form has been closed.',
                'exam_start_date' => $examStartDate
                    ? $examStartDate->format('d-m-Y')
                    : 'Not Available',
                'exam_end_date' => $examEndDate
                    ? $examEndDate->format('d-m-Y')
                    : 'Not Available',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | GET VERIFIED SELECTED SUBJECTS
        |--------------------------------------------------------------------------
        */

        $subjectIds = collect($request->subject_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $verifiedQuery = StudentSubject::with('subject')
            ->where('student_id', $student->id)
            ->where('course_type', $student->course_type)
            ->where('course_id', $student->course_id)
            ->where('branch_id', $student->branch_id)
            ->where('scheme_id', $student->scheme_id);

        if ($student->course_type === 'Semester Based') {
            $verifiedQuery->where('semester_id', $request->semester_id);
        }

        if ($student->course_type === 'Annual Based') {
            $verifiedQuery->where('annual_semester_id', $request->annual_semester_id);
        }

        $verifiedSubjects = $verifiedQuery
            ->whereIn('subject_id', $subjectIds)
            ->get();

        // Reject any subject which is not verified for this student/term.
        if ($verifiedSubjects->count() !== $subjectIds->count()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or unverified subject selected.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | CALCULATE EXAM FEE
        |--------------------------------------------------------------------------
        */

        $examAmount = 0;

        if ($examType === 'Regular') {

            // Regular: Fixed Exam Fee
            $examAmount = (float) $fee->regular_exam_fee;

        } elseif ($examType === 'Reappear') {

            // Count selected Theory and Practical papers
            $theoryCount = $verifiedSubjects->filter(function ($item) {
                return $item->subject && $item->subject->type === 'Theory';
            })->count();

            $practicalCount = $verifiedSubjects->filter(function ($item) {
                return $item->subject && $item->subject->type === 'Practical';
            })->count();

            // Theory Fee
            $theoryAmount = $theoryCount *
                (float) $fee->reappear_theory_exam_fee;

            $theoryMaxAmount = (float) $fee->reappear_theory_max_exam_fee;

            if ($theoryMaxAmount > 0) {
                $theoryAmount = min($theoryAmount, $theoryMaxAmount);
            }

            // Practical Fee
            $practicalAmount = $practicalCount *
                (float) $fee->reappear_practical_exam_fee;

            $practicalMaxAmount = (float) $fee->reappear_practical_max_exam_fee;

            if ($practicalMaxAmount > 0) {
                $practicalAmount = min($practicalAmount, $practicalMaxAmount);
            }

            $examAmount = $theoryAmount + $practicalAmount;

        } elseif ($examType === 'Special Chance') {

            // Special Chance: Only Theory Papers
            $theoryCount = $verifiedSubjects->filter(function ($item) {
                return $item->subject && $item->subject->type === 'Theory';
            })->count();

            $theoryAmount = $theoryCount *
                (float) $fee->special_chance_theory_exam_fee;

            $theoryMaxAmount = (float) $fee->special_chance_theory_max_exam_fee;

            if ($theoryMaxAmount > 0) {
                $theoryAmount = min($theoryAmount, $theoryMaxAmount);
            }

            $examAmount = $theoryAmount;
        }

        /*
        |--------------------------------------------------------------------------
        | LATE FEE CALCULATION
        |--------------------------------------------------------------------------
        */

        $lateAmount = 0;

        $lateFeePeriods = [
            [
                'start' => $fee->late_fee_start1,
                'end' => $fee->late_fee_end1,
                'amount' => $fee->late_fee_amount1,
            ],
            [
                'start' => $fee->late_fee_start2,
                'end' => $fee->late_fee_end2,
                'amount' => $fee->late_fee_amount2,
            ],
            [
                'start' => $fee->late_fee_start3,
                'end' => $fee->late_fee_end3,
                'amount' => $fee->late_fee_amount3,
            ],
        ];

        foreach ($lateFeePeriods as $period) {

            if (
                $period['start'] &&
                $period['end'] &&
                $period['amount'] !== null &&
                $today->between(
                    $period['start']->copy()->startOfDay(),
                    $period['end']->copy()->endOfDay()
                )
            ) {
                $lateAmount = (float) $period['amount'];

                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FINAL AMOUNT
        |--------------------------------------------------------------------------
        */

        $totalAmount = $examAmount + $lateAmount;

        return response()->json([
            'status' => 'success',
            'exam_fee' => round($examAmount, 2),
            'late_fee' => round($lateAmount, 2),
            'total_exam_fee' => round($totalAmount, 2),
        ]);
    }

    public function checkBlockStatus(Request $request)
    {
        $request->validate([
            'semester_id' => 'required|integer',
        ]);

        // Authenticated student
        $student = auth('student')->user();

        // Find active examination
        try {
            $activeExam = Exam::where('is_active', 1)->firstOrFail();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'status' => 'no_active_exam',
                'blocked' => true,
                'message' => 'Currently no active examination is available.'
            ]);
        }

        // Check Overall Block
        $overallBlock = BlockStudent::where('exam_id', $activeExam->id)
            ->where('student_id', $student->id)
            ->where('hold_type', 'overall')
            ->exists();

        if ($overallBlock) {

            return response()->json([
                'status' => 'blocked',
                'blocked' => true,
                'message' => 'You are not eligible! please contact in your college/university administration'
            ]);
        }

        // Check Semester-Wise Block
        $semesterBlock = BlockStudent::where('exam_id', $activeExam->id)
            ->where('student_id', $student->id)
            ->where('hold_type', 'sem-wise')
            ->where('semester_id', $request->semester_id)
            ->exists();

        if ($semesterBlock) {

            return response()->json([
                'status' => 'blocked',
                'blocked' => true,
                'message' => 'You are not eligible! please contact in your college/university administration'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'blocked' => false,
            'message' => 'Student is eligible for examination.'
        ]);
    }

    public function store(Request $request)
    {
        $student = auth('student')->user();

        if (!$student) {
            abort(403);
        }

        // Validate submitted fields
        $validated = $request->validate([

            'exam_type' => 'required|in:Regular,Reappear,Special Chance',

            'semester_id' => 'required|integer|exists:semesters,id',

            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'required|integer|distinct|exists:subjects,id',

            'exam_center_id' => 'required|integer|exists:institutes,id',

            'exam_fee' => 'required|numeric|min:0',
            'late_fee' => 'required|numeric|min:0',
            'total_exam_fee' => 'required|numeric|min:0',

            'last_appearing_session' => 'nullable|string|max:50',
            'last_appearing_year' => 'nullable|string|max:50',

        ]);

        try {

            $examinationForm = DB::transaction(function () use (
                $validated,
                $student
            ) {

                /*
                |--------------------------------------------------------------------------
                | GET ACTIVE EXAM WITH ROW LOCK
                |--------------------------------------------------------------------------
                */

                $activeExam = Exam::where('is_active', 1)
                    ->lockForUpdate()
                    ->first();

                if (!$activeExam) {
                    throw ValidationException::withMessages([
                        'exam' => 'Currently no active examination is available.'
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | CHECK BLOCKED STUDENT
                |--------------------------------------------------------------------------
                */

                $overallBlock = BlockStudent::where('exam_id', $activeExam->id)
                    ->where('student_id', $student->id)
                    ->where('hold_type', 'overall')
                    ->exists();

                $semesterBlock = BlockStudent::where('exam_id', $activeExam->id)
                    ->where('student_id', $student->id)
                    ->where('hold_type', 'sem-wise')
                    ->where('semester_id', $validated['semester_id'])
                    ->exists();

                if ($overallBlock || $semesterBlock) {
                    throw ValidationException::withMessages([
                        'student' => 'You are not eligible! Please contact your college/university administration.'
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | GENERATE FORM ID AND TRANSACTION ID
                |--------------------------------------------------------------------------
                */

                $examMonth = (string) $activeExam->exam_month;
                $examYear  = (string) $activeExam->exam_year;

                // Common prefix
                $prefix = $examMonth . $examYear;

                // Last 4 digits of university roll number
                $rollNumber = (string) $student->university_roll_no;

                $lastFourDigits = substr($rollNumber, -4);

                /*
                * Get previous form IDs for this exam.
                * Active exam row lock ensures simultaneous submissions
                * for this exam are processed one by one.
                */

                $existingForms = ExaminationForm::where(
                    'exam_id',
                    $activeExam->id
                )
                ->where('form_id', 'like', $prefix . '%')
                ->lockForUpdate()
                ->get(['form_id']);

                $lastSerial = 0;

                foreach ($existingForms as $existingForm) {

                    $existingId = (string) $existingForm->form_id;

                    $serialPart = substr(
                        $existingId,
                        strlen($prefix),
                        -4
                    );

                    if (ctype_digit($serialPart)) {
                        $lastSerial = max(
                            $lastSerial,
                            (int) $serialPart
                        );
                    }
                }

                // Next serial number
                $nextSerial = $lastSerial + 1;

                // Minimum 5 digits; automatically expands after 99999
                $serial = str_pad(
                    (string) $nextSerial,
                    5,
                    '0',
                    STR_PAD_LEFT
                );

                // Generate unique IDs
                $formId = $prefix . $serial . $lastFourDigits;

                $transactionId = 'HPTUEXAM' . $prefix . $serial . $lastFourDigits;


                /*
                |--------------------------------------------------------------------------
                | SAVE EXAMINATION FORM
                |--------------------------------------------------------------------------
                */

                $form = ExaminationForm::create([

                    'exam_id' => $activeExam->id,

                    'exam_type' => $validated['exam_type'],

                    'student_id' => $student->id,

                    'course_id' => $student->course_id,

                    'scheme_id' => $student->scheme_id,

                    'branch_id' => $student->branch_id,

                    'semester_id' => $validated['semester_id'],

                    'batch_id' => $student->batch_id,

                    'institute_id' => $student->institute_id,

                    'exam_center_id' => $validated['exam_center_id'],

                    'exam_fee' => $validated['exam_fee'],

                    'late_fee' => $validated['late_fee'],

                    'total_exam_fee' => $validated['total_exam_fee'],

                    'last_appearning_session' =>
                        $validated['last_appearing_session'] ?? null,

                    'last_appearing_year' =>
                        $validated['last_appearing_year'] ?? null,

                    'form_id' => $formId,

                    'transaction_id' => $transactionId,

                    'transaction_date' => null,

                    'payment_status' => 0,

                ]);


                /*
                |--------------------------------------------------------------------------
                | SAVE SELECTED SUBJECTS
                |--------------------------------------------------------------------------
                */

                $subjectData = [];

                foreach ($validated['subject_ids'] as $subjectId) {

                    $subjectData[] = [
                        'subject_id' => $subjectId,
                    ];

                }

                $form->choosedSubjects()->createMany($subjectData);


                return $form;

            }, 5);


            return redirect()
                ->route('student.examination-forms.pending', $examinationForm->id)
                ->with(
                    'success',
                    'Examination form submitted successfully. Your Form ID is ' .
                    $examinationForm->form_id
                );

        } catch (ValidationException $e) {

            throw $e;

        } catch (\Throwable $e) {

            report($e);

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to submit examination form. Please try again.'
                );
        }
    }

    public function filledExamForms()
    {
        $student = auth('student')->user();

        if (!$student) {
            abort(403);
        }

        $examinationForms = ExaminationForm::with([
            'exam',
            'semester',
        ])
        ->where('student_id', $student->id)
        ->latest()
        ->get();

        return view('student.examination-form.filled-exam-forms', compact(
            'student',
            'examinationForms'
        ));
    }


    /**
     * Show pending examination form.
     */
    public function pendingExamForms($id)
    {
        $student = auth('student')->user();

        if (!$student) {
            abort(403);
        }

        $examinationForm = ExaminationForm::with([
            'exam',
            'student',
            'course',
            'scheme',
            'branch',
            'semester',
            'batch',
            'institute',
            'examCenter',
            'choosedSubjects.subject',
        ])
        ->where('id', $id)
        ->where('student_id', $student->id)
        ->where('payment_status', 0)
        ->firstOrFail();

        $feeDetails = $this->calculatePendingFormFee($examinationForm);

        return view('student.examination-form.pending-exam-forms', compact(
            'student',
            'examinationForm',
            'feeDetails'
        ));
    }


    /**
     * Calculate current fee using the same ExamFee mapping
     * and subject-count rules as the initial examination form.
     */

    private function calculatePendingFormFee(ExaminationForm $form)
    {
        $today = Carbon::today();

        $exam = $form->exam;

        /*
        |--------------------------------------------------------------------------
        | FIND MATCHING EXAM FEE CONFIGURATION
        |--------------------------------------------------------------------------
        */

        $feeQuery = ExamFee::with([
            'course',
            'schemes',
            'branches',
            'semesters',
            'batches',
        ])
        ->where('course_id', $form->course_id)
        ->whereHas('schemes', function ($query) use ($form) {
            $query->where('schemes.id', $form->scheme_id);
        })
        ->whereHas('branches', function ($query) use ($form) {
            $query->where('branches.id', $form->branch_id);
        })
        ->whereHas('batches', function ($query) use ($form) {
            $query->where('batches.id', $form->batch_id);
        });

        // Semester mapping is required only for Semester Based courses.
        if ($form->student->course_type === 'Semester Based') {
            $feeQuery->whereHas('semesters', function ($query) use ($form) {
                $query->where('semesters.id', $form->semester_id);
            });
        }

        $examFeeConfig = $feeQuery->first();

        if (!$examFeeConfig) {
            return [
                'available' => false,
                'message' => 'No examination fee structure found for your profile.',
                'is_open' => false,
                'exam_fee' => 0,
                'late_fee' => 0,
                'total_exam_fee' => 0,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | CHECK EXAMINATION FEE WINDOW
        |--------------------------------------------------------------------------
        */

        $examStartDate = $examFeeConfig->exam_start_date
            ? Carbon::parse($examFeeConfig->exam_start_date)->startOfDay()
            : null;

        $examEndDate = $examFeeConfig->exam_end_date
            ? Carbon::parse($examFeeConfig->exam_end_date)->endOfDay()
            : null;

        $isOpen = $exam
            && (int) $exam->is_active === 1
            && $examStartDate
            && $examEndDate
            && $today->betweenIncluded(
                $examStartDate->copy()->startOfDay(),
                $examEndDate->copy()->startOfDay()
            );

        if (!$isOpen) {
            return [
                'available' => true,
                'message' => 'The examination form fee update window is closed.',
                'is_open' => false,
                'exam_fee' => 0,
                'late_fee' => 0,
                'total_exam_fee' => 0,
                'config' => $examFeeConfig,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | GET SELECTED SUBJECTS
        |--------------------------------------------------------------------------
        */

        $subjects = $form->choosedSubjects
            ->pluck('subject')
            ->filter();

        $theoryCount = $subjects->filter(function ($subject) {
            return strtolower(trim($subject->type ?? '')) === 'theory';
        })->count();

        $practicalCount = $subjects->filter(function ($subject) {
            return strtolower(trim($subject->type ?? '')) === 'practical';
        })->count();

        /*
        |--------------------------------------------------------------------------
        | HELPER: APPLY MAXIMUM FEE ONLY WHEN MAXIMUM IS GREATER THAN ZERO
        |--------------------------------------------------------------------------
        */

        $applyMaximum = function ($calculatedFee, $maximumFee) {

            $calculatedFee = (float) $calculatedFee;
            $maximumFee = (float) ($maximumFee ?? 0);

            if ($maximumFee > 0) {
                return min($calculatedFee, $maximumFee);
            }

            // Zero or NULL maximum means no maximum cap.
            return $calculatedFee;
        };

        /*
        |--------------------------------------------------------------------------
        | CALCULATE EXAM FEE
        |--------------------------------------------------------------------------
        */

        $examFee = 0;

        if ($form->exam_type === 'Regular') {

            // Regular: Fixed examination fee.
            $examFee = (float) $examFeeConfig->regular_exam_fee;

        } elseif ($form->exam_type === 'Reappear') {

            // Reappear Theory Fee.
            $theoryFee = $theoryCount *
                (float) $examFeeConfig->reappear_theory_exam_fee;

            $theoryFee = $applyMaximum(
                $theoryFee,
                $examFeeConfig->reappear_theory_max_exam_fee
            );

            // Reappear Practical Fee.
            $practicalFee = $practicalCount *
                (float) $examFeeConfig->reappear_practical_exam_fee;

            $practicalFee = $applyMaximum(
                $practicalFee,
                $examFeeConfig->reappear_practical_max_exam_fee
            );

            $examFee = $theoryFee + $practicalFee;

        } elseif ($form->exam_type === 'Special Chance') {

            // Special Chance: Theory subjects only.
            $theoryFee = $theoryCount *
                (float) $examFeeConfig->special_chance_theory_exam_fee;

            $examFee = $applyMaximum(
                $theoryFee,
                $examFeeConfig->special_chance_theory_max_exam_fee
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CALCULATE LATE FEE
        |--------------------------------------------------------------------------
        */

        $lateFee = 0;

        $latePeriods = [
            [
                'start' => $examFeeConfig->late_fee_start1,
                'end' => $examFeeConfig->late_fee_end1,
                'amount' => $examFeeConfig->late_fee_amount1,
            ],
            [
                'start' => $examFeeConfig->late_fee_start2,
                'end' => $examFeeConfig->late_fee_end2,
                'amount' => $examFeeConfig->late_fee_amount2,
            ],
            [
                'start' => $examFeeConfig->late_fee_start3,
                'end' => $examFeeConfig->late_fee_end3,
                'amount' => $examFeeConfig->late_fee_amount3,
            ],
        ];

        foreach ($latePeriods as $period) {

            if (empty($period['start']) || empty($period['end'])) {
                continue;
            }

            $periodStart = Carbon::parse($period['start'])->startOfDay();
            $periodEnd = Carbon::parse($period['end'])->endOfDay();

            if ($today->betweenIncluded($periodStart, $periodEnd)) {
                $lateFee = (float) ($period['amount'] ?? 0);
                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | RETURN FINAL FEE DETAILS
        |--------------------------------------------------------------------------
        */

        $examFee = round($examFee, 2);
        $lateFee = round($lateFee, 2);

        return [
            'available' => true,
            'message' => null,
            'is_open' => true,

            'exam_fee' => $examFee,
            'late_fee' => $lateFee,
            'total_exam_fee' => round($examFee + $lateFee, 2),

            'theory_count' => $theoryCount,
            'practical_count' => $practicalCount,

            'config' => $examFeeConfig,
        ];
    }



    /**
     * Update examination fee and late fee.
     */

    public function updatePendingExamFee($id)
    {
        $student = auth('student')->user();

        if (!$student) {
            abort(403);
        }

        try {

            DB::transaction(function () use ($id, $student) {

                $form = ExaminationForm::with([
                    'exam',
                    'student',
                    'choosedSubjects.subject',
                ])
                ->where('id', $id)
                ->where('student_id', $student->id)
                ->where('payment_status', 0)
                ->lockForUpdate()
                ->firstOrFail();

                $feeDetails = $this->calculatePendingFormFee($form);

                if (!$feeDetails['available'] || !$feeDetails['is_open']) {
                    throw ValidationException::withMessages([
                        'fee' => $feeDetails['message']
                            ?? 'Examination fee update period is closed.',
                    ]);
                }

                $examFee = (float) $feeDetails['exam_fee'];
                $lateFee = (float) $feeDetails['late_fee'];

                $totalExamFee = $examFee + $lateFee;

                $form->update([
                    'exam_fee'       => $examFee,
                    'late_fee'       => $lateFee,
                    'total_exam_fee' => $totalExamFee,
                ]);
            });

            return redirect()
                ->route('student.examination-forms.payment-proceed', $id)
                ->with('success', 'Examination fee updated successfully.');

        } catch (ValidationException $e) {

            throw $e;

        } catch (\Throwable $e) {

            report($e);

            return back()->with(
                'error',
                'Unable to update examination fee. Please try again.'
            );
        }
    }



    /**
     * Payment proceed page.
     */
    public function paymentProceed($id)
    {
        $student = auth('student')->user();

        if (!$student) {
            abort(403);
        }

        $examinationForm = ExaminationForm::with([
            'exam',
            'student',
        ])
        ->where('id', $id)
        ->where('student_id', $student->id)
        ->where('payment_status', 0)
        ->firstOrFail();

        return view('student.examination-form.payment-proceed', compact(
            'student',
            'examinationForm'
        ));
    }

    public function paidExamForm($id)
    {
        $student = auth('student')->user();

        if (!$student) {
            abort(403);
        }

        $examinationForm = ExaminationForm::with([
            'exam',
            'student',
            'course',
            'scheme',
            'branch',
            'semester',
            'batch',
            'institute',
            'examCenter',
            'choosedSubjects.subject',
        ])
        ->where('id', $id)
        ->where('student_id', $student->id)
        ->where('payment_status', 1)
        ->firstOrFail();

        return view('student.examination-form.paid-exam-forms', compact(
            'student',
            'examinationForm'
        ));
    }

    public function admitCard($id)
    {
        $student = auth('student')->user();

        if (!$student) {
            abort(403);
        }

        $examinationForm = ExaminationForm::with([
            'exam',
            'student',
            'course',
            'scheme',
            'branch',
            'semester',
            'batch',
            'institute',
            'examCenter',
            'choosedSubjects.subject',
        ])
        ->where('id', $id)
        ->where('student_id', $student->id)
        ->where('payment_status', 1)
        ->firstOrFail();

        // Admit card tabhi show hoga jab exam mein admit card enabled ho.
        if (!$examinationForm->exam || !$examinationForm->exam->show_admitcard) {
            abort(404);
        }

        return view('student.examination-form.admit-card', compact(
            'student',
            'examinationForm'
        ));
    }

}

