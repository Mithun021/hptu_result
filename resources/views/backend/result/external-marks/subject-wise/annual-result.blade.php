<div class="card border">
  <div class="card-header"><h5 class="card-title m-0">Apply Moderation</h5></div>
  <div class="card-body">

    <input type="hidden" name="exam_id" value="{{ $exam->id }}">

    <input type="hidden" name="course_type" value="{{ $courseType }}">

    <input type="hidden" name="course_id" value="{{ $course->id }}">

    <input type="hidden" name="scheme_id" value="{{ $schemeId }}">

    <input type="hidden" name="branch_id" value="{{ $branch->id }}">

    <input type="hidden" name="annual_semester_id" value="{{ $annualSemesterId }}">

    <input type="hidden" name="institute_id" value="{{ $instituteId }}">

    <input type="hidden" name="marks_type" value="{{ $marksType }}">

    <input type="hidden" name="batch_id" value="{{ $batchId }}">

    <input type="hidden" name="subject_id" value="{{ $subject->id }}">

    <input
        type="hidden"
        name="moderation_students"
        id="moderation_students"
        value="{{ json_encode($moderationStudents) }}"
    >

    <div class="row">
        <div class="form-group col-md-5">
            <label for="">Excluded Students(with seperate commas)</label>
            <input type="text" name="exculded_students" class="form-control" placeholder="ABC-2025-01-A,ABC-2025-02-A">
        </div>
        <div class="form-group col-md-3">
            <label for="">Moderational Marks</label>
            <input type="text" name="moderation_marks" class="form-control">
        </div>
        <div class="form-group col-md-2">

            <label for="moderation_action">
                Action
            </label>

            <select
                name="moderation_action"
                id="moderation_action"
                class="form-control"
            >

                <option value="apply">
                    Apply
                </option>

                <option value="revert">
                    Revert
                </option>

            </select>

        </div>
        <div class="form-group col-md-2">
            <button type="button" class="btn btn-primary my-3" id="apply-annual-moderation-btn">Apply</button>
        </div>
    </div>
  </div>
</div>

<style>

.result-table-a4 {
    width: 100%;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
}

.result-table-a4 th,
.result-table-a4 td {
    border: 1px solid #ddd;
    padding: 4px 8px; /* top-bottom kam */
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
    height: 30px;
    text-align: center;
    padding: 2px 6px;
    font-size: 12px;
}

.absent-label {
    color: red;
    font-weight: 600;
}
/* Chrome, Safari, Edge, Opera */
.marks-input::-webkit-outer-spin-button,
.marks-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Firefox */
.marks-input[type=number] {
    -moz-appearance: textfield;
}
.grace-marks-input,
.adjustment-type{
    font-size: 11px;
    height: 28px;
}

.save-adjustment{
    font-size: 11px;
    padding: 3px 6px;
}
</style>

@php

    $maxMarks = '-';

    if($marksType == 'theory_paper1'){

        $maxMarks = $subject->paper1;

    }elseif($marksType == 'theory_paper2'){

        $maxMarks = $subject->paper2;

    }elseif($marksType == 'practical_paper1'){

        $maxMarks = $subject->practical_paper1;

    }elseif($marksType == 'practical_paper2'){

        $maxMarks = $subject->practical_paper2;

    }

@endphp

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
            <th>Moderation Marks</th>
        </tr>

    </thead>

    <tbody>

        @forelse($students as $student)

            @php

                $mark = $rawMarks[$student->id] ?? null;

                $obtainedMarks = $mark->obtained_marks ?? '';
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
                                class="form-control form-control-sm marks-input save-marks no-export"
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
                                class="form-control form-control-sm marks-input save-marks no-export"
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
                                class="form-control form-control-sm grace-marks-input"
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