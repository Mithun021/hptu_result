
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

    .retotalling-subject-cell {
        background-color: #e5e5e5 !important;
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
                        $paper = $column['paper'];
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
                        $paper = $column['paper'];
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
                        $paper = $column['paper'];
                    @endphp

                    <th>
                        @if($subject->is_nc)

                            S/U

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

                    <td>{{ $studentIndex + 1 }}</td>

                    <td>{{ $student->student_name }}</td>

                    <td>{{ $student->university_roll_no }}</td>

                    @foreach($tableColumns as $column)

                        @php
                            $subject = $column['subject'];
                            $paper = $column['paper'];

                            /*
                            |--------------------------------------------------------------------------
                            | Get Marks Data
                            |--------------------------------------------------------------------------
                            */

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

                            /*
                            |--------------------------------------------------------------------------
                            | Retotalling Subject Mapping Check
                            |--------------------------------------------------------------------------
                            */

                            $isRetotallingSubject = in_array(
                                $subject->id,
                                $retotallingSubjects[$student->id] ?? []
                            );

                            /*
                            |--------------------------------------------------------------------------
                            | Enrollment Check
                            |--------------------------------------------------------------------------
                            */

                            $isEnrolled = isset($studentSubjects[$student->id]) &&
                                in_array(
                                    $subject->id,
                                    $studentSubjects[$student->id]
                                );

                            /*
                            |--------------------------------------------------------------------------
                            | Original Marks and Grade
                            |--------------------------------------------------------------------------
                            */

                            $obtained = $markData['obtained_marks'] ?? '';
                            $status = $markData['exam_status'] ?? 'present';
                            $grade = $markData['grade'] ?? '';

                            /*
                            |--------------------------------------------------------------------------
                            | Retotalling Revised Marks
                            |--------------------------------------------------------------------------
                            */

                            $revisedMarks = $markData['retotalling_obtained_marks'] ?? null;

                            $retotallingLocked = $markData['retotalling_marks_locked'] ?? 0;

                            /*
                            |--------------------------------------------------------------------------
                            | Final Retotalling Condition
                            |--------------------------------------------------------------------------
                            |
                            | Background AND revised marks will be applied only when:
                            |
                            | 1. Subject is mapped for retotalling.
                            | 2. Revised marks are NOT NULL.
                            | 3. retotalling_marks_locked = 1.
                            |
                            */

                            $hasLockedRetotallingMarks =
                                $isRetotallingSubject &&
                                $revisedMarks !== null &&
                                $retotallingLocked == 1;

                            /*
                            |--------------------------------------------------------------------------
                            | Use Revised Marks Only When Condition Matches
                            |--------------------------------------------------------------------------
                            */

                            if ($hasLockedRetotallingMarks) {
                                $obtained = $revisedMarks;
                            }

                        @endphp

                        <td class="{{ $hasLockedRetotallingMarks ? 'retotalling-subject-cell' : '' }}">

                            @if(!$isEnrolled)

                                <span class="text-muted">NA</span>

                            @elseif($subject->is_nc)

                                {{ $grade ?: '-' }}

                            @else

                                @if($status == 'absent')

                                    <span class="text-danger">AB</span>

                                @else

                                    {{ $obtained !== '' ? $obtained : '-' }}

                                @endif

                            @endif

                        </td>

                    @endforeach

                </tr>

            @endforeach

        </tbody>

    </table>

</div>
