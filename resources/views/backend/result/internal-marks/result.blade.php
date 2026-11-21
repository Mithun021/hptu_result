
<style>
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    table.overall-internal-result-table {
        width: 100%;
        border-collapse: collapse;
        border-spacing: 0;
        font-family: Arial, sans-serif;
        background: #fff;
        font-size: 11px !important;
    }

    table.overall-internal-result-table th,
    table.overall-internal-result-table td {
        border: 1px solid #222;
        padding: 5px 4px;
        text-align: center;
        vertical-align: middle;
        font-size: 10px;
    }

    table.overall-internal-result-table thead th {
        font-weight: 600;
    }

    .university-heading {
        font-size: 12px !important;
        font-weight: bold;
        text-transform: uppercase;
    }

    .result-heading {
        font-size: 10px !important;
        font-weight: bold;
    }

    .course-heading {
        font-size: 11px !important;
        font-weight: bold;
    }

    .subject-name {
        min-width: 75px;
        max-width: 100px;
        white-space: normal;
    }

    .student-detail {
        min-width: 75px;
    }

    .marks-cell {
        min-width: 45px;
    }

    .student-row {
        height: 65px;
    }

    .text-danger {
        color: red !important;
    }

    .fw-bold {
        font-weight: bold;
    }

    .font-size-8{
        font-size: 7px !important;
    }
    th.width-col-180 {
        min-width: 180px !important;
    }
    p{
        margin: 0;
    }

    @media print {

        @page {
            size: A4 landscape;
            margin: 5mm;
        }

        .table-responsive {
            overflow: visible;
        }

        table.overall-internal-result-table {
            width: 100%;
        }

        table.overall-internal-result-table th,
        table.result-table td {
            font-size: 8px;
            padding: 3px;
        }

        .no-print {
            display: none !important;
        }

        .student-row {
            page-break-inside: avoid;
        }


    }
</style>

@php

    /*
    |--------------------------------------------------------------------------
    | TOTAL TABLE COLUMNS
    |--------------------------------------------------------------------------
    */

    $totalColumns = 5;

    foreach ($subjects as $subject) {

        $totalColumns += ($subject->type == 'Practical') ? 2 : 1;
    }

@endphp

<div class="table-responsive">

    <table class="overall-internal-result-table">

        <thead>

            {{-- UNIVERSITY NAME --}}
            <tr>
                <th colspan="{{ $totalColumns }}" class="university-heading">
                    Himachal Pradesh Technical University, Hamirpur
                </th>
            </tr>

            {{-- RESULT HEADING --}}
            <tr>
                <th colspan="{{ $totalColumns }}" class="university-heading">
                    {{ ucwords(strtoupper($exam->exam_resultsheet_name ?? $exam->name )) }}
                </th>
            </tr>

            {{-- COURSE HEADING --}}
            <tr>
                <th colspan="{{ $totalColumns }}" class="course-heading">

                    {{ $course->name ?? $course->course_name ?? '' }}

                    |

                    {{ $branch->name ?? '' }}

                    |

                    {{ $className }}

                    |

                    Regular

                </th>
            </tr>

            {{-- SUBJECT CODE --}}
            <tr>

                <th colspan="5">Name & Particular of Candidate</th>

                @foreach ($subjects as $subject)

                    <th colspan="{{ $subject->type == 'Practical' ? 2 : 1 }}">

                        {{ $subject->subject_code ?? '' }}

                    </th>

                @endforeach

            </tr>

            {{-- SUBJECT NAME --}}
            <tr>
                <th rowspan="3" colspan="5"></th>
                @foreach ($subjects as $subject)

                    <th colspan="{{ $subject->type == 'Practical' ? 2 : 1 }}"
                        class="subject-name">

                        <spam class="font-size-8">{{ $subject->name ?? '' }}</spam>

                    </th>

                @endforeach

            </tr>

            {{-- SUBJECT TYPE --}}
            <tr>

                @foreach ($subjects as $subject)

                    <th colspan="{{ $subject->type == 'Practical' ? 2 : 1 }}">

                        {{ $subject->type ?? '' }}

                    </th>

                @endforeach

            </tr>

            {{-- INTERNAL / EXTERNAL HEADING --}}
            <tr>
                @foreach ($subjects as $subject)

                    <th>
                        Internal
                    </th>

                    @if ($subject->type == 'Practical')

                        <th>
                            External
                        </th>

                    @endif

                @endforeach

            </tr>

            {{-- MAXIMUM MARKS --}}
            <tr>

                <th>SN</th>

                <th>Roll No</th>

                <th class="width-col-180">College</th>

                <th>Name</th>

                <th>Father Name</th>

                @foreach ($subjects as $subject)

                    <th class="font-small">
                        Max Marks
                        <p>({{ $subject->internal_marks ?? 0 }})</p>
                    </th>

                    @if ($subject->type == 'Practical')

                        <th class="font-small">
                            Max Marks
                            <p>({{ $subject->external_marks ?? 0 }})</p>
                        </th>

                    @endif

                @endforeach

            </tr>

        </thead>

        <tbody>

            @forelse ($students as $index => $student)

                @php

                    $assignedSubjects = $studentSubjects[$student->id] ?? [];

                    // College short name first, otherwise full name
                    $collegeName = $student->institute->sort_name
                        ?? $student->institute->name
                        ?? '-';

                    if (trim((string) $collegeName) === '') {

                        $collegeName = $student->institute->name ?? '-';
                    }

                @endphp

                <tr class="student-row">

                    {{-- SERIAL NUMBER --}}
                    <td>
                        {{ $index + 1 }}
                    </td>

                    {{-- ROLL NUMBER --}}
                    <td>
                        {{ $student->university_roll_no ?? '-' }}
                    </td>

                    {{-- COLLEGE --}}
                    <td>
                        {{ $collegeName }}
                    </td>

                    {{-- STUDENT NAME --}}
                    <td>
                        {{ $student->student_name ?? '-' }}
                    </td>

                    {{-- FATHER NAME --}}
                    <td>
                        {{ $student->father_name ?? '-' }}
                    </td>

                    {{-- SUBJECT MARKS --}}
                    @foreach ($subjects as $subject)

                        @php

                            $isAssigned = in_array(
                                $subject->id,
                                $assignedSubjects
                            );

                            $internalMarks = $marks[$student->id][$subject->id]['internal'] ?? '-';

                            $externalMarks = $marks[$student->id][$subject->id]['external'] ?? '-';

                        @endphp

                        {{-- INTERNAL MARKS --}}
                        <td class="marks-cell">

                            @if ($isAssigned)

                                {!! $internalMarks !!}

                            @else

                                -

                            @endif

                        </td>

                        {{-- EXTERNAL MARKS ONLY FOR PRACTICAL --}}
                        @if ($subject->type == 'Practical')

                            <td class="marks-cell">

                                @if ($isAssigned)

                                    {!! $externalMarks !!}

                                @else

                                    -

                                @endif

                            </td>

                        @endif

                    @endforeach

                </tr>

            @empty

                <tr>
                    <td colspan="{{ $totalColumns }}">
                        No examination forms found for the selected filters.
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</div>
