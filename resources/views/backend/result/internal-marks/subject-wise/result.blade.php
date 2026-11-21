<style>

.result-table-a4 {
    width: 100%;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
}

.result-table-a4 th,
.result-table-a4 td {
    border: 1px solid #ddd;
    padding: 8px;
    font-size: 12px;
    text-align: center;
}

/* Table Header */
.result-table-a4 thead th {
    background: #f5f5f5;
    font-weight: 600;
}

/* Table Footer */
.result-table-a4 tfoot td {
    font-weight: 600;
    padding: 25px 8px 10px;
    text-align: center;
    vertical-align: bottom;
}

.result-table-a4 tfoot td::before {
    content: "";
    display: block;
    height: 40px;
}

/* Header */
.result-header h4 {
    margin: 2px 0;
}

.result-header p {
    margin: 4px 0;
    text-align: left;
}

/* Obtained Marks */
.obtained-marks-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
}

.obtained-marks-input {
    width: 90px;
    text-align: center;
}

.marks-absent-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    font-size: 11px;
}

.marks-absent-wrapper input {
    cursor: pointer;
}

td.align-left{
    text-align: left !important;
}
td.align-right{
    text-align: right !important;
}

</style>


<div class="table-responsive">

    <table class="result-table-a4">

        {{-- ===============================
             RESULT HEADER
        ================================ --}}

        <thead>

            <tr>
                <td colspan="7">Himachal Pradesh Technical Univesity,Hamirpur</td>
            </tr>
            <tr>
                <td colspan="7">{{ ucwords(strtoupper($exam->exam_resultsheet_name ?? $exam->name )) }}</td>
            </tr>
            <tr>
                <td colspan="7">{{ $course->name }} | {{ $scheme->name }} | {{ $branch->name ?? '' }} | {{ $className }} | Regular</td>
            </tr>
            <tr>
                <td colspan="5" class="align-left">Subject : {{ $subject->name }} ({{ $subject->subject_code }}) | {{ $subject->type }}</td>
                <td colspan="2" class="align-right">Maximum Marks : {{ $subject->internal_marks }}</td>
            </tr>


            {{-- ===============================
                 TABLE COLUMN HEADER
            ================================ --}}

            <tr>

                <th>
                    <input type="checkbox" id="check-all" class="no-export"> Sn no.
                </th>

                <th>Admission Number / Roll No</th>

                <th>Student Name</th>

                <th>Max Marks</th>

                <th>Obtained Marks</th>

                <th>Grace Marks</th>

                <th class="no-export">Status</th>

            </tr>

        </thead>


        {{-- ===============================
             STUDENT DATA
        ================================ --}}

        <tbody>
            @php
                $num = 0;
            @endphp
            @forelse($students as $student)

                @if(!in_array($student->id, $studentSubjects))
                    @continue
                @endif

                @php

                    $markData = $marks[$student->id] ?? null;

                    $obtainedMarks = $markData['obtained_marks'] ?? '';

                    $examStatus = $markData['exam_status'] ?? 'present';

                    $isLocked = $markData['is_locked'] ?? 0;

                    $graceMarks = $markData['grace_marks'] ?? 0;

                    $hasMarks = $markData !== null;

                    $isAbsent = $hasMarks && $examStatus == 'absent';

                @endphp

                <tr>

                    {{-- CHECKBOX --}}

                    <td class="">

                        <input
                            type="checkbox"
                            class="lock-check no-export"

                            data-student="{{ $student->id }}"
                            data-subject="{{ $subject->id }}"
                            data-marks-type="{{ $marksType }}"

                            data-course-type="{{ $course->course_type ?? $student->course_type }}"
                            data-course-id="{{ $course->id }}"
                            data-branch-id="{{ $branch->id }}"
                            data-batch-id="{{ $batch->id }}"
                            data-scheme-id="{{ $student->scheme_id }}"

                            data-semester-id="{{ request('semester_id') }}"
                            data-annual-semester-id="{{ request('annual_semester_id') }}"

                            data-exam-id="{{ request('exam_id') }}"
                            data-midsem-exam-id="{{ request('midsem_exam_id') }}"

                        >

                        {{ ++$num }}
                    </td>


                    {{-- ADMISSION / ROLL NUMBER --}}

                    <td>
                        {{ $student->admission_no ?? $student->university_roll_no }}
                    </td>


                    {{-- STUDENT NAME --}}

                    <td>
                        {{ $student->student_name }}
                    </td>


                    {{-- MAX MARKS --}}

                    <td>
                        {{ $subject->internal_marks }}
                    </td>


                    {{-- ===============================
                         OBTAINED MARKS
                    ================================ --}}

                    <td>

                        @if($hasMarks && $isLocked == 1)

                            {{-- LOCKED RECORD --}}

                            @if($examStatus == 'absent')

                                <span style="color:red;font-weight:600;">

                                    AB ({{ $obtainedMarks !== null && $obtainedMarks !== '' ? $obtainedMarks : 0 }})

                                </span>

                            @else

                                <span style="font-weight:600;">
                                    {{ $obtainedMarks }}
                                </span>

                            @endif

                        @else

                            {{-- EDITABLE / NEW RECORD --}}

                            <div class="obtained-marks-wrapper">

                                <input
                                    type="number"

                                    class="form-control form-control-sm obtained-marks-input"

                                    value="{{ $isAbsent ? 0 : $obtainedMarks }}"

                                    min="0"

                                    max="{{ $subject->internal_marks }}"

                                    step="0.01"

                                    placeholder="Enter Marks"

                                    style="width:90px;"

                                    {{ $isAbsent ? 'readonly' : '' }}

                                    data-student="{{ $student->id }}"
                                    data-subject="{{ $subject->id }}"
                                    data-marks-type="{{ $marksType }}"

                                    data-course-type="{{ $course->course_type ?? $student->course_type }}"
                                    data-course-id="{{ $course->id }}"
                                    data-branch-id="{{ $branch->id }}"
                                    data-batch-id="{{ $batch->id }}"
                                    data-scheme-id="{{ $student->scheme_id }}"

                                    data-semester-id="{{ request('semester_id') }}"
                                    data-annual-semester-id="{{ request('annual_semester_id') }}"

                                    data-exam-id="{{ request('exam_id') }}"
                                    data-midsem-exam-id="{{ request('midsem_exam_id') }}"
                                    data-max-marks="{{ $subject->internal_marks }}"

                                >


                                {{-- MARKS ABSENT CHECKBOX --}}

                                <div class="marks-absent-wrapper">

                                    <input
                                        type="checkbox"

                                        class="marks-absent-checkbox"

                                        id="absent_{{ $student->id }}"

                                        {{ $isAbsent ? 'checked' : '' }}

                                    >

                                    <label for="absent_{{ $student->id }}">
                                        Marks Absent
                                    </label>

                                </div>

                            </div>

                        @endif

                    </td>


                    {{-- ===============================
                         GRACE MARKS
                    ================================ --}}

                    @if($courseType == 'Annual Based')

                        <td>

                            @if($hasMarks)

                                {{ number_format((float) $graceMarks, 2) }}

                            @else

                                -

                            @endif

                        </td>

                    @else

                        <td>

                            @if($hasMarks)

                                @if($isLocked == 1)

                                    {{ number_format((float) $graceMarks, 2) }}

                                @else

                                    <input
                                        type="number"

                                        class="form-control form-control-sm grace-marks-input"

                                        value="{{ $graceMarks }}"

                                        min="0"

                                        style="width:80px;"

                                        data-student="{{ $student->id }}"
                                        data-subject="{{ $subject->id }}"
                                        data-marks-type="{{ $marksType }}"

                                        data-course-type="{{ $course->course_type ?? $student->course_type }}"
                                        data-course-id="{{ $course->id }}"
                                        data-branch-id="{{ $branch->id }}"
                                        data-batch-id="{{ $batch->id }}"
                                        data-scheme-id="{{ $student->scheme_id }}"

                                        data-semester-id="{{ request('semester_id') }}"
                                        data-annual-semester-id="{{ request('annual_semester_id') }}"

                                        data-exam-id="{{ request('exam_id') }}"
                                        data-midsem-exam-id="{{ request('midsem_exam_id') }}"

                                    >

                                @endif

                            @else

                                -

                            @endif

                        </td>

                    @endif


                    {{-- ===============================
                         STATUS
                    ================================ --}}

                    <td class="no-export">

                        @if($hasMarks)

                            @if($isLocked == 1)

                                <span class="badge bg-success">
                                    Locked
                                </span>

                            @else

                                <span class="badge bg-danger">
                                    Unlocked
                                </span>

                            @endif

                        @else

                            <span class="badge bg-secondary">
                                No Data
                            </span>

                        @endif

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="7">

                        <span style="color:red;">
                            No Students Found
                        </span>

                    </td>

                </tr>

            @endforelse

        </tbody>


        {{-- ===============================
             FOOTER
        ================================ --}}

        <tfoot>

            <tr>

                <td colspan="3">
                    Instructor(s)
                </td>

                <td colspan="4">
                    HOD
                </td>

            </tr>

        </tfoot>

    </table>

</div>
