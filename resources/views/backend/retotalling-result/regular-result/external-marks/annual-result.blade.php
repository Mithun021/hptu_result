
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

                        <th colspan="4">Result</th>

                    @else

                        <th id="for-nc-hide">
                            Paper 1
                            <br>
                            Max : {{ $subject->paper1 ? $subject->paper1 : '-' }}
                        </th>

                        <th id="for-nc-hide">
                            Paper 2
                            <br>
                            Max : {{ $subject->paper2 ? $subject->paper2 : '-' }}
                        </th>

                        <th id="for-nc-hide">
                            Paper 1
                            <br>
                            Max : {{ $subject->practical_paper1 ? $subject->practical_paper1 : '-' }}
                        </th>

                        <th id="for-nc-hide">
                            Paper 2
                            <br>
                            Max : {{ $subject->practical_paper2 ? $subject->practical_paper2 : '-' }}
                        </th>

                    @endif

                @endforeach

            </tr>

        </thead>

        <tbody>

            @foreach($students as $studentIndex => $student)

                <tr>

                    <td>{{ $studentIndex + 1 }}</td>

                    <td>{{ $student->student_name }}</td>

                    <td>{{ $student->university_roll_no }}</td>

                    @foreach($subjects as $subject)

                        @php
                            $isEnrolled = isset($studentSubjects[$student->id]) &&
                                in_array($subject->id, $studentSubjects[$student->id]);

                            $isRetotallingSubject = in_array(
                                $subject->id,
                                $retotallingSubjects[$student->id] ?? []
                            );
                        @endphp

                        @if($subject->is_nc)

                            @php
                                $ncData = $marks[$student->id][$subject->id]['external'] ?? null;

                                $grade = trim($ncData['grade'] ?? '');
                            @endphp

                            <td colspan="4">

                                @if(!$isEnrolled)

                                    <span class="text-muted">NA</span>

                                @else

                                    {{ $grade !== '' ? $grade : '-' }}

                                @endif

                            </td>

                        @else

                            {{-- THEORY PAPER 1 --}}
                            @php
                                $data = $marks[$student->id][$subject->id]['theory_paper1'] ?? null;

                                $obtained = $data['obtained_marks'] ?? '';
                                $status = $data['exam_status'] ?? 'present';

                                $revisedMarks = $data['retotalling_obtained_marks'] ?? null;
                                $retotallingLocked = $data['retotalling_marks_locked'] ?? 0;

                                $hasLockedRetotallingMarks =
                                    $isRetotallingSubject &&
                                    $revisedMarks !== null &&
                                    $retotallingLocked == 1;

                                if ($hasLockedRetotallingMarks) {
                                    $obtained = $revisedMarks;
                                }
                            @endphp

                            <td id="for-nc-hide"
                                class="{{ $hasLockedRetotallingMarks ? 'retotalling-subject-cell' : '' }}">

                                @if(!$subject->theory_credits)

                                    -

                                @elseif(!$isEnrolled)

                                    NA

                                @else

                                    {{ $status == 'absent' && !$hasLockedRetotallingMarks ? 'AB' : ($obtained !== '' ? $obtained : '-') }}

                                @endif

                            </td>


                            {{-- THEORY PAPER 2 --}}
                            @php
                                $data = $marks[$student->id][$subject->id]['theory_paper2'] ?? null;

                                $obtained = $data['obtained_marks'] ?? '';
                                $status = $data['exam_status'] ?? 'present';

                                $revisedMarks = $data['retotalling_obtained_marks'] ?? null;
                                $retotallingLocked = $data['retotalling_marks_locked'] ?? 0;

                                $hasLockedRetotallingMarks =
                                    $isRetotallingSubject &&
                                    $revisedMarks !== null &&
                                    $retotallingLocked == 1;

                                if ($hasLockedRetotallingMarks) {
                                    $obtained = $revisedMarks;
                                }
                            @endphp

                            <td id="for-nc-hide"
                                class="{{ $hasLockedRetotallingMarks ? 'retotalling-subject-cell' : '' }}">

                                @if(!$subject->theory_credits)

                                    -

                                @elseif(!$isEnrolled)

                                    NA

                                @else

                                    {{ $status == 'absent' && !$hasLockedRetotallingMarks ? 'AB' : ($obtained !== '' ? $obtained : '-') }}

                                @endif

                            </td>


                            {{-- PRACTICAL PAPER 1 --}}
                            @php
                                $data = $marks[$student->id][$subject->id]['practical_paper1'] ?? null;

                                $obtained = $data['obtained_marks'] ?? '';
                                $status = $data['exam_status'] ?? 'present';
                            @endphp

                            <td id="for-nc-hide">

                                @if(!$subject->practical_credits)

                                    -

                                @elseif(!$isEnrolled)

                                    NA

                                @else

                                    {{ $status == 'absent' ? 'AB' : ($obtained !== '' ? $obtained : '-') }}

                                @endif

                            </td>


                            {{-- PRACTICAL PAPER 2 --}}
                            @php
                                $data = $marks[$student->id][$subject->id]['practical_paper2'] ?? null;

                                $obtained = $data['obtained_marks'] ?? '';
                                $status = $data['exam_status'] ?? 'present';
                            @endphp

                            <td id="for-nc-hide">

                                @if(!$subject->practical_credits)

                                    -

                                @elseif(!$isEnrolled)

                                    NA

                                @else

                                    {{ $status == 'absent' ? 'AB' : ($obtained !== '' ? $obtained : '-') }}

                                @endif

                            </td>

                        @endif

                    @endforeach

                </tr>

            @endforeach

        </tbody>

    </table>

</div>
