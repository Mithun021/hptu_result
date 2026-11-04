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
        justify-content: flex-end; /* right side */
    }

    .profile-image img {
        width: 100px;
        height: 100px;
        margin-right: 20px;
    }
    .student-details table {
        width: 100%;
        border-collapse: collapse;
    }
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

    <div class="student-info">
        <div class="profile-image">
            <img src="{{ asset('assets/images/users/user-3.jpg') }}" alt="Profile Image">
        </div>
        <div class="student-details">
            <table>
                <tr>
                    <td width="30%">Name:</td>
                    <td width="30%"><strong>Mithun Kumar</strong></td>
                    <td width="20%">Year of Admission:</td>
                    <td width="20%"><strong>August 2023</strong></td>
                </tr>
                <tr>
                    <td width="30%">Father's Name:</td>
                    <td width="30%"><strong>Mr. Harminder Singh Sekhon</strong></td>
                    <td width="20%">Year of Completion:</td>
                    <td width="20%"><strong>-</strong></td>
                </tr>
                <tr>
                    <td width="30%">Mother's Name:</td>
                    <td width="30%"><strong>Ms. Saranjeet Kaur Sekhon</strong></td>
                    <td width="20%">Result Notification No.:</td>
                    <td width="20%"><strong>-</strong></td>
                </tr>
                <tr>
                    <td width="30%">Admission No:</td>
                    <td width="30%"><strong>L-2023-F-01-B</strong></td>
                    <td width="20%">Date of Notification:</td>
                    <td width="20%"><strong></strong></td>
                </tr>
                <tr>
                    <td width="30%">Name of the Institute:</td>
                    <td width="30%"><strong>College of Fisheries, Ludhiana</strong></td>
                    <td width="20%">Medium of Instruction.:</td>
                    <td width="20%"><strong></strong></td>
                </tr>
                <tr>
                    <td width="30%">Scholastic Probation(if ever placed):</td>
                    <td width="30%"><strong>_</strong></td>
                    <td width="20%">Eligibility for Merit Certificate:</td>
                    <td width="20%"><strong></strong></td>
                </tr>
                <tr>
                    <td width="30%">Dropped from University(if ever):</td>
                    <td width="30%"><strong>_</strong></td>
                    <td width="20%">Date of Re-admission:</td>
                    <td width="20%"><strong></strong></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="result-transcript">

        <!-- LEFT SIDE -->
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

                <!-- Semester I -->
                <tbody>
                    <tr class="horizontal-border">
                        <th colspan="4">Semester-I</th>
                    </tr>

                    @for($i=0;$i<4;$i++)
                    <tr>
                        <td>Fundamentals of Microbiology</td>
                        <td>MB-101</td>
                        <td>3</td>
                        <td>5.100</td>
                    </tr>
                    @endfor
                </tbody>

                <!-- Semester II -->
                <tbody>
                    <tr class="horizontal-border">
                        <th colspan="4">Semester-II</th>
                    </tr>

                    @for($i=0;$i<4;$i++)
                    <tr>
                        <td>Fundamentals of Microbiology</td>
                        <td>MB-101</td>
                        <td>3</td>
                        <td>5.100</td>
                    </tr>
                    @endfor
                </tbody>

                <!-- Semester III -->
                <tbody>
                    <tr class="horizontal-border">
                        <th colspan="4">Semester-III</th>
                    </tr>

                    @for($i=0;$i<4;$i++)
                    <tr>
                        <td>Fundamentals of Microbiology</td>
                        <td>MB-101</td>
                        <td>3</td>
                        <td>5.100</td>
                    </tr>
                    @endfor
                </tbody>

                <!-- Semester IV -->
                <tbody>
                    <tr class="horizontal-border">
                        <th colspan="4">Semester-IV</th>
                    </tr>

                    @for($i=0;$i<4;$i++)
                    <tr>
                        <td>Fundamentals of Microbiology</td>
                        <td>MB-101</td>
                        <td>3</td>
                        <td>5.100</td>
                    </tr>
                    @endfor
                </tbody>

            </table>
        </div>

        <!-- RIGHT SIDE -->
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

                <!-- Semester V -->
                <tbody>
                    <tr class="horizontal-border">
                        <th colspan="4">Semester-V</th>
                    </tr>

                    @for($i=0;$i<4;$i++)
                    <tr>
                        <td>Fundamentals of Microbiology</td>
                        <td>MB-101</td>
                        <td>3</td>
                        <td>5.100</td>
                    </tr>
                    @endfor
                </tbody>

                <!-- Semester VI -->
                <tbody>
                    <tr class="horizontal-border">
                        <th colspan="4">Semester-VI</th>
                    </tr>

                    @for($i=0;$i<4;$i++)
                    <tr>
                        <td>Fundamentals of Microbiology</td>
                        <td>MB-101</td>
                        <td>3</td>
                        <td>5.100</td>
                    </tr>
                    @endfor
                </tbody>

                <!-- Semester VII -->
                <tbody>
                    <tr class="horizontal-border">
                        <th colspan="4">Semester-VII</th>
                    </tr>

                    @for($i=0;$i<4;$i++)
                    <tr>
                        <td>Fundamentals of Microbiology</td>
                        <td>MB-101</td>
                        <td>3</td>
                        <td>5.100</td>
                    </tr>
                    @endfor
                </tbody>

                <!-- Semester VIII -->
                <tbody>
                    <tr class="horizontal-border">
                        <th colspan="4">Semester-VIII</th>
                    </tr>

                    @for($i=0;$i<4;$i++)
                    <tr>
                        <td>Fundamentals of Microbiology</td>
                        <td>MB-101</td>
                        <td>3</td>
                        <td>5.100</td>
                    </tr>
                    @endfor
                </tbody>

            </table>
        </div>

    </div>

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
                    <td>20</td>
                    <td>Total Credit Point</td>
                    <td>654.10</td>
                </tr>
                <tr>
                    <td>OCPA out of 10.000</td>
                    <td>5.451</td>
                    <td>Equivalent to</td>
                    <td>54.51% of marks</td>
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