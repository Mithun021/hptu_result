<style>

.result-table-a4 {
    width: 100%;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
}

.result-table-a4 th,
.result-table-a4 td {
    border: 1px solid #ddd;
    padding: 2px;
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
    width: 100%;
    text-align: center;
}

.absent-label {
    color: red;
    font-weight: 600;
}

.adjust-inc-dec-btn::-webkit-outer-spin-button,
.adjust-inc-dec-btn::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.adjust-inc-dec-btn[type=number] {
    -moz-appearance: textfield;
}

.mark-status {
    margin-right: 2px;
}
td.align-left{
    text-align: left !important;
}
td.align-right{
    text-align: right !important;
}
</style>

<div class="table-responsive">

@php

    $maxMarks = 0;

    /*
    =====================================
    NON-DIPLOMA COURSES (UG etc.)
    =====================================
    */

    if ($course->course != 'Diploma') {

        $maxMarks = $subject->external_marks ?? 0;

    } else {

        /*
        =====================================
        DIPLOMA COURSES
        =====================================
        */

        // Practical Subject
        if (
            $subject->type == 'Practical' &&
            ($marksType ?? '') == 'practical'
        ) {

            $maxMarks = $subject->mid_sem_marks3 ?? 0;

        }

        // Theory Subject - Theory Paper
        elseif (
            $subject->type == 'Theory' &&
            ($marksType ?? '') == 'external'
        ) {

            $maxMarks = $subject->external_marks ?? 0;

        }

        // Theory Subject - Practical Paper
        elseif (
            $subject->type == 'Theory' &&
            ($marksType ?? '') == 'practical'
        ) {

            $maxMarks = $subject->mid_sem_marks3 ?? 0;

        }
    }

@endphp

<table class="result-table-a4">

    <thead>

        <tr><td colspan="6" class="result-header">HIMACHAL PRADESH TECHNICAL UNIVESITY, HAMIRPUR</td></tr>
        <tr><td colspan="6">{{ $exam->exam_resultsheet_name ?? $exam->name }}</td></tr>
        <tr><td colspan="6">{{ $course->name }} | {{ $scheme->name }} | @if(!empty($branch->name) && $branch->name != 'NA') {{ $branch->name." | " }} @endif{{ $className }} | {{ $batch->name }} | Regular</td></tr>
        <tr><td colspan="4" class="align-left">Subject : {{ $subject->name }}({{ $subject->subject_code }}) | {{ $subject->type }}</td><td colspan="4" class="align-right">Max Marks : {{ $subject->external_marks }}</td></tr>

        <tr>
            <th width="40" class="no-export">
                <input type="checkbox" id="check-all"> Sn no.
            </th>

            <th>Admission Number / Roll No</th>
            <th>Student Name</th>
            <th>Max Marks</th>
            <th>Obtained Marks</th>
            <th>Grace Marks</th>
        </tr>

    </thead>

    <tbody>

        @php
            $num = 0
        @endphp
        @forelse($students as $student)

            @php

                $mark = $rawMarks[$student->id] ?? null;

                $obtainedMarks = $mark->obtained_marks ?? '';
                $graceMarks    = $mark->grace_marks ?? '';
                $examStatus    = $mark->exam_status ?? 'present';
                $isLocked     = $mark->is_locked ?? 0;

                $isAbsent = $examStatus == 'absent';

            @endphp

            <tr>

                <td class="no-export">

                    <input
                        type="checkbox"
                        class="lock-check"
                        data-student="{{ $student->id }}"
                        data-subject="{{ $subject->id }}"
                    >
                    {{ ++$num }}
                </td>

                <td>
                    {{ $student->admission_no }}
                    <br>
                    {{ $student->university_roll_no }}
                </td>

                <td>
                    {{ $student->student_name }}
                </td>

                <td>
                    {{ $maxMarks }}
                </td>

                {{-- OBTAINED MARKS --}}

                <td>

                    {{-- LOCKED --}}

                    @if($isLocked)

                        @if($examStatus == 'absent')

                            <span class="absent-label">AB</span>

                        @elseif($examStatus == 'umc')

                            <span class="text-danger fw-bold">UMC</span>

                        @elseif($examStatus == 'I/F')

                            <span class="text-danger fw-bold">I/F (DT)</span>

                        @else

                            {{ $obtainedMarks }}

                        @endif

                    @else

                        {{-- UNLOCKED --}}

                        <input
                            type="number"
                            class="form-control form-control-sm adjust-inc-dec-btn marks-input save-marks no-export"
                            value="{{ $isAbsent ? 0 : $obtainedMarks }}"
                            min="0"
                            max="{{ $maxMarks }}"
                            {{ $isAbsent ? 'readonly' : '' }}

                            data-student="{{ $student->id }}"
                            data-subject="{{ $subject->id }}"
                            data-max="{{ $maxMarks }}"

                            @if($courseType == 'Annual Based')
                                data-paper="{{ $marksType }}"
                            @endif
                        >

                        <div class="mt-1 no-export">

                            {{-- OB --}}

                            <label class="small me-1">
                                <input
                                    type="radio"
                                    class="mark-status"
                                    name="status_{{ $student->id }}_{{ $subject->id }}"
                                    data-student="{{ $student->id }}"
                                    data-subject="{{ $subject->id }}"
                                    value="present"
                                    {{ (!$mark || !$examStatus || $examStatus == 'present') ? 'checked' : '' }}>
                                OB
                            </label>

                            {{-- AB --}}

                            <label class="small me-1">
                                <input
                                    type="radio"
                                    class="mark-status"
                                    name="status_{{ $student->id }}_{{ $subject->id }}"
                                    data-student="{{ $student->id }}"
                                    data-subject="{{ $subject->id }}"
                                    value="absent"
                                    {{ $isAbsent ? 'checked' : '' }}>
                                AB
                            </label>

                            {{-- UMC --}}

                            <label class="small me-1">
                                <input
                                    type="radio"
                                    class="mark-status"
                                    name="status_{{ $student->id }}_{{ $subject->id }}"
                                    data-student="{{ $student->id }}"
                                    data-subject="{{ $subject->id }}"
                                    value="umc"
                                    {{ $examStatus == 'umc' ? 'checked' : '' }}>
                                UMC
                            </label>

                            {{-- DT = I/F --}}

                            <label class="small">
                                <input
                                    type="radio"
                                    class="mark-status"
                                    name="status_{{ $student->id }}_{{ $subject->id }}"
                                    data-student="{{ $student->id }}"
                                    data-subject="{{ $subject->id }}"
                                    value="I/F"
                                    {{ $examStatus == 'I/F' ? 'checked' : '' }}>
                                DT
                            </label>

                        </div>

                    @endif

                </td>

                {{-- GRACE MARKS COLUMN --}}

                <td>

                    {{-- LOCKED --}}

                    @if($isLocked)

                        <b>{{ $graceMarks }}</b>

                    @else

                        {{-- UNLOCKED --}}

                        <div class="d-flex gap-1 no-export">

                            <input
                                type="number"
                                class="form-control form-control-sm adjust-inc-dec-btn grace-mark-input"
                                min="0"
                                value="{{ $graceMarks }}"

                                data-student="{{ $student->id }}"
                                data-subject="{{ $subject->id }}"
                                data-exam="{{ $exam->id }}"
                            >

                            <button
                                type="button"
                                class="btn btn-sm btn-success save-grace-marks"

                                data-student="{{ $student->id }}"
                                data-subject="{{ $subject->id }}"
                                data-exam="{{ $exam->id }}"
                                data-marks-type="{{ $marksType ?? 'external' }}"
                            >
                                Save
                            </button>

                        </div>

                    @endif

                </td>

            </tr>

        @empty

            <tr>
                <td colspan="6" class="text-center">
                    No records found
                </td>
            </tr>

        @endforelse

    </tbody>

</table>

</div>


<script>

$(document).ready(function () {

    /*
    =========================================================
    TAB NAVIGATION
    =========================================================
    */

    const obtainedSelector = '.save-marks';
    const graceSelector   = '.grace-mark-input';

    function handleVerticalTab(selector) {

        $(document).on('keydown', selector, function (e) {

            if (e.key !== 'Tab') {
                return;
            }

            e.preventDefault();

            let current = $(this);

            let inputs = $(selector)
                .filter(':visible:not(:disabled)');

            let currentIndex = inputs.index(current);

            if (e.shiftKey) {

                for (let i = currentIndex - 1; i >= 0; i--) {

                    let target = $(inputs[i]);

                    if (target.length) {

                        target.focus();
                        target.select();

                        break;
                    }
                }

            } else {

                for (let i = currentIndex + 1; i < inputs.length; i++) {

                    let target = $(inputs[i]);

                    if (target.length) {

                        target.focus();
                        target.select();

                        break;
                    }
                }
            }

        });

    }

    handleVerticalTab(obtainedSelector);
    handleVerticalTab(graceSelector);

});

</script>
