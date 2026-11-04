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
    width: 70px;
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

/* Firefox */
.adjust-inc-dec-btn[type=number] {
    -moz-appearance: textfield;
}

</style>

<div class="table-responsive">

    {{-- @php
        dump($rawMarks);
    @endphp --}}
    
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

        <tr>
            <td colspan="7" class="result-header">

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
                        @if($branch)
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
                    Maximum Marks :
                    {{ $maxMarks }}
                </p>

            </td>
        </tr>

        <tr>
            <th width="40" class="no-export">
                <input type="checkbox" id="check-all">
            </th>
            <th>Admission Number / Roll No</th>
            <th>Student Name</th>
            <th>Max Marks</th>
            <th>Obtained Marks</th>
            <th>Grace Marks</th>
            <th>Moderation Marks</th>
        </tr>

    </thead>

    <tbody>

        @forelse($students as $student)
        

            @php

                $mark = $rawMarks[$student->id] ?? null;

                $obtainedMarks = $mark->obtained_marks ?? '';
                $graceMarks    = $mark->grace_marks ?? '';
                $examStatus    = $mark->exam_status ?? 'present';
                $isLocked      = $mark->is_locked ?? 0;

            @endphp

            <tr>

                <td class="no-export">
                    {{-- @if(!$isLocked) --}}
                        <input
                            type="checkbox"
                            class="lock-check"
                            data-student="{{ $student->id }}"
                            data-subject="{{ $subject->id }}"
                        >
                    {{-- @endif --}}

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

                <td>

                    {{-- LOCKED --}}
                    @if($isLocked)

                        @if($examStatus == 'absent')

                            <span class="absent-label">AB</span>

                        @else

                            {{ $obtainedMarks }}

                        @endif

                    @else

                        {{-- UNLOCKED --}}

                        @if($examStatus == 'absent')

                            <input
                                type="number"
                                class="form-control form-control-sm adjust-inc-dec-btn marks-input save-marks no-export" 
                                value="0"
                                min="0"

                                data-student="{{ $student->id }}"
                                data-subject="{{ $subject->id }}"
                                data-max="{{ $maxMarks }}"

                                @if($courseType == 'Annual Based')
                                    data-paper="{{ $marksType }}"
                                @endif
                            >

                            <div class="mt-1">

                                <label class="no-export">

                                    <input
                                        type="checkbox"
                                        checked
                                        class="mark-absent"

                                        data-student="{{ $student->id }}"
                                        data-subject="{{ $subject->id }}"
                                    >

                                    Absent

                                </label>

                            </div>

                        @else

                            <input
                                type="number"
                                class="form-control form-control-sm adjust-inc-dec-btn marks-input save-marks no-export"
                                value="{{ $obtainedMarks }}"
                                min="0"

                                data-student="{{ $student->id }}"
                                data-subject="{{ $subject->id }}"
                                data-max="{{ $maxMarks }}"

                                @if($courseType == 'Annual Based')
                                    data-paper="{{ $marksType }}"
                                @endif
                            >

                            <div class="mt-1">

                                <label class="no-export">

                                    <input
                                        type="checkbox"
                                        class="mark-absent"

                                        data-student="{{ $student->id }}"
                                        data-subject="{{ $subject->id }}"
                                    >

                                    Absent

                                </label>

                            </div>

                        @endif

                    @endif

                </td>
                
                {{-- GRACE MARKS COLUMN --}}
                {{-- <td>{{ $graceMarks }}</td> --}}

                {{-- ===================================== --}}
                {{-- GRACE MARKS COLUMN --}}
                {{-- ===================================== --}}
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

                <td>

                    @php

                        /*
                        =====================================
                        FETCH ADJUSTMENT
                        =====================================
                        */

                        $adjustment = $academicAdjustments[$student->id] ?? null;

                        /*
                        =====================================
                        DEFAULT VALUES
                        =====================================
                        */

                        $adjustmentMarks = $adjustment->adjustment_marks ?? 0;

                        $adjustmentType  = $adjustment->adjustment_type ?? '';

                    @endphp

                    {{-- LOCKED --}}
                    @if($isLocked)

                        <div>

                            <b>
                                {{ $adjustmentMarks }}
                            </b>

                            <br>

                            <small class="no-export">
                                {{ ucfirst($adjustmentType ?: '-') }}
                            </small>

                        </div>

                    @else

                        {{-- UNLOCKED --}}
                        <div class="d-flex gap-1 no-export">

                            {{-- GRACE INPUT --}}
                            <input
                                type="number"
                                class="form-control form-control-sm adjust-inc-dec-btn grace-marks-input"
                                min="0"
                                value="{{ $adjustmentMarks }}"

                                data-student="{{ $student->id }}"
                                data-subject="{{ $subject->id }}"
                                data-exam="{{ $exam->id }}"
                            >

                            <input 
                                type="hidden" 
                                name="adjustment_type"
                                class="form-control form-control-sm adjustment-type"
                                value="{{ $adjustmentType ?: 'moderation' }}"
                                data-student="{{ $student->id }}"
                                data-subject="{{ $subject->id }}"
                            >

                            {{-- ADJUSTMENT TYPE --}}
                            {{-- <select
                                class="form-control form-control-sm adjustment-type"

                                data-student="{{ $student->id }}"
                                data-subject="{{ $subject->id }}"
                            >

                                <option value="">Select Type</option>

                                <option
                                    value="grace"
                                    @selected($adjustmentType == 'grace')
                                >
                                    Grace
                                </option>

                                <option
                                    value="moderation"
                                    @selected($adjustmentType == 'moderation')
                                >
                                    Moderation
                                </option>

                                <option
                                    value="sports"
                                    @selected($adjustmentType == 'sports')
                                >
                                    Sports
                                </option>

                                <option
                                    value="medical"
                                    @selected($adjustmentType == 'medical')
                                >
                                    Medical
                                </option>

                            </select> --}} 

                            {{-- SAVE BUTTON --}}
                            <button
                                type="button"
                                class="btn btn-sm btn-primary save-adjustment"

                                data-student="{{ $student->id }}"
                                data-subject="{{ $subject->id }}"
                                data-exam="{{ $exam->id }}"
                                data-adjustment-scope="external"
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
                <td colspan="6">
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
    TAB NAVIGATION FOR OBTAINED / GRACE / MODERATION INPUTS
    =========================================================
    */

    /*
    =========================================================
    INPUT GROUPS
    =========================================================
    */

    const obtainedSelector   = '.save-marks';
    const graceSelector      = '.grace-mark-input';
    const moderationSelector = '.grace-marks-input';

    /*
    =========================================================
    HANDLE TAB NAVIGATION
    =========================================================
    */

    function handleVerticalTab(selector) {

        $(document).on('keydown', selector, function (e) {

            /*
            ============================================
            ONLY TAB KEY
            ============================================
            */

            if (e.key !== 'Tab') {
                return;
            }

            /*
            ============================================
            PREVENT DEFAULT TAB
            ============================================
            */

            e.preventDefault();

            /*
            ============================================
            GET CURRENT INPUT
            ============================================
            */

            let current = $(this);

            /*
            ============================================
            GET ALL VISIBLE ENABLED INPUTS
            ============================================
            */

            let inputs = $(selector)
                .filter(':visible:not(:disabled)');

            /*
            ============================================
            CURRENT INDEX
            ============================================
            */

            let currentIndex = inputs.index(current);

            /*
            ============================================
            SHIFT + TAB => PREVIOUS
            ============================================
            */

            if (e.shiftKey) {

                for (let i = currentIndex - 1; i >= 0; i--) {

                    let target = $(inputs[i]);

                    /*
                    ====================================
                    SKIP EMPTY / MISSING INPUTS
                    ====================================
                    */

                    if (target.length) {

                        target.focus();
                        target.select();

                        break;
                    }
                }

            } else {

                /*
                ========================================
                NORMAL TAB => NEXT
                ========================================
                */

                for (let i = currentIndex + 1; i < inputs.length; i++) {

                    let target = $(inputs[i]);

                    /*
                    ====================================
                    SKIP EMPTY / MISSING INPUTS
                    ====================================
                    */

                    if (target.length) {

                        target.focus();
                        target.select();

                        break;
                    }
                }
            }

        });

    }

    /*
    =========================================================
    APPLY FOR ALL 3 INPUT TYPES
    =========================================================
    */

    handleVerticalTab(obtainedSelector);
    handleVerticalTab(graceSelector);
    handleVerticalTab(moderationSelector);

});

</script>