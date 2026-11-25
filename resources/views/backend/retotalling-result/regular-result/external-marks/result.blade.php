
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

    table.result-table th.subject-name {
        font-size: 9px;
        font-weight: normal;
    }

    .retotalling-subject-cell {
        background-color: #e5e5e5 !important;
    }
</style>

<div class="table-responsive">

    @php
        /*
        |--------------------------------------------------------------------------
        | FILTER ONLY THEORY SUBJECTS
        |--------------------------------------------------------------------------
        */

        $filteredSubjects = $subjects->filter(function ($sub) {
            return $sub->type == 'Theory';
        })->values();

        $totalColumns = 5 + $filteredSubjects->count();
    @endphp

    <table class="result-table">

        <thead>

            {{-- RESULT HEADING --}}
            <tr>
                <td colspan="{{ $totalColumns }}" class="result-header">
                    HIMACHAL PRADESH TECHNICAL UNIVESITY, HAMIRPUR
                </td>
            </tr>

            {{-- EXAM NAME --}}
            <tr>
                <td colspan="{{ $totalColumns }}">
                    {{ $exam->exam_resultsheet_name ?? $exam->name }}
                </td>
            </tr>

            {{-- CLASS DETAILS --}}
            <tr>
                <td colspan="{{ $totalColumns }}">
                    {{ $course->name }}
                    | {{ $scheme->name ?? '' }}
                    | {{ $branch->name ?? '' }}
                    | {{ $className }}
                    | {{ $batch->name ?? '' }}
                    | Regular
                </td>
            </tr>

            {{-- SUBJECT NAME ROW --}}
            <tr>

                <th rowspan="3">SL</th>
                <th rowspan="3">Student Name</th>
                <th rowspan="3">Admission No</th>
                <th rowspan="3">College Name</th>
                <th rowspan="3">Father Name</th>

                @foreach($filteredSubjects as $subject)

                    <th class="subject-name">
                        {{ $subject->name }}
                    </th>

                @endforeach

            </tr>

            {{-- SUBJECT CODE ROW --}}
            <tr>

                @foreach($filteredSubjects as $subject)

                    <th>
                        Sub : {{ $subject->subject_code }}
                    </th>

                @endforeach

            </tr>

            {{-- MAX MARKS ROW --}}
            <tr>

                @foreach($filteredSubjects as $subject)

                    <th>
                        Max : {{ $subject->external_marks }}
                    </th>

                @endforeach

            </tr>

        </thead>

        <tbody>

            @php
                $num = 0;
            @endphp

            @foreach($students as $student)

                <tr>

                    {{-- SL --}}
                    <td>{{ ++$num }}</td>

                    {{-- STUDENT NAME --}}
                    <td>{{ $student->student_name }}</td>

                    {{-- ADMISSION / UNIVERSITY ROLL NO --}}
                    <td>{{ $student->university_roll_no }}</td>

                    {{-- COLLEGE --}}
                    <td>
                        {{ $student->institute->sort_name ?? $student->institute->name ?? '' }}
                    </td>

                    {{-- FATHER NAME --}}
                    <td>{{ $student->father_name }}</td>

                    @foreach($filteredSubjects as $subject)

                        @php

                            /*
                            |--------------------------------------------------------------------------
                            | 1. FETCH MARK DATA
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $courseType == 'Semester Based' &&
                                $course->course != 'Diploma'
                            ) {

                                $markData = $marks[$student->id][$subject->id] ?? null;

                            } else {

                                $markData = $marks[$student->id][$subject->id]['external']
                                    ?? null;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | 2. RETOTALLING SUBJECT MAPPING CHECK
                            |--------------------------------------------------------------------------
                            */

                            $isRetotallingSubject = in_array(
                                $subject->id,
                                $retotallingSubjects[$student->id] ?? []
                            );


                            /*
                            |--------------------------------------------------------------------------
                            | 3. STUDENT SUBJECT ENROLLMENT CHECK
                            |--------------------------------------------------------------------------
                            */

                            $isEnrolled =
                                isset($studentSubjects[$student->id]) &&
                                in_array(
                                    $subject->id,
                                    $studentSubjects[$student->id]
                                );


                            /*
                            |--------------------------------------------------------------------------
                            | 4. ORIGINAL MARKS / STATUS / GRADE
                            |--------------------------------------------------------------------------
                            */

                            $obtained = $markData['obtained_marks'] ?? '';
                            $status   = $markData['exam_status'] ?? 'present';
                            $grade    = $markData['grade'] ?? '';


                            /*
                            |--------------------------------------------------------------------------
                            | 5. RETOTALLING REVISED MARKS
                            |--------------------------------------------------------------------------
                            */

                            $revisedMarks = $markData['retotalling_obtained_marks'] ?? null;

                            $retotallingLocked = $markData['retotalling_marks_locked'] ?? 0;


                            /*
                            |--------------------------------------------------------------------------
                            | 6. FINAL RETOTALLING CONDITION
                            |--------------------------------------------------------------------------
                            */

                            $hasLockedRetotallingMarks =
                                $isRetotallingSubject &&
                                $revisedMarks !== null &&
                                $retotallingLocked == 1;


                            /*
                            |--------------------------------------------------------------------------
                            | 7. APPLY REVISED MARKS
                            |--------------------------------------------------------------------------
                            */

                            if ($hasLockedRetotallingMarks) {
                                $obtained = $revisedMarks;
                            }

                        @endphp

                        {{-- RESULT CELL --}}
                        <td class="{{ $hasLockedRetotallingMarks ? 'retotalling-subject-cell' : '' }}">

                            {{-- SUBJECT NOT ENROLLED --}}
                            @if(!$isEnrolled)

                                <span class="text-muted">NA</span>

                            {{-- NON CREDIT SUBJECT --}}
                            @elseif($subject->is_nc)

                                {{ $grade ?: '-' }}

                            {{-- EXTERNAL MARKS --}}
                            @else

                                @if($status == 'absent')

                                    <span class="text-danger">AB</span>

                                @elseif($status == 'present')

                                    {{ $obtained !== '' && $obtained !== null ? $obtained : '-' }}

                                @elseif(!empty($status))

                                    <span class="text-danger">
                                        {{ $status }}
                                    </span>

                                @else

                                    -

                                @endif

                            @endif

                        </td>

                    @endforeach

                </tr>

            @endforeach

        </tbody>

    </table>

</div>
