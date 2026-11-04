<style>
    table.result-table {
        width: 100%;
        border-collapse: collapse;
    }

    table.result-table th,
    table.result-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: center;
        font-size: 10px;
    }

    table.result-table table {
        width: 100%;
        border-collapse: collapse;
        border-spacing: 0;
        margin: 0;
    }

    table.result-table table th,
    table.result-table table td {
        border: 1px solid #ddd;
        padding: 4px;
        font-size: 10px;
    }
</style>

<div class="table-responsive">

    <table class="result-table">

        <thead>
            <tr>
                <td colspan="{{ 3 + ($subjects->count() * 3) }}">
                    
                    <center>
                        <h4>OFFICES OF CONTROLLER OF EXAMINATIONS</h4>
                        <h4>GURU ANGAD DEV VETERINARY & ANIMAL SCIENCES</h4>

                        <p style="font-size: 12px; font-weight : bold;">
                            {{ $exam->exam_resultsheet_name }}
                        </p>
                    </center>

                    <p style="text-align:left; margin:0;">
                        <b>
                            Class :
                            {{ $className }} 
                            {{ $course->name }}

                            @if(isset($branch) && $branch->name != 'NA')
                                / {{ $branch->name }}
                            @endif
                        </b>
                    </p>

                </td>
            </tr>

            <!-- Subject Heading Row -->
            <tr>
                <th rowspan="3">SL</th>
                <th rowspan="3">Student Name</th>
                <th rowspan="3">Admission No</th>

                @foreach($subjects as $subject)
                    <th colspan="3">
                        {{ $subject->type }}
                        <br>
                        Sub : {{ $subject->subject_code }}
                    </th>
                @endforeach
            </tr>

            <!-- Marks Heading Row -->
            <tr>
                @foreach($subjects as $subject)
                    <th>Internal 1</th>
                    <th>Internal 2</th>
                    <th>Internal 3</th>
                @endforeach
            </tr>

            <!-- Max Marks Row -->
            <tr>
                @foreach($subjects as $subject)
                    <th>{{ $subject->internal1 ?? '-' }}</th>
                    <th>{{ $subject->internal2 ?? '-' }}</th>
                    <th>{{ $subject->internal3 ?? '-' }}</th>
                @endforeach
            </tr>

        </thead>

        <tbody id="internalMarksTableBody">

            @foreach($students as $studentIndex => $student)
                <tr>

                    <td>{{ $studentIndex + 1 }}</td>

                    <td>{{ $student->student_name }}</td>

                    <td>{{ $student->university_roll_no }}</td>

                    @foreach($subjects as $subject)

                        @php
                            // ✅ ADDED: Enrollment check
                            $isEnrolled = isset($studentSubjects[$student->id]) &&
                                          in_array($subject->id, $studentSubjects[$student->id]);

                            $internal1 = $marks[$student->id][$subject->id]['Internal 1'] ?? '-';
                            $internal2 = $marks[$student->id][$subject->id]['Internal 2'] ?? '-';
                            $internal3 = $marks[$student->id][$subject->id]['Internal 3'] ?? '-';
                        @endphp

                        @if(!$isEnrolled)
                            {{-- ✅ NOT ENROLLED --}}
                            <td class="text-muted">NA</td>
                            <td class="text-muted">NA</td>
                            <td class="text-muted">NA</td>
                        @else
                            {{-- ✅ NORMAL --}}
                            <td>{!! $internal1 !!}</td>
                            <td>{!! $internal2 !!}</td>
                            <td>{!! $internal3 !!}</td>
                        @endif

                    @endforeach

                </tr>
            @endforeach

        </tbody>

    </table>

</div>