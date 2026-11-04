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
=====================================
SUBJECT MAX MARKS
=====================================
*/

if ($subject->type == "Theory") {

    $theoryMidMax   = $subject->mid_sem_marks1;
    $theoryQuizMax  = $subject->mid_sem_marks2;
    $theoryFinalMax = $subject->external_marks;

    $practicalMidMax   = 0;
    $practicalQuizMax  = 0;
    $practicalFinalMax = $subject->mid_sem_marks3;

} else {

    $theoryMidMax   = 0;
    $theoryQuizMax  = 0;
    $theoryFinalMax = 0;

    $practicalMidMax   = $subject->mid_sem_marks1;
    $practicalQuizMax  = $subject->mid_sem_marks2;
    $practicalFinalMax = $subject->mid_sem_marks3;
}

$theoryTotalMax    = $theoryMidMax + $theoryQuizMax + $theoryFinalMax;
$practicalTotalMax = $practicalMidMax + $practicalQuizMax + $practicalFinalMax;
$grandTotalMax     = $theoryTotalMax + $practicalTotalMax;

/*
=====================================
PASS RULE
=====================================
*/

$overallPassPercent = $passPercentage->overall_pass_percentage ?? 50;
$theoryMinPercent   = $passPercentage->theory_min_percentage ?? 20;

$requiredMarks  = ($grandTotalMax * $overallPassPercent) / 100;
$theoryMinMarks = ($theoryFinalMax * $theoryMinPercent) / 100;
$theoryPassMarks = ($theoryFinalMax * $overallPassPercent) / 100;
$theoryTotalPassMarks = ($theoryTotalMax * $overallPassPercent) / 100;

$practicalMinMarks = ($practicalFinalMax * 50) / 100;
$courseTypeName = $course->course;
@endphp


<table class="result-table result-table-a4">

<thead>

<tr>
<td colspan="{{ $courseTypeName == 'Diploma' ? 12 : 14 }}" class="result-header">

    <center>
        <h3>OFFICE OF CONTROLLER OF EXAMINATIONS</h3>
        <h4>GURU ANGAD DEV VETERINARY AND ANIMAL SCIENCES UNIVERSITY, LUDHIANA (INDIA)</h4>
        <h4>{{ $exam->exam_resultsheet_name ?? $exam->name }}</h4>
    </center>

    <div class="course-info">

        <div>
            <strong>Class :</strong>

            ({{ $className }}) -

            ({{ $course->name ?? '' }})

            @if(!empty($branch->name) && $branch->name != 'NA')
                ({{ $branch->name }})
            @endif
        </div>

        <div>
            <strong>Subject :</strong>
            {{ $subject->name }} ({{ $subject->subject_code }})
        </div>

    </div>

</td>
</tr>

<tr>

<th rowspan="3">Sr No</th>
<th rowspan="3">Student Name</th>
<th rowspan="3">Admission No</th>
<th rowspan="3">CP</th>

@if($subject->type == "Theory")

    {{-- THEORY SUBJECT --}}

    <th colspan="{{ ($course->course ?? '') == 'Diploma' ? 3 : 4 }}">Theory</th>
    <th rowspan="2">Total</th>

    <th colspan="{{ ($course->course ?? '') == 'Diploma' ? 2 : 3 }}">Practical</th>
    <th rowspan="2">Total</th>

@else

    {{-- PRACTICAL SUBJECT --}}

    <th colspan="{{ ($course->course ?? '') == 'Diploma' ? 2 : 3 }}">Theory</th>
    <th rowspan="2">Total</th>

    <th colspan="{{ ($course->course ?? '') == 'Diploma' ? 3 : 4 }}">Practical</th>
    <th rowspan="2">Total</th>

@endif

<th rowspan="2">G Total</th>

</tr>

<tr>

@if($subject->type == "Theory")

    <th>Mid Sem</th>
    @if($courseTypeName !== "Diploma")
    <th>Quiz</th>
    @endif
    <th>Grace</th>
    <th>Final</th>

    <th>Mid Sem</th>
    @if($courseTypeName !== "Diploma")
    <th>Quiz</th>
    @endif
    <th>Final</th>

@else

    <th>Mid Sem</th>
    @if($courseTypeName !== "Diploma")
    <th>Quiz</th>
    @endif
    <th>Final</th>

    <th>Mid Sem</th>
    @if($courseTypeName !== "Diploma")
    <th>Quiz</th>
    @endif
    <th>Grace</th>
    <th>Final</th>

@endif

</tr>

<tr>

@if($subject->type == "Theory")

    <th>{{ number_format($theoryMidMax, 2) }}</th>
    @if($courseTypeName !== "Diploma")
    <th>{{ number_format($theoryQuizMax, 2) }}</th>
    @endif
    <th>-</th>
    <th>{{ number_format($theoryFinalMax, 2) }}</th>
    <th>{{ number_format($theoryTotalMax, 2) }}</th>

    <th>{{ number_format($practicalMidMax, 2) }}</th>
    @if($courseTypeName !== "Diploma")
    <th>{{ number_format($practicalQuizMax, 2) }}</th>
    @endif
    <th>{{ number_format($practicalFinalMax, 2) }}</th>
    <th>{{ number_format($practicalTotalMax, 2) }}</th>

@else

    <th>{{ number_format($theoryMidMax, 2) }}</th>
    @if($courseTypeName !== "Diploma")
    <th>{{ number_format($theoryQuizMax, 2) }}</th>
    @endif
    <th>{{ number_format($theoryFinalMax, 2) }}</th>
    <th>{{ number_format($theoryTotalMax, 2) }}</th>

    <th>{{ number_format($practicalMidMax, 2) }}</th>
    @if($courseTypeName !== "Diploma")
    <th>{{ number_format($practicalQuizMax, 2) }}</th>
    @endif
    <th>-</th>
    <th>{{ number_format($practicalFinalMax, 2) }}</th>
    <th>{{ number_format($practicalTotalMax, 2) }}</th>

@endif

<th>{{ number_format($grandTotalMax, 2) }}</th>

</tr>

</thead>

<tbody>
@php
    $srNo = 1;
@endphp
@foreach($students as $key => $student)

@php

    // ✅ Enrollment check
    $isEnrolled = in_array($student->id, $studentSubjects);

    // ✅ Marks existence check
    $hasMid = isset($midSemMarks[$student->id]);
    $hasEnd = isset($endSemMarks[$student->id]);

    $hasAnyMark = $hasMid || $hasEnd;

@endphp

@if(!$isEnrolled)
    @continue
@endif

@if(!$isEnrolled || !$hasAnyMark)

<tr>

    <td>{{ $srNo++ }}</td>
    <td class="student-name">{{ $student->student_name }}</td>
    <td>{{ $student->university_roll_no }}</td>

    <td>-</td>

    @if($subject->type == "Theory")

        <td>-</td>
        @if($courseTypeName !== "Diploma")
        <td>-</td>
        @endif
        <td>-</td>
        <td>-</td>
        <td>-</td>

        <td>-</td>
        @if($courseTypeName !== "Diploma")
        <td>-</td>
        @endif
        <td>-</td>
        <td>-</td>

    @else

        <td>-</td>
        @if($courseTypeName !== "Diploma")
        <td>-</td>
        @endif
        <td>-</td>
        <td>-</td>

        <td>-</td>
        @if($courseTypeName !== "Diploma")
        <td>-</td>
        @endif
        <td>-</td>
        <td>-</td>
        <td>-</td>

    @endif

    <td>-</td>

</tr>

@else

@php

/*
=====================================
COMMON MARKS
=====================================
*/

$mid =
    $midSemMarks[$student->id]['Mid-Sem'][0]->obtained_marks ?? 0;

$quiz =
    $midSemMarks[$student->id]['Quiz'][0]->obtained_marks ?? 0;

$pracFromMid =
    $midSemMarks[$student->id]['Practical'][0]->obtained_marks ?? 0;

/*
=====================================
THEORY SUBJECT
=====================================
*/

if ($subject->type == "Theory") {

    // $endFinalOriginal =
    //     $endSemMarks[$student->id]->obtained_marks ?? 0;

    if ($courseTypeName == "Diploma") {

        $endFinalOriginal =
            $endSemMarks[$student->id]['external'][0]->obtained_marks ?? 0;

    } else {

        $endFinalOriginal =
            $endSemMarks[$student->id]->obtained_marks ?? 0;
    }

    // $endFinalGrace =
    //     $endSemMarks[$student->id]->grace_marks ?? 0;

    if ($courseTypeName == "Diploma") {

        $endFinalGrace =
            $endSemMarks[$student->id]['external'][0]->grace_marks ?? 0;

    } else {

        $endFinalGrace =
            $endSemMarks[$student->id]->grace_marks ?? 0;
    }

    $endFinalModeration =
        $adjustmentMarks[$student->id]['external'] ?? 0;

    $endFinal =
        $endFinalOriginal
        + $endFinalModeration;
    /*
    =====================================
    THEORY
    =====================================
    */

    $final = $endFinal;

    $theoryGrace = $endFinalGrace;

    $theoryTotal =
        $mid
        + $quiz
        + $final
        + $endFinalGrace;
    /*
    =====================================
    PRACTICAL
    =====================================
    */

    $practicalMid   = 0;
    $practicalQuiz  = 0;

    // $practicalFinal =
    //     ($courseTypeName == "UG")
    //         ? $pracFromMid
    //         : $endFinal;

    if ($courseTypeName == "Diploma") {

        $practicalFinal =
            $endSemMarks[$student->id]['practical'][0]->obtained_marks ?? 0;

    } else {

        // Existing UG Logic
        $practicalFinal = $pracFromMid;
    }

    $practicalTotal = $practicalFinal;

} else {

    /*
    =====================================
    PRACTICAL SUBJECT
    =====================================
    */

    $mid = $quiz = $final = 0;

    $theoryTotal = 0;

    /*
    =====================================
    PRACTICAL GRACE
    =====================================
    */

    // $endFinalOriginal = $pracFromMid;

    // $endFinalGrace =
    //     $midSemMarks[$student->id]['Practical'][0]->grace_marks ?? 0;

    if ($courseTypeName == "Diploma") {

        $endFinalOriginal =
            $endSemMarks[$student->id]['practical'][0]->obtained_marks ?? 0;

        $endFinalGrace =
            $endSemMarks[$student->id]['practical'][0]->grace_marks ?? 0;

    } else {

        // Existing UG Logic
        $endFinalOriginal = $pracFromMid;

        $endFinalGrace =
            $midSemMarks[$student->id]['Practical'][0]->grace_marks ?? 0;
    }

    $endFinalModeration =
        $adjustmentMarks[$student->id]['external'] ?? 0;

    // $endFinal =
    //     $endFinalOriginal
    //     + $endFinalGrace
    //     + $endFinalModeration;

    $endFinal =
        $endFinalOriginal
        + $endFinalModeration;

    /*
    =====================================
    PRACTICAL
    =====================================
    */

    $practicalMid =
        $midSemMarks[$student->id]['Mid-Sem'][0]->obtained_marks ?? 0;

    $practicalQuiz =
        $midSemMarks[$student->id]['Quiz'][0]->obtained_marks ?? 0;

    $practicalGrace = $endFinalGrace;

    $practicalFinal = $endFinal;

    $practicalTotal =
        $practicalMid
        + $practicalQuiz
        + $practicalFinal
        + $endFinalGrace;
}

/*
=====================================
TOTAL
=====================================
*/

$grandTotal = $theoryTotal + $practicalTotal;

$cp = $grandTotal / 10;


/*
=====================================
RESULT LOGIC
=====================================
*/

if ($courseTypeName == 'Diploma') {

    /*
    =====================================
    DIPLOMA RESULT LOGIC
    =====================================
    */

    // if (

    //     (
    //         $subject->type == 'Theory'
    //         &&
    //         ($final == 0 || $practicalFinal == 0)
    //     )

    //     ||

    //     (
    //         $subject->type == 'Practical'
    //         &&
    //         $practicalFinal == 0
    //     )

    // ) {

    //     $resultStatus = 'FAIL';
    //     $resultColor  = 'red';

    // } elseif ($grandTotal >= $requiredMarks) {

    //     $resultStatus = 'PASS';
    //     $resultColor  = 'green';

    // } else {

    //     $resultStatus = 'SUPPLE';
    //     $resultColor  = 'orange';
    // }
    
    // Check End Semester Exam Status
    $isAbsent = false;

    if ($subject->type == 'Theory') {

        $externalExamStatus = strtolower(
            trim(
                (string) (
                    $endSemMarks[$student->id]['external'][0]->exam_status ?? ''
                )
            )
        );

        $practicalExamStatus = strtolower(
            trim(
                (string) (
                    $endSemMarks[$student->id]['practical'][0]->exam_status ?? ''
                )
            )
        );

        $isAbsent = (
            $externalExamStatus == 'absent' ||
            $practicalExamStatus == 'absent'
        );

    } else {

        $practicalExamStatus = strtolower(
            trim(
                (string) (
                    $endSemMarks[$student->id]['practical'][0]->exam_status ?? ''
                )
            )
        );

        $isAbsent = ($practicalExamStatus == 'absent');
    }

    /*
    |--------------------------------------------------------------------------
    | RESULT STATUS
    |--------------------------------------------------------------------------
    */

    if ($isAbsent) {

        $resultStatus = 'FAIL';
        $resultColor  = 'red';

    } elseif ($grandTotal >= $requiredMarks) {

        $resultStatus = 'PASS';
        $resultColor  = 'green';

    } else {

        $resultStatus = 'SUPPLE';
        $resultColor  = 'orange';
    }

} else {

    if ($subject->type == "Practical") {

        if ($grandTotal >= $requiredMarks) {

            $resultStatus = 'PASS';
            $resultColor  = 'green';

        } else {

            $resultStatus = 'FAIL';
            $resultColor  = 'red';
        }

    } else {

        $hasPractical = $practicalFinalMax > 0;

        if (

            //$practicalFinal > 0
           // && $practicalFinal < $practicalMinMarks
           $hasPractical && (
                $practicalFinal == 0 ||
                // (
                //     $practicalFinal > 0
                //     && $practicalFinal < $practicalMinMarks
                // )
                 $practicalFinal < $practicalMinMarks
           )

        ){
            /*
            =====================================
            PRACTICAL FAIL
            =====================================
            */

            $resultStatus = 'FAIL';
            $resultColor  = 'red';

        } elseif (

            /*
            =====================================
            THEORY FINAL MUST BE >= MIN %
            =====================================
            */
            $final < $theoryMinMarks

        ) {

            $resultStatus = 'FAIL';
            $resultColor  = 'red';

        } elseif (

            /*
            =====================================
            THEORY TOTAL >= OVERALL %
            =====================================
            */
            $theoryTotal >= $theoryTotalPassMarks

        ) {

            $resultStatus = 'PASS';
            $resultColor  = 'green';

        } else {

            /*
            =====================================
            THEORY TOTAL < OVERALL %
            BUT FINAL >= MIN %
            =====================================
            */

            $resultStatus = 'SUPPLE';
            $resultColor  = 'orange';
        }
    }
}

@endphp

<tr>

<td>{{ $srNo++ }}</td>

<td class="student-name">
    {{ $student->student_name }}
</td>

<td>{{ $student->university_roll_no }}</td>

<td>

@if($resultStatus == 'PASS')

    <strong>{{ number_format($cp, 3) }}</strong>
    <br>
    <small class="no-export" style="color:green;">PASS</small>

@else

    <strong style="color:{{ $resultColor }}">
        {{ $resultStatus }}
    </strong>

    <br>

    <small class="no-export">{{ number_format($cp, 3) }}</small>

@endif

</td>

@if($subject->type == "Theory")

    {{-- THEORY SECTION --}}

    <td>{{ number_format($mid, 2) }}</td>

    @if($courseTypeName !== "Diploma")
    <td>{{ number_format($quiz, 2) }}</td>
    @endif

    <td>{{ number_format($theoryGrace, 2) }}</td>

    <td>

        {{ number_format($final, 2) }}

        <span class="no-export">
        @if($endFinalModeration > 0)

            <br>

            <small>

                ({{ number_format($endFinalOriginal, 2) }}
                + {{ number_format($endFinalModeration, 2) }})

            </small>

        @endif
        </span>

    </td>

    <td>
        {{ number_format($theoryTotal, 2) }}
        <span class="no-export">
       @if($endFinalGrace > 0)

            <br>

            <small>

                (+ {{ number_format($endFinalGrace, 2) }} Grace)

            </small>

        @endif
        </span>

    </td>

    {{-- PRACTICAL SECTION --}}

    <td>{{ number_format($practicalMid, 2) }}</td>

    @if($courseTypeName !== "Diploma")
    <td>{{ number_format($practicalQuiz, 2) }}</td>
    @endif

    <td>{{ number_format($practicalFinal, 2) }}</td>

    <td>{{ number_format($practicalTotal, 2) }}</td>

@else

    {{-- THEORY SECTION --}}

    <td>{{ number_format($mid, 2) }}</td>

    @if($courseTypeName !== "Diploma")
    <td>{{ number_format($quiz, 2) }}</td>
    @endif

    <td>{{ number_format($final, 2) }}</td>

    <td>{{ number_format($theoryTotal, 2) }}</td>

    {{-- PRACTICAL SECTION --}}

    <td>{{ number_format($practicalMid, 2) }}</td>

    @if($courseTypeName !== "Diploma")
    <td>{{ number_format($practicalQuiz, 2) }}</td>
    @endif

    <td>{{ number_format($practicalGrace, 2) }}</td>

    <td>

        {{ number_format($practicalFinal, 2) }}
        <span class="no-export">
        @if($endFinalModeration > 0)

            <br>

            <small>

                ({{ number_format($endFinalOriginal, 2) }}
                + {{ number_format($endFinalModeration, 2) }})

            </small>

        @endif
        </span>

    </td>

    <td>
        {{ number_format($practicalTotal, 2) }}

        <span class="no-export">
        @if($endFinalGrace > 0)
            <br>
            <small>

                (+ {{ number_format($endFinalGrace, 2) }} Grace)

            </small>

        @endif
        </span>

    </td>

@endif

<td>{{ number_format($grandTotal, 2) }}</td>

</tr>

@endif

@endforeach

</tbody>

<tfoot>

<tr>

<td colspan="{{ ($course->course ?? '') == 'Diploma' ? 3 : 4 }}" class="align-left">

    Prepared by

    <br><br><br>

    Checked by

</td>

<td colspan="4">
    Controller of Examinations
</td>

<td colspan="{{ ($course->course ?? '') == 'Diploma' ? 5 : 6 }}" class="align-right">

    Registrar, Guru Angad Dev Veterinary <br>
    and Animal Science University

</td>

</tr>

</tfoot>

</table>
