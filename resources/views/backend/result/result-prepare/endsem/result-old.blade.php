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
$filteredSubjects = $subjects->filter(function ($sub) use ($course) {
    return !(
        $course->course == 'UG' &&
        $sub->type == 'Practical' &&
        $sub->is_nc == 0
    );
});
@endphp

<table class="result-table">
    <thead class="result-header">
        <!-- ================= HEADER TITLE ================= -->
        <tr>
            <th colspan="{{ 3 + count($subjects) + 4 }}" style="text-align:center;">
                <h3 style="margin:0;">OFFICE OF CONTROLLER OF EXAMINATIONS</h3>
                <h4 style="margin:0;">
                    GURU ANGAD DEV VETERINARY AND ANIMAL SCIENCES UNIVERSITY, LUDHIANA (INDIA)
                </h4>
                <h4 style="margin:0;">
                    {{ $exam->exam_resultsheet_name ?? $exam->name }}
                </h4>
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

    @foreach($students as $key => $student)

        @php
            $remarks = [];
            $courseTypeName = $course->course;
            $totalCreditHours = 0;
            $totalWeightedGP  = 0;
        @endphp

        <tr>
            <td>{{ $key + 1 }}</td>
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

                        } elseif ($grade == 'Unsatisfactory') {

                            $class = 'fail';
                            $value = 'U';

                            $remarks[] = "Unsatisfactory {$sub->subject_code}";

                        }

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

                        $endFinalOriginal =
                            $endSemMarks[$student->id][$sub->id][0]->obtained_marks ?? 0;

                        $graceMarks =
                            $endSemMarks[$student->id][$sub->id][0]->grace_marks ?? 0;

                    } else {

                        /*
                        --------------------------------
                        PRACTICAL SUBJECT
                        Grace from MID SEM PRACTICAL
                        --------------------------------
                        */

                        $endFinalOriginal = $pracFromMid;

                        $graceMarks =
                            $midSemMarks[$student->id][$sub->id]['Practical'][0]->grace_marks ?? 0;
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

                        if ($courseTypeName == "UG") {

                            $practicalFinal = $pracFromMid;

                        } else {

                            $practicalFinal = $endFinal;
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

                    /*
                    =============================
                    RESULT
                    =============================
                    */

                    if ($sub->type == "Theory") {

                        /*
                        =============================
                        PRACTICAL FAIL CHECK
                        =============================
                        */

                        if (

                            $practicalFinal > 0
                            && $practicalFinal < $practicalMinMarks

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
                    }

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

                        $totalWeightedGP += ($value * $credit);
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

                $cgpa = $gpa;
            @endphp

            @php
                $finalGrade = '';

                foreach($grades as $g){
                    if($cgpa >= $g->percent_from && $cgpa <= $g->percent_upto){
                        $finalGrade = $g->grade_description;
                        break;
                    }
                }
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
            <td class="pass no-export">{{ $finalGrade }}</td>

        </tr>

    @endforeach

    </tbody>

    <tfoot>
        <tr>
            <td colspan="{{ 3 + count($subjects) + 4 }}">

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
 
<div style="text-align:center; margin-top:20px;">
    <button id="saveResultSheet" class="btn btn-success">
        Save Result Sheet
    </button>
</div>

<script>
$(document).on('click', '#saveResultSheet', function () {

    let btn = $(this);

    // ✅ Confirmation Alert
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to save this result sheet?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Save it!'
    }).then((result) => {

        if (!result.isConfirmed) return;

        btn.prop('disabled', true); // disable button

        let data = {
            exam_id: "{{ $exam->id }}",
            course_type: "{{ $courseType ?? '' }}",
            course_id: "{{ $course->id }}",
            branch_id: "{{ $branch->id }}",
            batch_id: "{{ $batch->id }}",
            scheme_id: "{{ $schemeId ?? '' }}",
            semester_id: "{{ $semesterId ?? '' }}",
            annual_semester_id: "{{ $annualSemesterId ?? '' }}",
            institute_id: "{{ $instituteId ?? '' }}",
            _token: "{{ csrf_token() }}"
        };

        // 🔄 Loading
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
            method: "POST",
            data: data,

            success: function (res) {

                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: 'Result saved successfully',
                    timer: 2000,
                    showConfirmButton: false
                });

                btn.prop('disabled', false);
            },

            error: function (err) {

                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    text: 'Error saving result'
                });

                btn.prop('disabled', false);
            }
        });

    });
});
</script> 