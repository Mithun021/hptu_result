<style>

    .result-table-a4 {
        width: 100%;
        border-collapse: collapse;
        font-family: Arial, sans-serif;
    }

    .result-table-a4 th,
    .result-table-a4 td {
        border: 1px solid #ddd;
        padding: 8px;
        font-size: 12px;
        text-align: center;
    }

    /*
    |--------------------------------------------------------------------------
    | Table Header
    |--------------------------------------------------------------------------
    */

    .result-table-a4 thead th {
        background: #f5f5f5;
        font-weight: 600;
    }

    /*
    |--------------------------------------------------------------------------
    | Table Footer
    |--------------------------------------------------------------------------
    */

    .result-table-a4 tfoot td {
        font-weight: 600;
        padding: 25px 8px 10px;
        text-align: center;
        vertical-align: bottom;
    }

    .result-table-a4 tfoot td::before {
        content: "";
        display: block;
        height: 40px;
    }

    /*
    |--------------------------------------------------------------------------
    | Result Header
    |--------------------------------------------------------------------------
    */

    .result-header h4 {
        margin: 2px 0;
    }

    .result-header p {
        margin: 4px 0;
        text-align: left;
    }

    td.align-left{
        text-align: left !important;
    }
    td.align-right{
        text-align: right !important;
    }

    .course-info{
        display:flex;
        justify-content:space-between;
        margin-bottom:6px;
        font-size:12px;
    }

</style>


<div class="table-responsive">

    <table class="result-table-a4">

        <thead>

            {{-- ==========================================================
                 RESULT HEADER
            =========================================================== --}}

            <tr>

                <td colspan="4" class="result-header">

                    <center>

                        <h4>
                            OFFICE OF THE CONTROLLER OF EXAMINATIONS
                        </h4>

                        <h4>
                            GURU ANGAD DEV VETERINARY AND ANIMAL SCIENCE UNIVERSITY,
                            LUDHIANA (INDIA)
                        </h4>

                        <h4>
                            Result of Final Examination
                            @if($resultSheets->isNotEmpty())
                                {{ $resultSheets->first()->subject?->subject_code ?? '' }}
                            @endif

                            @if($resultSheets->isNotEmpty())
                                For
                                {{ $resultSheets->first()->course?->name ?? '' }}

                                @if(
                                    !empty($resultSheets->first()->branch?->name) &&
                                    $resultSheets->first()->branch?->name !== 'NA'
                                )
                                    - {{ $resultSheets->first()->branch?->name }}
                                @endif
                            @endif

                            @if($resultSheets->isNotEmpty())
                                {{ $resultSheets->first()->exam?->description ?? '' }}
                            @endif
                        </h4>

                    </center>


                    {{-- ==================================================
                         INSTITUTE
                    =================================================== --}}

                    @if($resultSheets->isNotEmpty())

                    <br>

                    <div class="course-info">
                        <p style="margin-bottom: 0px">

                            <b>
                                {{ $resultSheets->first()->course?->name ?? '' }}

                                @if(
                                    !empty($resultSheets->first()->branch?->name) &&
                                    $resultSheets->first()->branch?->name !== 'NA'
                                )
                                    |
                                    {{ $resultSheets->first()->branch?->name }}
                                @endif

                                @if(
                                    !empty($resultSheets->first()->batch?->name)
                                )
                                    |
                                    Batch :
                                    {{ $resultSheets->first()->batch?->name }}
                                @endif
                            </b>

                        </p>

                        @if(!empty($resultSheets->first()->institute?->name))

                            <p style="margin-bottom: 0px">
                                <b>
                                    College :
                                    {{ $resultSheets->first()->institute?->name }}
                                </b>
                            </p>

                        @endif
                    </div>




                        {{-- ==============================================
                             SUBJECT
                        =============================================== --}}

                        {{-- <p>

                            <b>
                                Subject :
                                {{ $resultSheets->first()->subject?->name ?? '' }}
                            </b>

                        </p> --}}

                    @endif

                </td>

            </tr>


            {{-- ==========================================================
                 TABLE COLUMN HEADER
            =========================================================== --}}

            <tr>

                <th>
                    #
                </th>

                <th>
                    Admission Number / Roll No
                </th>

                <th>
                    Name of the Students
                </th>

                <th>
                    Result
                </th>

            </tr>

        </thead>


        <tbody>

            @forelse($resultSheets as $index => $result)

                <tr>

                    {{-- ==============================================
                         CHECKBOX + SERIAL NUMBER
                    =============================================== --}}

                    <td>

                        {{ $index + 1 }}

                    </td>


                    {{-- ==============================================
                         ROLL / ADMISSION NUMBER
                    =============================================== --}}

                    <td>

                        {{ $result->student?->university_roll_no
                            ?? $result->student?->admission_no
                            ?? $result->student?->roll_no
                            ?? '-' }}

                    </td>


                    {{-- ==============================================
                         STUDENT NAME
                    =============================================== --}}

                    <td class="align-left">

                        {{ $result->student?->student_name ?? '-' }}

                    </td>


                    {{-- ==============================================
                         ONLY NC GRADE
                    =============================================== --}}

                    <td class="align-left">

                        {{ $result->grade ?? '-' }}

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="4">

                        <div class="alert alert-warning mb-0 text-center">

                            No NC course result found for the selected criteria.

                        </div>

                    </td>

                </tr>

            @endforelse

        </tbody>


        {{-- ==========================================================
             FOOTER
        =========================================================== --}}

        @if($resultSheets->isNotEmpty())

            <tfoot>

                <tr>

                    <td colspan="4">

                        <div class="course-info">
                            <p>
                                Prepared by

                                <br><br><br>

                                Checked by
                            </p>
                            <p>Controller of Examinations</p>
                            <p>
                                Registrar, Guru Angad Dev Veterinary <br>
                                and Animal Science University
                            </p>
                        </div>

                    </td>

                </tr>

                {{-- <tr>

                    <td class="align-left">

                        Prepared by

                        <br><br><br>

                        Checked by

                    </td>

                    <td>
                        Controller of Examinations
                    </td>

                    <td colspan="2" class="align-right">

                        Registrar, Guru Angad Dev Veterinary <br>
                        and Animal Science University

                    </td>

                </tr> --}}

            </tfoot>

        @endif

    </table>

</div>
