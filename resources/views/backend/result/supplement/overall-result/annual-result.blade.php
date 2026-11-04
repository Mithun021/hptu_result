<style>
    .result-table {
        width: 100%;
        margin: auto;
        font-family: "Times New Roman", serif;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
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

    .supple {
        color: orange;
        font-weight: bold;
    }

    .remarks {
        text-align: left;
    }

    .cp-mark {
        display: block;
        font-size: 9px;
        line-height: 10px;
        color: #555;
        font-weight: normal;
        margin-top: 1px;
    }

    .supple-marks-detail {
        display: block;
        margin-top: 3px;
        font-size: 8px;
        line-height: 10px;
        color: #333;
        font-weight: normal;
        white-space: nowrap;
    }

    .supple-marks-detail span {
        display: inline-block;
        margin-right: 3px;
    }

    .supple-marks-detail .label {
        font-weight: bold;
        color: #555;
    }

    .marks-pending {
        color: #d97706;
        font-weight: bold;
        font-size: 11px;
    }
</style>


@php
    /*
    |--------------------------------------------------------------------------
    | SUBJECT ORDER
    |--------------------------------------------------------------------------
    | NC subjects last
    |--------------------------------------------------------------------------
    */

    $orderedSubjects = $subjects
        ->sortBy(function ($sub) {
            return $sub->is_nc == 1 ? 1 : 0;
        })
        ->values();
@endphp


<table class="result-table">

    <thead>

        <tr>
            <th colspan="{{ 3 + count($orderedSubjects) + 4 }}">

                <h3>
                    OFFICE OF CONTROLLER OF EXAMINATIONS
                </h3>

                <h4>
                    GURU ANGAD DEV VETERINARY AND ANIMAL SCIENCES UNIVERSITY
                </h4>

                <h4>
                    {{ $exam->exam_resultsheet_name ?? $exam->name }}
                </h4>

                <p style="text-align: left;">
                    {{ $className }}
                    {{ $course->name ?? '' }}

                    @if (!empty($branch->name) && strtoupper($branch->name) != 'NA')
                        ({{ $branch->name }})
                    @endif
                </p>

            </th>
        </tr>


        <tr>

            <th rowspan="2">
                Sr. No.
            </th>

            <th rowspan="2">
                Student Name
            </th>

            <th rowspan="2">
                Admission No.
            </th>

            <th colspan="{{ count($orderedSubjects) }}">
                Subjects
            </th>

            <th rowspan="2">
                Remarks
            </th>

            <th rowspan="2">
                GPA
            </th>

            <th rowspan="2">
                CGPA
            </th>

            <th rowspan="2">
                Grade
            </th>

        </tr>


        <tr>

            @foreach ($orderedSubjects as $sub)

                <th>
                    {{ $sub->subject_code }}
                </th>

            @endforeach

        </tr>

    </thead>


    <tbody>

        @php

            $summaryData = [];

            $gpSheetData = [];

        @endphp


        @foreach ($students as $key => $student)

            @php

                /*
                |--------------------------------------------------------------------------
                | STUDENT CALCULATION VARIABLES
                |--------------------------------------------------------------------------
                */

                $remarks = [];

                $totalCredit = 0;

                $totalGP = 0;

                $failCount = 0;

                $subjectResults = [];


                /*
                |--------------------------------------------------------------------------
                | STUDENT SUBJECTS
                |--------------------------------------------------------------------------
                */

                $enrolledStudentSubjects =
                    $studentSubjects->get(
                        $student->id,
                        collect()
                    );


                /*
                |--------------------------------------------------------------------------
                | REGULAR GP ROWS
                |--------------------------------------------------------------------------
                */

                $regularStudentRows =
                    $resultGpSheet->get(
                        $student->id,
                        collect()
                    );


                /*
                |--------------------------------------------------------------------------
                | SUPPLEMENTARY GP ROWS
                |--------------------------------------------------------------------------
                */

                $suppleStudentRows =
                    $suppleResultGpSheet->get(
                        $student->id,
                        collect()
                    );

            @endphp


            {{-- =========================================================
                 SUBJECT LOOP
            ========================================================== --}}

            @foreach ($orderedSubjects as $sub)

                @php

                    /*
                    |--------------------------------------------------------------------------
                    | ENROLLMENT
                    |--------------------------------------------------------------------------
                    */

                    $isEnrolled =
                        $enrolledStudentSubjects->contains(
                            'subject_id',
                            $sub->id
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | SUPPLEMENTARY SUBJECT
                    |--------------------------------------------------------------------------
                    */

                    $isSuppleSubject =
                        isset(
                            $suppleSubjectMap[$student->id][$sub->id]
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | REGULAR GP ROW
                    |--------------------------------------------------------------------------
                    */

                    $regularSubjectRows =
                        $regularStudentRows->get(
                            $sub->id,
                            collect()
                        );

                    $regularRow =
                        $regularSubjectRows->first();


                    /*
                    |--------------------------------------------------------------------------
                    | SUPPLEMENTARY SAVED GP ROW
                    |--------------------------------------------------------------------------
                    */

                    $suppleSubjectRows =
                        $suppleStudentRows->get(
                            $sub->id,
                            collect()
                        );

                    $suppleRow =
                        $suppleSubjectRows->first();


                    /*
                    |--------------------------------------------------------------------------
                    | DEFAULT VALUES
                    |--------------------------------------------------------------------------
                    */

                    $value = '-';

                    $class = '';

                    $result = null;

                    $resultGp = 0;

                    $grade = '';

                    $isCp = false;

                    $marksNotUploaded = false;


                    /*
                    |--------------------------------------------------------------------------
                    | GPA CREDIT
                    |--------------------------------------------------------------------------
                    | IMPORTANT:
                    |
                    | NC subject credit is ZERO only for GPA calculation.
                    |
                    | Actual subject credit is preserved separately later
                    | while preparing GP Sheet Data.
                    |--------------------------------------------------------------------------
                    */

                    $credit =
                        $sub->is_nc == 1
                            ? 0
                            : (float) ($sub->credits ?? 0);


                    /*
                    |--------------------------------------------------------------------------
                    | ACTUAL SUBJECT CREDIT
                    |--------------------------------------------------------------------------
                    | This is the real subject credit.
                    |
                    | It will be saved in GP Sheet even for NC subjects.
                    |--------------------------------------------------------------------------
                    */

                    $actualSubjectCredit =
                        (float) ($sub->credits ?? 0);


                    /*
                    |--------------------------------------------------------------------------
                    | SUPPLEMENTARY MARK DISPLAY DATA
                    |--------------------------------------------------------------------------
                    */

                    $suppleMarks = [
                        'int1' => null,
                        'int2' => null,
                        'th1'  => null,
                        'th2'  => null,
                        'p1'   => null,
                        'p2'   => null,
                    ];

                @endphp


                {{-- =====================================================
                     NOT ENROLLED
                ====================================================== --}}

                @if (!$isEnrolled)

                    @php

                        $subjectResults[$sub->id] = [

                            'value' => '-',

                            'class' => '',

                            'result' => null,

                            'result_gp' => 0,

                            'grade' => '',

                            'is_cp' => false,

                            'marks_not_uploaded' => false,

                            'credit' => 0,

                            /*
                            | Actual subject credit is kept separately.
                            */

                            'actual_credit' =>
                                $actualSubjectCredit,

                            'supple_marks' => $suppleMarks,

                        ];

                    @endphp


                {{-- =====================================================
                     NC SUBJECT
                ====================================================== --}}

                @elseif ($sub->is_nc == 1)

                    @php

                        /*
                        |--------------------------------------------------------------------------
                        | NC SUBJECT
                        |--------------------------------------------------------------------------
                        | Supplementary GP row gets priority.
                        | If not available, regular GP row is used.
                        |--------------------------------------------------------------------------
                        */

                        $effectiveNcRow =
                            $suppleRow ?? $regularRow;


                        /*
                        |--------------------------------------------------------------------------
                        | NC GPA CREDIT + GP
                        |--------------------------------------------------------------------------
                        | NC does NOT participate in GPA.
                        |--------------------------------------------------------------------------
                        */

                        $credit = 0;

                        $resultGp = 0;


                        if ($effectiveNcRow) {

                            $result =
                                strtoupper(
                                    trim(
                                        $effectiveNcRow->result ?? ''
                                    )
                                );


                            /*
                            |--------------------------------------------------------------------------
                            | NC GRADE
                            |--------------------------------------------------------------------------
                            */

                            $ncGrade =
                                strtolower(
                                    trim(
                                        $effectiveNcRow->grade ?? ''
                                    )
                                );


                            if ($ncGrade === 'satisfactory') {

                                $grade = 'S';

                                $value = 'S';

                                $class = 'pass';

                                $result = 'PASS';

                            }

                            elseif ($ncGrade === 'unsatisfactory') {

                                $grade = 'U';

                                $value = 'U';

                                $class = 'fail';

                                $result = 'FAIL';

                                $failCount++;

                                $remarks[] =
                                    "Fail {$sub->subject_code}";

                            }

                            elseif ($ncGrade === 'exempted') {

                                $grade = 'E';

                                $value = 'E';

                                $class = 'pass';

                                $result = 'PASS';

                            }

                            else {

                                /*
                                |--------------------------------------------------------------------------
                                | FALLBACK
                                |--------------------------------------------------------------------------
                                */

                                if ($result === 'PASS') {

                                    $grade = 'S';

                                    $value = 'S';

                                    $class = 'pass';

                                }

                                else {

                                    $grade = 'U';

                                    $value = 'U';

                                    $class = 'fail';

                                    $result = 'FAIL';

                                    $failCount++;

                                    $remarks[] =
                                        "Fail {$sub->subject_code}";

                                }

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | NC RESULT GP ALWAYS ZERO
                            |--------------------------------------------------------------------------
                            */

                            $resultGp = 0;


                            /*
                            |--------------------------------------------------------------------------
                            | NC CREDIT FOR GPA ALWAYS ZERO
                            |--------------------------------------------------------------------------
                            */

                            $credit = 0;

                        }

                    @endphp


                {{-- =====================================================
                     SUPPLEMENTARY SUBJECT
                ====================================================== --}}

                @elseif ($isSuppleSubject)

                    @php

                        $isCp = true;


                        /*
                        |--------------------------------------------------------------------------
                        | MID SEM - ORIGINAL MARKS
                        |--------------------------------------------------------------------------
                        */

                        $internal1Original =
                            $midSemMarks[$student->id][$sub->id]['Internal 1'][0]->obtained_marks
                            ?? null;


                        $internal2Original =
                            $midSemMarks[$student->id][$sub->id]['Internal 2'][0]->obtained_marks
                            ?? null;


                        $internal3Original =
                            $midSemMarks[$student->id][$sub->id]['Internal 3'][0]->obtained_marks
                            ?? null;


                        /*
                        |--------------------------------------------------------------------------
                        | MID SEM - GRACE MARKS
                        |--------------------------------------------------------------------------
                        */

                        $internal1Grace =
                            $midSemMarks[$student->id][$sub->id]['Internal 1'][0]->grace_marks
                            ?? 0;


                        $internal2Grace =
                            $midSemMarks[$student->id][$sub->id]['Internal 2'][0]->grace_marks
                            ?? 0;


                        $internal3Grace =
                            $midSemMarks[$student->id][$sub->id]['Internal 3'][0]->grace_marks
                            ?? 0;


                        /*
                        |--------------------------------------------------------------------------
                        | FINAL INTERNAL MARKS
                        |--------------------------------------------------------------------------
                        */

                        $internalMarks = [

                            [
                                'final' =>
                                    (float) ($internal1Original ?? 0)
                                    +
                                    (float) $internal1Grace,

                                'original' =>
                                    (float) ($internal1Original ?? 0),

                                'grace' =>
                                    (float) $internal1Grace,
                            ],

                            [
                                'final' =>
                                    (float) ($internal2Original ?? 0)
                                    +
                                    (float) $internal2Grace,

                                'original' =>
                                    (float) ($internal2Original ?? 0),

                                'grace' =>
                                    (float) $internal2Grace,
                            ],

                            [
                                'final' =>
                                    (float) ($internal3Original ?? 0)
                                    +
                                    (float) $internal3Grace,

                                'original' =>
                                    (float) ($internal3Original ?? 0),

                                'grace' =>
                                    (float) $internal3Grace,
                            ],

                        ];


                        /*
                        |--------------------------------------------------------------------------
                        | BEST TWO INTERNALS
                        |--------------------------------------------------------------------------
                        */

                        usort(
                            $internalMarks,
                            function ($a, $b) {
                                return $b['final'] <=> $a['final'];
                            }
                        );


                        $bestInternal1 =
                            $internalMarks[0]['final'] ?? 0;


                        $bestInternal2 =
                            $internalMarks[1]['final'] ?? 0;


                        $internal =
                            $bestInternal1
                            +
                            $bestInternal2;


                        /*
                        |--------------------------------------------------------------------------
                        | DISPLAY INTERNAL MARKS
                        |--------------------------------------------------------------------------
                        */

                        $suppleMarks['int1'] =
                            $internalMarks[0]['final'] ?? 0;


                        $suppleMarks['int2'] =
                            $internalMarks[1]['final'] ?? 0;


                        /*
                        |--------------------------------------------------------------------------
                        | SUPPLEMENTARY EXTERNAL MARKS
                        |--------------------------------------------------------------------------
                        */

                        $suppleTheory1 =
                            $suppleEndSemMarks[$student->id][$sub->id]['theory_paper1'][0]
                            ?? null;


                        $suppleTheory2 =
                            $suppleEndSemMarks[$student->id][$sub->id]['theory_paper2'][0]
                            ?? null;


                        $supplePractical1 =
                            $suppleEndSemMarks[$student->id][$sub->id]['practical_paper1'][0]
                            ?? null;


                        $supplePractical2 =
                            $suppleEndSemMarks[$student->id][$sub->id]['practical_paper2'][0]
                            ?? null;


                        /*
                        |--------------------------------------------------------------------------
                        | SUBJECT TYPE
                        |--------------------------------------------------------------------------
                        */

                        $isPracticalSubject =
                            strtolower(
                                trim($sub->type ?? '')
                            ) === 'practical';


                        /*
                        |--------------------------------------------------------------------------
                        | REQUIRED EXTERNAL MARKS
                        |--------------------------------------------------------------------------
                        */

                        if ($isPracticalSubject) {

                            $requiredExternalMarksUploaded =
                                $supplePractical1 !== null
                                &&
                                $supplePractical2 !== null;

                        }

                        else {

                            $requiredExternalMarksUploaded =
                                $suppleTheory1 !== null
                                &&
                                $suppleTheory2 !== null
                                &&
                                $supplePractical1 !== null
                                &&
                                $supplePractical2 !== null;

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | MARKS NOT UPLOADED
                        |--------------------------------------------------------------------------
                        */

                        if (!$requiredExternalMarksUploaded) {

                            $marksNotUploaded = true;

                            $value = 'Marks not upload';

                            $class = 'supple';

                            $result = null;

                            $resultGp = 0;


                            /*
                            |--------------------------------------------------------------------------
                            | STORE AVAILABLE MARKS
                            |--------------------------------------------------------------------------
                            */

                            $suppleMarks['th1'] =
                                $suppleTheory1
                                    ? (
                                        (float) ($suppleTheory1->obtained_marks ?? 0)
                                        +
                                        (float) ($suppleTheory1->grace_marks ?? 0)
                                    )
                                    : null;


                            $suppleMarks['th2'] =
                                $suppleTheory2
                                    ? (
                                        (float) ($suppleTheory2->obtained_marks ?? 0)
                                        +
                                        (float) ($suppleTheory2->grace_marks ?? 0)
                                    )
                                    : null;


                            $suppleMarks['p1'] =
                                $supplePractical1
                                    ? (
                                        (float) ($supplePractical1->obtained_marks ?? 0)
                                        +
                                        (float) ($supplePractical1->grace_marks ?? 0)
                                    )
                                    : null;


                            $suppleMarks['p2'] =
                                $supplePractical2
                                    ? (
                                        (float) ($supplePractical2->obtained_marks ?? 0)
                                        +
                                        (float) ($supplePractical2->grace_marks ?? 0)
                                    )
                                    : null;

                        }

                        else {

                            /*
                            |--------------------------------------------------------------------------
                            | EXTERNAL FINAL MARKS
                            |--------------------------------------------------------------------------
                            */

                            $t1 =
                                (float) ($suppleTheory1->obtained_marks ?? 0)
                                +
                                (float) ($suppleTheory1->grace_marks ?? 0);


                            $t2 =
                                (float) ($suppleTheory2->obtained_marks ?? 0)
                                +
                                (float) ($suppleTheory2->grace_marks ?? 0);


                            $p1 =
                                (float) ($supplePractical1->obtained_marks ?? 0)
                                +
                                (float) ($supplePractical1->grace_marks ?? 0);


                            $p2 =
                                (float) ($supplePractical2->obtained_marks ?? 0)
                                +
                                (float) ($supplePractical2->grace_marks ?? 0);


                            /*
                            |--------------------------------------------------------------------------
                            | STORE MARKS FOR DISPLAY
                            |--------------------------------------------------------------------------
                            */

                            $suppleMarks['th1'] = $t1;

                            $suppleMarks['th2'] = $t2;

                            $suppleMarks['p1'] = $p1;

                            $suppleMarks['p2'] = $p2;


                            /*
                            |--------------------------------------------------------------------------
                            | MAXIMUM INTERNAL MARKS
                            |--------------------------------------------------------------------------
                            */

                            $internalMaxArr = [

                                (float) ($sub->internal1 ?? 0),

                                (float) ($sub->internal2 ?? 0),

                                (float) ($sub->internal3 ?? 0),

                            ];


                            rsort($internalMaxArr);


                            $bestInternalMax1 =
                                $internalMaxArr[0] ?? 0;


                            $bestInternalMax2 =
                                $internalMaxArr[1] ?? 0;


                            /*
                            |--------------------------------------------------------------------------
                            | PRACTICAL SUBJECT
                            |--------------------------------------------------------------------------
                            */

                            if ($isPracticalSubject) {

                                $total =
                                    $internal
                                    +
                                    $p1
                                    +
                                    $p2;


                                $practicalTotal =
                                    $p1 + $p2;


                                $practicalTotalMax =
                                    (float) ($sub->practical_paper1 ?? 0)
                                    +
                                    (float) ($sub->practical_paper2 ?? 0);


                                $requiredPractical =
                                    (
                                        $practicalTotalMax
                                        *
                                        $overallPassPercent
                                    ) / 100;


                                $subjectPass =
                                    $practicalTotal >= $requiredPractical;

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | THEORY SUBJECT
                            |--------------------------------------------------------------------------
                            */

                            else {

                                $total =
                                    $internal
                                    +
                                    $t1
                                    +
                                    $t2
                                    +
                                    $p1
                                    +
                                    $p2;


                                /*
                                |--------------------------------------------------------------------------
                                | GROSS THEORY
                                |--------------------------------------------------------------------------
                                */

                                $grossTheory =
                                    $internal
                                    +
                                    $t1
                                    +
                                    $t2;


                                /*
                                |--------------------------------------------------------------------------
                                | PRACTICAL
                                |--------------------------------------------------------------------------
                                */

                                $practicalTotal =
                                    $p1 + $p2;


                                /*
                                |--------------------------------------------------------------------------
                                | MAXIMUM THEORY
                                |--------------------------------------------------------------------------
                                */

                                $grossTheoryMax =
                                    $bestInternalMax1
                                    +
                                    $bestInternalMax2
                                    +
                                    (float) ($sub->paper1 ?? 0)
                                    +
                                    (float) ($sub->paper2 ?? 0);


                                /*
                                |--------------------------------------------------------------------------
                                | MAXIMUM PRACTICAL
                                |--------------------------------------------------------------------------
                                */

                                $practicalTotalMax =
                                    (float) ($sub->practical_paper1 ?? 0)
                                    +
                                    (float) ($sub->practical_paper2 ?? 0);


                                /*
                                |--------------------------------------------------------------------------
                                | REQUIRED THEORY
                                |--------------------------------------------------------------------------
                                */

                                $requiredGrossTheory =
                                    (
                                        $grossTheoryMax
                                        *
                                        $overallPassPercent
                                    ) / 100;


                                /*
                                |--------------------------------------------------------------------------
                                | REQUIRED PRACTICAL
                                |--------------------------------------------------------------------------
                                */

                                $requiredPractical =
                                    (
                                        $practicalTotalMax
                                        *
                                        $overallPassPercent
                                    ) / 100;


                                /*
                                |--------------------------------------------------------------------------
                                | PASS CONDITIONS
                                |--------------------------------------------------------------------------
                                */

                                $grossTheoryPass =
                                    $grossTheory >= $requiredGrossTheory;


                                $practicalPass =
                                    $practicalTotal >= $requiredPractical;


                                $subjectPass =
                                    $grossTheoryPass
                                    &&
                                    $practicalPass;

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | RESULT + GP
                            |--------------------------------------------------------------------------
                            */

                            if ($subjectPass) {

                                $result = 'PASS';


                                $resultGp =
                                    round(
                                        $total / 10,
                                        3
                                    );


                                $value =
                                    number_format(
                                        $resultGp,
                                        3
                                    );


                                $class = 'pass';

                            }

                            else {

                                $result = 'FAIL';


                                $resultGp =
                                    round(
                                        $total / 10,
                                        3
                                    );


                                $value = 'Fail';

                                $class = 'fail';


                                $failCount++;


                                $remarks[] =
                                    "Fail {$sub->subject_code}";

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | GPA
                            |--------------------------------------------------------------------------
                            | Supplementary subject is NON-NC here.
                            |--------------------------------------------------------------------------
                            */

                            if ($subjectPass && $sub->is_nc != 1) {

                                $totalCredit += $credit;


                                $creditPoint =
                                    round(
                                        $resultGp * $credit,
                                        2
                                    );


                                $totalGP += $creditPoint;

                            }

                        }

                    @endphp


                {{-- =====================================================
                     NORMAL NON-SUPPLEMENTARY SUBJECT
                ====================================================== --}}

                @else

                    @php

                        if ($regularRow) {

                            $result =
                                strtoupper(
                                    trim(
                                        $regularRow->result ?? ''
                                    )
                                );


                            /*
                            |--------------------------------------------------------------------------
                            | RESULT GP
                            |--------------------------------------------------------------------------
                            */

                            $resultGp =
                                (float) (
                                    $regularRow->result_gp ?? 0
                                );


                            $grade =
                                $regularRow->grade ?? '';


                            /*
                            |--------------------------------------------------------------------------
                            | PASS
                            |--------------------------------------------------------------------------
                            */

                            if ($result === 'PASS') {

                                if ($sub->is_nc != 1) {

                                    $value =
                                        number_format(
                                            $resultGp,
                                            3
                                        );


                                    $class = 'pass';


                                    $totalCredit += $credit;


                                    $totalGP +=
                                        round(
                                            $resultGp * $credit,
                                            2
                                        );

                                }

                                else {

                                    /*
                                    |--------------------------------------------------------------------------
                                    | SAFETY: NC PASS
                                    |--------------------------------------------------------------------------
                                    */

                                    $credit = 0;

                                    $resultGp = 0;

                                    $value =
                                        $grade === 'Exempted'
                                            ? 'E'
                                            : (
                                                $grade === 'Satisfactory'
                                                    ? 'S'
                                                    : 'S'
                                            );

                                    $class = 'pass';

                                }

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | FAIL / OLD SUPPLE
                            |--------------------------------------------------------------------------
                            */

                            elseif (
                                $result === 'FAIL'
                                ||
                                $result === 'SUPPLE'
                            ) {

                                $result = 'FAIL';

                                $value = 'Fail';

                                $class = 'fail';

                                $failCount++;


                                $remarks[] =
                                    "Fail {$sub->subject_code}";

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | OTHER
                            |--------------------------------------------------------------------------
                            */

                            else {

                                $value =
                                    $result ?: '-';

                            }

                        }

                    @endphp

                @endif


                {{-- =====================================================
                     SUPPLEMENTARY DISPLAY *
                ====================================================== --}}

                @php

                    if (
                        $isCp
                        &&
                        !$marksNotUploaded
                    ) {

                        $value .= '*';

                    }

                @endphp


                {{-- =====================================================
                     STORE SUBJECT RESULT
                ====================================================== --}}

                @php

                    $subjectResults[$sub->id] = [

                        'value' =>
                            $value,

                        'class' =>
                            $class,

                        'result' =>
                            $result,

                        /*
                        |--------------------------------------------------------------------------
                        | NC RESULT GP ALWAYS ZERO
                        |--------------------------------------------------------------------------
                        */

                        'result_gp' =>
                            $sub->is_nc == 1
                                ? 0
                                : $resultGp,

                        'grade' =>
                            $grade,

                        'is_cp' =>
                            $isCp,

                        'marks_not_uploaded' =>
                            $marksNotUploaded,

                        /*
                        |--------------------------------------------------------------------------
                        | GPA CREDIT
                        |--------------------------------------------------------------------------
                        | NC = 0 because this value is used for GPA.
                        |--------------------------------------------------------------------------
                        */

                        'credit' =>
                            $sub->is_nc == 1
                                ? 0
                                : $credit,

                        /*
                        |--------------------------------------------------------------------------
                        | ACTUAL CREDIT
                        |--------------------------------------------------------------------------
                        | IMPORTANT:
                        | NC subject ka actual credit yahan preserve ho raha hai.
                        | GP Sheet ke liye isi ko use kiya jayega.
                        |--------------------------------------------------------------------------
                        */

                        'actual_credit' =>
                            $actualSubjectCredit,

                        'supple_marks' =>
                            $suppleMarks,

                    ];

                @endphp


            @endforeach


            {{-- =========================================================
                 FINAL GPA
            ========================================================== --}}

            @php

                /*
                |--------------------------------------------------------------------------
                | GPA
                |--------------------------------------------------------------------------
                | NC subjects are excluded because their GPA credit is 0.
                |--------------------------------------------------------------------------
                */

                $gpa =
                    $totalCredit > 0
                        ? number_format(
                            $totalGP / $totalCredit,
                            3
                        )
                        : 0;


                /*
                |--------------------------------------------------------------------------
                | ANNUAL BASED
                |--------------------------------------------------------------------------
                */

                $cgpa = $gpa;


                /*
                |--------------------------------------------------------------------------
                | GRADE
                |--------------------------------------------------------------------------
                */

                $gradeStatus = '';


                foreach ($grades as $g) {

                    if (
                        (float) $cgpa >=
                            (float) $g->percent_from
                        &&
                        (float) $cgpa <=
                            (float) $g->percent_upto
                    ) {

                        $gradeStatus =
                            $g->grade_description;

                        break;

                    }

                }


                /*
                |--------------------------------------------------------------------------
                | FINAL STUDENT RESULT
                |--------------------------------------------------------------------------
                */

                if ($failCount > 0) {

                    $studentResultStatus = 'FAIL';

                }

                else {

                    $studentResultStatus = 'PASS';

                }

            @endphp


            {{-- =========================================================
                 GP SHEET DATA
            ========================================================== --}}

            @foreach ($orderedSubjects as $sub)

                @php

                    $res =
                        $subjectResults[$sub->id]
                        ??
                        [

                            'value' => '-',

                            'class' => '',

                            'result' => null,

                            'result_gp' => 0,

                            'grade' => '',

                            'is_cp' => false,

                            'marks_not_uploaded' => false,

                            'credit' => 0,

                            /*
                            | Actual credit fallback
                            */

                            'actual_credit' =>
                                (float) ($sub->credits ?? 0),

                            'supple_marks' => [

                                'int1' => null,

                                'int2' => null,

                                'th1' => null,

                                'th2' => null,

                                'p1' => null,

                                'p2' => null,

                            ],

                        ];


                    /*
                    |--------------------------------------------------------------------------
                    | ONLY ENROLLED SUBJECT
                    |--------------------------------------------------------------------------
                    */

                    $isEnrolled =
                        $enrolledStudentSubjects->contains(
                            'subject_id',
                            $sub->id
                        );


                    if (!$isEnrolled) {

                        continue;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | ACTUAL GP SHEET CREDIT
                    |--------------------------------------------------------------------------
                    |
                    | IMPORTANT:
                    |
                    | NC subject:
                    |     credits   = actual subject credit
                    |     result_gp = 0
                    |
                    | Normal subject:
                    |     credits   = actual subject credit
                    |     result_gp = actual GP
                    |
                    |--------------------------------------------------------------------------
                    */

                    $gpCredit =
                        (float) (
                            $res['actual_credit']
                            ?? $sub->credits
                            ?? 0
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | GP SHEET RESULT GP
                    |--------------------------------------------------------------------------
                    */

                    $gpResultGp =
                        $sub->is_nc == 1
                            ? 0
                            : (float) (
                                $res['result_gp'] ?? 0
                            );


                    /*
                    |--------------------------------------------------------------------------
                    | SUPPLEMENTARY SUBJECT
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $res['is_cp']
                        &&
                        !$res['marks_not_uploaded']
                    ) {

                        $gpSheetData[] = [

                            'student_id' =>
                                $student->id,

                            'subject_id' =>
                                $sub->id,

                            'result' =>
                                $res['result'],


                            /*
                            |--------------------------------------------------------------------------
                            | NC RESULT GP = 0
                            |--------------------------------------------------------------------------
                            */

                            'result_gp' =>
                                $sub->is_nc == 1
                                    ? 0
                                    : $gpResultGp,


                            /*
                            |--------------------------------------------------------------------------
                            | IMPORTANT:
                            | NC CREDIT = ACTUAL CREDIT
                            |--------------------------------------------------------------------------
                            */

                            'credits' =>
                                $gpCredit,


                            'grade' =>
                                $res['grade'],


                            'exam_id' =>
                                $exam->id,


                            'course_type' =>
                                $courseType,


                            'course_id' =>
                                $course->id,


                            'scheme_id' =>
                                $schemeId ?? null,


                            'branch_id' =>
                                $branch->id,


                            'semester_id' =>
                                null,


                            'annual_semester_id' =>
                                $annualSemesterId ?? null,


                            'batch_id' =>
                                $batch->id,


                            'institute_id' =>
                                $instituteId ?? null,

                        ];

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | NON-SUPPLEMENTARY SUBJECT
                    |--------------------------------------------------------------------------
                    */

                    elseif (!$res['is_cp']) {

                        $regularSubjectRows =
                            $regularStudentRows->get(
                                $sub->id,
                                collect()
                            );


                        $regularRow =
                            $regularSubjectRows->first();


                        if ($regularRow) {

                            $gpResult =
                                strtoupper(
                                    trim(
                                        $regularRow->result ?? ''
                                    )
                                );


                            /*
                            |--------------------------------------------------------------------------
                            | OLD SUPPLE -> FAIL
                            |--------------------------------------------------------------------------
                            */

                            if ($gpResult === 'SUPPLE') {

                                $gpResult = 'FAIL';

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | NC SUBJECT
                            |--------------------------------------------------------------------------
                            */

                            if ($sub->is_nc == 1) {

                                /*
                                |--------------------------------------------------------------------------
                                | NC RESULT GP = 0
                                |--------------------------------------------------------------------------
                                */

                                $gp = 0;


                                /*
                                |--------------------------------------------------------------------------
                                | IMPORTANT:
                                | NC CREDIT = ACTUAL SUBJECT CREDIT
                                |--------------------------------------------------------------------------
                                */

                                $gpCredit =
                                    (float) (
                                        $res['actual_credit']
                                        ?? $sub->credits
                                        ?? 0
                                    );


                                /*
                                |--------------------------------------------------------------------------
                                | KEEP ORIGINAL DATABASE GRADE
                                |--------------------------------------------------------------------------
                                */

                                $gpGrade =
                                    $regularRow->grade ?? '';

                            }

                            else {

                                /*
                                |--------------------------------------------------------------------------
                                | NORMAL SUBJECT GP
                                |--------------------------------------------------------------------------
                                */

                                $gp =
                                    (float) (
                                        $regularRow->result_gp ?? 0
                                    );


                                /*
                                |--------------------------------------------------------------------------
                                | NORMAL SUBJECT CREDIT
                                |--------------------------------------------------------------------------
                                */

                                $gpCredit =
                                    (float) (
                                        $sub->credits ?? 0
                                    );


                                $gpGrade =
                                    $regularRow->grade ?? '';

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | GP SHEET DATA
                            |--------------------------------------------------------------------------
                            */

                            $gpSheetData[] = [

                                'student_id' =>
                                    $student->id,

                                'subject_id' =>
                                    $sub->id,

                                'result' =>
                                    $gpResult,


                                /*
                                |--------------------------------------------------------------------------
                                | NC GP ALWAYS ZERO
                                |--------------------------------------------------------------------------
                                */

                                'result_gp' =>
                                    $sub->is_nc == 1
                                        ? 0
                                        : $gp,


                                /*
                                |--------------------------------------------------------------------------
                                | IMPORTANT:
                                | NC CREDIT = ACTUAL CREDIT
                                |--------------------------------------------------------------------------
                                */

                                'credits' =>
                                    $gpCredit,


                                'grade' =>
                                    $gpGrade,


                                'exam_id' =>
                                    $exam->id,


                                'course_type' =>
                                    $courseType,


                                'course_id' =>
                                    $course->id,


                                'scheme_id' =>
                                    $schemeId ?? null,


                                'branch_id' =>
                                    $branch->id,


                                'semester_id' =>
                                    null,


                                'annual_semester_id' =>
                                    $annualSemesterId ?? null,


                                'batch_id' =>
                                    $batch->id,


                                'institute_id' =>
                                    $instituteId ?? null,

                            ];

                        }

                    }

                @endphp

            @endforeach


            {{-- =========================================================
                 SUMMARY DATA
            ========================================================== --}}

            @php

                $summaryData[] = [

                    'student_id' =>
                        $student->id,

                    'gpa' =>
                        $gpa,

                    'cgpa' =>
                        $cgpa,

                    'result_status' =>
                        $studentResultStatus,

                    'grade_status' =>
                        $gradeStatus,

                    'remarks' =>
                        count($remarks)
                            ? implode(
                                ', ',
                                $remarks
                            )
                            : 'Pass',

                    'exam_id' =>
                        $exam->id,

                    'course_type' =>
                        $courseType,

                    'course_id' =>
                        $course->id,

                    'scheme_id' =>
                        $schemeId ?? null,

                    'branch_id' =>
                        $branch->id,

                    'semester_id' =>
                        null,

                    'annual_semester_id' =>
                        $annualSemesterId ?? null,

                    'batch_id' =>
                        $batch->id,

                    'institute_id' =>
                        $instituteId ?? null,

                    'result_declare' =>
                        1,

                ];

            @endphp


            {{-- =========================================================
                 STUDENT RESULT ROW
            ========================================================== --}}

            <tr>

                <td>
                    {{ $key + 1 }}
                </td>


                <td class="left">
                    {{ $student->student_name }}
                </td>


                <td>
                    {{ $student->university_roll_no }}
                </td>


                @foreach ($orderedSubjects as $sub)

                    @php

                        $res =
                            $subjectResults[$sub->id]
                            ??
                            [

                                'value' => '-',

                                'class' => '',

                                'result' => null,

                                'result_gp' => 0,

                                'grade' => '',

                                'is_cp' => false,

                                'marks_not_uploaded' => false,

                                'credit' => 0,

                                'actual_credit' =>
                                    (float) ($sub->credits ?? 0),

                                'supple_marks' => [

                                    'int1' => null,

                                    'int2' => null,

                                    'th1' => null,

                                    'th2' => null,

                                    'p1' => null,

                                    'p2' => null,

                                ],

                            ];


                        $value =
                            $res['value'];


                        $class =
                            $res['class'];


                        $isCp =
                            $res['is_cp']
                            ?? false;


                        $marksNotUploaded =
                            $res['marks_not_uploaded']
                            ?? false;


                        $result =
                            $res['result']
                            ?? null;


                        $suppleMarks =
                            $res['supple_marks']
                            ??
                            [

                                'int1' => null,

                                'int2' => null,

                                'th1' => null,

                                'th2' => null,

                                'p1' => null,

                                'p2' => null,

                            ];

                    @endphp


                    <td class="{{ $class }}">

                        {{-- =================================================
                             MARKS NOT UPLOADED
                        ================================================== --}}

                        @if ($marksNotUploaded)

                            <span class="marks-pending">
                                {{ $value }}
                            </span>


                        {{-- =================================================
                             NC SUBJECT
                        ================================================== --}}

                        @elseif ($sub->is_nc)

                            <strong>
                                {{ $value }}
                            </strong>


                        {{-- =================================================
                             SUPPLEMENTARY SUBJECT
                        ================================================== --}}

                        @elseif ($isCp)

                            {{ $value }}


                        @else

                            {{ $value }}

                        @endif

                    </td>

                @endforeach


                {{-- =====================================================
                     REMARKS
                ====================================================== --}}

                <td class="remarks">

                    @if (count($remarks))

                        {{ implode(', ', $remarks) }}

                    @else

                        <span class="pass">
                            Pass
                        </span>

                    @endif

                </td>


                {{-- =====================================================
                     GPA
                ====================================================== --}}

                <td>
                    {{ $gpa }}
                </td>


                {{-- =====================================================
                     CGPA
                ====================================================== --}}

                <td>
                    {{ $cgpa }}
                </td>


                {{-- =====================================================
                     GRADE
                ====================================================== --}}

                <td>
                    {{ $gradeStatus }}
                </td>

            </tr>

        @endforeach

    </tbody>


    {{-- =============================================================
         FOOTER
    ============================================================== --}}

    <tfoot>

        <tr>

            <td colspan="{{ 3 + count($orderedSubjects) + 4 }}">

                <table width="100%" style="border: none;">

                    <tr>

                        <td
                            width="33.33%"
                            style="
                                border: none;
                                text-align: left;
                                font-size: 12px;
                            "
                        >

                            Prepared By

                            <br><br><br>

                            Checked By

                        </td>


                        <td
                            width="33.33%"
                            style="
                                border: none;
                                text-align: center;
                                font-size: 12px;
                            "
                        >

                            Controller of Examinations

                        </td>


                        <td
                            width="33.33%"
                            style="
                                border: none;
                                text-align: right;
                                font-size: 12px;
                            "
                        >

                            Registrar,

                            <br>

                            Guru Angad Dev Veterinary and Animal
                            Science University

                        </td>

                    </tr>

                </table>

            </td>

        </tr>

    </tfoot>

</table>


{{-- ================================================================
     SUMMARY DATA
================================================================ --}}

<input
    type="hidden"
    id="summaryData"
    value='@json($summaryData)'
>


{{-- ================================================================
     GP SHEET DATA
================================================================ --}}

<input
    type="hidden"
    id="gpSheetData"
    value='@json($gpSheetData)'
>


{{-- ================================================================
     DEBUG: SUMMARY DATA
================================================================ --}}

{{-- <div style="margin-top: 30px;">

    <h3 style="margin-bottom: 10px;">
        SUMMARY DATA
    </h3>

    <div style="overflow-x: auto;">

        <table
            style="
                width: 100%;
                border-collapse: collapse;
                font-family: Arial, sans-serif;
                font-size: 12px;
            "
        >

            <thead>

                <tr>

                    <th style="border:1px solid #000; padding:6px;">
                        #
                    </th>

                    @if (!empty($summaryData))

                        @foreach (array_keys($summaryData[0]) as $field)

                            <th style="border:1px solid #000; padding:6px;">
                                {{ $field }}
                            </th>

                        @endforeach

                    @endif

                </tr>

            </thead>


            <tbody>

                @forelse ($summaryData as $index => $row)

                    <tr>

                        <td style="border:1px solid #000; padding:6px;">
                            {{ $index + 1 }}
                        </td>

                        @foreach ($row as $field => $value)

                            <td
                                style="
                                    border:1px solid #000;
                                    padding:6px;
                                    text-align:center;
                                "
                            >

                                @if (is_array($value))

                                    <pre style="margin:0; text-align:left;">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>

                                @elseif (is_object($value))

                                    <pre style="margin:0; text-align:left;">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>

                                @else

                                    {{ $value }}

                                @endif

                            </td>

                        @endforeach

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="20"
                            style="
                                border:1px solid #000;
                                padding:10px;
                                text-align:center;
                            "
                        >
                            No Summary Data Found
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div> --}}


{{-- ================================================================
     DEBUG: GP SHEET DATA
================================================================ --}}

{{-- <div style="margin-top: 40px;">

    <h3 style="margin-bottom: 10px;">
        GP SHEET DATA
    </h3>

    <div style="overflow-x: auto;">

        <table
            style="
                width: 100%;
                border-collapse: collapse;
                font-family: Arial, sans-serif;
                font-size: 12px;
            "
        >

            <thead>

                <tr>

                    <th style="border:1px solid #000; padding:6px;">
                        #
                    </th>

                    @if (!empty($gpSheetData))

                        @foreach (array_keys($gpSheetData[0]) as $field)

                            <th style="border:1px solid #000; padding:6px;">
                                {{ $field }}
                            </th>

                        @endforeach

                    @endif

                </tr>

            </thead>


            <tbody>

                @forelse ($gpSheetData as $index => $row)

                    <tr>

                        <td style="border:1px solid #000; padding:6px;">
                            {{ $index + 1 }}
                        </td>

                        @foreach ($row as $field => $value)

                            <td
                                style="
                                    border:1px solid #000;
                                    padding:6px;
                                    text-align:center;
                                "
                            >

                                @if (is_array($value))

                                    <pre style="margin:0; text-align:left;">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>

                                @elseif (is_object($value))

                                    <pre style="margin:0; text-align:left;">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>

                                @else

                                    {{ $value }}

                                @endif

                            </td>

                        @endforeach

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="20"
                            style="
                                border:1px solid #000;
                                padding:10px;
                                text-align:center;
                            "
                        >
                            No GP Sheet Data Found
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div> --}}


{{-- ================================================================
     SAVE RESULT
================================================================ --}}

<div style="text-align: center; margin-top: 20px;">

    <button
        id="saveSuppleResultSheet"
        class="btn btn-success"
    >
        Save Compartment Result Sheet
    </button>

</div>


<script>

$("#saveSuppleResultSheet").click(function () {

    Swal.fire({

        title: "Are you sure?",

        text: "Do you want to Save / Update Compartment Result?",

        icon: "question",

        showCancelButton: true,

        confirmButtonText: "Yes, Save",

        cancelButtonText: "Cancel",

        confirmButtonColor: "#28a745"

    }).then((result) => {

        if (result.isConfirmed) {

            let gpSheetData =
                $("#gpSheetData").val();

            let summaryData =
                $("#summaryData").val();


            $.ajax({

                url: "{{ route('results.supple.save-overall-result') }}",

                type: "POST",

                data: {

                    _token:
                        $('meta[name="csrf-token"]').attr('content'),

                    gp_sheet_data:
                        gpSheetData,

                    summary_data:
                        summaryData

                },

                beforeSend: function () {

                    $("#saveSuppleResultSheet")
                        .prop("disabled", true)
                        .html(
                            '<i class="fa fa-spinner fa-spin"></i> Saving...'
                        );

                },

                success: function (res) {

                    $("#saveSuppleResultSheet")
                        .prop("disabled", false)
                        .html(
                            '<i class="mdi mdi-content-save"></i> Save / Update Result'
                        );


                    if (res.status) {

                        Swal.fire({

                            icon: 'success',

                            title: 'Success',

                            text: res.message

                        });

                    }

                    else {

                        Swal.fire({

                            icon: 'error',

                            title: 'Error',

                            text: res.message

                        });

                    }

                },

                error: function (xhr) {

                    $("#saveSuppleResultSheet")
                        .prop("disabled", false)
                        .html(
                            '<i class="mdi mdi-content-save"></i> Save / Update Result'
                        );


                    Swal.fire({

                        icon: 'error',

                        title: 'Error',

                        text:
                            xhr.responseJSON?.message
                            ??
                            'Something went wrong.'

                    });


                    console.log(
                        xhr.responseJSON
                    );

                }

            });

        }

    });

});

</script>
