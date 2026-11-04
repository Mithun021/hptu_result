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
                    {{ $exam->supple_result_sheet ?? $exam->name }}
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
            <th rowspan="2">Admission No.</th>
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

        @php $srNo = 1; @endphp

        @foreach($students as $student)

        <tr>

            <td>{{ $srNo++ }}</td>

            <td class="left">{{ $student->student_name }}</td>

            <td>{{ $student->university_roll_no }}</td>

            @php
                $grandTotalMarks = 0;   // SAME AS SUBJECT-WISE G TOTAL
                $remarks = [];
                // GPA Calculation
                $totalCreditHours = 0;
                $totalWeightedGP = 0;
            @endphp

            @foreach($filteredSubjects as $sub)

                @php

                /*
                =========================================================
                IS THIS SUBJECT SUPPLEMENTARY?
                =========================================================
                */

                $isSupple =
                    isset($suppleSubjectMap[$student->id][$sub->id]);

                /*
                =========================================================
                ORIGINAL SAVED RESULT
                =========================================================
                */

                $savedRow =
                    $resultGpSheet[$student->id][$sub->id][0] ?? null;

                $credit = $sub->credits ?? 0;

                /*
                =========================================================
                SUPPLE EXTERNAL MARK
                =========================================================
                */

                $suppleExternal =
                    $suppleEndSemMarks[$student->id][$sub->id]['external'][0] ?? null;

                $hasSuppleMarks = !is_null($suppleExternal);

                /*
                =========================================================
                DEFAULT
                =========================================================
                */

                $class = '';
                $value = '-';
                $smallGp = null;

                /*
                =========================================================
                CASE 1:
                NOT SUPPLE
                OR
                SUPPLE BUT MARKS NOT UPLOADED
                → SHOW ORIGINAL SAVED RESULT
                =========================================================
                */

                if (!$isSupple || ($isSupple && !$hasSuppleMarks)) {

                    if ($savedRow) {

                        $savedGp = number_format($savedRow->result_gp, 3);

                        if ($savedRow->result == 'PASS') {

                            $class = 'pass';
                            $value = $savedGp;

                            // GP → Marks conversion
                            $grandTotalMarks += ($savedRow->result_gp * 10);
                            $totalCreditHours += $credit;

                            $totalWeightedGP +=
                                ($savedRow->result_gp * $credit);

                        } elseif ($savedRow->result == 'SUPPLE') {

                            $class = 'supply';
                            $value = 'Supple';
                            $smallGp = $savedGp;

                            $remarks[] = 'Supple ' . $sub->subject_code;

                            $totalCreditHours += $credit;

                        } else {

                            $class = 'fail';
                            $value = 'Fail';
                            $smallGp = $savedGp;

                            $remarks[] = 'Fail ' . $sub->subject_code;

                            $totalCreditHours += $credit;
                        }
                    }

                } else {

                    /*
                    =========================================================
                    CASE 2:
                    SUPPLEMENTARY MARKS UPLOADED
                    APPLY SUBJECT-WISE SUPPLE LOGIC
                    =========================================================
                    */

                    /*
                    -----------------------------
                    INTERNAL MARKS
                    -----------------------------
                    */

                    $mid =
                        $midSemMarks[$student->id][$sub->id]['Mid-Sem'][0]->obtained_marks ?? 0;

                    $quiz =
                        $midSemMarks[$student->id][$sub->id]['Quiz'][0]->obtained_marks ?? 0;

                    /*
                    -----------------------------
                    SUPPLE EXTERNAL
                    -----------------------------
                    */

                    $external =
                        $suppleExternal->obtained_marks ?? 0;

                    $grace =
                        $suppleExternal->grace_marks ?? 0;

                    /*
                    =========================================================
                    THEORY TOTAL
                    SAME AS SUBJECT-WISE RESULT
                    =========================================================
                    */

                    $theoryTotal =
                        $mid
                        + $quiz
                        + $external
                        + $grace;

                    /*
                    =========================================================
                    PRACTICAL TOTAL
                    SAME AS SUBJECT-WISE RESULT
                    =========================================================
                    */

                    $practicalFinal = 0;

                    if ($sub->type == 'Theory') {

                        // Theory subject practical component
                        $practicalFinal =
                            $midSemMarks[$student->id][$sub->id]['Practical'][0]->obtained_marks ?? 0;
                    }

                    $practicalTotal = $practicalFinal;

                    /*
                    =========================================================
                    G TOTAL
                    SAME AS SUBJECT-WISE RESULT
                    =========================================================
                    */

                    $grandSubjectTotal =
                        $theoryTotal + $practicalTotal;

                    /*
                    =========================================================
                    THEORY PASS RULE
                    Mid + Quiz + External + Grace
                    >= 50% of Theory Max
                    =========================================================
                    */

                    $theoryTotalMax =
                        $sub->mid_sem_marks1
                        + $sub->mid_sem_marks2
                        + $sub->external_marks;

                    $requiredMarks =
                        ($theoryTotalMax * 50) / 100;

                    /*
                    =========================================================
                    GP
                    SAME AS SUBJECT-WISE RESULT
                    GP = G TOTAL / 10
                    =========================================================
                    */

                    $calculatedGp =
                        number_format($grandSubjectTotal / 10, 3);

                    /*
                    =========================================================
                    FINAL RESULT
                    =========================================================
                    */

                    if ($theoryTotal >= $requiredMarks) {

                        // PASS → show GP only

                        $class = 'pass';
                        $value = $calculatedGp;

                        // Add actual G Total
                        $grandTotalMarks += $grandSubjectTotal;

                        $totalCreditHours += $credit;

                        $totalWeightedGP +=
                            ($calculatedGp * $credit);

                    } else {

                        // FAIL → show Fail + small GP

                        $class = 'fail';
                        $value = 'Fail';
                        $smallGp = $calculatedGp;

                        $remarks[] = 'Fail ' . $sub->subject_code;

                        $totalCreditHours += $credit;
                    }
                }

                @endphp

                <td class="{{ $class }}">

                    <div style="font-weight:bold; font-size:13px;">

                        {{-- Same color as class, only add * when supple marks uploaded --}}
                        @if($isSupple && $hasSuppleMarks)

                            {{ $value }}<sup style="font-size:10px;">*</sup>

                        @else

                            {{ $value }}

                        @endif

                    </div>

                    @if($smallGp)

                        <small style="font-size:10px; color:#555;" class="no-export">
                            {{ $smallGp }}
                        </small>

                    @endif

                </td>

            @endforeach

            @php

                /*
                =========================================================
                CP / GPA / CGPA
                SAME AS SUBJECT-WISE SHEET
                CP = GRAND TOTAL / 10
                =========================================================
                */

                $gpa =
                    $totalCreditHours > 0
                        ? number_format(
                            $totalWeightedGP /
                            $totalCreditHours,
                            3
                        )
                        : 0;

                $cgpa = $gpa;

            @endphp

            <td class="remarks">

                @if(count($remarks))

                    {{ implode(', ', $remarks) }}

                @else

                    <span class="pass">Pass</span>

                @endif

            </td>

            <td class="pass no-export">{{ $gpa }}</td>

            <td class="pass no-export">{{ $cgpa }}</td>

            <td class="pass no-export">-</td>

        </tr>

        @endforeach

        </tbody>


</table>
