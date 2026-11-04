<style>
    .result-transcript{
        width: 100%;
        display: flex;  
        gap: 2px;
    }
    .result-transcript .half{
        width: 50%;
        padding: 10px;
    }
    .result-transcript table{
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #000;
    }
    table .horizontal-border{
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
    }
    table .horizontal-border th{
        text-align: center;
    }
    table tr td, table tr th{
        padding: 5px;
        font-size: 12px;
    }
    .profile-image {
        display: flex;
        justify-content: flex-end;
    }
    .profile-image img {
        width: 100px;
        height: 100px;
        margin-right: 20px;
    }
    .student-details table,
    .transcript-footer table {
        width: 100%;
        border-collapse: collapse;
    }
    .transcript-footer table thead{
        border-bottom: 1px solid #000;
    }
    .transcript-footer table tfoot .right-text{
        text-align: right;
    }
</style>

<div class="tanscript-table">

    {{-- ================= STUDENT INFO ================= --}}
    <div class="student-info">
        <div class="profile-image">
            <img src="{{ $imagePath }}" alt="Profile Image">
        </div>

        <div class="student-details">
            <table>
                <tr>
                    <td width="30%">Name:</td>
                    <td width="30%"><strong>{{ $student->student_name }}</strong></td>
                    <td width="20%">Year of Admission:</td>
                    <td width="20%">
                        <strong>
                            {{ $student->admission_year ? \Carbon\Carbon::parse($student->admission_year)->format('M Y') : '-' }}
                        </strong>
                    </td>
                </tr>
                <tr>
                    <td>Father's Name:</td>
                    <td><strong>{{ $student->father_name }}</strong></td>
                    <td>Year of Completion:</td>
                    <td>
                        <strong>
                            {{ $student->completion_year 
                                ? \Carbon\Carbon::parse($student->completion_year)->format('M Y') 
                                : '-' 
                            }}
                        </strong>
                    </td>
                </tr>
                <tr>
                    <td>Mother's Name:</td>
                    <td><strong>{{ $student->mother_name }}</strong></td>
                    <td>Result Notification No.:</td>
                    <td><strong>-</strong></td>
                </tr>
                <tr>
                    <td>Admission No:</td>
                    <td><strong>{{ $student->university_roll_no }}</strong></td>
                    <td>Date of Notification:</td>
                    <td><strong></strong></td>
                </tr>
                <tr>
                    <td>Name of the Institute:</td>
                    <td><strong>{{ $student->institute->name ?? '-' }}</strong></td>
                    <td>Medium of Instruction.:</td>
                    <td><strong>English</strong></td>
                </tr>
                <tr>
                    <td>Scholastic Probation(if ever placed):</td>
                    <td><strong>-</strong></td>
                    <td>Eligibility for Merit Certificate:</td>
                    <td><strong>No</strong></td>
                </tr>
                <tr>
                    <td>Dropped from University(if ever):</td>
                    <td><strong>-</strong></td>
                    <td>Date of Re-admission:</td>
                    <td><strong></strong></td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ================= RESULT ================= --}}
    <div class="result-transcript">

        @php
            $semesters = $grouped->values();
        @endphp

        {{-- ✅ IF ONLY 1 SEMESTER --}}
        @if($semesters->count() == 1)

            <div class="half" style="width:100%;">
                <table>
                    <thead>
                        <tr>
                            <th>Title of Course</th>
                            <th>Course no.</th>
                            <th>Credit Hours</th>
                            <th>Credit Points/Grade</th>
                        </tr>
                    </thead>

                    @foreach($semesters as $index => $rows)
                    <tbody>
                        <tr class="horizontal-border">
                            <th colspan="4">
                                {{ $courseType == "Semester Based" ? 'Semester' : 'Year' }} - {{ $rows->first()->semester_id ?? $rows->first()->annual_semester_id }}
                            </th>
                        </tr>

                        @foreach($rows as $row)
                        <tr>
                            <td>{{ $row->subject->name ?? '-' }}</td>
                            <td>{{ $row->subject->subject_code ?? '-' }}</td>
                            <td>{{ $row->subject->credits ?? '-' }}</td>
                            <td>{{ number_format($row->result_gp, 3) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    @endforeach

                </table>
            </div>

        @else

            {{-- ✅ NORMAL CASE (MULTIPLE SEMESTERS SAME AS YOUR CODE) --}}
            @php
                $left = $semesters->slice(0, 4);
                $right = $semesters->slice(4, 4);
            @endphp

            {{-- LEFT SIDE --}}
            <div class="half">
                <table>
                    <thead>
                        <tr>
                            <th>Title of Course</th>
                            <th>Course no.</th>
                            <th>Credit Hours</th>
                            <th>Credit Points/Grade</th>
                        </tr>
                    </thead>

                    @foreach($left as $index => $rows)
                    <tbody>
                        <tr class="horizontal-border">
                            <th colspan="4">Semester-{{ $index + 1 }}</th>
                        </tr>

                        @foreach($rows as $row)
                        <tr>
                            <td>{{ $row->subject->name ?? '-' }}</td>
                            <td>{{ $row->subject->subject_code ?? '-' }}</td>
                            <td>{{ $row->subject->credits ?? '-' }}</td>
                            <td>{{ number_format($row->result_gp, 3) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    @endforeach

                </table>
            </div>

            {{-- RIGHT SIDE --}}
            <div class="half">
                <table>
                    <thead>
                        <tr>
                            <th>Title of Course</th>
                            <th>Course no.</th>
                            <th>Credit Hours</th>
                            <th>Credit Points/Grade</th>
                        </tr>
                    </thead>

                    @foreach($right as $index => $rows)
                    <tbody>
                        <tr class="horizontal-border">
                            <th colspan="4">Semester-{{ $index + 5 }}</th>
                        </tr>

                        @foreach($rows as $row)
                        <tr>
                            <td>{{ $row->subject->name ?? '-' }}</td>
                            <td>{{ $row->subject->subject_code ?? '-' }}</td>
                            <td>{{ $row->subject->credits ?? '-' }}</td>
                            <td>{{ number_format($row->result_gp, 3) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    @endforeach

                </table>
            </div>

        @endif

    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="transcript-footer">
        <table>
            <thead>
                <tr>
                    <td>NSS/NSO/NCC (CNC)</td>
                    <td colspan="3">Grade: Satisfactory</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Credit Hrs</td>
                    <td>{{ $totalCredits }}</td>
                    <td>Total Credit Point</td>
                    <td>{{ number_format($totalPoints, 2) }}</td>
                </tr>
                <tr>
                    <td>OCPA out of 10.000</td>
                    <td>{{ number_format($cgpa, 3) }}</td>
                    <td>Equivalent to</td>
                    <td>{{ number_format($cgpa * 10, 2) }}% of marks</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td>Placed in</td>
                    <td colspan="3">Division</td>
                </tr>
                <tr>
                    <td colspan="2">Ludhiana <br>

                        Dated : ............................
                    </td>
                    <td colspan="2" class="right-text">Registrar</td>
                </tr>
                <tr>
                    <td colspan="4">*Cleared with supplementary examination <br>
                            #Cleared in subsequent semester <br>
                            <hr>
                            Note: For system of credit points and grading see reverse
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

</div>