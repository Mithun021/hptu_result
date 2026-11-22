
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

    @php
        $filteredSubjects = $subjects->filter(function ($sub) use ($course) {
            return !(
                $course->course == 'UG' &&
                $sub->type == 'Practical' &&
                $sub->is_nc == 0
            );
        });
    @endphp

    <table class="result-table">

        <thead>

            <tr>
                <td colspan="{{ 5 + $filteredSubjects->count() }}" class="result-header">
                    HIMACHAL PRADESH TECHNICAL UNIVESITY, HAMIRPUR
                </td>
            </tr>

            <tr>
                <td colspan="{{ 5 + $filteredSubjects->count() }}">
                    {{ $exam->exam_resultsheet_name ?? $exam->name }}
                </td>
            </tr>

            <tr>
                <td colspan="{{ 5 + $filteredSubjects->count() }}">
                    {{ $course->name }}
                    | {{ $scheme->name }}
                    | @if(!empty($branch->name) && $branch->name != 'NA')
                        {{ $branch->name }} |
                    @endif
                    {{ $className }}
                    | {{ $batch->name }}
                    | Regular
                </td>
            </tr>

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

            <tr>

                @foreach($filteredSubjects as $subject)

                    <th>
                        Sub : {{ $subject->subject_code }}
                    </th>

                @endforeach

            </tr>

            <tr>

                @foreach($filteredSubjects as $subject)

                    <th>

                        @if($subject->is_nc)
                            S/U
                        @else
                            Max : {{ $subject->external_marks }}
                        @endif

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

                    <td>{{ ++$num }}</td>

                    <td>{{ $student->student_name }}</td>

                    <td>{{ $student->university_roll_no }}</td>

                    <td>
                        {{ $student->institute->sort_name ?? $student->institute->name ?? '' }}
                    </td>

                    <td>{{ $student->father_name }}</td>

                    @foreach($filteredSubjects as $subject)

                        @php

                            /*
                            |--------------------------------------------------------------------------
                            | FETCH MARK DATA
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

                            $obtained = $markData['obtained_marks'] ?? null;
                            $grace    = $markData['grace_marks'] ?? null;
                            $status   = $markData['exam_status'] ?? '';
                            $grade    = $markData['grade'] ?? '';

                            /*
                            |--------------------------------------------------------------------------
                            | EXAMINATION FORM SUBJECT CHECK
                            |--------------------------------------------------------------------------
                            */

                            $isExamSubjectEligible =
                                isset($examFormSubjects[$student->id]) &&
                                in_array(
                                    $subject->id,
                                    $examFormSubjects[$student->id]
                                );

                            /*
                            |--------------------------------------------------------------------------
                            | GRACE MARKS CHECK
                            |--------------------------------------------------------------------------
                            */

                            $hasGrace = (
                                $grace !== null &&
                                $grace !== '' &&
                                (float) $grace != 0
                            );

                            $totalObtained = (float) ($obtained ?? 0)
                                + (float) ($grace ?? 0);

                            $formattedTotal = rtrim(
                                rtrim(number_format($totalObtained, 2, '.', ''), '0'),
                                '.'
                            );

                            $formattedObtained = rtrim(
                                rtrim(number_format((float) ($obtained ?? 0), 2, '.', ''), '0'),
                                '.'
                            );

                            $formattedGrace = rtrim(
                                rtrim(number_format((float) ($grace ?? 0), 2, '.', ''), '0'),
                                '.'
                            );

                        @endphp

                        <td>

                            @if(!$isExamSubjectEligible)

                                {{-- Subject not chosen --}}

                            @elseif($subject->is_nc)

                                {{ $grade ?: '-' }}

                            @else

                                @if($status == 'present')

                                    <strong>{{ $formattedTotal }}</strong>

                                    @if($hasGrace)

                                        <br>

                                        <small>
                                            ({{ $formattedObtained }} + {{ $formattedGrace }})
                                        </small>

                                    @endif

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
