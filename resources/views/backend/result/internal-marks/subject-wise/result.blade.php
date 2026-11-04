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

/* Footer top spacing for signature */
.result-table-a4 tfoot td::before {
    content: "";
    display: block;
    height: 40px;
}

/* Header section styling */
.result-header h4 {
    margin: 2px 0;
}

.result-header p {
    margin: 4px 0;
    text-align: left
}

</style>

<div class="table-responsive">

<table class="result-table-a4">

    <!-- ===============================
            RESULT HEADER
    ================================ -->

    <thead>

        <tr>
            <td colspan="7" class="result-header">

                <center>
                    <h4>CONTROLLER OF EXAMINATIONS</h4>

                    <h4>
                        GURU ANGAD DEV VETERINARY AND ANIMAL SCIENCE UNIVERSITY,
                        LUDHIANA (INDIA)
                    </h4>

                    <h4>{{ $className }} Session {{ $batch->name ?? '_' }}</h4>

                    <h4>{{ $midsemexam->mid_sem_exam_name }}</h4>
                </center>

                <p>
                    <b>{{ $course->name }} ({{ $branch->name ?? '' }})</b>
                </p>

                <p>
                    Subject : {{ $subject->name }} ({{ $subject->subject_code }})
                </p>

                <p>
                    Maximum Marks :

                    @if($marksType == 'Mid-Sem')
                        {{ $subject->mid_sem_marks1 }}

                    @elseif($marksType == 'Quiz')
                        {{ $subject->mid_sem_marks2 }}

                    @elseif($marksType == 'Practical')
                        {{ $subject->mid_sem_marks3 }}
                        
                    @elseif($marksType == 'Internal 1')

                        {{ $subject->internal1 }}

                    @elseif($marksType == 'Internal 2')

                        {{ $subject->internal2 }}

                    @elseif($marksType == 'Internal 3')

                        {{ $subject->internal3 }}

                    @else
                        -
                    @endif

                </p>

            </td>
        </tr>


        <!-- ===============================
                TABLE COLUMN HEADER
        ================================ -->

        <tr>
            <th class="no-export"><input type="checkbox" id="check-all"></th>
            <th>Admission Number / Roll No</th>
            <th>Student Name</th>
            <th>Max Marks</th>
            <th>Obtained Marks</th>
            <th>Grace Marks</th>
            <th>Moderation Marks</th>
            <th class="no-export">Status</th>
        </tr>

    </thead>


    <!-- ===============================
            STUDENT DATA
    ================================ -->

    <tbody>

        @forelse($students as $student)
        
            @if(!in_array($student->id, $studentSubjects))
                @continue
            @endif

            <tr>

                <td class="no-export">

                    <input
                        type="checkbox"
                        class="lock-check"

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

                </td>

                <!-- Admission / Roll -->
                <td>
                    {{ $student->admission_no ?? $student->university_roll_no }}
                </td>

                <!-- Student Name -->
                <td>
                    {{ $student->student_name }}
                </td>

                <!-- Max Marks -->
                <td>

                    @if($marksType == 'Mid-Sem')

                        {{ $subject->mid_sem_marks1 }}

                    @elseif($marksType == 'Quiz')

                        {{ $subject->mid_sem_marks2 }}

                    @elseif($marksType == 'Practical')

                        {{ $subject->mid_sem_marks3 }}

                    @elseif($marksType == 'Internal 1')

                        {{ $subject->internal1 }}

                    @elseif($marksType == 'Internal 2')

                        {{ $subject->internal2 }}

                    @elseif($marksType == 'Internal 3')

                        {{ $subject->internal3 }}

                    @else

                        -

                    @endif

                </td>
                
                <!-- Obtained Marks -->
                {{-- <td>

                    @php
                        $isEnrolled = in_array($student->id, $studentSubjects);
                    @endphp

                    @if(!$isEnrolled)
                        <span style="color:#999;">NA</span>

                    @elseif(array_key_exists($student->id, $marks))

                        @if($marks[$student->id] == 'AB')
                            <span style="color:red;font-weight:600;">AB</span>
                        @else
                            {{ $marks[$student->id] }}
                        @endif

                    @else

                        <span style="color:#df610d;">Marks Not Uploaded</span>

                    @endif

                </td> --}}
                <!-- Obtained Marks -->
                <td>

                    @php

                        $isEnrolled = in_array($student->id, $studentSubjects);

                    @endphp

                    @if(!$isEnrolled)

                        <span style="color:#999;">NA</span>

                    @elseif(array_key_exists($student->id, $marks))

                        @php

                            $obtainedMarks = $marks[$student->id]['obtained_marks'] ?? null;

                            $examStatus = $marks[$student->id]['exam_status'] ?? null;

                            $isLocked = $marks[$student->id]['is_locked'] ?? 0;

                        @endphp

                        @if($examStatus == 'absent')

                            <span style="color:red;font-weight:600;">
                                AB
                            </span>

                        @elseif($obtainedMarks !== null && $obtainedMarks !== '')

                            {{ $obtainedMarks }}

                        @else

                            <span style="color:#df610d;">
                                Marks Not Uploaded
                            </span>

                        @endif

                    @else

                        <span style="color:#df610d;">
                            Marks Not Uploaded
                        </span>

                    @endif

                </td>
                
                @if ($courseType == 'Annual Based')
                <td>
                     @if(array_key_exists($student->id, $marks))

                        @php

                            $graceMarks = $marks[$student->id]['grace_marks'] ?? 0;

                            $isLocked = $marks[$student->id]['is_locked'] ?? 0;

                        @endphp

                        

                            {{ $graceMarks }}

                        
                    @endif
                </td>
                
                @else
                
                <td>

                    @if(array_key_exists($student->id, $marks))

                        @php

                            $graceMarks = $marks[$student->id]['grace_marks'] ?? 0;

                            $isLocked = $marks[$student->id]['is_locked'] ?? 0;

                        @endphp

                        @if($isLocked == 1)

                            {{ $graceMarks }}

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
                
                
                <td>

                    @php

                        $adjustmentData = $academicAdjustments[$student->id] ?? null;

                        $moderationMarks = $adjustmentData->adjustment_marks ?? 0;

                        $adjustmentType = $adjustmentData->adjustment_type ?? 'moderation';

                        $isLocked = $marks[$student->id]['is_locked'] ?? 0;

                    @endphp

                    {{-- ONLY PRACTICAL SUBJECT --}}
                    @if(
                        strtolower($subject->type) == 'practical'
                        && $marksType == 'Practical'
                    )

                        @if(!$isEnrolled)

                            <span style="color:#999;">NA</span>

                        @elseif(array_key_exists($student->id, $marks))

                            {{-- LOCKED --}}
                            @if($isLocked == 1)

                                {{ $moderationMarks }}

                            @else

                                {{-- EDIT MODE --}}
                                <div style="display:flex;flex-direction:column;gap:4px;">

                                    <input
                                        type="number"
                                        class="form-control form-control-sm moderation-marks-input"

                                        value="{{ $moderationMarks }}"
                                        min="0"
                                        step="0.01"

                                        style="width:80px;"

                                        data-student="{{ $student->id }}"
                                        data-subject="{{ $subject->id }}"
                                        data-exam="{{ request('exam_id') }}"
                                        data-marks-type="{{ "external" }}"

                                        data-adjustment-scope="external"
                                    >

                                    <input type="hidden"
                                        class="form-control form-control-sm moderation-type" value="moderation"
                                        style="width:80px;"
                                    >

                                    {{-- <select
                                        class="form-control form-control-sm moderation-type"
                                        style="width:80px;"
                                    >
                                        <option value="moderation"
                                            {{ $adjustmentType == 'moderation' ? 'selected' : '' }}>
                                            Moderation
                                        </option>

                                        <option value="grace"
                                            {{ $adjustmentType == 'grace' ? 'selected' : '' }}>
                                            Grace
                                        </option>
                                    </select> --}}

                                    <button
                                        type="button"

                                        class="btn btn-primary btn-sm save-moderation"

                                        data-student="{{ $student->id }}"
                                        data-subject="{{ $subject->id }}"
                                        data-exam="{{ request('exam_id') }}"
                                        data-marks-type="{{ "external" }}"

                                        data-adjustment-scope="external"

                                        style="font-size:10px;padding:2px 5px;"
                                    >
                                        Save
                                    </button>

                                </div>

                            @endif

                        @else

                            -

                        @endif

                    @else

                        {{-- NON PRACTICAL --}}
                        -

                    @endif

                </td>
                
                
                <td class="no-export">

                    @if(array_key_exists($student->id, $marks))

                        @php

                            $isLocked = $marks[$student->id]['is_locked'] ?? 0;

                        @endphp

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
                    <span style="color:red;">No Students Found</span>
                </td>
            </tr>

        @endforelse

    </tbody>
    <tfoot>
        <tr>
            <td colspan="3">
                    Instructor(s)
            </td>
            <td colspan="3">
                    HOD
            </td>
        </tr>
    </tfoot>

</table>

</div>