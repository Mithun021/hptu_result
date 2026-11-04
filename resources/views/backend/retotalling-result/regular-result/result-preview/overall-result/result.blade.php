<style>
    .result-table {
        width: 100%;
        margin: auto;
        font-family: "Times New Roman", serif;
    }

    .result-table .header {
        text-align: center;
        margin-bottom: 10px;
    }

    .result-table .header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: bold;
    }

    .result-table .header h4 {
        margin: 0;
        font-size: 14px;
    }

    .result-table .header p {
        margin: 0;
        font-size: 13px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid #000;
        padding: 4px;
        text-align: center;
        font-size: 12px;
    }

    th {
        background: #e6e6e6;
        font-weight: bold;
    }

    td.left {
        text-align: left;
    }

    .fail {
        color: red;
        font-weight: bold;
    }

    .pass {
        color: green;
        font-weight: bold;
    }

    .supply {
        color: orange;
        font-weight: bold;
    }

    .remarks {
        text-align: left;
        font-size: 12px;
    }

    thead {
        display: table-header-group;
    }

    tfoot {
        display: table-footer-group;
    }
</style>

@php
    $filteredSubjects = $subjects;
@endphp

<table class="result-table">
    <thead class="result-header">
        <!-- ================= HEADER TITLE ================= -->
        <tr>
            <th colspan="{{ 3 + count($filteredSubjects) + 4 }}" style="text-align:center;">
                <h3 style="margin:0;">OFFICE OF CONTROLLER OF EXAMINATIONS</h3>
                <h4 style="margin:0;">
                    GURU ANGAD DEV VETERINARY AND ANIMAL SCIENCES UNIVERSITY, LUDHIANA (INDIA)
                </h4>
                <h4 style="margin:0;">
                    {{ $exam->exam_resultsheet_name ?? $exam->name }}
                </h4>
                <p style="text-align:left;">
                    {{ $className }}
                    {{ $course->name ?? '' }}
                    {{ (!empty($branch->name) && strtoupper($branch->name) != 'NA') ? "({$branch->name})" : '' }}
                </p>
            </th>
        </tr>
        <tr>
            <th rowspan="2">Sr. No.</th>
            <th rowspan="2">Student Name</th>
            <th rowspan="2" class="min-width">Admission No.</th>
            <th colspan="{{ count($filteredSubjects) }}">Subjects</th>
            <th rowspan="2">Remarks</th>
            <th rowspan="2" class="no-export">GPA</th>
            <th rowspan="2" class="no-export">CGPA</th>
            <th rowspan="2" class="no-export">Grade</th>
        </tr>

        <tr>
            @foreach($filteredSubjects as $sub)
                <th>{{ $sub->subject_code }}</th>
            @endforeach
        </tr>
    </thead>

    <tbody>

    @php
        $summaryData = [];
        $gpSheetData = [];
    @endphp

    @php
        $srNo = 1;
    @endphp

    @foreach($students as $key => $student)

        @php
            $remarks = [];
            $cgpaBlocked = false;
            $cgpaMessage = '';

            if ($courseType == 'Semester Based' && count($missingPublishedSemesters)) {

                $cgpaBlocked = true;

                $cgpaMessage =
                    'Semester ' .
                    implode(', ', $missingPublishedSemesters) .
                    ' result not saved/published.';
            }

            $courseTypeName = $course->course;
            $totalCreditHours = 0;
            $totalWeightedGP  = 0;

            /*
            ==========================
            PREVIOUS SEMESTER DATA
            ==========================
            */

            $previousCreditHours = 0;
            $previousWeightedGP  = 0;

            if(isset($previousGpSheets[$student->id])){

                foreach($previousGpSheets[$student->id] as $old){

                    // if ($old->result != 'PASS') {
                    //     continue;
                    // }

                    // $previousCreditHours += $old->credits;

                    // $previousWeightedGP += ($old->credits * $old->result_gp);

                    $isNc = (bool) optional($old->subject)->is_nc;

                    // NC subject ka credit aur credit point dono count nahi honge
                    if ($isNc) {
                        continue;
                    }

                    $credit = (float) ($old->credits ?? 0);

                    $previousCreditHours += $credit;

                    // FAIL / SUPPLE ka GP = 0 maana jayega
                    $gp = in_array($old->result, ['FAIL', 'SUPPLE'])
                        ? 0
                        : $old->result_gp;

                    $previousWeightedGP += round($credit * $gp, 2);

                }

            }

            // Check if student is enrolled in ANY subject
            $hasEnrollment =
                isset($studentSubjects[$student->id]) &&
                $studentSubjects[$student->id]->count() > 0;
        @endphp

        @if(!$hasEnrollment)
            @continue
        @endif

        <tr>
            <td>{{ $srNo++ }}</td>
            <td class="left">{{ $student->student_name }}</td>
            <td>{{ $student->university_roll_no }}</td>

            @foreach($filteredSubjects as $sub)

                @php

                /*
                =============================
                ENROLLMENT + MARK CHECK
                =============================
                */

                $isEnrolled = isset($studentSubjects[$student->id]) &&
                    $studentSubjects[$student->id]->contains('subject_id', $sub->id);

                $hasMid = isset($midSemMarks[$student->id][$sub->id]);
                $hasEnd = isset($endSemMarks[$student->id][$sub->id]);

                $hasAnyMark = $hasMid || $hasEnd;

                /*
                =============================
                RETOTALLING APPLICATION CHECK
                =============================
                */

                $retotallingApplication = $retotallingStudents->first(function ($application) use ($student, $sub) {
                    return (int) $application->student_id === (int) $student->id
                        && $application->subjects->contains('subjects_id', $sub->id);
                });

                /*
                =============================
                DEFAULT VALUES
                =============================
                */

                $class = '';
                $value = '-';

                if ($isEnrolled && $hasAnyMark) {

                    if ($sub->is_nc) {

                        $grade =
                            $endSemMarks[$student->id][$sub->id][0]->grade ?? '';

                        if ($grade == 'Satisfactory') {

                            $class = 'pass';
                            $value = 'S';

                        } elseif ($grade == 'Exempted') {

                            $class = 'pass';
                            $value = 'E';

                        } elseif ($grade == 'Unsatisfactory') {

                            $class = 'fail';
                            $value = 'U';

                            $remarks[] = "Unsatisfactory {$sub->subject_code}";

                        }

                        // $ncGrade = $value == 'S'
                        //     ? 'Satisfactory'
                        //     : 'Unsatisfactory';

                        // $ncResult = $value == 'S'
                        //     ? 'PASS'
                        //     : 'FAIL';

                        $ncGrade = match ($value) {
                            'S' => 'Satisfactory',
                            'E' => 'Exempted',
                            default => 'Unsatisfactory',
                        };

                        $ncResult = in_array($value, ['S', 'E'])
                            ? 'PASS'
                            : 'FAIL';

                        $gpSheetData[] = [
                            'student_id' => $student->id,
                            'subject_id' => $sub->id,
                            'result' => $ncResult,
                            'result_gp' => 0,
                            'grade' => $ncGrade,
                            'exam_id' => $exam->id,
                            'course_type' => $courseType,
                            'course_id' => $course->id,
                            'scheme_id' => $schemeId ?? null,
                            'branch_id' => $branch->id,
                            'semester_id' => $semesterId ?? null,
                            'annual_semester_id' => $annualSemesterId ?? null,
                            'batch_id' => $batch->id,
                            'institute_id' => $instituteId ?? null,
                        ];

                    } else {

                    /*
                    =============================
                    FETCH MARKS
                    =============================
                    */

                    $mid   = $midSemMarks[$student->id][$sub->id]['Mid-Sem'][0]->obtained_marks ?? 0;

                    $quiz  = $midSemMarks[$student->id][$sub->id]['Quiz'][0]->obtained_marks ?? 0;

                    $pracFromMid =
                        $midSemMarks[$student->id][$sub->id]['Practical'][0]->obtained_marks ?? 0;

                    /*
                    =============================
                    NEW GRACE + MODERATION LOGIC
                    =============================
                    */

                    if ($sub->type == "Theory") {

                        /*
                        --------------------------------
                        THEORY SUBJECT
                        Grace from END SEM
                        --------------------------------
                        */

                        if ($courseTypeName == "Diploma") {

                            $endFinalData =
                                $endSemMarks[$student->id][$sub->id]['external'][0] ?? null;

                            $endFinalOriginal =
                                $endFinalData->obtained_marks ?? 0;

                            $graceMarks =
                                $endFinalData->grace_marks ?? 0;

                        } else {

                            // Existing UG Logic
                            $endFinalData =
                                $endSemMarks[$student->id][$sub->id][0] ?? null;

                            $endFinalOriginal =
                                $endFinalData->obtained_marks ?? 0;

                            $graceMarks =
                                $endFinalData->grace_marks ?? 0;
                        }

                        /*
                        --------------------------------
                        RETOTALLING MARKS
                        Apply only when:
                        1. Application exists for student + subject
                        2. Retotalling marks are not null
                        3. Retotalling marks are non-zero
                        4. Retotalling marks are locked
                        --------------------------------
                        */

                        if (
                            $retotallingApplication &&
                            $endFinalData &&
                            $endFinalData->retotalling_obtained_marks !== null &&
                            (float) $endFinalData->retotalling_obtained_marks != 0 &&
                            (int) $endFinalData->retotalling_marks_locked === 1
                        ) {

                            $endFinalOriginal =
                                $endFinalData->retotalling_obtained_marks;
                        }

                    } else {

                        /*
                        --------------------------------
                        PRACTICAL SUBJECT
                        Grace from MID SEM PRACTICAL
                        --------------------------------
                        */

                        // $endFinalOriginal = $pracFromMid;

                        // $graceMarks =
                        //     $midSemMarks[$student->id][$sub->id]['Practical'][0]->grace_marks ?? 0;

                        if ($courseTypeName == "Diploma") {

                            $endFinalOriginal =
                                $endSemMarks[$student->id][$sub->id]['practical'][0]->obtained_marks ?? 0;

                            $graceMarks =
                                $endSemMarks[$student->id][$sub->id]['practical'][0]->grace_marks ?? 0;

                        } else {

                            // Existing UG Logic
                            $endFinalOriginal = $pracFromMid;

                            $graceMarks =
                                $midSemMarks[$student->id][$sub->id]['Practical'][0]->grace_marks ?? 0;
                        }

                    }

                    /*
                    =============================
                    MODERATION MARKS
                    =============================
                    */

                    $moderationMarks =
                        $academicAdjustments[$student->id][$sub->id]['external'][0]->adjustment_marks ?? 0;

                    /*
                    =============================
                    FINAL MARKS
                    =============================
                    */

                    $endFinal =
                        $endFinalOriginal
                        + $moderationMarks;

                    /*
                    =============================
                    THEORY / PRACTICAL LOGIC
                    =============================
                    */

                    if ($sub->type == "Theory") {

                        // THEORY
                        $final = $endFinal;

                        // $theoryTotal = $mid + $quiz + $final;

                        $theoryTotal =
                            $mid
                            + $quiz
                            + $final
                            + $graceMarks;

                        // PRACTICAL
                        $practicalMid = 0;
                        $practicalQuiz = 0;

                        // if ($courseTypeName == "UG") {
                        //
                        //     $practicalFinal = $pracFromMid;
                        //
                        // } else {
                        //
                        //     $practicalFinal = $endFinal;
                        // }

                        if ($courseTypeName == "Diploma") {

                            $practicalFinal =
                                $endSemMarks[$student->id][$sub->id]['practical'][0]->obtained_marks ?? 0;

                        } else {

                            // Existing UG Logic
                            $practicalFinal = $pracFromMid;
                        }

                        $practicalTotal = $practicalFinal;

                    } else {

                        // THEORY ZERO
                        $mid = 0;
                        $quiz = 0;
                        $final = 0;

                        $theoryTotal = 0;

                        // PRACTICAL
                        $practicalMid =
                            $midSemMarks[$student->id][$sub->id]['Mid-Sem'][0]->obtained_marks ?? 0;

                        $practicalQuiz =
                            $midSemMarks[$student->id][$sub->id]['Quiz'][0]->obtained_marks ?? 0;

                        if ($courseTypeName == "UG") {

                            $practicalFinal = $endFinal;

                        } else {

                            $practicalFinal = $endFinal;
                        }

                        // $practicalTotal =
                        //     $practicalMid
                        //     + $practicalQuiz
                        //     + $practicalFinal;

                        $practicalTotal =
                            $practicalMid
                            + $practicalQuiz
                            + $practicalFinal
                            + $graceMarks;
                    }

                    /*
                    =============================
                    TOTAL
                    =============================
                    */

                    $total = $theoryTotal + $practicalTotal;

                    /*
                    =============================
                    SUBJECT GP
                    =============================
                    */

                    $subjectGp = number_format($total / 10, 3);

                    /*
                    =============================
                    MAX MARKS
                    =============================
                    */

                    if ($sub->type == "Theory") {

                        $theoryFinalMax = $sub->external_marks;

                        $grandMax =
                            $sub->mid_sem_marks1 +
                            $sub->mid_sem_marks2 +
                            $sub->external_marks +
                            $sub->mid_sem_marks3;

                    } else {

                        $theoryFinalMax = $sub->external_marks;

                        $grandMax =
                            $sub->mid_sem_marks1 +
                            $sub->mid_sem_marks2 +
                            $sub->mid_sem_marks3;
                    }

                    /*
                    =============================
                    PASS RULE
                    =============================
                    */

                    $requiredMarks =
                        ($grandMax * $overallPassPercent) / 100;

                    $theoryMinMarks =
                        ($theoryFinalMax * $theoryMinPercent) / 100;

                    $theoryPassMarks =
                        ($theoryFinalMax * $overallPassPercent) / 100;

                    $theoryTotalMax =
                        $sub->mid_sem_marks1
                        + $sub->mid_sem_marks2
                        + $sub->external_marks;

                    $theoryTotalPassMarks =
                        ($theoryTotalMax * $overallPassPercent) / 100;

                    $practicalMinMarks =
                        ($sub->mid_sem_marks3 * 50) / 100;

                    $hasPractical = $sub->mid_sem_marks3 > 0;

                    /*
                    =============================
                    RESULT
                    =============================
                    */

                    if ($courseTypeName == 'Diploma') {

                        /*
                        =====================================
                        DIPLOMA RESULT LOGIC
                        =====================================
                        */

                        if ($sub->type == 'Theory') {

                            $theoryExamStatus =
                                strtolower(
                                    $endSemMarks[$student->id][$sub->id]['external'][0]->exam_status
                                    ?? 'present'
                                );

                            $practicalExamStatus =
                                strtolower(
                                    $endSemMarks[$student->id][$sub->id]['practical'][0]->exam_status
                                    ?? 'present'
                                );

                            /*
                            =====================================
                            ABSENT = FAIL
                            =====================================
                            */

                            if (
                                $theoryExamStatus == 'absent'
                                || $practicalExamStatus == 'absent'
                            ) {

                                $status = 'FAIL';

                                $class  = 'fail';

                                $value  = 'Fail';

                                $remarks[] = "Fail {$sub->subject_code}";
                            }

                            /*
                            =====================================
                            PASS
                            =====================================
                            */

                            elseif ($total >= $requiredMarks) {

                                $status = 'PASS';

                                $class  = 'pass';

                                $value  = number_format($total / 10, 3);
                            }

                            /*
                            =====================================
                            PRESENT BUT FAILED MARKS
                            =====================================
                            */

                            else {

                                $status = 'SUPPLE';

                                $class  = 'supply';

                                $value  = 'Supple';

                                $remarks[] = "Supple {$sub->subject_code}";
                            }

                        } else {

                            /*
                            =====================================
                            PRACTICAL SUBJECT
                            =====================================
                            */

                            $practicalExamStatus =
                                strtolower(
                                    $endSemMarks[$student->id][$sub->id]['practical'][0]->exam_status
                                    ?? 'present'
                                );

                            /*
                            =====================================
                            ABSENT = FAIL
                            =====================================
                            */

                            if ($practicalExamStatus == 'absent') {

                                $status = 'FAIL';

                                $class  = 'fail';

                                $value  = 'Fail';

                                $remarks[] = "Fail {$sub->subject_code}";
                            }

                            /*
                            =====================================
                            PASS
                            =====================================
                            */

                            elseif ($total >= $requiredMarks) {

                                $status = 'PASS';

                                $class  = 'pass';

                                $value  = number_format($total / 10, 3);
                            }

                            /*
                            =====================================
                            PRESENT BUT FAILED MARKS
                            =====================================
                            */

                            else {

                                $status = 'SUPPLE';

                                $class  = 'supply';

                                $value  = 'Supple';

                                $remarks[] = "Supple {$sub->subject_code}";
                            }
                        }

                    } else {

                        if ($sub->type == "Theory") {

                            /*
                            =============================
                            PRACTICAL FAIL CHECK
                            =============================
                            */

                            if (

                                //$practicalFinal > 0
                                // && $practicalFinal < $practicalMinMarks

                                //    $practicalFinal == 0
                                //     || (
                                //         $practicalFinal > 0
                                //         && $practicalFinal < $practicalMinMarks
                                //     )

                                $hasPractical &&
                                (
                                    $practicalFinal == 0
                                    || $practicalFinal < $practicalMinMarks
                                )

                            ) {

                                $status = 'FAIL';

                                $class  = 'fail';

                                $value  = 'Fail';

                                $remarks[] = "Fail {$sub->subject_code}";
                            }

                            /*
                            =============================
                            THEORY PASS
                            =============================
                            */

                            elseif (

                                $final < $theoryMinMarks

                            ) {

                                $status = 'FAIL';

                                $class  = 'fail';

                                $value  = 'Fail';

                                $remarks[] = "Fail {$sub->subject_code}";
                            }

                            /*
                            =============================
                            THEORY PASS
                            Theory Total >= Overall %
                            =============================
                            */

                            elseif (

                                $theoryTotal >= $theoryTotalPassMarks

                            ) {

                                $status = 'PASS';

                                $class  = 'pass';

                                $value  = number_format($total / 10, 3);
                            }

                            /*
                            =============================
                            THEORY SUPPLE
                            Theory Total < Overall %
                            but Final >= Min %
                            =============================
                            */

                            else {

                                $status = 'SUPPLE';

                                $class  = 'supply';

                                $value  = 'Supple';

                                $remarks[] = "Supple {$sub->subject_code}";
                            }

                        } else {

                            /*
                            =============================
                            PRACTICAL SUBJECT
                            =============================
                            */

                            if ($total >= $requiredMarks) {

                                $status = 'PASS';

                                $class  = 'pass';

                                $value  = number_format($total / 10, 3);

                            } else {

                                $status = 'FAIL';

                                $class  = 'fail';

                                $value  = 'Fail';

                                $remarks[] = "Fail {$sub->subject_code}";
                            }
                        }  // end result check login

                    }

                    $gpSheetData[] = [
                        'student_id' => $student->id,
                        'subject_id' => $sub->id,
                        'result' => $status,
                        'result_gp' => $subjectGp,
                        'grade' => '',
                        'exam_id' => $exam->id,
                        'course_type' => $courseType,
                        'course_id' => $course->id,
                        'scheme_id' => $schemeId ?? null,
                        'branch_id' => $branch->id,
                        'semester_id' => $semesterId ?? null,
                        'annual_semester_id' => $annualSemesterId ?? null,
                        'batch_id' => $batch->id,
                        'institute_id' => $instituteId ?? null,
                    ];

                    /*
                    =============================
                    GPA CREDIT CALCULATION
                    =============================
                    */

                    $credit = $sub->credits ?? 0;

                    // Har case me credit count hoga
                    $totalCreditHours += $credit;

                    // Weighted GP sirf tab add hoga jab numeric ho (PASS case)
                    if (is_numeric($value)) {

                        $totalWeightedGP += (float) number_format($value * $credit, 2, '.', '');
                    }

                }

                }  // end is enrolled + has marks check

                @endphp

                {{-- <td class="{{ $class }}">
                    <a href="javascript:void(0);"
                    class="result-detail-btn"
                    data-student_id="{{ $student->id }}"
                    data-subject_id="{{ $sub->id }}"
                    style="text-decoration:none; color:inherit; font-weight:bold;">
                        {{ $value }}
                    </a>
                </td> --}}

                <td class="{{ $class }}">

                    @if($sub->is_nc)

                        <span style="font-weight:bold;">
                            {{ $value }}
                        </span>

                    @else

                        <a href="javascript:void(0);"
                        class="result-detail-btn"
                        data-student_id="{{ $student->id }}"
                        data-subject_id="{{ $sub->id }}"
                        style="text-decoration:none; color:inherit; font-weight:bold;">
                            {{ $value }}
                        </a>

                    @endif

                </td>

            @endforeach

            @php
                $gpa = $totalCreditHours > 0
                    ? number_format($totalWeightedGP / $totalCreditHours, 3)
                    : 0;

                /*
                =========================
                CGPA
                =========================
                */

                $overallCredits =
                    $previousCreditHours
                    + $totalCreditHours;

                $overallWeightedGP = round(
                    $previousWeightedGP
                    + $totalWeightedGP,
                    2
                );

                // $cgpa = $overallCredits > 0
                //    ? number_format($overallWeightedGP / $overallCredits, 3)
                //    : 0;

                if ($cgpaBlocked) {

                    $cgpa = '';

                } else {

                    $cgpa = $overallCredits > 0
                        ? number_format($overallWeightedGP / $overallCredits, 3)
                        : '';
                }
            @endphp

            @php
                $finalGrade = '';

                if (!$cgpaBlocked) {
                    foreach($grades as $g){
                        if($cgpa >= $g->percent_from && $cgpa <= $g->percent_upto){
                            $finalGrade = $g->grade_description;
                            break;
                        }
                    }
                }
            @endphp

            @php

                $studentResultStatus = 'PASS';

                foreach ($remarks as $remark) {

                    if (str_contains($remark, 'Fail')) {
                        $studentResultStatus = 'FAIL';
                        break;
                    }

                    if (str_contains($remark, 'Supple')) {
                        $studentResultStatus = 'SUPPLE';
                    }
                }

                $summaryData[] = [
                    'student_id' => $student->id,
                    'gpa' => $gpa,
                    'cgpa' => $cgpa,
                    'result_status' => $studentResultStatus,
                    'grade_status' => $finalGrade,
                    'remarks' => count($remarks)
                        ? implode(', ', $remarks)
                        : 'Pass',
                    'exam_id' => $exam->id,
                    'course_type' => $courseType,
                    'course_id' => $course->id,
                    'scheme_id' => $schemeId ?? null,
                    'branch_id' => $branch->id,
                    'semester_id' => $semesterId ?? null,
                    'annual_semester_id' => $annualSemesterId ?? null,
                    'batch_id' => $batch->id,
                    'institute_id' => $instituteId ?? null,
                    'result_declare' => 1
                ];

            @endphp

            <td class="remarks">
                @if(count($remarks))
                    {{ implode(', ', $remarks) }}
                @else
                    <span class="pass">Pass</span>
                @endif
            </td>

            {{-- {{ $gpa }} --}}
            <td class="pass no-export">{{ $gpa }}</td>

            {{-- {{ $cgpa }} --}}
            <td class="pass no-export">
                {{-- {{ $cgpa }} --}}
                @if($cgpaBlocked)

                    -

                @else

                    {{ $cgpa }}

                @endif

            </td>

            {{-- {{ $finalGrade }} --}}
            <td class="pass no-export">
                {{-- {{ $finalGrade }} --}}
                @if($cgpaBlocked)

                    <span style="color:#c0392b;font-weight:bold;font-size:11px;">
                        {{ $cgpaMessage }}
                    </span>

                @else

                    {{ $finalGrade }}

                @endif
            </td>

        </tr>

    @endforeach

    </tbody>

    <tfoot>
        <tr>
            <td colspan="{{ 3 + count($filteredSubjects) + 4 }}">

                <table width="100%" style="border:none;">
                    <tr>

                        <td width="33.33%" style="border:none; text-align:left; font-size:12px;">
                            Prepared By
                            <br><br><br>
                            Checked By
                        </td>

                        <td width="33.33%" style="border:none; text-align:center; font-size:12px;">
                            Controller of Examinations
                        </td>

                        <td width="33.33%" style="border:none; text-align:right; font-size:12px;">
                            Registrar,<br>
                            Guru Angad Dev Veterinary and Animal Science University
                        </td>

                    </tr>
                </table>

            </td>
        </tr>
    </tfoot>
</table>


<!-- <h3>StudentResultSummary Preview</h3>

<table border="1" width="100%">
    <tr>
        <th>Student ID</th>
        <th>Course Type</th>
        <th>GPA</th>
        <th>CGPA</th>
        <th>Result Status</th>
        <th>Remarks</th>
        <th>Grade</th>
    </tr>

    @foreach($summaryData as $row)
        <tr>
            <td>{{ $row['student_id'] }}</td>
            <td>{{ $row['course_type'] }}</td>
            <td>{{ $row['gpa'] }}</td>
            <td>{{ $row['cgpa'] }}</td>
            <td>{{ $row['result_status'] }}</td>
            <td>{{ $row['remarks'] }}</td>
            <td>{{ $row['grade_status'] }}</td>
        </tr>
    @endforeach
</table> -->

<!-- <hr>
<h3>ResultGpSheet Preview</h3>

<table border="1" width="100%">
    <tr>
        <th>Student ID</th>
        <th>Subject ID</th>
        <th>Result</th>
        <th>Result GP</th>
        <th>Course Type</th>
    </tr>

    @foreach($gpSheetData as $row)
        <tr>
            <td>{{ $row['student_id'] }}</td>
            <td>{{ $row['subject_id'] }}</td>
            <td>{{ $row['result'] }}</td>
            <td>{{ $row['result_gp'] }}</td>
            <td>{{ $row['course_type'] }}</td>
        </tr>
    @endforeach
</table> -->

<input type="hidden" id="summaryData" value='@json($summaryData)'>
<input type="hidden" id="gpSheetData" value='@json($gpSheetData)'>

<div style="text-align:center; margin-top:20px;">
    <button id="saveResultSheet" class="btn btn-success">
        Save Result Sheet
    </button>
</div>

<script>
$(document).on('click', '#saveResultSheet', function () {

    let btn = $(this);

    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to save this result sheet?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Save it!'
    }).then((result) => {

        if (!result.isConfirmed) {
            return;
        }

        btn.prop('disabled', true);

        Swal.fire({
            title: 'Please wait...',
            text: 'Saving result sheet...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({

            url: "{{ route('results.save.result.sheet') }}",
            type: "POST",

            contentType: "application/json",
            processData: false,

            data: JSON.stringify({

                exam_id: "{{ $exam->id }}",

                summary_data: @json($summaryData),

                gp_sheet_data: @json($gpSheetData)

            }),

            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },

            success: function (res) {

                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: res.message ?? 'Result saved successfully',
                    timer: 2000,
                    showConfirmButton: false
                });

                btn.prop('disabled', false);
            },

            error: function (xhr) {

                let message = 'Error saving result';

                if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    text: message
                });

                btn.prop('disabled', false);
            }
        });

    });
});
</script>

