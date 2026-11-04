
<style>
    .result-table-a4 {
        width: 100%;
        border-collapse: collapse;
        font-family: Arial, sans-serif;
    }

    .result-table-a4 th,
    .result-table-a4 td {
        border: 1px solid #ddd;
        padding: 4px;
        font-size: 12px;
        text-align: center;
        vertical-align: middle;
    }

    .result-table-a4 thead th {
        background: #f5f5f5;
        font-weight: 600;
    }

    .result-header h4 {
        margin: 2px 0;
    }

    .result-header p {
        margin: 4px 0;
        text-align: left;
    }

    .marks-input {
        text-align: center;
    }

    .absent-label {
        color: red;
        font-weight: 600;
    }

    .retotalling-marks-input::-webkit-outer-spin-button,
    .retotalling-marks-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .retotalling-marks-input[type=number] {
        -moz-appearance: textfield;
    }
</style>

@php
    /*
    |--------------------------------------------------------------------------
    | Maximum Marks According To Marks Type
    |--------------------------------------------------------------------------
    */

    $maxMarks = 0;

    if (($marksType ?? '') == 'external') {

        $maxMarks = $subject->external_marks ?? 0;

    } elseif (($marksType ?? '') == 'theory_paper1') {

        $maxMarks = $subject->paper1 ?? 0;

    } elseif (($marksType ?? '') == 'theory_paper2') {

        $maxMarks = $subject->paper2 ?? 0;

    }
@endphp

<input type="hidden" id="marksType" value="{{ $marksType }}">

<div class="table-responsive">

    <table class="result-table-a4">

        <thead>

            <tr>
                <td colspan="6" class="result-header">

                    <center>
                        <h4>CONTROLLER OF EXAMINATIONS</h4>

                        <h4>
                            GURU ANGAD DEV VETERINARY AND ANIMAL SCIENCE UNIVERSITY,
                            LUDHIANA (INDIA)
                        </h4>

                        <h4>{{ $className }}</h4>

                        <h4>{{ $exam->name }}</h4>
                    </center>

                    <p>
                        <b>
                            {{ $course->name }}
                            @if($branch && !empty($branch->name) && strtoupper(trim($branch->name)) !== 'NA')
                                - {{ $branch->name }}
                            @endif
                        </b>
                    </p>

                    <p>
                        Subject :
                        {{ $subject->name }}
                        ({{ $subject->subject_code }})
                    </p>

                    <p>
                        Maximum Marks : {{ $maxMarks }}
                    </p>

                </td>
            </tr>

            <tr>
                <th class="no-export">
                    <input type="checkbox" id="check-all">
                </th>

                <th>Admission Number / Roll No</th>
                <th>Student Name</th>
                <th>Max Marks</th>
                <th>Obtained Marks</th>
                <th>Retotalling Obtained Marks</th>
            </tr>

        </thead>

        <tbody>

            @forelse($students as $student)

                @php
                    $mark = $rawMarks[$student->id] ?? null;

                    $obtainedMarks = $mark->obtained_marks ?? '';

                    $examStatus = strtolower(
                        trim($mark->exam_status ?? 'present')
                    );

                    $retotallingMarks = $mark->retotalling_obtained_marks ?? '';

                    $retotallingLocked = $mark->retotalling_marks_locked ?? 0;
                @endphp

                <tr>

                    {{-- LOCK / UNLOCK CHECKBOX --}}
                    <td class="no-export">

                        <input
                            type="checkbox"
                            class="lock-check"
                            data-student="{{ $student->id }}"
                            data-subject="{{ $subject->id }}"
                            data-exam="{{ $exam->id }}"
                        >

                    </td>

                    {{-- ADMISSION / ROLL NUMBER --}}
                    <td>
                        {{ $student->university_roll_no }}
                    </td>

                    {{-- STUDENT NAME --}}
                    <td>
                        {{ $student->student_name }}
                    </td>

                    {{-- MAX MARKS --}}
                    <td>
                        {{ $maxMarks }}
                    </td>

                    {{-- ORIGINAL OBTAINED MARKS --}}
                    <td>

                        @if($mark)

                            @if($examStatus == 'absent')

                                <span class="absent-label">AB</span>

                            @else

                                {{ $obtainedMarks }}

                            @endif

                        @else

                            -

                        @endif

                    </td>

                    {{-- RETOTALLING OBTAINED MARKS --}}
                    <td>

                        @if($mark)

                            {{-- LOCKED --}}
                            @if($retotallingLocked == 1)

                                <b>
                                    {{ $retotallingMarks !== null && $retotallingMarks !== ''
                                        ? $retotallingMarks
                                        : '-' }}
                                </b>

                            @else

                                {{-- UNLOCKED --}}
                                <input
                                    type="number"
                                    class="form-control form-control-sm retotalling-marks-input no-export"
                                    value="{{ $retotallingMarks }}"
                                    min="0"
                                    max="{{ $maxMarks }}"
                                    step="0.01"

                                    data-student="{{ $student->id }}"
                                    data-subject="{{ $subject->id }}"
                                    data-exam="{{ $exam->id }}"
                                    data-max="{{ $maxMarks }}"
                                    data-marks-type="{{ $marksType }}"
                                >

                            @endif

                        @else

                            -

                        @endif

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="6">
                        No mapped students found.
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</div>

<script>
    $(document).ready(function () {

        /*
        |--------------------------------------------------------------------------
        | CHECK ALL
        |--------------------------------------------------------------------------
        */

        $(document).off('change', '#check-all');

        $(document).on('change', '#check-all', function () {

            let isChecked = $(this).is(':checked');

            $('.lock-check').prop('checked', isChecked);

        });

        /*
        |--------------------------------------------------------------------------
        | INDIVIDUAL CHECKBOX
        |--------------------------------------------------------------------------
        */

        $(document).off('change', '.lock-check');

        $(document).on('change', '.lock-check', function () {

            let totalCheckboxes = $('.lock-check').length;

            let checkedCheckboxes = $('.lock-check:checked').length;

            $('#check-all').prop(
                'checked',
                totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes
            );

        });

    });
</script>
