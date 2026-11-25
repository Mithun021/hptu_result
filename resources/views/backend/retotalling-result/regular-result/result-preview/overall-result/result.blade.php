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

    /* Existing failed color */
    td.failed-bg {
        background-color: #fd4242;
        color: #FFF;
    }

    /* Retotalling applied - PASS */
    td.retotal-pass {
        background-color: #B4FF69 !important;
        color: #000 !important;
    }

    /* Retotalling applied - FAIL */
    td.retotal-fail {
        background-color: #FF9169 !important;
        color: #000 !important;
    }
</style>

@php
    /*
    |--------------------------------------------------------------------------
    | FILTER SUBJECTS - ONLY ENROLLED SUBJECTS
    |--------------------------------------------------------------------------
    */

    $studentIds = $students->pluck('id')->toArray();

    $enrolledSubjectIds = \App\Models\ExaminationFormChoosedSubject::query()
        ->join(
            'examination_form',
            'examination_form_choosed_subjects.examination_form_id',
            '=',
            'examination_form.id'
        )
        ->where('examination_form.exam_id', $exam->id)
        ->where('examination_form.exam_type', 'Regular')
        ->where('examination_form.payment_status', 1)
        ->where('examination_form.course_id', $course->id)
        ->where('examination_form.branch_id', $branch->id)
        ->where('examination_form.scheme_id', $scheme->id)
        ->where('examination_form.semester_id', $semester->id)
        ->where('examination_form.batch_id', $batch->id)
        ->whereIn('examination_form.student_id', $studentIds)
        ->when(!empty($instituteId), function ($query) use ($instituteId) {
            $query->where('examination_form.institute_id', $instituteId);
        })
        ->pluck('examination_form_choosed_subjects.subject_id')
        ->unique()
        ->values()
        ->toArray();

    $filteredSubjects = $subjects->filter(function ($subject) use ($enrolledSubjectIds) {
        return in_array($subject->id, $enrolledSubjectIds);
    })->values();

    /*
    |--------------------------------------------------------------------------
    | TABLE SETTINGS
    |--------------------------------------------------------------------------
    */

    $subjectCount = $filteredSubjects->count();

    $totalColumns = 5 + ($subjectCount * 4) + 9;

    $examMonth = strtoupper($exam->exam_month ?? '');
    $examYear = $exam->exam_year ?? '';
    $sessionName = trim($examMonth . ' ' . $examYear);

    $semesterName = $className;
    $courseName = $course->name ?? '';
    $branchName = $branch->name ?? '';
    $schemeName = $scheme->name ?? '';

    $universityName = 'Himachal Pradesh Technical Univesity, Hamirpur';

    $isFirstSemester = strtoupper(trim($semester->name ?? '')) === 'I';

    $specialGrades = ['AB', 'UMC', 'I/F', 'R', 'F'];
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
                    || Retotalling
                </th>
            </tr>

            {{-- SUBJECT CODES + SUMMARY HEADERS --}}
            <tr>
                <th colspan="5">
                    Name & Particular of Candidate
                </th>

                @foreach($filteredSubjects as $subject)
                    <th colspan="4">
                        {{ $subject->subject_code }}
                    </th>
                @endforeach

                <th rowspan="6">Result</th>

                <th rowspan="6">
                    <span class="vertical-text">Total Current Credit Point</span>
                </th>

                <th rowspan="6">
                    <span class="vertical-text">Current Grade Point Earn</span>
                </th>

                <th rowspan="6">
                    <span class="vertical-text">SGPA</span>
                </th>

                <th rowspan="6">
                    <span class="vertical-text">Previous Credit Point</span>
                </th>

                <th rowspan="6">
                    <span class="vertical-text">Previous Grade Point Earn</span>
                </th>

                <th rowspan="6">
                    <span class="vertical-text">CGPA</span>
                </th>

                <th rowspan="6">
                    <span class="vertical-text">Previous Result Status</span>
                </th>

                <th rowspan="6">
                    <span class="vertical-text">Remark</span>
                </th>
            </tr>

            {{-- SUBJECT NAMES --}}
            <tr>
                <th rowspan="2" colspan="5"></th>

                @foreach($filteredSubjects as $subject)
                    <th colspan="4">
                        {{ $subject->name }} - {{ $subject->type }}
                    </th>
                @endforeach
            </tr>

            {{-- EXTERNAL / INTERNAL / TOTAL / GRADE --}}
            <tr>
                @foreach($filteredSubjects as $subject)
                    <th><span class="vertical-text">External</span></th>
                    <th><span class="vertical-text">Internal</span></th>
                    <th><span class="vertical-text">Total</span></th>
                    <th><span class="vertical-text">Grade</span></th>
                @endforeach
            </tr>

            {{-- MAXIMUM MARKS --}}
            <tr>
                <th colspan="5"></th>

                @foreach($filteredSubjects as $subject)
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

                @foreach($filteredSubjects as $subject)
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

                @foreach($filteredSubjects as $subject)
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

                    /*
                    |--------------------------------------------------------------------------
                    | FAILED SUBJECT CODES
                    |--------------------------------------------------------------------------
                    */

                    $failedSubjectCodes = [];

                    foreach ($filteredSubjects as $subject) {
                        $subjectData = $studentSubjects[$subject->id] ?? null;
                        $subjectResult = $subjectData['result'] ?? '';

                        if ($subjectResult !== '' && $subjectResult !== 'PASS') {
                            $failedSubjectCodes[] = $subject->subject_code;
                        }
                    }

                    $currentResult = !empty($failedSubjectCodes)
                        ? implode(', ', $failedSubjectCodes)
                        : 'PASS';
                @endphp

                <tr>
                    {{-- SERIAL --}}
                    <td>{{ $loop->iteration }}</td>

                    {{-- ROLL NO --}}
                    <td>{{ $student->university_roll_no }}</td>

                    {{-- COLLEGE --}}
                    <td>{{ $studentInstituteName }}</td>

                    {{-- STUDENT NAME --}}
                    <td>{{ $student->student_name }}</td>

                    {{-- FATHER NAME --}}
                    <td>{{ $student->father_name }}</td>

                    {{-- SUBJECTS --}}
                    @foreach($filteredSubjects as $subject)

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

                            // Retotalling applied for this subject and locked marks used
                            $retotallingApplied = $subjectData['retotalling_applied'] ?? false;

                            /*
                            |--------------------------------------------------------------------------
                            | TOTAL DISPLAY
                            |--------------------------------------------------------------------------
                            */

                            $totalDisplay = $total;

                            if (in_array(strtoupper(trim((string)$grade)), $specialGrades)) {
                                $totalDisplay = $grade;
                            }

                            $isSpecialTotal = in_array(
                                strtoupper(trim((string)$totalDisplay)),
                                $specialGrades
                            );

                            /*
                            |--------------------------------------------------------------------------
                            | RETOTALLING GRADE COLOR
                            |--------------------------------------------------------------------------
                            */

                            if ($retotallingApplied) {
                                $gradeCellClass = $result === 'PASS'
                                    ? 'retotal-pass'
                                    : 'retotal-fail';
                            } else {
                                $gradeCellClass = $isFailed ? 'failed-bg' : '';
                            }
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
                            @if($totalDisplay !== null && $totalDisplay !== '')
                                @if(in_array(strtoupper(trim((string)$totalDisplay)), $specialGrades))
                                    {{ $totalDisplay }}
                                @else
                                    {{ number_format((float)$totalDisplay, 2) }}
                                @endif
                            @endif
                        </td>

                        {{-- GRADE / RESULT --}}
                        <td class="{{ $gradeCellClass }}">

                            @if($result !== '')

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
                        {{ $currentResult }}
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
                        @if(!$isFirstSemester)
                            {{ $studentResult['previous_result_status'] ?? '' }}
                        @endif
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
