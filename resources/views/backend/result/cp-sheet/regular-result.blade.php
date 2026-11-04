<style>
    .dmc-print-wrapper {
        position: relative;
        width: 100%;
    }

    .dmc-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 500px;
        height: auto;
        transform: translate(-50%, -50%);
        opacity: 0.08;
        pointer-events: none;
        z-index: 0;
    }

    .dmc-table {
        position: relative;
        z-index: 1;
    }

    table.dmc-table {
        border: 0px;
        border-collapse: collapse;
        width: 100%;
        font-size: 14px;
    }

    table.dmc-table thead tr td {
        font-weight: bold;
    }

    /*table.dmc-table thead .header_info {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
    }

    table.dmc-table thead .header_info .personal-detail {
        width: 50%;
        font-weight: inherit;
    }*/
    
    table.dmc-table thead .header_info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        column-gap: 0px;
        margin-top: 6px;
    }

    table.dmc-table thead .header_info .personal-detail {
        display: grid;
        grid-template-columns: 150px 1fr;
        align-items: center;
        width: 100%;
        font-weight: inherit;
    }

    table.dmc-table thead .header_info .personal-detail span {
        font-weight: normal;
        white-space: nowrap;
    }

    table.dmc-table thead .header_info .personal-detail b {
        font-weight: 600;
        white-space: nowrap;
    }

    table.dmc-table tbody {
        border-bottom: 1px solid #000 !important;
    }

    table.dmc-table tbody tr td {
        border: none !important;
    }

    table.dmc-table tfoot tr td {
        border: none !important;
    }

    table.dmc-table tfoot tr.bottom-signature td {
        padding-top: 60px;
    }

    table.dmc-table tfoot tr.bottom-signature td.text-right {
        text-align: right;
    }

    tr.top-bottom-border {
        border-top: 1px solid #000 !important;
        border-bottom: 1px solid #000 !important;
    }
    td.text-end {
        text-align: right !important;
    }
    table tbody tr td{
        padding : 0px;
        margin : 0px;
    }
</style>

@php
    function romanToNumber($roman)
    {
        $roman = strtoupper(trim($roman));
    
        $map = [
            'I'   => 1,
            'II'  => 2,
            'III' => 3,
            'IV'  => 4,
            'V'   => 5,
            'VI'  => 6,
            'VII'  => 7,
            'VIII'=> 8,
            'IX'  => 9,
            'X'   => 10,
        ];
    
        return $map[$roman] ?? null;
    }
    
    //function getAcademicYearFromSemester($semesterName)
    //{
    //    $semesterNumber = romanToNumber($semesterName);

      //  if (!$semesterNumber) {
        //    return '';
    //    }

      //  return (int) ceil($semesterNumber / 2);
    // }
    function getAcademicYearFromSemester($semesterName)
    {
        $semesterNumber = romanToNumber($semesterName);

        if (!$semesterNumber) {
            return '';
        }

        // Repeat 1, 2 for every pair of semesters
        return (($semesterNumber - 1) % 2) + 1;
    }
@endphp

@foreach($students as $studentData)

    @php

        /*
        |--------------------------------------------------------------------------
        | STUDENT DATA
        |--------------------------------------------------------------------------
        */

        $student  = $studentData['student'];
        $summary  = $studentData['summary'];
        // $subjects = $studentData['subjects'];
        $subjects = $studentData['subjects']
        ->sortBy(function ($subject) {
            return optional($subject->subject)->is_nc ? 1 : 0;
        })
        ->values();


        /*
        |--------------------------------------------------------------------------
        | STUDENT BASIC INFORMATION
        |--------------------------------------------------------------------------
        */

        $studentName =
            $student->name
            ?? $student->student_name
            ?? $student->full_name
            ?? '';


        $admissionNo =
            $student->university_roll_no
            ?? $student->reg_no
            ?? '';


        $fatherName =
            $student->father_name
            ?? $student->father
            ?? $student->fathers_name
            ?? '';


        $motherName =
            $student->mother_name
            ?? $student->mother
            ?? $student->mothers_name
            ?? '';


        /*
        |--------------------------------------------------------------------------
        | SEMESTER / ANNUAL TERM
        |--------------------------------------------------------------------------
        */

        if (($summary->course_type ?? '') === 'Semester Based') {

            $semesterName =
                optional($summary->semester)->name
                ?? optional($summary->semester)->semester_name
                ?? $summary->semester_id
                ?? '';

        } else {

            $semesterName =
                optional($summary->annualSemester)->name
                ?? optional($summary->annualSemester)->annual_semester_name
                ?? $summary->annual_semester_id
                ?? '';
        }
        
        // Student's actual current semester
        $studentSemesterName = optional($student->semester)->name ?? '';


        /*
        |--------------------------------------------------------------------------
        | DEGREE / COURSE
        |--------------------------------------------------------------------------
        */

        // $degreeProgram =
        //     optional($summary->course)->name
        //     ?? optional($summary->course)->course_name
        //     ?? '';
        $degreeProgram = optional($summary->course)->name ?? '';

        $branchName = optional($summary->branch)->name ?? '';

        if (!empty($branchName) && strtoupper(trim($branchName)) !== 'NA') {
            $degreeProgram .= ' ( ' . $branchName. ' ) ';
        }


        /*
        |--------------------------------------------------------------------------
        | TOTAL CREDIT HOURS
        |--------------------------------------------------------------------------
        */

        // $totalCreditHours = $subjects->sum(function ($subject) {

        //     return (float) ($subject->credits ?? 0);

        // });

        $totalCreditHours = $subjects->sum(function ($subject) {

            $isNc = (bool) optional($subject->subject)->is_nc;

            if ($isNc) {
                return 0;
            }

            return (float) ($subject->credits ?? 0);
        });


        /*
        |--------------------------------------------------------------------------
        | TOTAL CREDIT POINTS
        |--------------------------------------------------------------------------
        |
        | PASS:
        | credits × result_gp
        |
        | Other result:
        | 0
        |
        */

        $totalCreditPoints = $subjects->sum(function ($subject) {

                $isNc = (bool) optional($subject->subject)->is_nc;

                // NC subject has no credit points
                if ($isNc) {
                    return 0;
                }

                $credits = (float) ($subject->credits ?? 0);

                $result = strtoupper(
                    trim($subject->result ?? '')
            );

            $resultGp = (float) ($subject->result_gp ?? 0);

            if ($result === 'PASS') {
                return round($credits * $resultGp, 2);
            }

            return 0;
        });


        /*
        |--------------------------------------------------------------------------
        | GPA / CGPA
        |--------------------------------------------------------------------------
        */

        $gpa = $summary->gpa ?? '';

        // $cgpa = $summary->cgpa ?? '';


        /*
        |--------------------------------------------------------------------------
        | OVERALL PERFORMANCE
        |--------------------------------------------------------------------------
        */

        $overallPerformance =
            $summary->grade_status
            ?? '';


        /*
        |--------------------------------------------------------------------------
        | CURRENT DATE
        |--------------------------------------------------------------------------
        */

        //$currentDate = now()->format('d-m-Y');
        $printDate = \Carbon\Carbon::parse($printDate)->format('d-m-Y');


        /*
        |--------------------------------------------------------------------------
        | LESS CREDIT HOURS / POINTS
        |--------------------------------------------------------------------------
        */

        $lessCreditHours = 0;

        $lessCreditPoints = 0;


        /*
        |--------------------------------------------------------------------------
        | PREVIOUS CREDIT HOURS / POINTS
        |--------------------------------------------------------------------------
        */

        $previousCreditHours =
            (float) ($studentData['previous_credit_hours'] ?? 0);

        $previousCreditPoints =
            (float) ($studentData['previous_credit_points'] ?? 0);


        /*
        |--------------------------------------------------------------------------
        | CUMULATIVE CREDIT HOURS
        |--------------------------------------------------------------------------
        */

        $cumulativeCreditHours =
            $totalCreditHours
            + $previousCreditHours
            - $lessCreditHours;


        /*
        |--------------------------------------------------------------------------
        | CUMULATIVE CREDIT POINTS
        |--------------------------------------------------------------------------
        */

        $cumulativeCreditPoints =
            $totalCreditPoints
            + $previousCreditPoints
            - $lessCreditPoints;
            
        $cgpa = $cumulativeCreditHours > 0
        ? ($cumulativeCreditPoints / $cumulativeCreditHours)
        : '';

    @endphp


    <div class="dmc-sheet table-responsive">
        <div class="dmc-print-wrapper">

            <img
                class="dmc-watermark"
                src="{{ asset('assets/images/watermark.png') }}"
                alt=""
            >

            <table class="table dmc-table dmc-result-table">

                <thead class="table-header">

                    <tr>
                        <td colspan="6">

                            <br><br><br>
                            <center>
                                <h3>
                                    GURU ANGAD DEV VETERINARY AND ANIMAL SCIENCES UNIVERSITY, LUDHIANA (INDIA)
                                </h3>
                                <br>
                            </center>

                            <div class="header_info">

                                {{--<div class="personal-detail">
                                    <span>Name:</span>
                                    <b>{{ ucwords(strtolower($studentName)) }}</b>
                                </div>--}}
                                <div class="personal-detail">
                                    <span>Name:</span>
                                    <b>
                                        {{ collect(explode(' ', ucwords(strtolower($studentName))))
                                            ->map(fn($word) => strlen($word) === 2 ? strtoupper($word) : $word)
                                            ->implode(' ') }}
                                    </b>
                                </div>

                                <div class="personal-detail">
                                    <span>Admn. No:</span>
                                    <b>{{ $admissionNo }}</b>
                                </div>

                            </div>

                            <div class="header_info">

                                {{--<div class="personal-detail">
                                    <span>Father's Name:</span>
                                    <b>Mr. {{ $fatherName }}</b>
                                </div>

                                <div class="personal-detail">
                                    <span>Mother's Name:</span>
                                    <b>Ms. {{ $motherName }}</b>
                                </div>
                                
                                <div class="personal-detail">
                                    <span>Father's Name:</span>
                                    <b>
                                        Mr. {{ ucwords(strtolower(preg_replace('/^Mr\.?\s*/i', '', trim($fatherName)))) }}
                                    </b>
                                </div>--}}
                                
                                <div class="personal-detail">
                                    <span>Father's Name:</span>
                                    <b>
                                        Mr. {{ collect(explode(' ', ucwords(strtolower(preg_replace('/^Mr\.?\s*/i', '', trim($fatherName))))))
                                            ->map(fn($word) => strlen($word) === 2 ? strtoupper($word) : $word)
                                            ->implode(' ') }}
                                    </b>
                                </div>
                                
                                <div class="personal-detail">
                                    <span>Mother's Name:</span>
                                    <b>
                                        Ms. {{ ucwords(strtolower(preg_replace('/^Ms\.?\s*/i', '', trim($motherName)))) }}
                                    </b>
                                </div>

                            </div>


                            <div class="header_info">

                                <div class="personal-detail">
                                    <span>Semester:</span>
                                    <b>{{ $exam->exam_month ?? '' }} {{ getAcademicYearFromSemester($semesterName) }} ({{ $studentData['available_semester_count'] ?? 0 }})<br></b>
                                    {{-- <b>{{ $exam->exam_month ?? '' }} {{ getAcademicYearFromSemester($semesterName) }} ({{ romanToNumber($semesterName) }})<br></b> 
                                    <b>{{ $exam->exam_month ?? '' }} {{ romanToNumber($semesterName) }} ({{ romanToNumber($studentSemesterName) }})</b> --}}
                                </div>

                                <div class="personal-detail">
                                    <span>Programme:</span>
                                    <b>{{ $degreeProgram }}</b>
                                </div>

                            </div>

                        </td>
                    </tr>


                    <tr>
                        <td colspan="6" class="spacing"></td>
                    </tr>


                    <tr class="top-bottom-border">

                        <td width="10%">
                            Sr.No
                        </td>

                        <td colspan="2" width="50%">
                            Course Title
                        </td>

                        <td width="20%">
                            Course No.
                        </td>

                        <td width="10%">
                            Cr.Hr.
                        </td>

                        <td width="10%">
                            Gr.Pts.
                        </td>

                    </tr>

                </thead>


                <tbody>

                    @forelse($subjects as $index => $subject)

                        @php

                            /*
                            |--------------------------------------------------------------------------
                            | SUBJECT NAME
                            |--------------------------------------------------------------------------
                            */

                            $subjectName =
                                optional($subject->subject)->name
                                ?? optional($subject->subject)->subject_name
                                ?? '';


                            /*
                            |--------------------------------------------------------------------------
                            | SUBJECT CODE
                            |--------------------------------------------------------------------------
                            */

                            $subjectCode =
                                optional($subject->subject)->code
                                ?? optional($subject->subject)->subject_code
                                ?? optional($subject->subject)->course_code
                                ?? '';


                            /*
                            |--------------------------------------------------------------------------
                            | CREDIT HOURS
                            |--------------------------------------------------------------------------
                            */

                            // $creditHours =
                            //     (float) ($subject->credits ?? 0);
                            // $result = strtoupper(
                            //     trim($subject->result ?? '')
                            // );

                            // $resultGp = $result === 'PASS'
                            //     ? ($subject->result_gp ?? '')
                            //     : 'FAIL';

                            $isNc = (bool) optional($subject->subject)->is_nc;


                            /*
                            |--------------------------------------------------------------------------
                            | CREDIT HOURS
                            |--------------------------------------------------------------------------
                            */

                            // $creditHours = $isNc
                            //     ? 0
                            //     : (float) ($subject->credits ?? 0);

                            $creditHours = (float) ($subject->credits ?? 0);


                            /*
                            |--------------------------------------------------------------------------
                            | RESULT / GRADE
                            |--------------------------------------------------------------------------
                            |
                            | result_gp_sheet.grade will be displayed
                            | instead of obtained marks / result GP.
                            |
                            */

                            $grade = $subject->grade ?? '';

                        @endphp


                        <tr>

                            <td>
                                {{ $index + 1 }}
                            </td>

                            <td colspan="2">
                                {{ $subjectName }}
                            </td>

                            <td>
                                {{ $subjectCode }}
                            </td>

                            <td>
                                {{-- @if($isNc)
                                    0
                                @else
                                    {{ rtrim(rtrim(number_format($creditHours, 2), '0'), '.') }}
                                @endif --}}
                                {{ rtrim(rtrim(number_format($creditHours, 2), '0'), '.') }}
                                @if($isNc)
                                    (NG)
                                @endif
                            </td>

                            {{-- <td>
                                {{ $isNc ? $grade : ($subject->result_gp ?? '') }}
                                <br>
                                <small class="text-danger" style="font-size: 10px;">

                                    @if($isNc)
                                        {{ $grade }}
                                    @else
                                        {{ rtrim(rtrim(number_format($creditHours, 2), '0'), '.') }}
                                        ×
                                        {{ $subject->result_gp ?? 0 }}
                                        =
                                        {{ number_format($creditHours * ($subject->result_gp ?? 0), 2) }}
                                    @endif

                                </small>
                            </td> --}}

                            <td>

                                @if($isNc)

                                    {{ $grade }}

                                @else

                                    @php
                                        $result = strtoupper(trim($subject->result ?? ''));
                                    @endphp

                                    @if($result === 'PASS')

                                        {{ $subject->result_gp ?? '' }}

                                        <br>

                                        <small class="text-danger no-export" style="font-size: 10px;">
                                            {{ rtrim(rtrim(number_format($creditHours, 2), '0'), '.') }}
                                            ×
                                            {{ $subject->result_gp ?? 0 }}
                                            =
                                            {{ number_format($creditHours * ($subject->result_gp ?? 0), 2) }}
                                        </small>

                                    @else

                                        Fail

                                        {{-- <br>

                                        <small class="text-danger" style="font-size: 10px;">
                                            {{ rtrim(rtrim(number_format($creditHours, 2), '0'), '.') }}
                                            ×
                                            {{ $subject->result_gp ?? 0 }}
                                            =
                                            {{ number_format($creditHours * ($subject->result_gp ?? 0), 2) }}
                                        </small> --}}

                                    @endif

                                @endif

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td colspan="6">
                                No subject result found.
                            </td>

                        </tr>

                    @endforelse

                </tbody>


                <tfoot>

                    <tr>

                        <td colspan="2">
                            Total Cr. Hrs:
                            <b>{{ rtrim(rtrim(number_format($totalCreditHours, 2), '0'), '.') }}</b>
                        </td>

                        <td colspan="2">
                            Total Cr. Pts. :
                            <b>{{ number_format($totalCreditPoints, 2) }}</b>
                        </td>

                        <td colspan="2" class="text-end">
                            GPA:
                            <b>@if($gpa !== '')
                                {{ number_format((float) $gpa, 3) }}
                            @endif</b>
                        </td>

                    </tr>


                    <tr>

                        <td colspan="2">
                            Prev. Cr. Hrs:
                            <b>{{ rtrim(rtrim(number_format($previousCreditHours, 2), '0'), '.') }}</b>
                        </td>

                        <td colspan="4">
                            Prev. Cr. Pts. :
                            <b>{{ number_format($previousCreditPoints, 2) }}</b>
                        </td>

                    </tr>


                    <tr>

                        <td colspan="2">
                            Less Cr. Hrs:
                            <b>{{ rtrim(rtrim(number_format($lessCreditHours, 2), '0'), '.') }}</b>
                        </td>

                        <td colspan="2">
                            Less Cr. Pts. :
                            <b>{{ number_format($lessCreditPoints, 2) }}</b> 
                            
                            
                        </td>
                        <td colspan="2" class="text-end">
                            For Repeating
                        </td>

                    </tr>


                    <tr>

                        <td colspan="2">
                            Cumu. Cr. Hrs:
                            <b>{{ rtrim(rtrim(number_format($cumulativeCreditHours, 2), '0'), '.') }}</b>
                        </td>

                        <td colspan="4">
                            Cumu. Cr. Pts. :
                            <b>{{ number_format($cumulativeCreditPoints, 2) }}</b>
                        </td>

                    </tr>
                    
                    <tr>
                        <td colspan="6" class="text-end">
                            CGPA:
                            <b>@if($cgpa !== '')
                                {{ number_format((float) $cgpa, 3) }}
                            @endif</b>
                        </td>
                    </tr>


                    <tr>

                        <td colspan="6">
                            Overall Performance :
                            <b>{{ $overallPerformance }}</b>
                        </td>

                    </tr>


                    <tr class="bottom-signature">

                        <td colspan="3">
                            Date:
                            {{ $printDate }}
                        </td>

                        <td colspan="3" class="text-right">
                            Registrar
                        </td>

                    </tr>

                </tfoot>

            </table>

        </div>
    </div>

@endforeach
