<style>
    table.result-table {
        width: 100%;
        border-collapse: collapse;
    }

    table.result-table th,
    table.result-table td {
        border: 1px solid #ddd;
        padding: 6px;
        text-align: center;
        font-size: 10px;
    }

    .marks-input {
        width: 60px;
        text-align: center;
    }
    .small-checkbox{
        transform: scale(0.7);
    }
    .lock-label{
        font-size:9px;
        display:inline-flex;
        align-items:center;
        gap:3px;
    }
    .marks-input::-webkit-outer-spin-button,
    .marks-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Firefox */
    .marks-input[type=number] {
        -moz-appearance: textfield;
    }
</style>

<div class="table-responsive">

{{-- @php
$filteredSubjects = $subjects->filter(function($sub) use ($course) {
    return !($course->course == 'UG' && $sub->type == 'Practical');
});
@endphp --}}

@php
$filteredSubjects = $subjects->filter(function ($sub) use ($course) {
    return !(
        $course->course == 'UG' &&
        $sub->type == 'Practical' &&
        $sub->is_nc == 0
    );
});
@endphp


@php

$tableColumns = collect();

foreach ($filteredSubjects as $subject) {

    // NC Subject
    if ($subject->is_nc) {

        $tableColumns->push([
            'subject' => $subject,
            'paper'   => 'nc'
        ]);

        continue;
    }

    // Diploma Theory => 2 Columns
    if (
        $course->course == 'Diploma' &&
        $subject->type == 'Theory'
    ) {

        $tableColumns->push([
            'subject' => $subject,
            'paper'   => 'external'
        ]);

        $tableColumns->push([
            'subject' => $subject,
            'paper'   => 'practical'
        ]);

    } else {

        $tableColumns->push([
            'subject' => $subject,
            'paper'   => strtolower($subject->type)
        ]);

    }
}

@endphp

<table class="result-table">

    <thead>

        <tr>
            <td colspan="{{ 3 + $tableColumns->count() }}">

                <center>
                    <h4>OFFICES OF CONTROLLER OF EXAMINATIONS</h4>
                    <h4>GURU ANGAD DEV VETERINARY & ANIMAL SCIENCES</h4>

                    <p style="font-size:12px;font-weight:bold;">
                        {{ $exam->exam_resultsheet_name }}
                    </p>
                </center>

                <p style="text-align:left;margin:0;">
                    <b>
                        Class :
                        {{ $className }} {{ $course->name }}

                        @if(isset($branch) && $branch->name != 'NA')
                            / {{ $branch->name }}
                        @endif
                    </b>
                </p>

            </td>
        </tr>

        <tr>

            <th rowspan="3">SL</th>
            <th rowspan="3">Student Name</th>
            <th rowspan="3">Admission No</th>

            @foreach($tableColumns as $column)
                @php
                    $subject = $column['subject'];
                    $paper   = $column['paper'];
                @endphp
                <th>

                    @if($subject->is_nc)
                        Non Credit
                    @elseif($paper == 'external')

                        Theory

                    @elseif($paper == 'practical')

                        Practical

                    @endif

                </th>
            @endforeach

        </tr>

        <tr>
            @foreach($tableColumns as $column)
                @php
                    $subject = $column['subject'];
                    $paper   = $column['paper'];
                @endphp
                <th>

                    @if($paper == 'external')
                        Sub : {{ $subject->subject_code }} (T)
                    @elseif($paper == 'practical')
                        Sub : {{ $subject->subject_code }} (P)
                    @else
                        Sub : {{ $subject->subject_code }}
                    @endif

                </th>
            @endforeach
        </tr>

        <tr>
            @foreach($tableColumns as $column)
                @php
                    $subject = $column['subject'];
                    $paper   = $column['paper'];
                @endphp
                <th>

                    @if($subject->is_nc)

                        S/U
                        
                        <br>

                        <label class="no-export lock-label text-danger">
                            <input
                                type="checkbox"
                                class="nc-lock-all small-checkbox"
                                data-subject="{{ $subject->id }}"
                            >
                            Lock All
                        </label>

                    @elseif($paper == 'external')

                        Max : {{ $subject->external_marks }}

                    @else

                        Max : {{ $subject->mid_sem_marks3 }}

                    @endif

                </th>
            @endforeach
        </tr>

    </thead>

    <tbody>

        @foreach($students as $studentIndex => $student)

            <tr>

                <td>
                    <input type="checkbox"
                    class="row-check no-export"
                    data-student="{{ $student->id }}">

                    {{ $studentIndex + 1 }}
                </td>

                <td>{{ $student->student_name }}</td>

                <td>{{ $student->university_roll_no }}</td>

                @foreach($tableColumns as $column)

                    @php
                        $subject = $column['subject'];
                        $paper   = $column['paper'];
                    @endphp

                    @php
                        // $markData = $marks[$student->id][$subject->id] ?? null;

                        if (
                            $courseType == 'Semester Based' &&
                            $course->course != 'Diploma'
                        ) {

                            $markData = $marks[$student->id][$subject->id] ?? null;

                        } else {

                            $currentMarksType = $paper;

                            if ($paper == 'theory') {
                                $currentMarksType = 'external';
                            }

                            $markData =
                                $marks[$student->id][$subject->id][$currentMarksType]
                                ?? null;
                        }

                        $obtained = $markData['obtained_marks'] ?? '';
                        $status   = $markData['exam_status'] ?? 'present';
                        $isLocked = $markData['is_locked'] ?? 0;
                        $grade    = $markData['grade'] ?? '';

                        // ✅ ADDED: Enrollment check
                        $isEnrolled = isset($studentSubjects[$student->id]) &&
                                    in_array($subject->id, $studentSubjects[$student->id]);

                        $maxMarks = 0;

                        if ($subject->is_nc) {

                            $maxMarks = 0;

                        } elseif ($paper == 'external') {

                            $maxMarks = $subject->external_marks ?? 0;

                        } else {

                            $maxMarks = $subject->mid_sem_marks3 ?? 0;

                        }
                    @endphp

                    <td>

                        @if($subject->is_nc)

                            {{-- NON CREDIT SUBJECT --}}

                            @if(!$isEnrolled)

                                <span class="text-muted">NA</span>

                            @elseif($isLocked == 1)

                                {{ $grade ?: '-' }}

                            @else

                                <label style="font-size:9px;display:block;text-align:left;">

                                    <input
                                        type="radio"
                                        class="grade-radio"
                                        name="grade_{{ $student->id }}_{{ $subject->id }}"
                                        data-student="{{ $student->id }}"
                                        data-subject="{{ $subject->id }}"
                                        value="Satisfactory"
                                        {{ $grade == 'Satisfactory' ? 'checked' : '' }}
                                    >

                                    Satisfactory

                                </label>

                                <label style="font-size:9px;display:block;text-align:left;">

                                    <input
                                        type="radio"
                                        class="grade-radio"
                                        name="grade_{{ $student->id }}_{{ $subject->id }}"
                                        data-student="{{ $student->id }}"
                                        data-subject="{{ $subject->id }}"
                                        value="Unsatisfactory"
                                        {{ $grade == 'Unsatisfactory' ? 'checked' : '' }}
                                    >

                                    Unsatisfactory

                                </label>
                                
                                <label style="font-size:9px;display:block;text-align:left;">

                                    <input
                                        type="radio"
                                        class="grade-radio"
                                        name="grade_{{ $student->id }}_{{ $subject->id }}"
                                        data-student="{{ $student->id }}"
                                        data-subject="{{ $subject->id }}"
                                        value="Exempted"
                                        {{ $grade == 'Exempted' ? 'checked' : '' }}
                                    >

                                    Exempted

                                </label>

                            @endif

                        @else

                            {{-- NORMAL SUBJECT --}}

                            @if(!$isEnrolled)

                                <span class="text-muted">NA</span>

                            @elseif($isLocked == 1)

                                @if($status == 'absent')
                                    <span class="text-danger">AB</span>
                                @else
                                    {{ $obtained }}
                                @endif

                            @else

                                <input
                                    type="number"
                                    class="marks-input save-marks no-export"
                                    data-student="{{ $student->id }}"
                                    data-subject="{{ $subject->id }}"
                                    {{-- data-max="{{ $subject->external_marks }}" --}}
                                    data-max="{{ $maxMarks }}"
                                    value="{{ $status == 'absent' ? 0 : $obtained }}"
                                    {{-- max="{{ $subject->external_marks }}" --}}
                                    max="{{ $maxMarks }}"
                                >

                                <br>
 
                                <label style="font-size:9px" class="no-export lock-label">

                                    <input
                                        type="checkbox"
                                        class="mark-absent small-checkbox"
                                        data-student="{{ $student->id }}"
                                        data-subject="{{ $subject->id }}"
                                        value="1"
                                        @if($status == 'absent') checked @endif
                                    >

                                    Absent

                                </label>

                            @endif

                        @endif

                        @if($isEnrolled)

                            <br>

                            <label class="no-export lock-label">

                                <input
                                    type="checkbox"
                                    class="lock-check small-checkbox"
                                    data-student="{{ $student->id }}"
                                    data-subject="{{ $subject->id }}"
                                >

                                @if($isLocked == 1)
                                    Unlock
                                @else
                                    Lock
                                @endif

                            </label>

                        @endif

                    </td>

                @endforeach

            </tr>

        @endforeach

    </tbody>

</table>

</div>

<script>
$(document).on('change', '.nc-lock-all', function () {

    let subjectId = $(this).data('subject');
    let isChecked = $(this).is(':checked');

    // Sirf isi NC subject ke saare student lock checkbox
    $('.lock-check[data-subject="' + subjectId + '"]')
        .prop('checked', isChecked);

});
</script>