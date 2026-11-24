
<style>
    .result-sheet {
        width: 100%;
        overflow-x: auto;
        color: #000;
    }

    .result-sheet table {
        width: max-content;
        min-width: 100%;
        border: 1px solid #000;
        border-collapse: collapse;
        font-size: 9px;
    }

    .result-sheet table tr th {
        text-align: center;
    }

    .result-sheet table tr th,
    .result-sheet table tr td {
        border: 1px solid #000;
        border-collapse: collapse;
        text-align: center;
    }

    .vertical-text {
        writing-mode: vertical-rl;
        transform: rotate(360deg);
        white-space: nowrap;
    }

    .mini-text {
        font-size: 6px;
    }

    .result-sheet table tbody tr td:nth-child(2),
    .result-sheet table tbody tr td:nth-child(3),
    .result-sheet table tbody tr td:nth-child(4) {
        min-width: 120px;
    }

    .result-sheet table thead tr:last-child th:nth-child(2),
    .result-sheet table thead tr:last-child th:nth-child(3),
    .result-sheet table thead tr:last-child th:nth-child(4) {
        min-width: 120px;
    }

    .result-sheet table tbody tr td {
        vertical-align: top;
    }

    td.failed-bg {
        background-color: #fd4242;
        color: #FFF;
    }
</style>

@php
    $subjectCount = $subjects->count();

    $totalColumns = 5 + ($subjectCount * 4) + 9;

    $examMonth = strtoupper($exam->exam_month ?? '');

    $examYear = $exam->exam_year ?? '';

    $sessionName = trim($examMonth . ' ' . $examYear);

    $semesterName = $className;

    $courseName = $course->name ?? '';

    $branchName = $branch->name ?? '';

    $schemeName = $scheme->name ?? '';

    $universityName = 'Himachal Pradesh Technical Univesity, Hamirpur';
@endphp

<div class="result-sheet">
    <table>
        <thead>

            {{-- UNIVERSITY NAME --}}
            <tr>
                <th colspan="{{ $totalColumns }}">
                    {{ $universityName }}
                </th>
            </tr>

            {{-- EXAM SESSION --}}
            <tr>
                <th colspan="{{ $totalColumns }}">
                    RESULT TABULATION SHEET OF EXAMINATION SESSION,
                    {{ $sessionName }}
                </th>
            </tr>

            {{-- COURSE DETAILS --}}
            <tr>
                <th colspan="{{ $totalColumns }}">
                    {{ $courseName }}
                    @if(!empty($branchName))
                        | {{ $branchName }}
                    @endif
                    | {{ $schemeName }}
                    | {{ $semesterName }}
                    || Regular
                </th>
            </tr>

            {{-- SUBJECT CODES + SUMMARY HEADERS --}}
            <tr>
                <th colspan="5">
                    Name & Particular of Candidate
                </th>

                @foreach($subjects as $subject)
                    <th colspan="4">
                        {{ $subject->subject_code }}
                    </th>
                @endforeach

                <th rowspan="6">
                    Result
                </th>

                <th rowspan="6">
                    <span class="vertical-text">
                        Total Current Credit Point
                    </span>
                </th>

                <th rowspan="6">
                    <span class="vertical-text">
                        Current Grade Point Earn
                    </span>
                </th>

                <th rowspan="6">
                    <span class="vertical-text">
                        SGPA
                    </span>
                </th>

                <th rowspan="6">
                    <span class="vertical-text">
                        Previous Credit Point
                    </span>
                </th>

                <th rowspan="6">
                    <span class="vertical-text">
                        Previous Grade Point Earn
                    </span>
                </th>

                <th rowspan="6">
                    <span class="vertical-text">
                        CGPA
                    </span>
                </th>

                <th rowspan="6">
                    <span class="vertical-text">
                        Previous Result Status
                    </span>
                </th>

                <th rowspan="6">
                    <span class="vertical-text">
                        Remark
                    </span>
                </th>
            </tr>

            {{-- SUBJECT NAMES --}}
            <tr>
                <th rowspan="2" colspan="5"></th>

                @foreach($subjects as $subject)
                    <th colspan="4">
                        {{ $subject->name }} - {{ $subject->type }}
                    </th>
                @endforeach
            </tr>

            {{-- EXTERNAL / INTERNAL / TOTAL / GRADE --}}
            <tr>
                @foreach($subjects as $subject)
                    <th>
                        <span class="vertical-text">External</span>
                    </th>

                    <th>
                        <span class="vertical-text">Internal</span>
                    </th>

                    <th>
                        <span class="vertical-text">Total</span>
                    </th>

                    <th>
                        <span class="vertical-text">Grade</span>
                    </th>
                @endforeach
            </tr>

            {{-- MAXIMUM MARKS --}}
            <tr>
                <th colspan="5"></th>

                @foreach($subjects as $subject)
                    <th>{{ $subject->external_marks }}</th>
                    <th>{{ $subject->internal_marks }}</th>
                    <th>
                        {{ (float)$subject->external_marks + (float)$subject->internal_marks }}
                    </th>
                    <th></th>
                @endforeach
            </tr>

            {{-- PASS MARKS --}}
            <tr>
                <th colspan="5"></th>

                @foreach($subjects as $subject)
                    @php
                        $passPercentage = \App\Models\PassPercentage::where('course_id', $course->id)
                            ->where('scheme_id', $scheme->id)
                            ->first();

                        $passPercent = $passPercentage->overall_pass_percentage ?? 0;
                    @endphp

                    <th>
                        {{ round(($subject->external_marks * $passPercent) / 100, 2) }}
                    </th>

                    <th>
                        {{ round(($subject->internal_marks * $passPercent) / 100, 2) }}
                    </th>

                    <th>
                        {{ round(
                            (($subject->external_marks + $subject->internal_marks) * $passPercent) / 100,
                            2
                        ) }}
                    </th>

                    <th></th>
                @endforeach
            </tr>

            {{-- CREDIT --}}
            <tr>
                <th>SN</th>
                <th>Roll No</th>
                <th>College</th>
                <th>Name</th>
                <th>Father Name</th>

                @foreach($subjects as $subject)
                    <th></th>
                    <th></th>
                    <th></th>
                    <th>{{ $subject->credits }}</th>
                @endforeach
            </tr>

        </thead>

        {{-- STUDENT RESULT --}}
        <tbody>

            @foreach($students as $index => $student)

                @php
                    $studentResult = $marks[$student->id] ?? [];

                    $studentSubjects = $studentResult['subjects'] ?? [];

                    $studentInstituteName = $institutes[$student->institute_id] ?? '';
                @endphp

                <tr>
                    {{-- SERIAL --}}
                    <td>{{ $loop->iteration }}</td>

                    {{-- ROLL NO --}}
                    <td>
                        {{ $student->university_roll_no }}
                    </td>

                    {{-- COLLEGE --}}
                    <td>
                        {{ $studentInstituteName }}
                    </td>

                    {{-- STUDENT NAME --}}
                    <td>
                        {{ $student->student_name }}
                    </td>

                    {{-- FATHER NAME --}}
                    <td>
                        {{ $student->father_name }}
                    </td>

                    {{-- SUBJECTS --}}
                    @foreach($subjects as $subject)

                        @php
                            $subjectData = $studentSubjects[$subject->id] ?? null;

                            $internal = $subjectData['internal_marks'] ?? null;

                            $external = $subjectData['external_marks'] ?? null;

                            $total = $subjectData['total'] ?? null;

                            $result = $subjectData['result'] ?? '';

                            $percentage = $subjectData['percentage'] ?? null;

                            $grade = $subjectData['grade'] ?? '';

                            $gradePoint = $subjectData['grade_point'] ?? 0;

                            $creditScore = $subjectData['credit_score'] ?? 0;

                            $isFailed = $result !== '' && $result !== 'PASS';
                        @endphp

                        {{-- EXTERNAL MARKS --}}
                        <td>
                            {{ $external !== null ? number_format((float)$external, 2) : '' }}
                        </td>

                        {{-- INTERNAL MARKS --}}
                        <td>
                            {{ $internal !== null ? number_format((float)$internal, 2) : '' }}
                        </td>

                        {{-- TOTAL --}}
                        <td>
                            {{ $total !== null ? number_format((float)$total, 2) : '' }}
                        </td>

                        {{-- GRADE / RESULT --}}
                        <td class="{{ $isFailed ? 'failed-bg' : '' }}">

                            @if($result !== '')

                                {{-- Grade column shows marks_grades.grade_name; result status is separate --}}
                                {{ $grade !== '' ? $grade : $result }}

                                @if($result === 'PASS')

                                    <br>

                                    <span class="mini-text">
                                        {{ number_format((float)$percentage, 2) }}%
                                    </span>

                                    <br>

                                    <span class="mini-text">
                                        {{ $gradePoint }}
                                    </span>

                                    <br>

                                    <span class="mini-text">
                                        {{ number_format((float)$creditScore, 2) }}
                                    </span>

                                @endif

                            @endif

                        </td>

                    @endforeach

                    {{-- CURRENT RESULT --}}
                    <td>
                        {{ $studentResult['result'] ?? '' }}
                    </td>

                    {{-- CURRENT CREDIT --}}
                    <td>
                        {{ number_format((float)($studentResult['current_credit_point'] ?? 0), 2) }}
                    </td>

                    {{-- CURRENT GRADE POINT EARN --}}
                    <td>
                        {{ number_format((float)($studentResult['current_grade_point_earn'] ?? 0), 2) }}
                    </td>

                    {{-- SGPA --}}
                    <td>
                        {{ number_format((float)($studentResult['sgpa'] ?? 0), 2) }}
                    </td>

                    {{-- PREVIOUS CREDIT --}}
                    <td>
                        {{ number_format((float)($studentResult['previous_credit_point'] ?? 0), 2) }}
                    </td>

                    {{-- PREVIOUS GRADE POINT EARN --}}
                    <td>
                        {{ number_format((float)($studentResult['previous_grade_point_earn'] ?? 0), 2) }}
                    </td>

                    {{-- CGPA --}}
                    <td>
                        {{ number_format((float)($studentResult['cgpa'] ?? 0), 2) }}
                    </td>

                    {{-- PREVIOUS RESULT STATUS --}}
                    <td>
                        {{ $studentResult['previous_result_status'] ?? '' }}
                    </td>

                    {{-- REMARK --}}
                    <td>
                        {{ $studentResult['remark'] ?? '' }}
                    </td>

                </tr>

            @endforeach

        </tbody>
    </table>
</div>
