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
   // $filteredSubjects = $subjects;
   
   $filteredSubjects = $subjects
        ->sortBy(function ($subject) {
            return $subject->is_nc == 1 ? 1 : 0;
        })
        ->values();
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
            <th rowspan="2" class="export">GPA</th>
            <th rowspan="2" class="export">CGPA</th>
            <th rowspan="2" class="export">Grade</th>
        </tr>

        <tr>
            @foreach($filteredSubjects as $sub)
                <th>{{ $sub->subject_code }}</th>
            @endforeach
        </tr>
    </thead>

    @php
        $summaryData = [];
        $gpSheetData = [];
    @endphp
    <tbody>

        @php $srNo = 1; @endphp

        @foreach($students as $student)
        @php

            $enrolledSubjects = $studentSubjects[$student->id] ?? collect();

        @endphp

        @if($enrolledSubjects->isEmpty())

            @continue

        @endif

        <tr>

            <td>{{ $srNo++ }}</td>

            <td class="left">{{ $student->student_name }}</td>

            <td>{{ $student->university_roll_no }}</td>

            @php
                $grandTotalMarks = 0;   // SAME AS SUBJECT-WISE G TOTAL
                $remarks = [];

                $cgpaBlocked = false;
                $cgpaMessage = '';
                $isStudentSupple = 0;

                // if ($courseType == 'Semester Based' && count($missingPublishedSemesters)) {

                //     $cgpaBlocked = true;

                //     $cgpaMessage =
                //         'Semester ' .
                //         implode(', ', $missingPublishedSemesters) .
                //         ' result not saved/published.';
                // }

                if (
                    $courseType == 'Semester Based' &&
                    isset($missingPublishedSemesters[$student->id])
                ) {

                    $cgpaBlocked = true;

                    $cgpaMessage =
                        'Semester ' .
                        implode(', ', $missingPublishedSemesters[$student->id]) .
                        ' result not saved/published.';
                }

                // GPA Calculation
                $totalCreditHours = 0;
                $totalWeightedGP = 0;

                $previousCreditHours =
                    $previousSemesterSummary[$student->id]['credits'] ?? 0;

                $previousWeightedGP =
                    $previousSemesterSummary[$student->id]['weighted_gp'] ?? 0;
            @endphp

            @foreach($filteredSubjects as $sub)
            
                @php
                    $isNC = (int) ($sub->is_nc ?? 0) === 1;
                @endphp


                {{-- =====================================================
                    NC SUBJECT
                    ===================================================== --}}

                @if($isNC)

                    @php

                         /*
                        =========================================================
                        NC SUBJECT
                        Priority:
                        1. Supplementary grade IF actual grade exists
                        2. Otherwise Regular grade
                        =========================================================
                        */

                        $regularNcRow =
                            $resultGpSheet[$student->id][$sub->id][0]
                            ?? null;

                        $suppleNcRow =
                            $suppleResultGpSheet[$student->id][$sub->id][0]
                            ?? null;

                        /*
                        ---------------------------------------------------------
                        Supplementary row should override only when it has grade
                        ---------------------------------------------------------
                        */

                        if (
                            $suppleNcRow &&
                            !is_null($suppleNcRow->grade) &&
                            trim($suppleNcRow->grade) !== ''
                        ) {

                            $savedRow = $suppleNcRow;

                        } else {

                            $savedRow = $regularNcRow;

                        }

                        $ncGrade = $savedRow->grade ?? null;

                        $ncGradeShort = match (strtolower(trim($ncGrade ?? ''))) {

                            'satisfactory'   => 'S',
                            'unsatisfactory' => 'U',
                            'exempted'       => 'E',

                            default => $ncGrade ?? '-',

                        };

                        $gpSheetData[] = [

                            'student_id' => $student->id,
                            'subject_id' => $sub->id,

                            // 'credits' => 0,
                            'credits' => $sub->credits ?? 0,

                            'result' => 'PASS',
                            'is_supple' => 0,
                            'result_gp' => 0,

                            'grade' => $ncGrade,

                            'exam_id' => $exam->id,
                            'course_type' => $courseType,
                            'course_id' => $course->id,
                            'scheme_id' => $schemeId,
                            'branch_id' => $branch->id,
                            'semester_id' => $semesterId,
                            'annual_semester_id' => $annualSemesterId,
                            'batch_id' => $batch->id,
                            'institute_id' => $instituteId,

                        ];

                    @endphp

                    <td>
                        <div style="font-weight:bold; font-size:13px;">
                            {{ $ncGradeShort }}
                        </div>
                    </td>

                    @continue

                @endif

                {{-- IS NC subject End --}}

                @php

                /*
                =========================================================
                IS THIS SUBJECT SUPPLEMENTARY?
                =========================================================
                */

                $isSupple =
                    isset($suppleSubjectMap[$student->id][$sub->id]);
                    
                $hasSuppleMarks = false;
                    
                if ($isSupple && $hasSuppleMarks) {
                    $isStudentSupple = 1;
                }

                /*
                =========================================================
                ORIGINAL SAVED RESULT
                =========================================================
                */

                $savedRow =
                    $resultGpSheet[$student->id][$sub->id][0] ?? null;

                /*
                =====================================
                If Supplementary Result Saved
                then use latest GP Sheet
                =====================================
                */

                $savedRow =
                    $suppleResultGpSheet[$student->id][$sub->id][0]
                    ?? $savedRow;

                $credit = $sub->credits ?? 0;

                /*
                =========================================================
                SUPPLE EXTERNAL MARK
                =========================================================
                */

                $suppleExternal =
                    $suppleEndSemMarks[$student->id][$sub->id]['external'][0] ?? null;

                $hasSuppleMarks = !is_null($suppleExternal);

                if ($isSupple && $hasSuppleMarks) {
                    $isStudentSupple = 1;
                }
                
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
                                number_format(($savedRow->result_gp * $credit),2);

                            $gpSheetData[] = [
                                'student_id' => $student->id,
                                'subject_id' => $sub->id,
                                'credits' => $credit,
                                'result' => $savedRow->result,
                                'is_supple' => 0,
                                'result_gp' => $savedRow->result_gp,
                                'grade' => $savedRow->grade ?? null,

                                'exam_id' => $exam->id,
                                'course_type' => $courseType,

                                'course_id' => $course->id,
                                'scheme_id' => $schemeId,
                                'branch_id' => $branch->id,
                                'semester_id' => $semesterId,
                                'annual_semester_id' => $annualSemesterId,
                                'batch_id' => $batch->id,
                                'institute_id' => $instituteId,
                            ];

                        } elseif ($savedRow->result == 'SUPPLE') {

                            $class = 'supply';
                            $value = 'Supple';
                            $smallGp = $savedGp;

                            $remarks[] = 'Supple ' . $sub->subject_code;

                            $totalCreditHours += $credit;

                            $gpSheetData[] = [
                                'student_id' => $student->id,
                                'subject_id' => $sub->id,
                                'credits' => $credit,
                                'result' => $savedRow->result,
                                'is_supple' => 0,
                                'result_gp' => $savedRow->result_gp,
                                'grade' => $savedRow->grade ?? null,

                                'exam_id' => $exam->id,
                                'course_type' => $courseType,

                                'course_id' => $course->id,
                                'scheme_id' => $schemeId,
                                'branch_id' => $branch->id,
                                'semester_id' => $semesterId,
                                'annual_semester_id' => $annualSemesterId,
                                'batch_id' => $batch->id,
                                'institute_id' => $instituteId,
                            ];

                        } else {

                            $class = 'fail';
                            $value = 'Fail';
                            $smallGp = $savedGp;

                            $remarks[] = 'Fail ' . $sub->subject_code;

                            $totalCreditHours += $credit;

                            $gpSheetData[] = [
                                'student_id' => $student->id,
                                'subject_id' => $sub->id,
                                'credits' => $credit,
                                'result' => $savedRow->result,
                                'is_supple' => 0,
                                'result_gp' => $savedRow->result_gp,
                                'grade' => $savedRow->grade ?? null,

                                'exam_id' => $exam->id,
                                'course_type' => $courseType,

                                'course_id' => $course->id,
                                'scheme_id' => $schemeId,
                                'branch_id' => $branch->id,
                                'semester_id' => $semesterId,
                                'annual_semester_id' => $annualSemesterId,
                                'batch_id' => $batch->id,
                                'institute_id' => $instituteId,
                            ];
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
                        $subjectResultStatus = 'PASS';

                        // Add actual G Total
                        $grandTotalMarks += $grandSubjectTotal;

                        $totalCreditHours += $credit;

                        $totalWeightedGP +=
                            ($calculatedGp * $credit);

                    } else {

                        // FAIL → show Fail + small GP

                        $class = 'fail';
                        $value = 'Fail';
                        $subjectResultStatus = 'FAIL';
                        $smallGp = $calculatedGp;

                        $remarks[] = 'Fail ' . $sub->subject_code;

                        $totalCreditHours += $credit;
                    }
                }

                if ($isSupple && $hasSuppleMarks) {

                    $gpSheetData[] = [

                        'student_id' => $student->id,
                        'subject_id' => $sub->id,
                        'credits'    => $credit,

                        'result'     => $subjectResultStatus,
                        'is_supple' => 1,
                        'result_gp'  => $calculatedGp,
                        'grade'      => null,

                        'exam_id' => $exam->id,
                        'course_type' => $courseType,
                        'course_id' => $course->id,
                        'scheme_id' => $schemeId,
                        'branch_id' => $branch->id,
                        'semester_id' => $semesterId,
                        'annual_semester_id' => $annualSemesterId,
                        'batch_id' => $batch->id,
                        'institute_id' => $instituteId,

                    ];

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

                // $cgpa = $gpa;

                /*
                ==========================================
                CGPA
                ==========================================
                */

                $currentWeightedGP = $totalWeightedGP;
                $currentCredits    = $totalCreditHours;

                $totalAllWeightedGP =
                    $previousWeightedGP +
                    $currentWeightedGP;

                $totalAllCredits =
                    $previousCreditHours +
                    $currentCredits;

                $cgpa = '-';
                $finalGrade = null;

                if(!$cgpaBlocked){

                    $cgpa = $totalAllCredits > 0
                        ? number_format(
                            $totalAllWeightedGP / $totalAllCredits,
                            3
                        )
                        : '-';

                   /*
                    ===============================
                    Grade From GPA
                    ===============================
                    */

                    if ($cgpa !== '-') {

                        $gradeRow = $grades
                            ->where('percent_from', '<=', (float) $gpa)
                            ->where('percent_upto', '>=', (float) $gpa)
                            ->first();

                        $finalGrade = $gradeRow
                            ? ($gradeRow->grade_name ?? $gradeRow->grade_description ?? null)
                            : null;
                    }

                }

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

                    'cgpa'=>$cgpaBlocked ? null : $cgpa,
                    
                    'is_supple' => $isStudentSupple,

                    'result_status' => $studentResultStatus,

                    'grade_status'=> $finalGrade ?? '',

                    'remarks' => count($remarks)
                        ? implode(', ', $remarks)
                        : 'Pass',

                    'exam_id' => $exam->id,
                    'course_type' => $courseType,

                    'course_id' => $course->id,
                    'scheme_id' => $schemeId,
                    'branch_id' => $branch->id,
                    'semester_id' => $semesterId,
                    'annual_semester_id' => $annualSemesterId,
                    'batch_id' => $batch->id,
                    'institute_id' => $instituteId,

                    'result_declare' => 1,

                ];

            @endphp

            <td class="remarks">

                @if(count($remarks))

                    {{ implode(', ', $remarks) }}

                @else

                    <span class="pass">Pass</span>

                @endif

            </td>

            <td class="pass export">{{ $gpa }}</td>

            {{-- <td class="pass export">{{ $cgpa }}</td> --}}
            <td class="pass export">

            @if($cgpaBlocked)

            -

            @else

            {{ $cgpa }}

            @endif

            </td>


            <td class="export">

                @if($cgpaBlocked)

                    <span class="fail">-</span>

                    <br>

                    <small class="text-danger" style="font-size:10px;">
                        {{ $cgpaMessage }}
                    </small>

                @else

                    {{ $finalGrade }}

                @endif

            </td>

        </tr>

        @endforeach

        </tbody>


</table>

{{-- <hr>

<h3>GP Sheet Data</h3>

<table border="1" cellpadding="5" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>#</th>
            <th>Student ID</th>
            <th>Subject ID</th>
            <th>Credits</th>
            <th>Result</th>
            <th>is supple</th>
            <th>Result GP</th>
            <th>Grade</th>

            <th>Exam ID</th>
            <th>Course Type</th>
            <th>Course ID</th>
            <th>Scheme ID</th>
            <th>Branch ID</th>
            <th>Semester ID</th>
            <th>Annual Semester ID</th>
            <th>Batch ID</th>
            <th>Institute ID</th>
        </tr>
    </thead>

    <tbody>

    @foreach($gpSheetData as $i => $row)

        <tr>

            <td>{{ $i + 1 }}</td>

            <td>{{ $row['student_id'] }}</td>

            <td>{{ $row['subject_id'] }}</td>

            <td>{{ $row['credits'] }}</td>

            <td>{{ $row['result'] }}</td>

            <td>{{ $row['is_supple'] }}</td>

            <td>{{ $row['result_gp'] }}</td>

            <td>{{ $row['grade'] }}</td>

            <td>{{ $row['exam_id'] }}</td>

            <td>{{ $row['course_type'] }}</td>

            <td>{{ $row['course_id'] }}</td>

            <td>{{ $row['scheme_id'] }}</td>

            <td>{{ $row['branch_id'] }}</td>

            <td>{{ $row['semester_id'] }}</td>

            <td>{{ $row['annual_semester_id'] }}</td>

            <td>{{ $row['batch_id'] }}</td>

            <td>{{ $row['institute_id'] }}</td>

        </tr>

    @endforeach

    </tbody>
</table> --}}
{{--
<br><br>

<h3>Result Summary Data</h3>

<table border="1" cellpadding="5" cellspacing="0" width="100%">

    <thead>

        <tr>

            <th>#</th>

            <th>Student ID</th>

            <th>GPA</th>

            <th>CGPA</th>
            
            <th>Is Supple</th>

            <th>Result Status</th>

            <th>Grade Status</th>

            <th>Remarks</th>

            <th>Exam ID</th>

            <th>Course Type</th>

            <th>Course ID</th>

            <th>Scheme ID</th>

            <th>Branch ID</th>

            <th>Semester ID</th>

            <th>Annual Semester ID</th>

            <th>Batch ID</th>

            <th>Institute ID</th>

            <th>Result Declare</th>

        </tr>

    </thead>

    <tbody>

    @foreach($summaryData as $i => $row)

        <tr>

            <td>{{ $i + 1 }}</td>

            <td>{{ $row['student_id'] }}</td>

            <td>{{ $row['gpa'] }}</td>

            <td>{{ $row['cgpa'] }}</td>
            
            <td>{{ $row['is_supple'] }}</td>

            <td>{{ $row['result_status'] }}</td>

            <td>{{ $row['grade_status'] }}</td>

            <td>{{ $row['remarks'] }}</td>

            <td>{{ $row['exam_id'] }}</td>

            <td>{{ $row['course_type'] }}</td>

            <td>{{ $row['course_id'] }}</td>

            <td>{{ $row['scheme_id'] }}</td>

            <td>{{ $row['branch_id'] }}</td>

            <td>{{ $row['semester_id'] }}</td>

            <td>{{ $row['annual_semester_id'] }}</td>

            <td>{{ $row['batch_id'] }}</td>

            <td>{{ $row['institute_id'] }}</td>

            <td>{{ $row['result_declare'] }}</td>

        </tr>

    @endforeach

    </tbody>

</table> --}}

<input type="hidden" id="result_gp_sheet_data"
       value='@json($gpSheetData)'>

<input type="hidden" id="result_summary_data"
       value='@json($summaryData)'>

<div class="text-center mt-3">
    <button type="button"
            class="btn btn-success"
            id="saveSuppleResult">
        <i class="mdi mdi-content-save"></i>
        Save / Update Result
    </button>
</div>

<script>

$("#saveSuppleResult").click(function () {

    Swal.fire({
        title: "Are you sure?",
        text: "Do you want to Save / Update Supplementary Result?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes, Save",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#28a745"
    }).then((result) => {

        if(result.isConfirmed){

            let gpSheetData = $("#result_gp_sheet_data").val();
            let summaryData = $("#result_summary_data").val();

            $.ajax({

                url: "{{ route('results.supple.save-overall-result') }}",
                type: "POST",

                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    gp_sheet_data: gpSheetData,
                    summary_data: summaryData
                },

                beforeSend:function(){

                    $("#saveSuppleResult")
                        .prop("disabled",true)
                        .html('<i class="fa fa-spinner fa-spin"></i> Saving...');

                },

                success:function(res){

                    $("#saveSuppleResult")
                        .prop("disabled",false)
                        .html('<i class="mdi mdi-content-save"></i> Save / Update Result');

                    if(res.status){

                        Swal.fire({
                            icon:'success',
                            title:'Success',
                            text:res.message
                        });

                    }else{

                        Swal.fire({
                            icon:'error',
                            title:'Error',
                            text:res.message
                        });

                    }

                },

                error:function(xhr){

                    $("#saveSuppleResult")
                        .prop("disabled",false)
                        .html('<i class="mdi mdi-content-save"></i> Save / Update Result');

                    Swal.fire({
                        icon:'error',
                        title:'Error',
                        text:xhr.responseJSON?.message ?? 'Something went wrong.'
                    });

                    console.log(xhr.responseJSON);

                }

            });

        }

    });

});

</script>
