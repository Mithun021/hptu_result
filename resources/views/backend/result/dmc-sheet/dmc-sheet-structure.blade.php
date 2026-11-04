<style>
    /* ================================
       DMC SHEET
    ================================= */
    .compartment-sheet {
        width: 100%;
        position: relative;
        overflow: hidden;
    }

    /* ================================
       WATERMARK
    ================================= */
    .compartment-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 500px;
        height: 500px;
        transform: translate(-50%, -50%);
        background-image: url("{{ asset('assets/images/watermark.png') }}");
        background-repeat: no-repeat;
        background-position: center center;
        background-size: contain;
        opacity: 0.08;
        pointer-events: none;
        z-index: 0;
    }

    .compartment-watermark img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }

    /* ================================
       TABLE
    ================================= */
    table.compartment-table {
        position: relative;
        width: 100%;
        border: 0;
        border-collapse: collapse;
        font-size: 14px;
        z-index: 1;
    }

    table.compartment-table thead,
    table.compartment-table tbody,
    table.compartment-table tfoot {
        position: relative;
        z-index: 2;
    }

    /* ================================
       HEADER
    ================================= */
    table.compartment-table thead tr td,
    table.compartment-table thead tr th {
        font-weight: bold;
        border: 1px solid #000 !important;
        border-collapse: collapse;
        font-size: 12px;
        text-align: center;
        vertical-align: middle !important;
    }

    /* ================================
       NO BORDER HEADER ROW
    ================================= */
    table.compartment-table thead tr.no-border,
    table.compartment-table thead tr.no-border td,
    table.compartment-table thead tr.no-border th,
    table.compartment-table .no-border {
        border: none !important;
    }

    /* ================================
       HEADER INFORMATION
    ================================= */
    table.compartment-table thead .header_info {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
    }

    table.compartment-table thead .header_info .personal-detail {
        width: 50%;
        font-weight: inherit;
        text-align: left;
    }

    /* ================================
       BODY
    ================================= */
    table.compartment-table tbody {
        border: 1px solid #000 !important;
    }

    table.compartment-table tbody tr td {
        border: 1px solid #000 !important;
        text-align: center;
        vertical-align: middle !important;
        font-size: 12px;
    }

    /* ================================
       FOOTER
    ================================= */
    table.compartment-table tfoot tr td {
        border: none !important;
        vertical-align: middle !important;
    }

    table.compartment-table tfoot tr.bottom-signature td {
        padding-top: 60px;
    }

    /* ================================
       TEXT ALIGNMENT
    ================================= */
    .text-left {
        text-align: left !important;
    }

    table.compartment-table tfoot tr.bottom-signature td.text-right {
        text-align: right !important;
    }

    /* ================================
       MARKS HEADER
    ================================= */
    tr.top-bottom-border {
        border: 1px solid #000 !important;
        text-align: center;
        vertical-align: middle !important;
    }

    tr.top-bottom-border td,
    tr.top-bottom-border th {
        border: 1px solid #000 !important;
        text-align: center;
        vertical-align: middle !important;
    }

    /* ================================
       FLEX
    ================================= */
    .flex-between {
        display: flex;
        justify-content: space-between;
    }

    /* ================================
       COLLEGE LOGO
    ================================= */
    .clg-logo {
        width: 100px;
        height: auto;
        position: relative;
        top: 0;
        left: 0;
        margin: 10px auto;
    }

    /* ================================
       MARGIN
    ================================= */
    .margin-b25 {
        margin-bottom: 25px;
    }
</style>

<div id="dmc-print-container">
    <div class="compartment-sheet table-responsive">

        {{-- WATERMARK --}}
        <div class="compartment-watermark">
            <img src="{{ asset('assets/images/watermark.png') }}" alt="Watermark">
        </div>

        {{-- DMC TABLE --}}
        <table class="table compartment-table compartment-result-table">

            <thead class="table-header">

                {{-- HEADER INFORMATION --}}
                <tr class="no-border">
                    <td colspan="9" class="no-border">

                        <br>

                        <div class="flex-between">
                            <p>
                                Sr. No.
                                <span>____________________</span>
                            </p>

                            <p>
                                Admission No.
                                <span>____________________</span>
                            </p>
                        </div>

                        <center>

                            <h4>
                                GURU ANGAD DEV VETERINARY AND ANIMAL
                                SCIENCES UNIVERSITY, LUDHIANA (INDIA)
                            </h4>

                            <img
                                src="{{ asset('assets/images/watermark.png') }}"
                                alt="College Logo"
                                class="clg-logo"
                            >

                            <h4 class="margin-b25">
                                DETAILED MARKS CERTIFICATE
                            </h4>

                            <h4 class="margin-b25">
                                FIRST PROFESSIONAL (B.V.SC. & A.H.) 2025
                            </h4>

                            <h4 class="margin-b25">
                                College Name Display...........
                            </h4>

                        </center>

                        {{-- PERSONAL DETAILS --}}
                        <div class="header_info">
                            <div class="personal-detail">
                                <span>Name:</span>
                                <b>Mithun Kumar</b>
                            </div>

                            <div class="personal-detail">
                                <span>Batch:</span>
                                <b>2024</b>
                            </div>
                        </div>

                        <div class="header_info">
                            <div class="personal-detail">
                                <span>Father's Name:</span>
                                <b>Sahdeo Mahto</b>
                            </div>

                            <div class="personal-detail">
                                <span>Mother's Name:</span>
                                <b>Ranjana Mahto</b>
                            </div>
                        </div>

                    </td>
                </tr>

                {{-- TABLE HEADER - ROW 1 --}}
                <tr class="top-bottom-border">

                    <th rowspan="3">
                        Subject
                    </th>

                    <th rowspan="3">
                        Credit
                        <br>
                        Hrs.
                    </th>

                    <th colspan="4">
                        Marks Obtained
                    </th>

                    <th rowspan="3">
                        Total
                        <br>
                        100
                    </th>

                    <th rowspan="3">
                        Grade
                        <br>
                        Points
                        <br>
                        (10 Points
                        <br>
                        Basic)
                    </th>

                    <th rowspan="3">
                        Credit
                        <br>
                        Points
                    </th>

                </tr>

                {{-- TABLE HEADER - ROW 2 --}}
                <tr class="top-bottom-border">

                    <th colspan="2">
                        Internal Assessment
                    </th>

                    <th colspan="2">
                        Annual
                    </th>

                </tr>

                {{-- TABLE HEADER - ROW 3 --}}
                <tr class="top-bottom-border">

                    <th>
                        First
                        <br>
                        10
                    </th>

                    <th>
                        Second
                        <br>
                        10
                    </th>

                    <th>
                        Theory
                        <br>
                        40
                    </th>

                    <th>
                        Practical
                        <br>
                        40
                    </th>

                </tr>

            </thead>

            {{-- TABLE BODY --}}
            <tbody>

                <tr>
                    <td class="text-left">
                        Livestock Production Management
                    </td>

                    <td>4+3</td>
                    <td>10.12</td>
                    <td>2.86</td>
                    <td>17.10</td>
                    <td>13.34</td>
                    <td>28.98</td>
                    <td>-</td>
                    <td>Fail</td>
                </tr>

                <tr>
                    <td class="text-left">
                        Livestock Production Management
                    </td>

                    <td>4+3</td>
                    <td>10.12</td>
                    <td>2.86</td>
                    <td>17.10</td>
                    <td>13.34</td>
                    <td>28.98</td>
                    <td>-</td>
                    <td>Fail</td>
                </tr>

                <tr>
                    <td class="text-left">
                        Livestock Production Management
                    </td>

                    <td>4+3</td>
                    <td>10.12</td>
                    <td>2.86</td>
                    <td>17.10</td>
                    <td>13.34</td>
                    <td>28.98</td>
                    <td>-</td>
                    <td>Fail</td>
                </tr>

                <tr>
                    <td class="text-left">
                        Non Credit Course 1
                    </td>

                    <td>
                        0+1
                        <br>
                        (NC)
                    </td>

                    <td colspan="7">
                        Satisfactory
                    </td>
                </tr>

                <tr>
                    <td class="text-left">
                        Non Credit Course 2
                    </td>

                    <td>
                        0+1
                        <br>
                        (NC)
                    </td>

                    <td colspan="7">
                        Satisfactory
                    </td>
                </tr>

            </tbody>

            {{-- FOOTER --}}
            <tfoot>

                <tr>
                    <td colspan="9">

                        <div class="flex-between">
                            <p>CURRENT : Total Credit Hrs: 18</p>
                            <p>Total Credit Points Earned: 180</p>
                            <p>GPA: 7.811/10.000</p>
                        </div>

                        <br>
                        &nbsp;&nbsp;&nbsp; RESULT :
                        <b><b>Eligible for compartment </b>in Vertenary Anatomy</b>
                    </td>
                </tr>

                <tr class="bottom-signature">

                    <td colspan="3">
                        Date: 15-1-2025
                    </td>

                    <td colspan="6" class="text-right">
                        Assistant Registrar(Academic)
                        <br>
                        for Registrar
                    </td>

                </tr>

            </tfoot>

        </table>

    </div>

</div>
