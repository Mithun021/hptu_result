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

    .remarks {
        text-align: left;
    }
</style>

<table class="result-table">
    <thead>
        <tr>
            <th colspan="{{ 3 + count($subjects) + 4 }}">
                <h3>OFFICE OF CONTROLLER OF EXAMINATIONS</h3>

                <h4>
                    GURU ANGAD DEV VETERINARY AND ANIMAL SCIENCES UNIVERSITY
                </h4>

                <h4>
                    {{ $exam->exam_resultsheet_name ?? $exam->name }}
                </h4>

                <p style="text-align: left;">
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

            <th colspan="{{ count($subjects) }}">
                Subjects
            </th>

            <th rowspan="2">Remarks</th>
            <th rowspan="2">GPA</th>
            <th rowspan="2">CGPA</th>
            <th rowspan="2">Grade</th>
        </tr>

        <tr>
            @foreach ($subjects->sortBy(function ($sub) {
                return $sub->is_nc == 1 ? 1 : 0;
            }) as $sub)
                <th>{{ $sub->subject_code }}</th>
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
                $remarks = [];
                $totalCredit = 0;
                $totalGP = 0;
                $failCount = 0;
                $subjectResults = [];
            @endphp

            @foreach ($subjects->sortBy(function ($sub) {
                return $sub->is_nc == 1 ? 1 : 0;
            }) as $sub)
                @php
                    $isEnrolled = isset($studentSubjects[$student->id]) &&
                        $studentSubjects[$student->id]->contains('subject_id', $sub->id);

                    $hasMid = isset($midSemMarks[$student->id][$sub->id]);

                    $hasEnd = isset($endSemMarks[$student->id][$sub->id]) &&
                        count($endSemMarks[$student->id][$sub->id]) > 0;

                    $value = '-';
                    $class = '';
                @endphp

                @if ($sub->is_nc == 1)
                    @if ($isEnrolled)
                        @php
                            $external = $endSemMarks[$student->id][$sub->id]['external'][0] ?? null;
                            $gradeValue = $external->grade ?? '-';

                            $value = $gradeValue;

                            if (strtolower($gradeValue) == 'fail') {
                                $class = 'fail';
                                $failCount++;
                                $remarks[] = "Fail {$sub->subject_code}";
                            } else {
                                $class = 'pass';
                            }

                            $ncResult = strtolower($gradeValue) == 'fail'
                                ? 'FAIL'
                                : 'PASS';

                            $ncGrade = strtolower($gradeValue) == 'fail'
                                ? 'Unsatisfactory'
                                : 'Satisfactory';

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
                        @endphp
                    @endif
                @else
                    @php
                        if ($isEnrolled && ($hasMid || $hasEnd)) {
                            /*
                            |--------------------------------------------------------------------------
                            | INTERNAL MARKS
                            |--------------------------------------------------------------------------
                            */

                            $internal1Original =
                                $midSemMarks[$student->id][$sub->id]['Internal 1'][0]->obtained_marks ?? 0;

                            $internal2Original =
                                $midSemMarks[$student->id][$sub->id]['Internal 2'][0]->obtained_marks ?? 0;

                            $internal3Original =
                                $midSemMarks[$student->id][$sub->id]['Internal 3'][0]->obtained_marks ?? 0;

                            $internal1Grace =
                                $midSemMarks[$student->id][$sub->id]['Internal 1'][0]->grace_marks ?? 0;

                            $internal2Grace =
                                $midSemMarks[$student->id][$sub->id]['Internal 2'][0]->grace_marks ?? 0;

                            $internal3Grace =
                                $midSemMarks[$student->id][$sub->id]['Internal 3'][0]->grace_marks ?? 0;

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

                            $internal =
                                $bestInternal1['final'] +
                                $bestInternal2['final'];

                            /*
                            |--------------------------------------------------------------------------
                            | THEORY ORIGINAL
                            |--------------------------------------------------------------------------
                            */

                            $t1Data =
                                $endSemMarks[$student->id][$sub->id]['theory_paper1'][0] ?? null;

                            $t2Data =
                                $endSemMarks[$student->id][$sub->id]['theory_paper2'][0] ?? null;

                            $t1Original =
                                $t1Data->obtained_marks ?? 0;

                            $t2Original =
                                $t2Data->obtained_marks ?? 0;

                            /*
                            |--------------------------------------------------------------------------
                            | RETOTALLING MARKS
                            |--------------------------------------------------------------------------
                            */

                            $retotallingApplication = $retotallingStudents->first(function ($application) use ($student, $sub) {
                                return (int) $application->student_id === (int) $student->id
                                    && $application->subjects->contains('subjects_id', $sub->id);
                            });

                            /*
                            |--------------------------------------------------------------------------
                            | APPLY RETOTALLING THEORY PAPER 1
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $retotallingApplication &&
                                $t1Data &&
                                $t1Data->retotalling_obtained_marks !== null &&
                                (float) $t1Data->retotalling_obtained_marks != 0 &&
                                (int) $t1Data->retotalling_marks_locked === 1
                            ) {
                                $t1Original = $t1Data->retotalling_obtained_marks;
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | APPLY RETOTALLING THEORY PAPER 2
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $retotallingApplication &&
                                $t2Data &&
                                $t2Data->retotalling_obtained_marks !== null &&
                                (float) $t2Data->retotalling_obtained_marks != 0 &&
                                (int) $t2Data->retotalling_marks_locked === 1
                            ) {
                                $t2Original = $t2Data->retotalling_obtained_marks;
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | PRACTICAL ORIGINAL
                            |--------------------------------------------------------------------------
                            */

                            $p1Original =
                                $endSemMarks[$student->id][$sub->id]['practical_paper1'][0]->obtained_marks ?? 0;

                            $p2Original =
                                $endSemMarks[$student->id][$sub->id]['practical_paper2'][0]->obtained_marks ?? 0;

                            /*
                            |--------------------------------------------------------------------------
                            | MODERATION / ADJUSTMENT
                            |--------------------------------------------------------------------------
                            */

                            $theoryAdjustment1 =
                                $academicAdjustments[$student->id][$sub->id]['theory_paper1'][0]->adjustment_marks ?? 0;

                            $theoryAdjustment2 =
                                $academicAdjustments[$student->id][$sub->id]['theory_paper2'][0]->adjustment_marks ?? 0;

                            $practicalAdjustment1 =
                                $academicAdjustments[$student->id][$sub->id]['practical_paper1'][0]->adjustment_marks ?? 0;

                            $practicalAdjustment2 =
                                $academicAdjustments[$student->id][$sub->id]['practical_paper2'][0]->adjustment_marks ?? 0;

                            /*
                            |--------------------------------------------------------------------------
                            | FINAL THEORY
                            |--------------------------------------------------------------------------
                            */

                            $t1 = $t1Original + $theoryAdjustment1;
                            $t2 = $t2Original + $theoryAdjustment2;

                            /*
                            |--------------------------------------------------------------------------
                            | FINAL PRACTICAL
                            |--------------------------------------------------------------------------
                            */

                            $p1 = $p1Original + $practicalAdjustment1;
                            $p2 = $p2Original + $practicalAdjustment2;

                            /*
                            |--------------------------------------------------------------------------
                            | TOTAL
                            |--------------------------------------------------------------------------
                            */

                            $total =
                                $internal +
                                $t1 +
                                $t2 +
                                $p1 +
                                $p2;

                            /*
                            |--------------------------------------------------------------------------
                            | MAXIMUM MARKS
                            |--------------------------------------------------------------------------
                            */

                            $internalMaxArr = [
                                $sub->internal1 ?? 0,
                                $sub->internal2 ?? 0,
                                $sub->internal3 ?? 0,
                            ];

                            rsort($internalMaxArr);

                            /*
                            |--------------------------------------------------------------------------
                            | GROSS THEORY MAXIMUM
                            |--------------------------------------------------------------------------
                            */

                            $grossTheoryMax =
                                ($internalMaxArr[0] ?? 0) +
                                ($internalMaxArr[1] ?? 0) +
                                ($sub->paper1 ?? 0) +
                                ($sub->paper2 ?? 0);

                            /*
                            |--------------------------------------------------------------------------
                            | PRACTICAL MAXIMUM
                            |--------------------------------------------------------------------------
                            */

                            $practicalTotalMax =
                                ($sub->practical_paper1 ?? 0) +
                                ($sub->practical_paper2 ?? 0);

                            /*
                            |--------------------------------------------------------------------------
                            | REQUIRED PASS MARKS
                            |--------------------------------------------------------------------------
                            */

                            $requiredGrossTheory =
                                ($grossTheoryMax * $overallPassPercent) / 100;

                            $requiredPractical =
                                ($practicalTotalMax * $overallPassPercent) / 100;

                            /*
                            |--------------------------------------------------------------------------
                            | ACTUAL GROSS THEORY
                            |--------------------------------------------------------------------------
                            */

                            $grossTheory =
                                $internal +
                                $t1 +
                                $t2;

                            /*
                            |--------------------------------------------------------------------------
                            | ACTUAL PRACTICAL
                            |--------------------------------------------------------------------------
                            */

                            $practicalTotal =
                                $p1 +
                                $p2;

                            /*
                            |--------------------------------------------------------------------------
                            | SEPARATE PASS CONDITIONS
                            |--------------------------------------------------------------------------
                            |
                            | Gross Theory must meet overall pass percentage.
                            | Practical A+B must meet overall pass percentage.
                            | Both conditions are mandatory.
                            |
                            */

                            $grossTheoryPass =
                                $grossTheory >= $requiredGrossTheory;

                            $practicalPass =
                                $practicalTotal >= $requiredPractical;

                            $subjectPass =
                                $grossTheoryPass && $practicalPass;

                            /*
                            |--------------------------------------------------------------------------
                            | RESULT / GRADE POINT
                            |--------------------------------------------------------------------------
                            */

                            if ($subjectPass) {
                                $gradePoint = round($total / 10, 3);
                                $value = number_format($gradePoint, 3);
                                $class = 'pass';
                            } else {
                                $value = 'Fail';
                                $class = 'fail';
                                $failCount++;
                                $remarks[] = "Fail {$sub->subject_code}";
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | RESULT STATUS
                            |--------------------------------------------------------------------------
                            */

                            // $status = 'FAIL';

                            // if ($subjectPass) {
                            //    $status = 'PASS';
                            // } else {
                            //    $status = ($failCount <= 2)
                            //        ? 'SUPPLE'
                            //        : 'FAIL';
                            // }

                            $status = $subjectPass ? 'PASS' : 'FAIL';

                            /*
                            |--------------------------------------------------------------------------
                            | SAVE RESULT DATA
                            |--------------------------------------------------------------------------
                            */

                            $gpSheetData[] = [
                                'student_id' => $student->id,
                                'subject_id' => $sub->id,
                                'result' => $status,
                                'result_gp' => round($total / 10, 3),
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
                            |--------------------------------------------------------------------------
                            | CREDIT
                            |--------------------------------------------------------------------------
                            */

                            $credit = $sub->credits ?? 0;
                            $totalCredit += $credit;

                            /*
                            |--------------------------------------------------------------------------
                            | CREDIT POINT ONLY IF PASS
                            |--------------------------------------------------------------------------
                            */

                            if ($subjectPass) {
                                $gradePoint = round($total / 10, 3);
                                $creditPoint = round(($gradePoint * $credit), 2);
                                $totalGP += $creditPoint;
                            }
                        }
                    @endphp
                @endif

                @php
                    $subjectResults[$sub->id] = [
                        'value' => $value,
                        'class' => $class,
                    ];
                @endphp
            @endforeach

            @php

                /*
                ==========================================
                FINAL SUPPLE / FAIL STATUS
                ==========================================
                */

                if ($failCount > 0) {

                    foreach ($gpSheetData as $index => $gpRow) {

                        // Sirf current student ke records
                        if ($gpRow['student_id'] != $student->id) {
                            continue;
                        }

                        // Sirf failed subjects
                        if ($gpRow['result'] !== 'FAIL') {
                            continue;
                        }

                        // 1-2 failed subjects = SUPPLE
                        // 3+ failed subjects = FAIL
                        $gpSheetData[$index]['result'] =
                            ($failCount <= 2)
                                ? 'SUPPLE'
                                : 'FAIL';
                    }
                }

            @endphp

            <tr>
                <td>{{ $key + 1 }}</td>

                <td class="left">
                    {{ $student->student_name }}
                </td>

                <td>
                    {{ $student->university_roll_no }}
                </td>

                @foreach ($subjects->sortBy(function ($sub) {
                    return $sub->is_nc == 1 ? 1 : 0;
                }) as $sub)
                    @php
                        $res = $subjectResults[$sub->id];
                        $value = $res['value'];
                        $class = $res['class'];

                        if ($class == 'fail' && $failCount <= 2) {
                            $value = 'Supple';
                            $class = 'fail';
                        }
                    @endphp

                    <td class="{{ $class }}">
                        @if (!$sub->is_nc)
                            <a
                                href="javascript:void(0);"
                                class="result-detail-btn"
                                data-student_id="{{ $student->id }}"
                                data-subject_id="{{ $sub->id }}"
                                style="text-decoration: none; color: inherit; font-weight: bold;"
                            >
                                {{ $value }}
                            </a>
                        @else
                            <strong>{{ $value }}</strong>
                        @endif
                    </td>
                @endforeach

                @php
                    $gpa = $totalCredit > 0
                        ? number_format($totalGP / $totalCredit, 3)
                        : 0;

                    $cgpa = $gpa;
                    $grade = '';

                    foreach ($grades as $g) {
                        if (
                            $cgpa >= $g->percent_from &&
                            $cgpa <= $g->percent_upto
                        ) {
                            $grade = $g->grade_description;
                            break;
                        }
                    }

                    $studentResultStatus = 'PASS';

                    if ($failCount > 2) {
                        $studentResultStatus = 'FAIL';
                    } elseif ($failCount > 0) {
                        $studentResultStatus = 'SUPPLE';
                    }

                    $summaryData[] = [
                        'student_id' => $student->id,
                        'gpa' => $gpa,
                        'cgpa' => $cgpa,
                        'result_status' => $studentResultStatus,
                        'grade_status' => $grade,
                        'remarks' => count($remarks)
                            ? (
                                $failCount <= 2
                                    ? str_replace(
                                        'Fail',
                                        'Supple',
                                        implode(', ', $remarks)
                                    )
                                    : implode(', ', $remarks)
                            )
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
                        'result_declare' => 1,
                    ];
                @endphp

                <td class="remarks">
                    @if (count($remarks))
                        @if ($failCount <= 2)
                            {{ str_replace('Fail', 'Supple', implode(', ', $remarks)) }}
                        @else
                            {{ implode(', ', $remarks) }}
                        @endif
                    @else
                        <span class="pass">Pass</span>
                    @endif
                </td>

                <td>{{ $gpa }}</td>
                <td>{{ $cgpa }}</td>
                <td>{{ $grade }}</td>
            </tr>
        @endforeach
    </tbody>

    <tfoot>
        <tr>
            <td colspan="{{ 3 + count($subjects) + 4 }}">
                <table width="100%" style="border: none;">
                    <tr>
                        <td
                            width="33.33%"
                            style="border: none; text-align: left; font-size: 12px;"
                        >
                            Prepared By
                            <br><br><br>
                            Checked By
                        </td>

                        <td
                            width="33.33%"
                            style="border: none; text-align: center; font-size: 12px;"
                        >
                            Controller of Examinations
                        </td>

                        <td
                            width="33.33%"
                            style="border: none; text-align: right; font-size: 12px;"
                        >
                            Registrar,<br>
                            Guru Angad Dev Veterinary and Animal Science University
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </tfoot>
</table>

<input
    type="hidden"
    id="summaryData"
    value='@json($summaryData)'
>

<input
    type="hidden"
    id="gpSheetData"
    value='@json($gpSheetData)'
>

{{-- <h3>StudentResultSummary Preview</h3>

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
</table> --}}

{{-- <hr>
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
</table> --}}

<div style="text-align: center; margin-top: 20px;">
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
                method: "POST",
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

