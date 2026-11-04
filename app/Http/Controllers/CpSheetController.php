<?php

namespace App\Http\Controllers;

use App\Models\AnnualSemester;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ResultGpSheet;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentResultSummary;
use App\Models\SuppleResultGpSheet;
use App\Models\SuppleResultSummary;
use Illuminate\Http\Request;

class CpSheetController extends Controller
{
    public function endSemIndex()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.cp-sheet.index', compact('courses', 'exams'));
    }
    
    public function fetchRegularCpSheet(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. BASIC VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'exam_id' => 'required',
            'course_type' => 'required',
            'course_id' => 'required',
            'branch_id' => 'required',
            'batch_id' => 'required',
            'institute_id' => 'required',

            'semester_id' => 'required_if:course_type,Semester Based',
            'annual_semester_id' => 'required_if:course_type,Annual Based',

            'scheme_id' => 'nullable',
            'student_roll_number' => 'nullable',
            'print_date' => 'required',
        ]);


        $printDate       = $request->print_date;
        $courseType       = $request->course_type;
        $semesterId       = $request->semester_id;
        $annualSemesterId = $request->annual_semester_id;
        $exam = Exam::find($request->exam_id);


        if ($courseType == "Semester Based") {

            $semester = Semester::find($semesterId);

            $className = $semester
                ? $semester->name
                : '';

        } else {

            $semester = AnnualSemester::find($annualSemesterId);

            $className = $semester
                ? $semester->name
                : '';
        }


        /*
        |--------------------------------------------------------------------------
        | 2. COMMON FILTERS
        |--------------------------------------------------------------------------
        */

        $filters = [
            'exam_id'            => $request->exam_id,
            'course_type'        => $request->course_type,
            'course_id'          => $request->course_id,
            'scheme_id'          => $request->scheme_id,
            'branch_id'          => $request->branch_id,
            'batch_id'           => $request->batch_id,
            'institute_id'       => $request->institute_id,
            'semester_id'        => $request->semester_id,
            'annual_semester_id' => $request->annual_semester_id,
        ];


        /*
        |--------------------------------------------------------------------------
        | 3. FIND STUDENT ID FROM ROLL NUMBER
        |--------------------------------------------------------------------------
        */

        $studentId = null;

        if (!empty($request->student_roll_number)) {

            $student = Student::where(
                'university_roll_no',
                $request->student_roll_number
            )->first();

            if (!$student) {

                return response()->json([
                    'status' => false,
                    'message' => 'Student not found with this roll number.'
                ], 404);
            }

            $studentId = $student->id;
        }


        /*
        |--------------------------------------------------------------------------
        | 4. REGULAR RESULT SUMMARY
        |--------------------------------------------------------------------------
        */

        $regularSummaryQuery = StudentResultSummary::with([
            'student',
            'course',
            'branch',
            'scheme',
            'exam',
            'semester',
            'annualSemester',
            'batch',
            'institute',
        ]);


        $regularSummaryQuery
            ->where('exam_id', $filters['exam_id'])
            ->where('course_type', $filters['course_type'])
            ->where('course_id', $filters['course_id'])
            ->where('branch_id', $filters['branch_id'])
            ->where('batch_id', $filters['batch_id'])
            ->where('institute_id', $filters['institute_id']);


        /*
        |--------------------------------------------------------------------------
        | OPTIONAL SCHEME
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['scheme_id'])) {

            $regularSummaryQuery->where(
                'scheme_id',
                $filters['scheme_id']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SEMESTER / ANNUAL SEMESTER
        |--------------------------------------------------------------------------
        */

        if ($filters['course_type'] === 'Semester Based') {

            $regularSummaryQuery->where(
                'semester_id',
                $filters['semester_id']
            );

        } else {

            $regularSummaryQuery->where(
                'annual_semester_id',
                $filters['annual_semester_id']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | OPTIONAL STUDENT
        |--------------------------------------------------------------------------
        */

        if ($studentId) {

            $regularSummaryQuery->where(
                'student_id',
                $studentId
            );
        }
        
        $regularSummaries = $regularSummaryQuery
            ->get()
            ->sortBy(function ($summary) {
                return $summary->student->university_roll_no ?? '';
            })
            ->keyBy('student_id');


        /*
        |--------------------------------------------------------------------------
        | 5. NO RESULT
        |--------------------------------------------------------------------------
        */

        if ($regularSummaries->isEmpty()) {

            return response()->json([
                'status' => false,
                'message' => 'No regular result found for the selected filters.'
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | 6. STUDENT IDS
        |--------------------------------------------------------------------------
        */

        $studentIds = $regularSummaries
            ->keys()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | 7. PREVIOUS SEMESTERS
        |--------------------------------------------------------------------------
        */

        $previousSemesterIds   = collect();
        $previousSemesterNames = collect();


        if ($filters['course_type'] === 'Semester Based') {

            $romanToNumber = [

                'I'    => 1,
                'II'   => 2,
                'III'  => 3,
                'IV'   => 4,
                'V'    => 5,
                'VI'   => 6,
                'VII'  => 7,
                'VIII' => 8,
                'IX'   => 9,
                'X'    => 10,
                'XI'   => 11,
                'XII'  => 12,

            ];


            $currentSemester = Semester::find(
                $filters['semester_id']
            );


            if ($currentSemester) {

                $currentSemesterName = strtoupper(
                    trim($currentSemester->name)
                );


                $currentSemesterNumber =
                    $romanToNumber[$currentSemesterName] ?? null;


                if (
                    $currentSemesterNumber
                    &&
                    $currentSemesterNumber > 1
                ) {

                    $previousSemesterNumber =
                        $currentSemesterNumber - 1;


                    $allSemesters = Semester::get();


                    foreach ($allSemesters as $semester) {

                        $semesterName = strtoupper(
                            trim($semester->name)
                        );


                        $semesterNumber =
                            $romanToNumber[$semesterName] ?? null;


                        if (
                            $semesterNumber
                            &&
                            $semesterNumber >= 1
                            &&
                            $semesterNumber <= $previousSemesterNumber
                        ) {

                            $previousSemesterIds->push(
                                $semester->id
                            );

                            $previousSemesterNames->put(
                                $semester->id,
                                $semester->name
                            );
                        }
                    }


                    $previousSemesterIds =
                        $previousSemesterIds
                            ->sort(function ($a, $b) use (
                                $previousSemesterNames,
                                $romanToNumber
                            ) {

                                $nameA = strtoupper(
                                    trim(
                                        $previousSemesterNames->get($a)
                                    )
                                );

                                $nameB = strtoupper(
                                    trim(
                                        $previousSemesterNames->get($b)
                                    )
                                );


                                $numberA =
                                    $romanToNumber[$nameA] ?? 999;

                                $numberB =
                                    $romanToNumber[$nameB] ?? 999;


                                return $numberA <=> $numberB;
                            })
                            ->values();
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 8. FETCH REGULAR GP SHEET
        |--------------------------------------------------------------------------
        */

        $regularGpQuery = ResultGpSheet::with([
            'student',
            'subject',
            'course',
            'branch',
            'scheme',
            'exam',
            'semester',
            'annualSemester',
            'batch',
            'institute',
        ])
            ->whereIn('student_id', $studentIds)
            ->where('exam_id', $filters['exam_id'])
            ->where('course_type', $filters['course_type'])
            ->where('course_id', $filters['course_id'])
            ->where('branch_id', $filters['branch_id'])
            ->where('batch_id', $filters['batch_id'])
            ->where('institute_id', $filters['institute_id']);


        if (!empty($filters['scheme_id'])) {

            $regularGpQuery->where(
                'scheme_id',
                $filters['scheme_id']
            );
        }


        if ($filters['course_type'] === 'Semester Based') {

            $regularGpQuery->where(
                'semester_id',
                $filters['semester_id']
            );

        } else {

            $regularGpQuery->where(
                'annual_semester_id',
                $filters['annual_semester_id']
            );
        }


        $regularGpSheets = $regularGpQuery
            ->get()
            ->groupBy('student_id');


        /*
        |--------------------------------------------------------------------------
        | 9. PREVIOUS SEMESTERS REGULAR GP SHEET
        |--------------------------------------------------------------------------
        */

        $previousRegularGpSheets = collect();


        if (
            $filters['course_type'] === 'Semester Based'
            &&
            $previousSemesterIds->isNotEmpty()
        ) {

            $previousRegularGpQuery = ResultGpSheet::with([
                'student',
                'subject',
                'course',
                'branch',
                'scheme',
                'exam',
                'semester',
                'batch',
                'institute',
            ])
                ->whereIn('student_id', $studentIds)
                ->where('course_type', $filters['course_type'])
                ->where('course_id', $filters['course_id'])
                ->where('branch_id', $filters['branch_id'])
                ->where('batch_id', $filters['batch_id'])
                ->where('institute_id', $filters['institute_id'])
                ->whereIn(
                    'semester_id',
                    $previousSemesterIds
                );


            /*
            |--------------------------------------------------------------------------
            | IMPORTANT:
            | Previous semester mein current exam_id intentionally nahi lagaya.
            |--------------------------------------------------------------------------
            */

            if (!empty($filters['scheme_id'])) {

                $previousRegularGpQuery->where(
                    'scheme_id',
                    $filters['scheme_id']
                );
            }


            $previousRegularGpSheets =
                $previousRegularGpQuery
                    ->get()
                    ->groupBy('student_id');
        }


        /*
        |--------------------------------------------------------------------------
        | 9A. PREVIOUS SEMESTERS SUPPLEMENTARY GP SHEET
        |--------------------------------------------------------------------------
        |
        | NEW LOGIC:
        |
        | Har previous semester ke liye supplementary data separately check hoga.
        |
        | Agar kisi student ka kisi previous semester mein supplementary GP
        | data mila:
        |
        |     SUPPLE DATA => use
        |
        | Agar nahi mila:
        |
        |     REGULAR DATA => use
        |
        | Current exam_id intentionally use nahi kar rahe hain because previous
        | semester ka supplementary result kisi different exam mein ho sakta hai.
        |
        */

        $previousSuppleGpSheets = collect();


        if (
            $filters['course_type'] === 'Semester Based'
            &&
            $previousSemesterIds->isNotEmpty()
        ) {

            $previousSuppleGpQuery = SuppleResultGpSheet::with([
                'student',
                'subject',
                'course',
                'branch',
                'scheme',
                'exam',
                'semester',
                'batch',
                'institute',
            ])
                ->whereIn('student_id', $studentIds)
                ->where('course_type', $filters['course_type'])
                ->where('course_id', $filters['course_id'])
                ->where('branch_id', $filters['branch_id'])
                ->where('batch_id', $filters['batch_id'])
                ->where('institute_id', $filters['institute_id'])
                ->whereIn(
                    'semester_id',
                    $previousSemesterIds
                );


            if (!empty($filters['scheme_id'])) {

                $previousSuppleGpQuery->where(
                    'scheme_id',
                    $filters['scheme_id']
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Group:
            | student_id
            |     └── semester_id
            |--------------------------------------------------------------------------
            */

            $previousSuppleGpSheets =
                $previousSuppleGpQuery
                    ->get()
                    ->groupBy('student_id')
                    ->map(function ($studentRows) {

                        return $studentRows->groupBy(
                            'semester_id'
                        );
                    });
        }


        /*
        |--------------------------------------------------------------------------
        | 10. BUILD FINAL STUDENT DATA
        |--------------------------------------------------------------------------
        */

        $students = collect();


        foreach ($regularSummaries as $studentId => $regularSummary) {

            $student = $regularSummary->student;


            /*
            |--------------------------------------------------------------------------
            | REGULAR SUBJECTS ONLY
            |--------------------------------------------------------------------------
            */

            $regularSubjects = $regularGpSheets
                ->get($studentId, collect())
                ->values();


            /*
            |--------------------------------------------------------------------------
            | PREVIOUS CREDIT CALCULATION
            |--------------------------------------------------------------------------
            */

            $previousCreditHours  = 0;
            $previousCreditPoints = 0;
            $availableSemesterCount = $regularSubjects->isNotEmpty() ? 1 : 0;


            if (
                $filters['course_type'] === 'Semester Based'
                &&
                $previousSemesterIds->isNotEmpty()
            ) {

                /*
                |--------------------------------------------------------------------------
                | IMPORTANT:
                |
                | Har previous semester ko individually process karenge.
                |
                | Priority:
                |
                | 1. Supple GP Sheet
                | 2. Regular GP Sheet
                |
                | Isse:
                |
                | Sem 1 = Supple
                | Sem 2 = Regular
                |
                | ya
                |
                | Sem 1 = Regular
                | Sem 2 = Supple
                |
                | dono possible hain.
                |--------------------------------------------------------------------------
                */

                foreach ($previousSemesterIds as $previousSemesterId) {

                    /*
                    |--------------------------------------------------------------------------
                    | SUPPLEMENTARY DATA CHECK
                    |--------------------------------------------------------------------------
                    */

                    $suppleSubjects = $previousSuppleGpSheets
                        ->get($studentId, collect())
                        ->get($previousSemesterId, collect())
                        ->values();


                    /*
                    |--------------------------------------------------------------------------
                    | REGULAR DATA
                    |--------------------------------------------------------------------------
                    */

                    $regularPreviousSubjects = $previousRegularGpSheets
                        ->get($studentId, collect())
                        ->where(
                            'semester_id',
                            $previousSemesterId
                        )
                        ->values();


                    /*
                    |--------------------------------------------------------------------------
                    | SOURCE SELECTION
                    |--------------------------------------------------------------------------
                    |
                    | Supplementary data mila to regular data completely override.
                    |--------------------------------------------------------------------------
                    */

                    if ($suppleSubjects->isNotEmpty()) {

                        $previousSubjects = $suppleSubjects;

                    } else {

                        $previousSubjects = $regularPreviousSubjects;
                    }
                    
                    // Count previous semester only if result exists
                    if ($previousSubjects->isNotEmpty()) {
                        $availableSemesterCount++;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | PREVIOUS CREDIT HOURS
                    |--------------------------------------------------------------------------
                    */

                    $previousCreditHours +=
                        $previousSubjects->sum(function ($subject) {

                            $isNc = (bool) optional(
                                $subject->subject
                            )->is_nc;


                            /*
                            | NC subject ka credit count nahi hoga
                            */

                            if ($isNc) {
                                return 0;
                            }


                            return (float) (
                                $subject->credits ?? 0
                            );
                        });


                    /*
                    |--------------------------------------------------------------------------
                    | PREVIOUS CREDIT POINTS
                    |--------------------------------------------------------------------------
                    */

                    $previousCreditPoints +=
                        $previousSubjects->sum(function ($subject) {

                            $isNc = (bool) optional(
                                $subject->subject
                            )->is_nc;


                            /*
                            | NC subject ka credit point count nahi hoga
                            */

                            if ($isNc) {
                                return 0;
                            }


                            $credits = (float) (
                                $subject->credits ?? 0
                            );


                            $result = strtoupper(
                                trim($subject->result ?? '')
                            );


                            $resultGp = (float) (
                                $subject->result_gp ?? 0
                            );


                            if ($result === 'PASS') {

                                return round(
                                    $credits * $resultGp,
                                    2
                                );
                            }


                            return 0;
                        });
                }
            }


            /*
            |--------------------------------------------------------------------------
            | FINAL STUDENT
            |--------------------------------------------------------------------------
            */

            $students->push([

                'student_id' => $studentId,

                'student' => $student,

                'summary' => $regularSummary,

                'regular_summary' => $regularSummary,

                'subjects' => $regularSubjects,

                'is_supplementary' => false,

                'previous_semester_ids' =>
                    $previousSemesterIds,

                'previous_semester_names' =>
                    $previousSemesterNames,

                'previous_credit_hours' =>
                    $previousCreditHours,

                'previous_credit_points' =>
                    $previousCreditPoints,
                    
                'available_semester_count' => $availableSemesterCount,

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 11. RETURN REGULAR CP SHEET
        |--------------------------------------------------------------------------
        */

        return view(
            'backend.result.cp-sheet.regular-result',
            compact(
                'printDate',
                'students',
                'filters',
                'className',
                'exam',
            )
        );
    }

    // public function fetchRegularCpSheet(Request $request)
    // {
    //     /*
    //     |--------------------------------------------------------------------------
    //     | 1. BASIC VALIDATION
    //     |--------------------------------------------------------------------------
    //     */

    //     $request->validate([
    //         'exam_id' => 'required',
    //         'course_type' => 'required',
    //         'course_id' => 'required',
    //         'branch_id' => 'required',
    //         'batch_id' => 'required',
    //         'institute_id' => 'required',

    //         'semester_id' => 'required_if:course_type,Semester Based',
    //         'annual_semester_id' => 'required_if:course_type,Annual Based',

    //         'scheme_id' => 'nullable',
    //         'student_roll_number' => 'nullable',
    //     ]);


    //     $courseType       = $request->course_type;
    //     $semesterId       = $request->semester_id;
    //     $annualSemesterId = $request->annual_semester_id;
    //     $exam = Exam::find($request->exam_id);


    //     if ($courseType == "Semester Based") {

    //         $semester = Semester::find($semesterId);

    //         $className = $semester
    //             ? $semester->name
    //             : '';

    //     } else {

    //         $semester = AnnualSemester::find($annualSemesterId);

    //         $className = $semester
    //             ? $semester->name
    //             : '';
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | 2. COMMON FILTERS
    //     |--------------------------------------------------------------------------
    //     */

    //     $filters = [
    //         'exam_id'            => $request->exam_id,
    //         'course_type'        => $request->course_type,
    //         'course_id'          => $request->course_id,
    //         'scheme_id'          => $request->scheme_id,
    //         'branch_id'          => $request->branch_id,
    //         'batch_id'           => $request->batch_id,
    //         'institute_id'       => $request->institute_id,
    //         'semester_id'        => $request->semester_id,
    //         'annual_semester_id' => $request->annual_semester_id,
    //     ];


    //     /*
    //     |--------------------------------------------------------------------------
    //     | 3. FIND STUDENT ID FROM ROLL NUMBER
    //     |--------------------------------------------------------------------------
    //     */

    //     $studentId = null;

    //     if (!empty($request->student_roll_number)) {

    //         $student = Student::where(
    //             'university_roll_no',
    //             $request->student_roll_number
    //         )->first();

    //         if (!$student) {

    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Student not found with this roll number.'
    //             ], 404);
    //         }

    //         $studentId = $student->id;
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | 4. REGULAR RESULT SUMMARY
    //     |--------------------------------------------------------------------------
    //     */

    //     $regularSummaryQuery = StudentResultSummary::with([
    //         'student',
    //         'course',
    //         'branch',
    //         'scheme',
    //         'exam',
    //         'semester',
    //         'annualSemester',
    //         'batch',
    //         'institute',
    //     ]);


    //     $regularSummaryQuery
    //         ->where('exam_id', $filters['exam_id'])
    //         ->where('course_type', $filters['course_type'])
    //         ->where('course_id', $filters['course_id'])
    //         ->where('branch_id', $filters['branch_id'])
    //         ->where('batch_id', $filters['batch_id'])
    //         ->where('institute_id', $filters['institute_id']);


    //     /*
    //     |--------------------------------------------------------------------------
    //     | OPTIONAL SCHEME
    //     |--------------------------------------------------------------------------
    //     */

    //     if (!empty($filters['scheme_id'])) {

    //         $regularSummaryQuery->where(
    //             'scheme_id',
    //             $filters['scheme_id']
    //         );
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | SEMESTER / ANNUAL SEMESTER
    //     |--------------------------------------------------------------------------
    //     */

    //     if ($filters['course_type'] === 'Semester Based') {

    //         $regularSummaryQuery->where(
    //             'semester_id',
    //             $filters['semester_id']
    //         );

    //     } else {

    //         $regularSummaryQuery->where(
    //             'annual_semester_id',
    //             $filters['annual_semester_id']
    //         );
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | OPTIONAL STUDENT
    //     |--------------------------------------------------------------------------
    //     */

    //     if ($studentId) {

    //         $regularSummaryQuery->where(
    //             'student_id',
    //             $studentId
    //         );
    //     }


    //     // $regularSummaries = $regularSummaryQuery
    //     //     ->get()
    //     //     ->keyBy('student_id');
        
    //     $regularSummaries = $regularSummaryQuery
    //         ->get()
    //         ->sortBy(function ($summary) {
    //             return strtoupper(
    //                 trim($summary->student->university_roll_no ?? '')
    //             );
    //         })
    //         ->values()
    //         ->keyBy('student_id');


    //     /*
    //     |--------------------------------------------------------------------------
    //     | 5. NO RESULT
    //     |--------------------------------------------------------------------------
    //     */

    //     if ($regularSummaries->isEmpty()) {

    //         return response()->json([
    //             'status' => false,
    //             'message' => 'No regular result found for the selected filters.'
    //         ], 404);
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | 6. STUDENT IDS
    //     |--------------------------------------------------------------------------
    //     */

    //     $studentIds = $regularSummaries
    //         ->keys()
    //         ->values();


    //     /*
    //     |--------------------------------------------------------------------------
    //     | 7. PREVIOUS SEMESTERS
    //     |--------------------------------------------------------------------------
    //     */

    //     $previousSemesterIds   = collect();
    //     $previousSemesterNames = collect();


    //     if ($filters['course_type'] === 'Semester Based') {

    //         $romanToNumber = [

    //             'I'    => 1,
    //             'II'   => 2,
    //             'III'  => 3,
    //             'IV'   => 4,
    //             'V'    => 5,
    //             'VI'   => 6,
    //             'VII'  => 7,
    //             'VIII' => 8,
    //             'IX'   => 9,
    //             'X'    => 10,
    //             'XI'   => 11,
    //             'XII'  => 12,

    //         ];


    //         $currentSemester = Semester::find(
    //             $filters['semester_id']
    //         );


    //         if ($currentSemester) {

    //             $currentSemesterName = strtoupper(
    //                 trim($currentSemester->name)
    //             );


    //             $currentSemesterNumber =
    //                 $romanToNumber[$currentSemesterName] ?? null;


    //             if (
    //                 $currentSemesterNumber
    //                 &&
    //                 $currentSemesterNumber > 1
    //             ) {

    //                 $previousSemesterNumber =
    //                     $currentSemesterNumber - 1;


    //                 $allSemesters = Semester::get();


    //                 foreach ($allSemesters as $semester) {

    //                     $semesterName = strtoupper(
    //                         trim($semester->name)
    //                     );


    //                     $semesterNumber =
    //                         $romanToNumber[$semesterName] ?? null;


    //                     if (
    //                         $semesterNumber
    //                         &&
    //                         $semesterNumber >= 1
    //                         &&
    //                         $semesterNumber <= $previousSemesterNumber
    //                     ) {

    //                         $previousSemesterIds->push(
    //                             $semester->id
    //                         );

    //                         $previousSemesterNames->put(
    //                             $semester->id,
    //                             $semester->name
    //                         );
    //                     }
    //                 }


    //                 $previousSemesterIds =
    //                     $previousSemesterIds
    //                         ->sort(function ($a, $b) use (
    //                             $previousSemesterNames,
    //                             $romanToNumber
    //                         ) {

    //                             $nameA = strtoupper(
    //                                 trim(
    //                                     $previousSemesterNames->get($a)
    //                                 )
    //                             );

    //                             $nameB = strtoupper(
    //                                 trim(
    //                                     $previousSemesterNames->get($b)
    //                                 )
    //                             );


    //                             $numberA =
    //                                 $romanToNumber[$nameA] ?? 999;

    //                             $numberB =
    //                                 $romanToNumber[$nameB] ?? 999;


    //                             return $numberA <=> $numberB;
    //                         })
    //                         ->values();
    //             }
    //         }
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | 8. FETCH REGULAR GP SHEET
    //     |--------------------------------------------------------------------------
    //     */

    //     $regularGpQuery = ResultGpSheet::with([
    //         'student',
    //         'subject',
    //         'course',
    //         'branch',
    //         'scheme',
    //         'exam',
    //         'semester',
    //         'annualSemester',
    //         'batch',
    //         'institute',
    //     ])
    //         ->whereIn('student_id', $studentIds)
    //         ->where('exam_id', $filters['exam_id'])
    //         ->where('course_type', $filters['course_type'])
    //         ->where('course_id', $filters['course_id'])
    //         ->where('branch_id', $filters['branch_id'])
    //         ->where('batch_id', $filters['batch_id'])
    //         ->where('institute_id', $filters['institute_id']);


    //     if (!empty($filters['scheme_id'])) {

    //         $regularGpQuery->where(
    //             'scheme_id',
    //             $filters['scheme_id']
    //         );
    //     }


    //     if ($filters['course_type'] === 'Semester Based') {

    //         $regularGpQuery->where(
    //             'semester_id',
    //             $filters['semester_id']
    //         );

    //     } else {

    //         $regularGpQuery->where(
    //             'annual_semester_id',
    //             $filters['annual_semester_id']
    //         );
    //     }


    //     $regularGpSheets = $regularGpQuery
    //         ->get()
    //         ->groupBy('student_id');


    //     /*
    //     |--------------------------------------------------------------------------
    //     | 9. PREVIOUS SEMESTERS REGULAR GP SHEET
    //     |--------------------------------------------------------------------------
    //     */

    //     $previousRegularGpSheets = collect();


    //     if (
    //         $filters['course_type'] === 'Semester Based'
    //         &&
    //         $previousSemesterIds->isNotEmpty()
    //     ) {

    //         $previousRegularGpQuery = ResultGpSheet::with([
    //             'student',
    //             'subject',
    //             'course',
    //             'branch',
    //             'scheme',
    //             'exam',
    //             'semester',
    //             'batch',
    //             'institute',
    //         ])
    //             ->whereIn('student_id', $studentIds)
    //             ->where('course_type', $filters['course_type'])
    //             ->where('course_id', $filters['course_id'])
    //             ->where('branch_id', $filters['branch_id'])
    //             ->where('batch_id', $filters['batch_id'])
    //             ->where('institute_id', $filters['institute_id'])
    //             ->whereIn(
    //                 'semester_id',
    //                 $previousSemesterIds
    //             );


    //         /*
    //         |--------------------------------------------------------------------------
    //         | IMPORTANT:
    //         | Previous semester mein current exam_id intentionally nahi lagaya.
    //         |--------------------------------------------------------------------------
    //         */

    //         if (!empty($filters['scheme_id'])) {

    //             $previousRegularGpQuery->where(
    //                 'scheme_id',
    //                 $filters['scheme_id']
    //             );
    //         }


    //         $previousRegularGpSheets =
    //             $previousRegularGpQuery
    //                 ->get()
    //                 ->groupBy('student_id');
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | 10. BUILD FINAL STUDENT DATA
    //     |--------------------------------------------------------------------------
    //     */

    //     $students = collect();


    //     foreach ($regularSummaries as $studentId => $regularSummary) {

    //         $student = $regularSummary->student;


    //         /*
    //         |--------------------------------------------------------------------------
    //         | REGULAR SUBJECTS ONLY
    //         |--------------------------------------------------------------------------
    //         */

    //         $regularSubjects = $regularGpSheets
    //             ->get($studentId, collect())
    //             ->values();


    //         /*
    //         |--------------------------------------------------------------------------
    //         | PREVIOUS CREDIT CALCULATION
    //         |--------------------------------------------------------------------------
    //         */

    //         $previousCreditHours  = 0;
    //         $previousCreditPoints = 0;


    //         if (
    //             $filters['course_type'] === 'Semester Based'
    //             &&
    //             $previousSemesterIds->isNotEmpty()
    //         ) {

    //             $previousSubjects =
    //                 $previousRegularGpSheets
    //                     ->get($studentId, collect())
    //                     ->values();


    //             /*
    //             |--------------------------------------------------------------------------
    //             | PREVIOUS CREDIT HOURS
    //             |--------------------------------------------------------------------------
    //             */

    //             $previousCreditHours =
    //                 $previousSubjects->sum(function ($subject) {
                        
    //                     $isNc = (bool) optional($subject->subject)->is_nc;

    //                     // NC subject ka credit count nahi hoga
    //                     if ($isNc) {
    //                         return 0;
    //                     }

    //                     return (float) (
    //                         $subject->credits ?? 0
    //                     );
    //                 });


    //             /*
    //             |--------------------------------------------------------------------------
    //             | PREVIOUS CREDIT POINTS
    //             |--------------------------------------------------------------------------
    //             */

    //             $previousCreditPoints =
    //                 $previousSubjects->sum(function ($subject) {

    //                     $isNc = (bool) optional($subject->subject)->is_nc;

    //                     // NC subject ka credit point bhi count nahi hoga
    //                     if ($isNc) {
    //                         return 0;
    //                     }

    //                     $credits = (float) (
    //                         $subject->credits ?? 0
    //                     );


    //                     $result = strtoupper(
    //                         trim($subject->result ?? '')
    //                     );


    //                     $resultGp = (float) (
    //                         $subject->result_gp ?? 0
    //                     );


    //                     if ($result === 'PASS') {

    //                         return round($credits * $resultGp, 2);
    //                     }


    //                     return 0;
    //                 });
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | FINAL STUDENT
    //         |--------------------------------------------------------------------------
    //         */

    //         $students->push([

    //             'student_id' => $studentId,

    //             'student' => $student,

    //             'summary' => $regularSummary,

    //             'regular_summary' => $regularSummary,

    //             'subjects' => $regularSubjects,

    //             'is_supplementary' => false,

    //             'previous_semester_ids' =>
    //                 $previousSemesterIds,

    //             'previous_semester_names' =>
    //                 $previousSemesterNames,

    //             'previous_credit_hours' =>
    //                 $previousCreditHours,

    //             'previous_credit_points' =>
    //                 $previousCreditPoints,

    //         ]);
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | 11. RETURN REGULAR CP SHEET
    //     |--------------------------------------------------------------------------
    //     */

    //     return view(
    //         'backend.result.cp-sheet.regular-result',
    //         compact(
    //             'students',
    //             'filters',
    //             'className',
    //             'exam',
    //         )
    //     );
    // }

    public function suppleIndex()
    {
        $courses = Course::all();
        $exams = Exam::latest()->get();
        return view('backend.result.cp-sheet.supple.index', compact('courses', 'exams'));
    }

    public function fetchSuppleCpSheet(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. BASIC VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'exam_id' => 'required',
            'course_type' => 'required',
            'course_id' => 'required',
            'branch_id' => 'required',
            'batch_id' => 'required',
            'institute_id' => 'required',

            'semester_id' => 'required_if:course_type,Semester Based',
            'annual_semester_id' => 'required_if:course_type,Annual Based',

            'scheme_id' => 'nullable',
            'student_roll_number' => 'nullable',
        ]);


        $printDate       = $request->print_date;
        $courseType       = $request->course_type;
        $semesterId       = $request->semester_id;
        $annualSemesterId = $request->annual_semester_id;
        $exam = Exam::find($request->exam_id);


        if ($courseType == "Semester Based") {

            $semester = Semester::find($semesterId);

            $className = $semester
                ? $semester->name 
                : '';

        } else {

            $semester = AnnualSemester::find($annualSemesterId);

            $className = $semester
                ? $semester->name
                : '';
        }


        /*
        |--------------------------------------------------------------------------
        | 2. COMMON FILTERS
        |--------------------------------------------------------------------------
        */

        $filters = [
            'exam_id'            => $request->exam_id,
            'course_type'        => $request->course_type,
            'course_id'          => $request->course_id,
            'scheme_id'          => $request->scheme_id,
            'branch_id'          => $request->branch_id,
            'batch_id'           => $request->batch_id,
            'institute_id'       => $request->institute_id,
            'semester_id'        => $request->semester_id,
            'annual_semester_id' => $request->annual_semester_id,
        ];


        /*
        |--------------------------------------------------------------------------
        | 3. FIND STUDENT ID FROM ROLL NUMBER
        |--------------------------------------------------------------------------
        */

        $studentId = null;


        if (!empty($request->student_roll_number)) {

            $student = Student::where(
                'university_roll_no',
                $request->student_roll_number
            )->first();


            if (!$student) {

                return response()->json([
                    'status' => false,
                    'message' => 'Student not found with this roll number.'
                ], 404);
            }


            $studentId = $student->id;
        }


        /*
        |--------------------------------------------------------------------------
        | 4. SUPPLEMENTARY RESULT SUMMARY
        |--------------------------------------------------------------------------
        */

        $suppleSummaryQuery = SuppleResultSummary::with([
            'student',
            'course',
            'branch',
            'scheme',
            'exam',
            'batch',
            'institute',
            'semester',
            'annualSemester',
        ])
            ->where('exam_id', $filters['exam_id'])
            ->where('course_type', $filters['course_type'])
            ->where('course_id', $filters['course_id'])
            ->where('branch_id', $filters['branch_id'])
            ->where('batch_id', $filters['batch_id'])
            ->where('institute_id', $filters['institute_id'])
            ->where('is_supple', 1);

        /*
        |--------------------------------------------------------------------------
        | OPTIONAL SCHEME
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['scheme_id'])) {

            $suppleSummaryQuery->where(
                'scheme_id',
                $filters['scheme_id']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SEMESTER / ANNUAL SEMESTER
        |--------------------------------------------------------------------------
        */

        if ($filters['course_type'] === 'Semester Based') {

            $suppleSummaryQuery->where(
                'semester_id',
                $filters['semester_id']
            );

        } else {

            $suppleSummaryQuery->where(
                'annual_semester_id',
                $filters['annual_semester_id']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | OPTIONAL STUDENT
        |--------------------------------------------------------------------------
        */

        if ($studentId) {

            $suppleSummaryQuery->where(
                'student_id',
                $studentId
            );
        }


        $suppleSummaries = $suppleSummaryQuery
            ->get()
            ->keyBy('student_id');


        /*
        |--------------------------------------------------------------------------
        | 5. NO RESULT
        |--------------------------------------------------------------------------
        */

        if ($suppleSummaries->isEmpty()) {

            return response()->json([
                'status' => false,
                'message' => 'No supplementary/reappear result found for the selected filters.'
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | 6. STUDENT IDS
        |--------------------------------------------------------------------------
        */

        $studentIds = $suppleSummaries
            ->keys()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | 7. PREVIOUS SEMESTERS
        |--------------------------------------------------------------------------
        */

        $previousSemesterIds   = collect();
        $previousSemesterNames = collect();


        if ($filters['course_type'] === 'Semester Based') {

            $romanToNumber = [

                'I'    => 1,
                'II'   => 2,
                'III'  => 3,
                'IV'   => 4,
                'V'    => 5,
                'VI'   => 6,
                'VII'  => 7,
                'VIII' => 8,
                'IX'   => 9,
                'X'    => 10,
                'XI'   => 11,
                'XII'  => 12,

            ];


            $currentSemester = Semester::find(
                $filters['semester_id']
            );


            if ($currentSemester) {

                $currentSemesterName = strtoupper(
                    trim($currentSemester->name)
                );


                $currentSemesterNumber =
                    $romanToNumber[$currentSemesterName] ?? null;


                if (
                    $currentSemesterNumber
                    &&
                    $currentSemesterNumber > 1
                ) {

                    $previousSemesterNumber =
                        $currentSemesterNumber - 1;


                    $allSemesters = Semester::get();


                    foreach ($allSemesters as $semester) {

                        $semesterName = strtoupper(
                            trim($semester->name)
                        );


                        $semesterNumber =
                            $romanToNumber[$semesterName] ?? null;


                        if (
                            $semesterNumber
                            &&
                            $semesterNumber >= 1
                            &&
                            $semesterNumber <= $previousSemesterNumber
                        ) {

                            $previousSemesterIds->push(
                                $semester->id
                            );

                            $previousSemesterNames->put(
                                $semester->id,
                                $semester->name
                            );
                        }
                    }


                    $previousSemesterIds =
                        $previousSemesterIds
                            ->sort(function ($a, $b) use (
                                $previousSemesterNames,
                                $romanToNumber
                            ) {

                                $nameA = strtoupper(
                                    trim(
                                        $previousSemesterNames->get($a)
                                    )
                                );

                                $nameB = strtoupper(
                                    trim(
                                        $previousSemesterNames->get($b)
                                    )
                                );


                                $numberA =
                                    $romanToNumber[$nameA] ?? 999;

                                $numberB =
                                    $romanToNumber[$nameB] ?? 999;


                                return $numberA <=> $numberB;
                            })
                            ->values();
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 8. FETCH SUPPLEMENTARY GP SHEET
        |--------------------------------------------------------------------------
        */

        $suppleGpQuery = SuppleResultGpSheet::with([
            'student',
            'subject',
            'course',
            'branch',
            'scheme',
            'exam',
            'batch',
        ])
            ->whereIn('student_id', $studentIds)
            ->where('exam_id', $filters['exam_id'])
            ->where('course_type', $filters['course_type'])
            ->where('course_id', $filters['course_id'])
            ->where('branch_id', $filters['branch_id'])
            ->where('batch_id', $filters['batch_id']);


        /*
        |--------------------------------------------------------------------------
        | OPTIONAL SCHEME
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['scheme_id'])) {

            $suppleGpQuery->where(
                'scheme_id',
                $filters['scheme_id']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SEMESTER / ANNUAL SEMESTER
        |--------------------------------------------------------------------------
        */

        if ($filters['course_type'] === 'Semester Based') {

            $suppleGpQuery->where(
                'semester_id',
                $filters['semester_id']
            );

        } else {

            $suppleGpQuery->where(
                'annual_semester_id',
                $filters['annual_semester_id']
            );
        }


        $suppleGpSheets = $suppleGpQuery
            ->get()
            ->groupBy('student_id');


        /*
        |--------------------------------------------------------------------------
        | 9. PREVIOUS SEMESTERS SUPPLEMENTARY GP SHEET
        |--------------------------------------------------------------------------
        */

        $previousSuppleGpSheets = collect();


        if (
            $filters['course_type'] === 'Semester Based'
            &&
            $previousSemesterIds->isNotEmpty()
        ) {

            $previousSuppleGpQuery = SuppleResultGpSheet::with([
                'student',
                'subject',
                'course',
                'branch',
                'exam',
                'batch',
            ])
                ->whereIn('student_id', $studentIds)
                ->where('course_type', $filters['course_type'])
                ->where('course_id', $filters['course_id'])
                ->where('branch_id', $filters['branch_id'])
                ->where('batch_id', $filters['batch_id'])
                ->whereIn(
                    'semester_id',
                    $previousSemesterIds
                );


            /*
            |--------------------------------------------------------------------------
            | IMPORTANT:
            | Previous semester mein current exam_id intentionally nahi lagaya.
            |--------------------------------------------------------------------------
            */

            if (!empty($filters['scheme_id'])) {

                $previousSuppleGpQuery->where(
                    'scheme_id',
                    $filters['scheme_id']
                );
            }


            $previousSuppleGpSheets =
                $previousSuppleGpQuery
                    ->get()
                    ->groupBy('student_id');
        }


        /*
        |--------------------------------------------------------------------------
        | 10. BUILD FINAL STUDENT DATA
        |--------------------------------------------------------------------------
        */

        $students = collect();


        foreach ($suppleSummaries as $studentId => $suppleSummary) {

            $student = $suppleSummary->student;


            /*
            |--------------------------------------------------------------------------
            | SUPPLEMENTARY SUBJECTS ONLY
            |--------------------------------------------------------------------------
            */

            $suppleSubjects = $suppleGpSheets
                ->get($studentId, collect())
                ->sortBy(function ($subject) {
                    return strtolower(trim($subject->subject->subject_code ?? ''));
                })
                ->values();


            /*
            |--------------------------------------------------------------------------
            | PREVIOUS CREDIT CALCULATION
            |--------------------------------------------------------------------------
            */

            $previousCreditHours  = 0;
            $previousCreditPoints = 0;


            if (
                $filters['course_type'] === 'Semester Based'
                &&
                $previousSemesterIds->isNotEmpty()
            ) {

                $previousSubjects =
                    $previousSuppleGpSheets
                        ->get($studentId, collect())
                        ->values();


                /*
                |--------------------------------------------------------------------------
                | PREVIOUS CREDIT HOURS
                |--------------------------------------------------------------------------
                */

                $previousCreditHours =
                    $previousSubjects->sum(function ($subject) {

                        if ((int) optional($subject->subject)->is_nc === 1) {
                            return 0;
                        }

                        return (float) (
                            $subject->credits ?? 0
                        );
                    });


                /*
                |--------------------------------------------------------------------------
                | PREVIOUS CREDIT POINTS
                |--------------------------------------------------------------------------
                */

                $previousCreditPoints =
                    $previousSubjects->sum(function ($subject) {

                        if ((int) optional($subject->subject)->is_nc === 1) {
                            return 0;
                        }

                        $credits = (float) (
                            $subject->credits ?? 0
                        );


                        $result = strtoupper(
                            trim($subject->result ?? '')
                        );


                        $resultGp = (float) (
                            $subject->result_gp ?? 0
                        );


                        if ($result === 'PASS') {

                           return round($credits * $resultGp, 2);
                        }


                        return 0;
                    });
            }


            /*
            |--------------------------------------------------------------------------
            | FINAL STUDENT
            |--------------------------------------------------------------------------
            */

            $students->push([

                'student_id' => $studentId,

                'student' => $student,

                'summary' => $suppleSummary,

                'supple_summary' => $suppleSummary,

                'subjects' => $suppleSubjects,

                'is_supplementary' => true,

                'previous_semester_ids' =>
                    $previousSemesterIds,

                'previous_semester_names' =>
                    $previousSemesterNames,

                'previous_credit_hours' =>
                    $previousCreditHours,

                'previous_credit_points' =>
                    $previousCreditPoints,

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 11. RETURN SUPPLEMENTARY CP SHEET
        |--------------------------------------------------------------------------
        */

        return view(
            'backend.result.cp-sheet.supple.supple-result',
            compact(
                'printDate',
                'students',
                'filters',
                'className',
                'exam',
            )
        );
    }

}
