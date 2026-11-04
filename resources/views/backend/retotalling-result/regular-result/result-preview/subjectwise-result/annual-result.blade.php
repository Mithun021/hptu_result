<style>
    .result-container {
        width: 100%;
        margin: auto;
        font-family: Arial, Helvetica, sans-serif;
    }

    .result-header {
        text-align: center;
        margin-bottom: 10px;
    }

    .result-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: bold;
    }

    .result-header h4 {
        margin: 2px 0;
        font-size: 16px;
    }

    .result-header p {
        margin: 4px 0;
        font-size: 14px;
    }

    .course-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
        font-size: 12px;
    }

    .result-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .result-table th,
    .result-table td {
        border: 1px solid #000;
        padding: 6px;
        text-align: center;
    }

    .result-table th {
        background: #f2f2f2;
        font-weight: bold;
    }

    .result-table td.student-name {
        text-align: left;
    }

    td.align-left {
        text-align: left;
    }

    td.align-right {
        text-align: right;
    }
</style>

@php
    /*
    |--------------------------------------------------------------------------
    | PASS PERCENTAGE
    |--------------------------------------------------------------------------
    */

    $overallPassPercent = $passPercentage->overall_pass_percentage ?? 50;

    /*
    |--------------------------------------------------------------------------
    | MAXIMUM MARKS
    |--------------------------------------------------------------------------
    */

    $internalMarksArray = [
        $subject->internal1 ?? 0,
        $subject->internal2 ?? 0,
        $subject->internal3 ?? 0,
    ];

    rsort($internalMarksArray);

    $internalFirstMax = $internalMarksArray[0] ?? 0;
    $internalSecondMax = $internalMarksArray[1] ?? 0;

    $theoryPaper1Max = $subject->paper1 ?? 0;
    $theoryPaper2Max = $subject->paper2 ?? 0;
    $theoryTotalMax = $theoryPaper1Max + $theoryPaper2Max;

    $grossTheoryMax = $internalFirstMax + $internalSecondMax + $theoryTotalMax;

    $practicalPaper1Max = $subject->practical_paper1 ?? 0;
    $practicalPaper2Max = $subject->practical_paper2 ?? 0;
    $practicalTotalMax = $practicalPaper1Max + $practicalPaper2Max;

    /*
    |--------------------------------------------------------------------------
    | REQUIRED PASS MARKS
    |--------------------------------------------------------------------------
    */

    $requiredGrossTheoryMarks =
        ($grossTheoryMax * $overallPassPercent) / 100;

    $requiredPracticalMarks =
        ($practicalTotalMax * $overallPassPercent) / 100;
@endphp

<table class="result-table result-table-a4">
    <thead>
        <tr>
            <td colspan="16" class="result-header">
                <center>
                    <h3>OFFICE OF CONTROLLER OF EXAMINATIONS</h3>

                    <h4>
                        GURU ANGAD DEV VETERINARY AND ANIMAL SCIENCES UNIVERSITY,
                        LUDHIANA (INDIA)
                    </h4>

                    <h4>
                        {{ $exam->exam_resultsheet_name ?? $exam->name }}
                    </h4>
                </center>

                <div class="course-info">
                    <div>
                        <strong>Class :</strong>
                        ({{ $className }}) -
                        ({{ $course->name ?? '' }})

                        @if (!empty($branch->name) && $branch->name != 'NA')
                            ({{ $branch->name }})
                        @endif
                    </div>

                    <div>
                        <strong>Subject :</strong>
                        {{ $subject->name }}
                        ({{ $subject->subject_code }})
                    </div>
                </div>
            </td>
        </tr>

        <tr>
            <td rowspan="2">Sr.No.</td>
            <td rowspan="2">Admission No</td>
            <td rowspan="2">Student Name</td>

            <td colspan="2">
                Internal<br>Assisment
            </td>

            <td colspan="4">Theory</td>
            <td colspan="3">Practical</td>

            <td rowspan="2">
                Gross <br>
                Total<br>
                ({{ number_format($grossTheoryMax + $practicalTotalMax, 0) }})
            </td>

            <td rowspan="2">
                Grade <br>
                Point
            </td>

            <td rowspan="2">
                Credit <br>
                Point
            </td>

            <td rowspan="2">
                Overall <br>
                Standing
            </td>
        </tr>

        <tr>
            <td>
                First<br>
                ({{ number_format($internalFirstMax, 0) }})
            </td>

            <td>
                Second<br>
                ({{ number_format($internalSecondMax, 0) }})
            </td>

            <td>
                Paper<br>(A)<br>
                [{{ number_format($theoryPaper1Max, 0) }}]
            </td>

            <td>
                Paper<br>(B)<br>
                [{{ number_format($theoryPaper2Max, 0) }}]
            </td>

            <td>
                Paper<br>(A+B)<br>
                [{{ number_format($theoryTotalMax, 0) }}]
            </td>

            <td>
                Gross Total<br>
                [{{ number_format($grossTheoryMax, 0) }}]
            </td>

            <td>
                Paper<br>(A)<br>
                [{{ number_format($practicalPaper1Max, 0) }}]
            </td>

            <td>
                Paper<br>(B)<br>
                [{{ number_format($practicalPaper2Max, 0) }}]
            </td>

            <td>
                Paper<br>(A+B)<br>
                [{{ number_format($practicalTotalMax, 0) }}]
            </td>
        </tr>
    </thead>

    <tbody>
        @foreach ($students as $key => $student)
            @php
                /*
                |--------------------------------------------------------------------------
                | ENROLLMENT CHECK
                |--------------------------------------------------------------------------
                */

                $isEnrolled = in_array($student->id, $studentSubjects);
            @endphp

            @if (!$isEnrolled)
                <tr>
                    <td>{{ $key + 1 }}</td>

                    <td>{{ $student->university_roll_no }}</td>

                    <td class="student-name">
                        {{ $student->student_name }}
                    </td>

                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
            @else
                @php
                    /*
                    |--------------------------------------------------------------------------
                    | INTERNAL MARKS
                    |--------------------------------------------------------------------------
                    */

                    $internal1Original =
                        $midSemMarks[$student->id]['Internal 1'][0]->obtained_marks ?? 0;

                    $internal2Original =
                        $midSemMarks[$student->id]['Internal 2'][0]->obtained_marks ?? 0;

                    $internal3Original =
                        $midSemMarks[$student->id]['Internal 3'][0]->obtained_marks ?? 0;

                    $internal1Grace =
                        $midSemMarks[$student->id]['Internal 1'][0]->grace_marks ?? 0;

                    $internal2Grace =
                        $midSemMarks[$student->id]['Internal 2'][0]->grace_marks ?? 0;

                    $internal3Grace =
                        $midSemMarks[$student->id]['Internal 3'][0]->grace_marks ?? 0;

                    $internal1 = $internal1Original + $internal1Grace;
                    $internal2 = $internal2Original + $internal2Grace;
                    $internal3 = $internal3Original + $internal3Grace;

                    $internalMarks = [
                        [
                            'final' => $internal1,
                            'original' => $internal1Original,
                            'grace' => $internal1Grace,
                            'label' => 'Internal 1',
                        ],
                        [
                            'final' => $internal2,
                            'original' => $internal2Original,
                            'grace' => $internal2Grace,
                            'label' => 'Internal 2',
                        ],
                        [
                            'final' => $internal3,
                            'original' => $internal3Original,
                            'grace' => $internal3Grace,
                            'label' => 'Internal 3',
                        ],
                    ];

                    usort($internalMarks, function ($a, $b) {
                        return $b['final'] <=> $a['final'];
                    });

                    $bestInternal1 = $internalMarks[0];
                    $bestInternal2 = $internalMarks[1];

                    $internalTotal =
                        $bestInternal1['final'] +
                        $bestInternal2['final'];

                    /*
                    |--------------------------------------------------------------------------
                    | THEORY MARKS
                    |--------------------------------------------------------------------------
                    */

                    $theoryPaper1Data =
                        $endSemMarks[$student->id]['theory_paper1'][0] ?? null;

                    $theoryPaper2Data =
                        $endSemMarks[$student->id]['theory_paper2'][0] ?? null;

                    /*
                    |--------------------------------------------------------------------------
                    | THEORY PAPER 1 RETOTALLING
                    |--------------------------------------------------------------------------
                    */

                    $theoryPaper1RetotallingMarks =
                        $theoryPaper1Data->retotalling_obtained_marks ?? null;

                    $theoryPaper1RetotallingLocked =
                        $theoryPaper1Data->retotalling_marks_locked ?? 0;

                    $theoryPaper1Original =
                        $theoryPaper1RetotallingMarks !== null &&
                        (float) $theoryPaper1RetotallingMarks != 0 &&
                        (int) $theoryPaper1RetotallingLocked === 1
                            ? $theoryPaper1RetotallingMarks
                            : ($theoryPaper1Data->obtained_marks ?? 0);

                    /*
                    |--------------------------------------------------------------------------
                    | THEORY PAPER 2 RETOTALLING
                    |--------------------------------------------------------------------------
                    */

                    $theoryPaper2RetotallingMarks =
                        $theoryPaper2Data->retotalling_obtained_marks ?? null;

                    $theoryPaper2RetotallingLocked =
                        $theoryPaper2Data->retotalling_marks_locked ?? 0;

                    $theoryPaper2Original =
                        $theoryPaper2RetotallingMarks !== null &&
                        (float) $theoryPaper2RetotallingMarks != 0 &&
                        (int) $theoryPaper2RetotallingLocked === 1
                            ? $theoryPaper2RetotallingMarks
                            : ($theoryPaper2Data->obtained_marks ?? 0);

                    /*
                    |--------------------------------------------------------------------------
                    | THEORY MODERATION
                    |--------------------------------------------------------------------------
                    */

                    $theoryPaper1Moderation =
                        $adjustmentMarks[$student->id]['theory_paper1'] ?? 0;

                    $theoryPaper2Moderation =
                        $adjustmentMarks[$student->id]['theory_paper2'] ?? 0;

                    $theoryPaper1 =
                        $theoryPaper1Original + $theoryPaper1Moderation;

                    $theoryPaper2 =
                        $theoryPaper2Original + $theoryPaper2Moderation;

                    $theoryTotal =
                        $theoryPaper1 + $theoryPaper2;

                    /*
                    |--------------------------------------------------------------------------
                    | GROSS THEORY
                    |--------------------------------------------------------------------------
                    */

                    $grossTheory =
                        $internalTotal + $theoryTotal;

                    /*
                    |--------------------------------------------------------------------------
                    | PRACTICAL MARKS
                    |--------------------------------------------------------------------------
                    */

                    $practicalPaper1Original =
                        $endSemMarks[$student->id]['practical_paper1'][0]->obtained_marks ?? 0;

                    $practicalPaper2Original =
                        $endSemMarks[$student->id]['practical_paper2'][0]->obtained_marks ?? 0;

                    $practicalPaper1Moderation =
                        $adjustmentMarks[$student->id]['practical_paper1'] ?? 0;

                    $practicalPaper2Moderation =
                        $adjustmentMarks[$student->id]['practical_paper2'] ?? 0;

                    $practicalPaper1 =
                        $practicalPaper1Original + $practicalPaper1Moderation;

                    $practicalPaper2 =
                        $practicalPaper2Original + $practicalPaper2Moderation;

                    $practicalTotal =
                        $practicalPaper1 + $practicalPaper2;

                    /*
                    |--------------------------------------------------------------------------
                    | GRAND TOTAL
                    |--------------------------------------------------------------------------
                    */

                    $grandTotal =
                        $grossTheory + $practicalTotal;

                    /*
                    |--------------------------------------------------------------------------
                    | GRADE POINT
                    |--------------------------------------------------------------------------
                    */

                    $gradePoint =
                        round($grandTotal / 10, 3);

                    /*
                    |--------------------------------------------------------------------------
                    | RESULT
                    |--------------------------------------------------------------------------
                    */

                    $grossTheoryPass =
                        $grossTheory >= $requiredGrossTheoryMarks;

                    $practicalPass =
                        $practicalTotal >= $requiredPracticalMarks;

                    if ($grossTheoryPass && $practicalPass) {
                        $resultStatus = 'PASS';
                        $resultColor = 'green';
                    } else {
                        $resultStatus = 'FAIL';
                        $resultColor = 'red';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | CREDIT POINT
                    |--------------------------------------------------------------------------
                    */

                    if ($resultStatus == 'PASS') {
                        $creditPoint = number_format(
                            $gradePoint * ($subject->credits ?? 0),
                            2
                        );
                    } else {
                        $creditPoint = 'FAIL';
                    }
                @endphp

                <tr>
                    <td>{{ $key + 1 }}</td>

                    <td>
                        {{ $student->university_roll_no }}
                    </td>

                    <td class="student-name">
                        {{ $student->student_name }}
                    </td>

                    <td>
                        {{ number_format($bestInternal1['final'], 2) }}

                        @if ($bestInternal1['grace'] > 0)
                            <small class="no-export">
                                <br>
                                ({{ $bestInternal1['original'] }}
                                + {{ $bestInternal1['grace'] }})
                            </small>
                        @endif
                    </td>

                    <td>
                        {{ number_format($bestInternal2['final'], 2) }}

                        @if ($bestInternal2['grace'] > 0)
                            <small class="no-export">
                                <br>
                                ({{ $bestInternal2['original'] }}
                                + {{ $bestInternal2['grace'] }})
                            </small>
                        @endif
                    </td>

                    <td>
                        {{ number_format($theoryPaper1, 2) }}

                        @if ($theoryPaper1Moderation > 0)
                            <small class="no-export">
                                <br>
                                ({{ $theoryPaper1Original }}
                                + {{ $theoryPaper1Moderation }})
                            </small>
                        @endif
                    </td>

                    <td>
                        {{ number_format($theoryPaper2, 2) }}

                        @if ($theoryPaper2Moderation > 0)
                            <small class="no-export">
                                <br>
                                ({{ $theoryPaper2Original }}
                                + {{ $theoryPaper2Moderation }})
                            </small>
                        @endif
                    </td>

                    <td>
                        {{ number_format($theoryTotal, 2) }}
                    </td>

                    <td>
                        {{ number_format($grossTheory, 2) }}
                    </td>

                    <td>
                        {{ number_format($practicalPaper1, 2) }}

                        @if ($practicalPaper1Moderation > 0)
                            <small class="no-export">
                                <br>
                                ({{ $practicalPaper1Original }}
                                + {{ $practicalPaper1Moderation }})
                            </small>
                        @endif
                    </td>

                    <td>
                        {{ number_format($practicalPaper2, 2) }}

                        @if ($practicalPaper2Moderation > 0)
                            <small class="no-export">
                                <br>
                                ({{ $practicalPaper2Original }}
                                + {{ $practicalPaper2Moderation }})
                            </small>
                        @endif
                    </td>

                    <td>
                        {{ number_format($practicalTotal, 2) }}
                    </td>

                    <td>
                        <strong>
                            {{ number_format($grandTotal, 2) }}
                        </strong>
                    </td>

                    <td>
                        {{ number_format($gradePoint, 3) }}
                    </td>

                    <td>
                        {{ $creditPoint }}
                    </td>

                    <td>
                        {{ $creditPoint }}

                        <small class="no-export">
                            <br>
                            <strong style="color: {{ $resultColor }}">
                                {{ $resultStatus }}
                            </strong>
                        </small>
                    </td>
                </tr>
            @endif
        @endforeach
    </tbody>

    <tfoot>
        <tr>
            <td colspan="5" class="align-left">
                Prepared by
                <br><br><br>
                Checked by
            </td>

            <td colspan="5">
                Controller of Examinations
            </td>

            <td colspan="6" class="align-right">
                Registrar, Guru Angad Dev Veterinary
                <br>
                and Animal Science University
            </td>
        </tr>
    </tfoot>
</table>
