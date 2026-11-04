<style>

    .result-table-a4 {
        width: 100%;
        border-collapse: collapse;
        font-family: Arial, sans-serif;
    }

    .result-table-a4 th,
    .result-table-a4 td {
        border: 1px solid #ddd;
        padding: 8px;
        font-size: 12px;
        text-align: center;
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE HEADER
    |--------------------------------------------------------------------------
    */

    .result-table-a4 thead th {
        background: #f5f5f5;
        font-weight: 600;
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE FOOTER
    |--------------------------------------------------------------------------
    */

    .result-table-a4 tfoot td {
        font-weight: 600;
        padding: 25px 8px 10px;
        text-align: center;
        vertical-align: bottom;
    }

    .result-table-a4 tfoot td::before {
        content: "";
        display: block;
        height: 40px;
    }

    /*
    |--------------------------------------------------------------------------
    | RESULT HEADER
    |--------------------------------------------------------------------------
    */

    .result-header h4 {
        margin: 2px 0;
    }

    .result-header p {
        margin: 4px 0;
        text-align: left;
    }

    /*
    |--------------------------------------------------------------------------
    | ALIGNMENT
    |--------------------------------------------------------------------------
    */

    td.align-left {
        text-align: left !important;
    }

    td.align-right {
        text-align: right !important;
    }

    /*
    |--------------------------------------------------------------------------
    | COURSE INFO
    |--------------------------------------------------------------------------
    */

    .course-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
        font-size: 12px;
    }

</style>


<div class="table-responsive">

    <table class="result-table-a4">

        <thead>

            {{-- ==========================================================
                 RESULT HEADER
            =========================================================== --}}

            <tr>

                <td colspan="4" class="result-header">

                    <center>

                        <h4>
                            OFFICE OF THE CONTROLLER OF EXAMINATIONS
                        </h4>

                        <h4>
                            GURU ANGAD DEV VETERINARY AND ANIMAL SCIENCE UNIVERSITY,
                            LUDHIANA (INDIA)
                        </h4>

                        <h4>

                            Result of Annual Examination Session 

                            @if($resultSheets->isNotEmpty())

                                {{ $resultSheets->first()->exam?->exam_month ?? '' }}

                            @endif

                        </h4>

                    </center>


                    {{-- ==================================================
                         COURSE / BATCH / COLLEGE
                    =================================================== --}}

                    @if($resultSheets->isNotEmpty())

                        <br>

                        <div class="course-info">

                            <p style="margin-bottom: 0px">

                                <b>

                                    {{ $resultSheets->first()->course?->name ?? '' }}

                                    @if(
                                        !empty($resultSheets->first()->branch?->name) &&
                                        $resultSheets->first()->branch?->name !== 'NA'
                                    )

                                        |
                                        {{ $resultSheets->first()->branch?->name }}

                                    @endif


                                    @if(
                                        !empty($resultSheets->first()->batch?->name)
                                    )

                                        |
                                        Batch :
                                        {{ $resultSheets->first()->batch?->name }}

                                    @endif

                                </b>

                            </p>


                            @if(
                                !empty(
                                    $resultSheets->first()->institute?->name
                                )
                            )

                                <p style="margin-bottom: 0px">

                                    <b>

                                        College :
                                        {{ $resultSheets->first()->institute?->name }}

                                    </b>

                                </p>

                            @endif

                        </div>

                    @endif

                </td>

            </tr>


            {{-- ==========================================================
                 TABLE HEADER
            =========================================================== --}}

            <tr>

                <th>
                    #
                </th>

                <th>
                    Admission Number / Roll No
                </th>

                <th>
                    Name of the Students
                </th>

                <th>
                    Result
                </th>

            </tr>

        </thead>


        <tbody>

            {{-- ==========================================================
                 GROUP RESULT BY STUDENT
            =========================================================== --}}

            @php

                $studentResults = $resultSheets->groupBy('student_id');

            @endphp


            @foreach($studentResults as $studentId => $studentSheets)

                @php

                    /*
                    |--------------------------------------------------------------------------
                    | STUDENT
                    |--------------------------------------------------------------------------
                    */

                    $student = $studentSheets->first()->student;


                    /*
                    |--------------------------------------------------------------------------
                    | STUDENT SUMMARY
                    |--------------------------------------------------------------------------
                    */

                    $summary = $summaries->get($studentId);


                    /*
                    |--------------------------------------------------------------------------
                    | COUNT PASS
                    |--------------------------------------------------------------------------
                    */

                    $passCount = $studentSheets
                        ->filter(function ($sheet) {

                            return strtoupper(
                                trim($sheet->result ?? '')
                            ) === 'PASS';

                        })
                        ->count();


                    /*
                    |--------------------------------------------------------------------------
                    | GET SUPPLEMENTARY SUBJECTS
                    |--------------------------------------------------------------------------
                    */

                    $suppleSheets = $studentSheets
                        ->filter(function ($sheet) {

                            return strtoupper(
                                trim($sheet->result ?? '')
                            ) === 'SUPPLE';

                        });


                    /*
                    |--------------------------------------------------------------------------
                    | SUPPLEMENTARY COUNT
                    |--------------------------------------------------------------------------
                    */

                    $suppleCount = $suppleSheets->count();


                    /*
                    |--------------------------------------------------------------------------
                    | GET FAIL SUBJECTS
                    |--------------------------------------------------------------------------
                    */

                    $failCount = $studentSheets
                        ->filter(function ($sheet) {

                            return strtoupper(
                                trim($sheet->result ?? '')
                            ) === 'FAIL';

                        })
                        ->count();


                    /*
                    |--------------------------------------------------------------------------
                    | FINAL DISPLAY RESULT
                    |--------------------------------------------------------------------------
                    |
                    | 0 SUPPLE + ALL PASS
                    |      => GPA
                    |
                    | 1 SUPPLE
                    |      => Eligible for Subject
                    |
                    | 2 SUPPLE
                    |      => Eligible for Subject 1 and Subject 2
                    |
                    | 3 OR MORE SUPPLE
                    |      => FAIL
                    |
                    | ANY FAIL
                    |      => FAIL
                    |
                    |--------------------------------------------------------------------------
                    */

                    if ($failCount > 0) {

                        /*
                        |--------------------------------------------------------------------------
                        | ANY FAIL
                        |--------------------------------------------------------------------------
                        */

                        $displayResult = 'FAIL';

                    } elseif ($suppleCount > 2) {

                        /*
                        |--------------------------------------------------------------------------
                        | MORE THAN 2 SUPPLEMENTARY
                        |--------------------------------------------------------------------------
                        */

                        $displayResult = 'FAIL';

                    } elseif ($suppleCount === 2) {

                        /*
                        |--------------------------------------------------------------------------
                        | EXACTLY 2 SUPPLEMENTARY
                        |--------------------------------------------------------------------------
                        */

                        $suppleSubjectNames = $suppleSheets
                            ->map(function ($sheet) {

                                return $sheet->subject?->name ?? '';

                            })
                            ->filter()
                            ->values()
                            ->toArray();


                        $displayResult =
                            'Eligible for compartment in ' .
                            implode(
                                ' and ',
                                $suppleSubjectNames
                            );

                    } elseif ($suppleCount === 1) {

                        /*
                        |--------------------------------------------------------------------------
                        | EXACTLY 1 SUPPLEMENTARY
                        |--------------------------------------------------------------------------
                        */

                        $subjectName = $suppleSheets
                            ->first()
                            ->subject?->name ?? '';


                        $displayResult =
                            'Eligible for compartment in ' .
                            $subjectName;

                    } elseif (
                        $studentSheets->count() > 0 &&
                        $passCount === $studentSheets->count()
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | ALL SUBJECTS PASS
                        |--------------------------------------------------------------------------
                        */

                        $displayResult =
                            $summary?->gpa ?? '';

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | FALLBACK
                        |--------------------------------------------------------------------------
                        */

                        $displayResult = 'FAIL';

                    }

                @endphp


                {{-- ======================================================
                     STUDENT ROW
                ======================================================= --}}

                <tr>

                    {{-- ==================================================
                         SERIAL NUMBER
                    =================================================== --}}

                    <td>

                        {{ $loop->iteration }}

                    </td>


                    {{-- ==================================================
                         ADMISSION / ROLL NUMBER
                    =================================================== --}}

                    <td>

                        {{
                            $student?->admission_no
                            ?? $student?->university_roll_no
                            ?? $student?->roll_no
                            ?? $student?->registration_no
                            ?? ''
                        }}

                    </td>


                    {{-- ==================================================
                         STUDENT NAME
                    =================================================== --}}

                    <td class="align-left">

                        @php

                            $studentName = $student?->student_name;

                        @endphp


                        {{ $studentName }}

                    </td>


                    {{-- ==================================================
                         RESULT
                    =================================================== --}}

                    <td class="align-left">

                        {{ $displayResult }}

                    </td>

                </tr>


            @endforeach

        </tbody>


        {{-- ==============================================================
             FOOTER
        ============================================================== --}}

        @if($resultSheets->isNotEmpty())

            <tfoot>

                <tr>

                    <td colspan="4">

                        <div class="course-info">

                            <p>

                                Prepared by

                                <br><br><br>

                                Checked by

                            </p>


                            <p>

                                Controller of Examinations

                            </p>


                            <p>

                                Registrar, Guru Angad Dev Veterinary
                                <br>

                                and Animal Science University

                            </p>

                        </div>

                    </td>

                </tr>

            </tfoot>

        @endif


    </table>

</div>
