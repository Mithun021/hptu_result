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

    .supple-mark {
        font-weight: bold;
    }

    .absent-mark {
        color: red;
        font-weight: bold;
    }

    .old-mark {
        color: #777;
        font-size: 10px;
    }
</style>


@php

    /*
    |--------------------------------------------------------------------------
    | PASS PERCENTAGE
    |--------------------------------------------------------------------------
    */

    $overallPassPercent =
        $passPercentage->overall_pass_percentage ?? 50;


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

    $internalFirstMax =
        $internalMarksArray[0] ?? 0;

    $internalSecondMax =
        $internalMarksArray[1] ?? 0;


    /*
    |--------------------------------------------------------------------------
    | THEORY MAXIMUM
    |--------------------------------------------------------------------------
    */

    $theoryPaper1Max =
        $subject->paper1 ?? 0;

    $theoryPaper2Max =
        $subject->paper2 ?? 0;

    $theoryTotalMax =
        $theoryPaper1Max +
        $theoryPaper2Max;


    /*
    |--------------------------------------------------------------------------
    | GROSS THEORY MAXIMUM
    |--------------------------------------------------------------------------
    */

    $grossTheoryMax =
        $internalFirstMax +
        $internalSecondMax +
        $theoryTotalMax;


    /*
    |--------------------------------------------------------------------------
    | PRACTICAL MAXIMUM
    |--------------------------------------------------------------------------
    */

    $practicalPaper1Max =
        $subject->practical_paper1 ?? 0;

    $practicalPaper2Max =
        $subject->practical_paper2 ?? 0;

    $practicalTotalMax =
        $practicalPaper1Max +
        $practicalPaper2Max;


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

        {{-- HEADER --}}
        <tr>

            <td colspan="16" class="result-header">

                <center>

                    <h3>
                        OFFICE OF CONTROLLER OF EXAMINATIONS
                    </h3>

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


        {{-- MAIN HEADING --}}
        <tr>

            <td rowspan="2">
                Sr.No.
            </td>

            <td rowspan="2">
                Admission No
            </td>

            <td rowspan="2">
                Student Name
            </td>

            <td colspan="2">
                Internal<br>
                Assisment
            </td>

            <td colspan="4">
                Theory
            </td>

            <td colspan="3">
                Practical
            </td>

            <td rowspan="2">

                Gross <br>
                Total<br>

                ({{ number_format(
                    $grossTheoryMax + $practicalTotalMax,
                    0
                ) }})

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


        {{-- SUB HEADING --}}
        <tr>

            <td>

                First<br>

                ({{ number_format(
                    $internalFirstMax,
                    0
                ) }})

            </td>


            <td>

                Second<br>

                ({{ number_format(
                    $internalSecondMax,
                    0
                ) }})

            </td>


            <td>

                Paper<br>
                (A)<br>

                [{{ number_format(
                    $theoryPaper1Max,
                    0
                ) }}]

            </td>


            <td>

                Paper<br>
                (B)<br>

                [{{ number_format(
                    $theoryPaper2Max,
                    0
                ) }}]

            </td>


            <td>

                Paper<br>
                (A+B)<br>

                [{{ number_format(
                    $theoryTotalMax,
                    0
                ) }}]

            </td>


            <td>

                Gross Total<br>

                [{{ number_format(
                    $grossTheoryMax,
                    0
                ) }}]

            </td>


            <td>

                Paper<br>
                (A)<br>

                [{{ number_format(
                    $practicalPaper1Max,
                    0
                ) }}]

            </td>


            <td>

                Paper<br>
                (B)<br>

                [{{ number_format(
                    $practicalPaper2Max,
                    0
                ) }}]

            </td>


            <td>

                Paper<br>
                (A+B)<br>

                [{{ number_format(
                    $practicalTotalMax,
                    0
                ) }}]

            </td>

        </tr>

    </thead>


    <tbody>

        @foreach ($students as $key => $student)

            @php

                /*
                |--------------------------------------------------------------------------
                | ENROLLMENT
                |--------------------------------------------------------------------------
                */

                $isEnrolled =
                    in_array(
                        $student->id,
                        $studentSubjects
                    );


                /*
                |--------------------------------------------------------------------------
                | MARK EXISTENCE
                |--------------------------------------------------------------------------
                */

                $hasMid =
                    isset(
                        $midSemMarks[$student->id]
                    );

                $hasEnd =
                    isset(
                        $endSemMarks[$student->id]
                    );

                $hasSupple =
                    isset(
                        $suppleEndSemMarks[$student->id]
                    );


                $hasAnyMark =
                    $hasMid ||
                    $hasEnd ||
                    $hasSupple;

            @endphp


            @if (!$isEnrolled || !$hasAnyMark)

                <tr>

                    <td>
                        {{ $key + 1 }}
                    </td>

                    <td>
                        {{ $student->university_roll_no }}
                    </td>

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
                    |
                    | Grace marks are retained.
                    | Moderation marks are NOT used.
                    |--------------------------------------------------------------------------
                    */

                    $internal1Original =
                        $midSemMarks[$student->id]['Internal 1'][0]->obtained_marks ?? 0;

                    $internal2Original =
                        $midSemMarks[$student->id]['Internal 2'][0]->obtained_marks ?? 0;

                    $internal3Original =
                        $midSemMarks[$student->id]['Internal 3'][0]->obtained_marks ?? 0;


                    /*
                    |--------------------------------------------------------------------------
                    | GRACE MARKS
                    |--------------------------------------------------------------------------
                    */

                    $internal1Grace =
                        $midSemMarks[$student->id]['Internal 1'][0]->grace_marks ?? 0;

                    $internal2Grace =
                        $midSemMarks[$student->id]['Internal 2'][0]->grace_marks ?? 0;

                    $internal3Grace =
                        $midSemMarks[$student->id]['Internal 3'][0]->grace_marks ?? 0;


                    /*
                    |--------------------------------------------------------------------------
                    | FINAL INTERNAL MARKS
                    |--------------------------------------------------------------------------
                    */

                    $internal1 =
                        $internal1Original +
                        $internal1Grace;

                    $internal2 =
                        $internal2Original +
                        $internal2Grace;

                    $internal3 =
                        $internal3Original +
                        $internal3Grace;


                    /*
                    |--------------------------------------------------------------------------
                    | INTERNAL ARRAY
                    |--------------------------------------------------------------------------
                    */

                    $internalMarks = [

                        [
                            'final' =>
                                $internal1,

                            'original' =>
                                $internal1Original,

                            'grace' =>
                                $internal1Grace,

                            'label' =>
                                'Internal 1',
                        ],

                        [
                            'final' =>
                                $internal2,

                            'original' =>
                                $internal2Original,

                            'grace' =>
                                $internal2Grace,

                            'label' =>
                                'Internal 2',
                        ],

                        [
                            'final' =>
                                $internal3,

                            'original' =>
                                $internal3Original,

                            'grace' =>
                                $internal3Grace,

                            'label' =>
                                'Internal 3',
                        ],

                    ];


                    /*
                    |--------------------------------------------------------------------------
                    | SORT BEST TWO INTERNALS
                    |--------------------------------------------------------------------------
                    */

                    usort(
                        $internalMarks,
                        function ($a, $b) {

                            return
                                $b['final']
                                <=>
                                $a['final'];

                        }
                    );


                    $bestInternal1 =
                        $internalMarks[0];

                    $bestInternal2 =
                        $internalMarks[1];


                    /*
                    |--------------------------------------------------------------------------
                    | INTERNAL TOTAL
                    |--------------------------------------------------------------------------
                    */

                    $internalTotal =
                        $bestInternal1['final'] +
                        $bestInternal2['final'];


                    /*
                    |--------------------------------------------------------------------------
                    | REGULAR THEORY MARKS
                    |--------------------------------------------------------------------------
                    */

                    $theoryPaper1Regular =
                        $endSemMarks[$student->id]['theory_paper1'][0]->obtained_marks
                        ?? 0;

                    $theoryPaper2Regular =
                        $endSemMarks[$student->id]['theory_paper2'][0]->obtained_marks
                        ?? 0;


                    /*
                    |--------------------------------------------------------------------------
                    | SUPPLEMENTARY THEORY MARKS
                    |--------------------------------------------------------------------------
                    |
                    | Supplementary marks completely replace the
                    | respective regular external theory paper.
                    |
                    | NO MODERATION IS APPLIED.
                    |--------------------------------------------------------------------------
                    */

                    $theoryPaper1Supple =
                        $suppleEndSemMarks[$student->id]['theory_paper1'][0]
                        ?? null;

                    $theoryPaper2Supple =
                        $suppleEndSemMarks[$student->id]['theory_paper2'][0]
                        ?? null;


                    /*
                    |--------------------------------------------------------------------------
                    | THEORY PAPER 1 FINAL SOURCE
                    |--------------------------------------------------------------------------
                    */

                    if ($theoryPaper1Supple !== null) {

                        $theoryPaper1Original =
                            $theoryPaper1Supple->obtained_marks ?? 0;

                        $theoryPaper1IsSupple = true;

                    } else {

                        $theoryPaper1Original =
                            $theoryPaper1Regular;

                        $theoryPaper1IsSupple = false;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | THEORY PAPER 2 FINAL SOURCE
                    |--------------------------------------------------------------------------
                    */

                    if ($theoryPaper2Supple !== null) {

                        $theoryPaper2Original =
                            $theoryPaper2Supple->obtained_marks ?? 0;

                        $theoryPaper2IsSupple = true;

                    } else {

                        $theoryPaper2Original =
                            $theoryPaper2Regular;

                        $theoryPaper2IsSupple = false;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | FINAL THEORY MARKS
                    |--------------------------------------------------------------------------
                    |
                    | IMPORTANT:
                    | Only original / supplementary marks are used.
                    | Moderation marks are completely removed.
                    |--------------------------------------------------------------------------
                    */

                    $theoryPaper1 =
                        $theoryPaper1Original;

                    $theoryPaper2 =
                        $theoryPaper2Original;


                    /*
                    |--------------------------------------------------------------------------
                    | THEORY TOTAL
                    |--------------------------------------------------------------------------
                    */

                    $theoryTotal =
                        $theoryPaper1 +
                        $theoryPaper2;


                    /*
                    |--------------------------------------------------------------------------
                    | GROSS THEORY
                    |--------------------------------------------------------------------------
                    */

                    $grossTheory =
                        $internalTotal +
                        $theoryTotal;


                    /*
                    |--------------------------------------------------------------------------
                    | REGULAR PRACTICAL MARKS
                    |--------------------------------------------------------------------------
                    */

                    $practicalPaper1Regular =
                        $endSemMarks[$student->id]['practical_paper1'][0]->obtained_marks
                        ?? 0;

                    $practicalPaper2Regular =
                        $endSemMarks[$student->id]['practical_paper2'][0]->obtained_marks
                        ?? 0;


                    /*
                    |--------------------------------------------------------------------------
                    | SUPPLEMENTARY PRACTICAL MARKS
                    |--------------------------------------------------------------------------
                    */

                    $practicalPaper1Supple =
                        $suppleEndSemMarks[$student->id]['practical_paper1'][0]
                        ?? null;

                    $practicalPaper2Supple =
                        $suppleEndSemMarks[$student->id]['practical_paper2'][0]
                        ?? null;


                    /*
                    |--------------------------------------------------------------------------
                    | PRACTICAL PAPER 1 FINAL SOURCE
                    |--------------------------------------------------------------------------
                    */

                    if ($practicalPaper1Supple !== null) {

                        $practicalPaper1Original =
                            $practicalPaper1Supple->obtained_marks ?? 0;

                        $practicalPaper1IsSupple = true;

                    } else {

                        $practicalPaper1Original =
                            $practicalPaper1Regular;

                        $practicalPaper1IsSupple = false;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | PRACTICAL PAPER 2 FINAL SOURCE
                    |--------------------------------------------------------------------------
                    */

                    if ($practicalPaper2Supple !== null) {

                        $practicalPaper2Original =
                            $practicalPaper2Supple->obtained_marks ?? 0;

                        $practicalPaper2IsSupple = true;

                    } else {

                        $practicalPaper2Original =
                            $practicalPaper2Regular;

                        $practicalPaper2IsSupple = false;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | FINAL PRACTICAL MARKS
                    |--------------------------------------------------------------------------
                    |
                    | IMPORTANT:
                    | No moderation marks are applied.
                    |--------------------------------------------------------------------------
                    */

                    $practicalPaper1 =
                        $practicalPaper1Original;

                    $practicalPaper2 =
                        $practicalPaper2Original;


                    /*
                    |--------------------------------------------------------------------------
                    | PRACTICAL TOTAL
                    |--------------------------------------------------------------------------
                    */

                    $practicalTotal =
                        $practicalPaper1 +
                        $practicalPaper2;


                    /*
                    |--------------------------------------------------------------------------
                    | GRAND TOTAL
                    |--------------------------------------------------------------------------
                    */

                    $grandTotal =
                        $grossTheory +
                        $practicalTotal;


                    /*
                    |--------------------------------------------------------------------------
                    | GRADE POINT
                    |--------------------------------------------------------------------------
                    |
                    | Same formula as regular Annual result.
                    |--------------------------------------------------------------------------
                    */

                    $gradePoint =
                        round(
                            $grandTotal / 10,
                            3
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | PASS CHECK
                    |--------------------------------------------------------------------------
                    |
                    | Gross Theory and Practical are checked separately.
                    |--------------------------------------------------------------------------
                    */

                    $grossTheoryPass =
                        $grossTheory >=
                        $requiredGrossTheoryMarks;

                    $practicalPass =
                        $practicalTotal >=
                        $requiredPracticalMarks;


                    /*
                    |--------------------------------------------------------------------------
                    | RESULT STATUS
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $grossTheoryPass &&
                        $practicalPass
                    ) {

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
                    |
                    | Credit Point is calculated ONLY after PASS.
                    |
                    | Formula:
                    |
                    | Grade Point × Subject Credits
                    |--------------------------------------------------------------------------
                    */

                    if ($resultStatus === 'PASS') {

                        $creditPointValue =
                            $gradePoint *
                            ($subject->credits ?? 0);

                        $creditPoint =
                            number_format(
                                $creditPointValue,
                                2
                            );

                    } else {

                        $creditPointValue = null;

                        $creditPoint = 'FAIL';

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | OVERALL STANDING
                    |--------------------------------------------------------------------------
                    */

                    $overallStanding =
                        $creditPoint;

                @endphp


                <tr>

                    {{-- SR NO --}}
                    <td>
                        {{ $key + 1 }}
                    </td>


                    {{-- ADMISSION NO --}}
                    <td>
                        {{ $student->university_roll_no }}
                    </td>


                    {{-- STUDENT NAME --}}
                    <td class="student-name">
                        {{ $student->student_name }}
                    </td>


                    {{-- INTERNAL 1 --}}
                    <td>

                        {{ number_format(
                            $bestInternal1['final'],
                            2
                        ) }}

                        @if ($bestInternal1['grace'] > 0)

                            <small class="old-mark">

                                <br>

                                (
                                {{ $bestInternal1['original'] }}
                                +
                                {{ $bestInternal1['grace'] }}
                                )

                            </small>

                        @endif

                    </td>


                    {{-- INTERNAL 2 --}}
                    <td>

                        {{ number_format(
                            $bestInternal2['final'],
                            2
                        ) }}

                        @if ($bestInternal2['grace'] > 0)

                            <small class="old-mark">

                                <br>

                                (
                                {{ $bestInternal2['original'] }}
                                +
                                {{ $bestInternal2['grace'] }}
                                )

                            </small>

                        @endif

                    </td>


                    {{-- THEORY PAPER 1 --}}
                    <td>

                        <strong
                            @if ($theoryPaper1IsSupple)
                                class="supple-mark"
                            @endif
                        >

                            {{ number_format(
                                $theoryPaper1,
                                2
                            ) }}

                        </strong>


                        @if ($theoryPaper1IsSupple)

                            <small class="supple-mark no-export">

                                <br>
                                (Comp.)

                            </small>

                        @endif

                    </td>


                    {{-- THEORY PAPER 2 --}}
                    <td>

                        <strong
                            @if ($theoryPaper2IsSupple)
                                class="supple-mark"
                            @endif
                        >

                            {{ number_format(
                                $theoryPaper2,
                                2
                            ) }}

                        </strong>


                        @if ($theoryPaper2IsSupple)

                            <small class="supple-mark no-export">

                                <br>
                                (Comp.)

                            </small>

                        @endif

                    </td>


                    {{-- THEORY TOTAL --}}
                    <td>

                        {{ number_format(
                            $theoryTotal,
                            2
                        ) }}

                    </td>


                    {{-- GROSS THEORY --}}
                    <td>

                        {{ number_format(
                            $grossTheory,
                            2
                        ) }}

                    </td>


                    {{-- PRACTICAL PAPER 1 --}}
                    <td>

                        <strong
                            @if ($practicalPaper1IsSupple)
                                class="supple-mark"
                            @endif
                        >

                            {{ number_format(
                                $practicalPaper1,
                                2
                            ) }}

                        </strong>


                        @if ($practicalPaper1IsSupple)

                            <small class="supple-mark no-export">

                                <br>
                                (Supple)

                            </small>

                        @endif

                    </td>


                    {{-- PRACTICAL PAPER 2 --}}
                    <td>

                        <strong
                            @if ($practicalPaper2IsSupple)
                                class="supple-mark"
                            @endif
                        >

                            {{ number_format(
                                $practicalPaper2,
                                2
                            ) }}

                        </strong>


                        @if ($practicalPaper2IsSupple)

                            <small class="supple-mark no-export">

                                <br>
                                (Supple)

                            </small>

                        @endif

                    </td>


                    {{-- PRACTICAL TOTAL --}}
                    <td>

                        {{ number_format(
                            $practicalTotal,
                            2
                        ) }}

                    </td>


                    {{-- GRAND TOTAL --}}
                    <td>

                        <strong>

                            {{ number_format(
                                $grandTotal,
                                2
                            ) }}

                        </strong>

                    </td>


                    {{-- GRADE POINT --}}
                    <td>

                        {{ number_format(
                            $gradePoint,
                            3
                        ) }}

                    </td>


                    {{-- CREDIT POINT --}}
                    <td>

                        {{ $creditPoint }}

                    </td>


                    {{-- OVERALL STANDING --}}
                    <td>

                        {{ $overallStanding }}

                        <small class="no-export">

                            <br>

                            <strong
                                style="color: {{ $resultColor }}"
                            >

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