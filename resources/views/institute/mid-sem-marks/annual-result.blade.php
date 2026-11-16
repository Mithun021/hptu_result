<style>
    table{
        width: 100%;
        border: 1px solid #000;
        border-collapse: collapse;
    }
    table tr th, table tr td{
        border: 1px solid #000;
        font-size: 12px;
        padding: 5px;
    }
    .marks-input::-webkit-outer-spin-button,
    .marks-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    
    /* Chrome, Edge, Safari */
    input.obtained-marks[type="number"]::-webkit-inner-spin-button,
    input.obtained-marks[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none !important;
        appearance: none !important;
        margin: 0 !important;
        display: none !important;
    }
    
    /* Firefox */
    input.obtained-marks[type="number"] {
        -moz-appearance: textfield !important;
        appearance: textfield !important;
    }
</style>

<table class="result-table-a4 result-table">
    <thead class="result-header">
        <tr>
            <td colspan="7">
                <center>
                    <h4>CONTROLLER OF EXAMINATION</h4>
                    <h4>GURU ANGAD DEV VETERINARY AND ANIMAL SCIENCES UNIVERSITY, LUDHIANA(INIDA)</h4>
                    <h5>{{ $institute->name ?? 'College Name' }}</h5>

                    <h5>
                        @if($annualSemester)
                            {{ $annualSemester->name }} (Annual)
                        @elseif($semester)
                            {{ $semester->name }} Semester
                        @endif

                        Session {{ $batch->name ?? '' }}
                    </h5>

                    <h5>{{ $exam->mid_sem_exam_name ?? '' }}</h5>
                </center>

                <p>
                    Subject : {{ $subject->name ?? '-' }} - {{ $subject->subject_code ?? '-' }}
                </p>

                <p>
                    <strong>Cr.Hr. :</strong> {{ $subject->credits ?? '-' }}
                    &nbsp;&nbsp;&nbsp;&nbsp;

                    <strong>Maximum Marks :</strong> {{ $maxMarks ?? 0 }}

                    &nbsp;&nbsp;&nbsp;&nbsp;

                    <strong>Marks Type :</strong>
                    {{ request('marks_type') }}
                </p>
            </td>
        </tr>

        <tr>
            <th class="no-export">
                <input type="checkbox" id="select_all_students">
            </th>
            <th class="no-export">#</th>
            <th>Student Name</th>
            <th>University Roll No</th>
            <th>Father Name</th>
            <th>Max Marks</th>
            <th>Obtained Marks</th>
        </tr>
    </thead>

    <tbody>
        @php $num = 0 @endphp
        @forelse($students as $key => $student)

            @if(!in_array($student->id, $studentSubjects))
                @continue
            @endif

            @php
                $marksRecord = $allMarks[$student->id] ?? null;
                $isAbsent = $marksRecord && $marksRecord->exam_status === 'absent';
                $isEnrolled = in_array($student->id, $studentSubjects);
            @endphp

            <tr class="{{ !$isEnrolled ? 'no-export' : '' }}">
                <td class="no-export">
                    @if($isEnrolled)
                        <input type="checkbox"
                            class="student-checkbox"
                            data-id="{{ $marksRecord->id ?? '' }}"
                            {{ $marksRecord && $marksRecord->university_forword == 0 ? '' : 'disabled' }}
                            style="{{ $marksRecord && $marksRecord->university_forword == 0 ? '' : 'display:none;' }}">
                    @endif
                    {{--  @if($marksRecord && $marksRecord->university_forword == 0 && $isEnrolled)
                        <input type="checkbox"
                            class="student-checkbox"
                            data-id="{{ $marksRecord->id }}">
                    @endif
                    @if(
                        $marksRecord &&
                        $isEnrolled &&
                        (
                            auth('institute')->user()->account_type === 'institute'
                            || $marksRecord->university_forword == 0
                        )
                    )
                        <input type="checkbox"
                            class="student-checkbox"
                            data-id="{{ $marksRecord->id }}">
                    @endif  --}}
                </td>

                <td class="no-export">{{ ++$num }}</td>
                <td>{{ $student->student_name }}</td>
                <td>{{ $student->university_roll_no }}</td>
                <td>{{ $student->father_name }}</td>
                <td>{{ $maxMarks }}</td>
                
                {{-- <td>
                    @if(!$isEnrolled)

                        <span class="text-muted">NA</span>
                    @elseif($subject->is_nc == 1)

                        @if($marksRecord && $marksRecord->is_locked == 1)
                            <span class="fw-bold text-primary">
                                {{ $marksRecord->grade ?? '-' }}
                            </span>

                            @if($marksRecord->university_forword == 1)
                                <div class="no-export">
                                    <span class="badge bg-warning mt-1">
                                        Forwarded to University
                                    </span>
                                </div>
                            @endif

                        @else
                            <div class="no-export">

                                <label class="me-2">
                                    <input type="radio"
                                        name="grade_{{ $student->id }}"
                                        value="Satisfactory"
                                        class="grade-radio"
                                        data-student="{{ $student->id }}"
                                        {{ ($marksRecord->grade ?? '') == 'Satisfactory' ? 'checked' : '' }}>
                                    Satisfactory
                                </label>

                                <label>
                                    <input type="radio"
                                        name="grade_{{ $student->id }}"
                                        value="Unsatisfactory"
                                        class="grade-radio"
                                        data-student="{{ $student->id }}"
                                        {{ ($marksRecord->grade ?? '') == 'Unsatisfactory' ? 'checked' : '' }}>
                                    Unsatisfactory
                                </label>

                            </div>

                        @endif

                    @elseif($marksRecord && $marksRecord->is_locked == 1)

                        <span class="fw-bold text-success">
                            {{ $marksRecord->obtained_marks ?? 0 }}
                        </span>

                        @if($marksRecord->university_forword == 1)
                            <div class="no-export">
                                <span class="badge bg-warning mt-1">
                                    Forwarded to University
                                </span>
                            </div>
                        @endif

                    @else

                        <div>
                            <input type="number"
                                class="form-control form-control-sm obtained-marks no-export"
                                data-student="{{ $student->id }}"
                                data-max="{{ $maxMarks }}"
                                max="{{ $maxMarks }}"
                                min="0"
                                value="{{ $isAbsent ? 0 : ($marksRecord->obtained_marks ?? '') }}"
                                {{ $isAbsent ? 'readonly' : '' }}>

                            <div class="form-check mt-1 no-export">
                                <input type="checkbox"
                                    class="form-check-input mark-absent"
                                    data-student="{{ $student->id }}"
                                    {{ $isAbsent ? 'checked' : '' }}>

                                <label class="form-check-label small text-danger">
                                    Mark Absent
                                </label>
                            </div>
                        </div>

                    @endif
                </td> --}}

                <td>
                    @if(!$isEnrolled)

                        <span class="text-muted">NA</span>

                    @elseif($marksRecord && $marksRecord->is_locked == 1)

                        <span class="fw-bold text-success">
                            {{ $marksRecord->obtained_marks ?? 0 }}
                        </span>

                        @if($marksRecord->university_forword == 1)
                            <div class="no-export">
                                <span class="badge bg-warning mt-1">
                                    Forwarded to University
                                </span>
                            </div>
                        @endif

                    @else

                        <div>
                            <input type="number"
                                class="form-control form-control-sm obtained-marks no-export"
                                data-student="{{ $student->id }}"
                                data-max="{{ $maxMarks }}"
                                max="{{ $maxMarks }}"
                                min="0"
                                value="{{ $isAbsent ? 0 : ($marksRecord->obtained_marks ?? '') }}"
                                {{ $isAbsent ? 'readonly' : '' }}>

                            <div class="form-check mt-1 no-export">
                                <input type="checkbox"
                                    class="form-check-input mark-absent"
                                    data-student="{{ $student->id }}"
                                    {{ $isAbsent ? 'checked' : '' }}>

                                <label class="form-check-label small text-danger">
                                    Mark Absent
                                </label>
                            </div>
                        </div>

                    @endif
                </td>


            </tr>

        @empty
            <tr>
                <td colspan="7" class="text-center text-danger">
                    No Students Found
                </td>
            </tr>
        @endforelse
    </tbody>

    <tfoot>
        <tr>
            <td colspan="3">
                Signature of Instructor(s)
            </td>
            <td colspan="4">
                HOD
            </td>
        </tr>
    </tfoot>
</table>