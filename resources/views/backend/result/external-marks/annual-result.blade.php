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

<table class="result-table">

    <thead>

        <tr>
            <td colspan="{{ 3 + ($subjects->count() * 4) }}">

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

            @foreach($subjects as $subject)

                @if($subject->is_nc)

                    <th colspan="4">
                        {{ $subject->subject_code }}
                        <br>
                        Non Credit Course
                    </th>

                @else

                    <th colspan="4" id="for-nc-hide">
                        Sub - {{ $subject->subject_code }}
                    </th>

                @endif

            @endforeach
        </tr>

        <tr>

            @foreach($subjects as $subject)

                @if($subject->is_nc)

                    <th colspan="4">S/U</th>

                @else

                    <th colspan="2" id="for-nc-hide">Theory</th>
                    <th colspan="2" id="for-nc-hide">Practical</th>

                @endif

            @endforeach

        </tr>

        <tr>

            @foreach($subjects as $subject)

                @if($subject->is_nc)

                    <th colspan="4">
                        Result
                        <br>

                        <label class="no-export lock-label text-danger">
                            <input
                                type="checkbox"
                                class="nc-lock-all small-checkbox"
                                data-subject="{{ $subject->id }}"
                            >
                            Lock All
                        </label>
                    </th>

                @else

                    <th id="for-nc-hide">
                        Paper 1
                        <br>
                        Max :
                        {{ $subject->paper1 ? $subject->paper1 : '-' }}
                    </th>

                    <th id="for-nc-hide">
                        Paper 2
                        <br>
                        Max :
                        {{ $subject->paper2 ? $subject->paper2 : '-' }}
                    </th>

                    <th id="for-nc-hide">
                        Paper 1
                        <br>
                        Max :
                        {{ $subject->practical_paper1 ? $subject->practical_paper1 : '-' }}
                    </th>

                    <th id="for-nc-hide">
                        Paper 2
                        <br>
                        Max :
                        {{ $subject->practical_paper2 ? $subject->practical_paper2 : '-' }}
                    </th>

                @endif

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

                @foreach($subjects as $subject)

                    @php
                        // $markData = $marks[$student->id][$subject->id] ?? null;

                        // $obtained = $markData['obtained_marks'] ?? '';
                        // $status   = $markData['exam_status'] ?? 'present';
                        // $isLocked = $markData['is_locked'] ?? 0;
                        // $grade    = $markData['grade'] ?? '';

                        $isEnrolled = isset($studentSubjects[$student->id]) &&
                                    in_array($subject->id, $studentSubjects[$student->id]);
                    @endphp

                    @if($subject->is_nc)

                        @php
                            $ncData   = $marks[$student->id][$subject->id]['external'] ?? null;
                            $grade    = trim($ncData['grade'] ?? '');
                            $isLocked = $ncData['is_locked'] ?? 0;
                        @endphp

                        <td colspan="4">

                            @if(!$isEnrolled)

                                <span class="text-muted">NA</span>

                            @else

                                {{-- ✅ SHOW VALUE / INPUT BASED ON LOCK --}}
                                @if($isLocked)

                                    {{-- LOCKED VIEW --}}
                                    <div>{{ $grade ?? '-' }}</div>

                                @else

                                    {{-- EDIT MODE --}}
                                    <label>
                                        <input type="radio"
                                            class="grade-radio"
                                            name="grade_{{ $student->id }}_{{ $subject->id }}"
                                            value="Satisfactory"
                                            {{ $grade === 'Satisfactory' ? 'checked' : '' }}
                                        >
                                        Satisfactory
                                    </label>

                                    <br>

                                    <label>
                                        <input type="radio"
                                            class="grade-radio"
                                            name="grade_{{ $student->id }}_{{ $subject->id }}"
                                            value="Unsatisfactory"
                                            {{ $grade === 'Unsatisfactory' ? 'checked' : '' }}
                                        >
                                        Unsatisfactory
                                    </label>
                                    
                                    
                                    <br>

                                    <label>
                                        <input type="radio"
                                            class="grade-radio"
                                            name="grade_{{ $student->id }}_{{ $subject->id }}"
                                            value="Exempted"
                                            {{ $grade === 'Exempted' ? 'checked' : '' }}
                                        >
                                        Exempted
                                    </label>
                                @endif

                                {{-- ✅ ALWAYS SHOW LOCK/UNLOCK --}} 
                                <br>

                                <label class="no-export lock-label">

                                    <input
                                        type="checkbox"
                                        class="lock-check small-checkbox"
                                        data-student="{{ $student->id }}"
                                        data-subject="{{ $subject->id }}"
                                        {{-- {{ $isLocked ? 'checked' : '' }}  --}}
                                    >

                                    @if($isLocked)
                                        Unlock
                                    @else
                                        Lock
                                    @endif

                                </label>

                            @endif

                        </td>

                    @else

                        {{-- THEORY PAPER 1 --}}
                        @php
                            $data      = $marks[$student->id][$subject->id]['theory_paper1'] ?? null;
                            $obtained  = $data['obtained_marks'] ?? '';
                            $status    = $data['exam_status'] ?? 'present';
                            $isLocked  = $data['is_locked'] ?? 0;
                        @endphp
                        <td id="for-nc-hide">

                            @if(!$subject->theory_credits)

                                -

                            @elseif(!$isEnrolled)

                                NA

                            @elseif($isLocked)

                                {{ $status == 'absent' ? 'AB' : $obtained }}

                            @else

                                <input
                                    type="number"
                                    class="marks-input save-marks no-export"
                                    data-student="{{ $student->id }}"
                                    data-subject="{{ $subject->id }}"
                                    data-max="{{ $subject->paper1 ?? 0 }}"
                                    data-paper="theory_paper1"
                                    value="{{ $status == 'absent' ? 0 : $obtained }}"
                                >

                                <br>

                                <label class="no-export lock-label">

                                    <input
                                        type="checkbox"
                                        class="mark-absent small-checkbox"
                                        data-student="{{ $student->id }}"
                                        data-subject="{{ $subject->id }}"
                                        data-paper="theory_paper1"
                                        value="1"
                                        @if($status == 'absent') checked @endif
                                    >

                                    Absent

                                </label>

                            @endif

                            @if($subject->theory_credits && $isEnrolled)

                                <br>

                                <label class="no-export lock-label">

                                    <input
                                        type="checkbox"
                                        class="lock-check small-checkbox"
                                        data-student="{{ $student->id }}"
                                        data-subject="{{ $subject->id }}"
                                        data-paper="theory_paper1"
                                    >

                                    @if($isLocked == 1)
                                        Unlock
                                    @else
                                        Lock
                                    @endif

                                </label>

                            @endif

                        </td>

                        {{-- THEORY PAPER 2 --}}
                        @php
                            $data      = $marks[$student->id][$subject->id]['theory_paper2'] ?? null;
                            $obtained  = $data['obtained_marks'] ?? '';
                            $status    = $data['exam_status'] ?? 'present';
                            $isLocked  = $data['is_locked'] ?? 0;
                        @endphp
                        <td id="for-nc-hide">

                            @if(!$subject->theory_credits)

                                -

                            @elseif(!$isEnrolled)

                                NA

                            @elseif($isLocked)

                                {{ $status == 'absent' ? 'AB' : $obtained }}

                            @else

                                <input
                                    type="number"
                                    class="marks-input save-marks no-export"
                                    data-student="{{ $student->id }}"
                                    data-subject="{{ $subject->id }}"
                                    data-max="{{ $subject->paper2 ?? 0 }}"
                                    data-paper="theory_paper2"
                                    value="{{ $status == 'absent' ? 0 : $obtained }}"
                                >

                                <br>

                                <label class="no-export lock-label">

                                    <input
                                        type="checkbox"
                                        class="mark-absent small-checkbox"
                                        data-student="{{ $student->id }}"
                                        data-subject="{{ $subject->id }}"
                                        data-paper="theory_paper2"
                                        value="1"
                                        @if($status == 'absent') checked @endif
                                    >

                                    Absent

                                </label>

                            @endif

                            @if($subject->theory_credits && $isEnrolled)

                                <br>

                                <label class="no-export lock-label">

                                    <input
                                        type="checkbox"
                                        class="lock-check small-checkbox"
                                        data-student="{{ $student->id }}"
                                        data-subject="{{ $subject->id }}"
                                        data-paper="theory_paper2"
                                    >

                                    @if($isLocked == 1)
                                        Unlock
                                    @else
                                        Lock
                                    @endif

                                </label>

                            @endif

                        </td>

                        {{-- PRACTICAL PAPER 1 --}}
                        @php
                            $data      = $marks[$student->id][$subject->id]['practical_paper1'] ?? null;
                            $obtained  = $data['obtained_marks'] ?? '';
                            $status    = $data['exam_status'] ?? 'present';
                            $isLocked  = $data['is_locked'] ?? 0;
                        @endphp
                        <td id="for-nc-hide">

                            @if(!$subject->practical_credits)

                                -

                            @elseif(!$isEnrolled)

                                NA

                            @elseif($isLocked)

                                {{ $status == 'absent' ? 'AB' : $obtained }}

                            @else

                                <input
                                    type="number"
                                    class="marks-input save-marks no-export"
                                    data-student="{{ $student->id }}"
                                    data-subject="{{ $subject->id }}"
                                    data-max="{{ $subject->practical_paper1 ?? 0 }}"
                                    data-paper="practical_paper1"
                                    value="{{ $status == 'absent' ? 0 : $obtained }}"
                                >

                                <br>

                                <label class="no-export lock-label">

                                    <input
                                        type="checkbox"
                                        class="mark-absent small-checkbox"
                                        data-student="{{ $student->id }}"
                                        data-subject="{{ $subject->id }}"
                                        data-paper="practical_paper1"
                                        value="1"
                                        @if($status == 'absent') checked @endif
                                    >

                                    Absent

                                </label>

                            @endif

                            @if($subject->practical_credits && $isEnrolled)

                                <br>

                                <label class="no-export lock-label">

                                    <input
                                        type="checkbox"
                                        class="lock-check small-checkbox"
                                        data-student="{{ $student->id }}"
                                        data-subject="{{ $subject->id }}"
                                        data-paper="practical_paper1"
                                    >

                                    @if($isLocked == 1)
                                        Unlock
                                    @else
                                        Lock
                                    @endif

                                </label>

                            @endif

                        </td>

                        {{-- PRACTICAL PAPER 2 --}}
                        @php
                            $data      = $marks[$student->id][$subject->id]['practical_paper2'] ?? null;
                            $obtained  = $data['obtained_marks'] ?? '';
                            $status    = $data['exam_status'] ?? 'present';
                            $isLocked  = $data['is_locked'] ?? 0;
                        @endphp
                        <td id="for-nc-hide">

                            @if(!$subject->practical_credits)

                                -

                            @elseif(!$isEnrolled)

                                NA

                            @elseif($isLocked)

                                {{ $status == 'absent' ? 'AB' : $obtained }}

                            @else

                                <input
                                    type="number"
                                    class="marks-input save-marks no-export"
                                    data-student="{{ $student->id }}"
                                    data-subject="{{ $subject->id }}"
                                    data-max="{{ $subject->practical_paper2 ?? 0 }}"
                                    data-paper="practical_paper2"
                                    value="{{ $status == 'absent' ? 0 : $obtained }}"
                                >

                                <br>

                                <label class="no-export lock-label">

                                    <input
                                        type="checkbox"
                                        class="mark-absent small-checkbox"
                                        data-student="{{ $student->id }}"
                                        data-subject="{{ $subject->id }}"
                                        data-paper="practical_paper2"
                                        value="1"
                                        @if($status == 'absent') checked @endif
                                    >

                                    Absent

                                </label>

                            @endif

                            @if($subject->practical_credits && $isEnrolled)

                                <br>

                                <label class="no-export lock-label">

                                    <input
                                        type="checkbox"
                                        class="lock-check small-checkbox"
                                        data-student="{{ $student->id }}"
                                        data-subject="{{ $subject->id }}"
                                        data-paper="practical_paper2"
                                    >

                                    @if($isLocked == 1)
                                        Unlock
                                    @else
                                        Lock
                                    @endif

                                </label>

                            @endif

                        </td>

                    @endif

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