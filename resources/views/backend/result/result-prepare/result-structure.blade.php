
<style>
    /* =========================================
       RESULT SHEET WRAPPER
    ========================================= */
    .result-sheet {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        color: #000;
        -webkit-overflow-scrolling: touch;
    }

    /* =========================================
       MAIN TABLE
    ========================================= */
    .result-sheet table {
        width: max-content;
        min-width: 100%;
        table-layout: fixed;
        border: 1px solid #000;
        border-collapse: collapse;
        border-spacing: 0;
        font-family: Arial, sans-serif;
        font-size: 9px;
        color: #000;
    }

    .result-sheet table th,
    .result-sheet table td {
        border: 1px solid #000;
        border-collapse: collapse;
        padding: 2px 3px;
        text-align: center;
        vertical-align: middle;
        line-height: 1.2;
        overflow-wrap: break-word;
    }

    .result-sheet table th {
        font-weight: normal;
    }

    /* =========================================
       FIXED COLUMN WIDTHS
       25 COLUMNS TOTAL

       1-5   : Student Details
       6-17  : Subject Details
       18-25 : Result Details
    ========================================= */

    /* Student Details */
    .result-sheet col.sn-col {
        width: 26px;
    }

    .result-sheet col.roll-col {
        width: 76px;
    }

    .result-sheet col.college-col {
        width: 76px;
    }

    .result-sheet col.name-col {
        width: 78px;
    }

    .result-sheet col.father-col {
        width: 78px;
    }

    /* Subject Columns */
    .result-sheet col.external-col {
        width: 24px;
    }

    .result-sheet col.internal-col {
        width: 24px;
    }

    .result-sheet col.total-col {
        width: 25px;
    }

    .result-sheet col.grade-col {
        width: 29px;
    }

    /* Result Summary Columns */
    .result-sheet col.result-col {
        width: 42px;
    }

    .result-sheet col.credit-col {
        width: 27px;
    }

    .result-sheet col.grade-point-col {
        width: 27px;
    }

    .result-sheet col.sgpa-col {
        width: 30px;
    }

    .result-sheet col.previous-status-col {
        width: 34px;
    }

    .result-sheet col.remark-col {
        width: 42px;
    }

    /* =========================================
       VERTICAL HEADINGS
    ========================================= */
    .result-sheet .vertical-text {
        writing-mode: vertical-rl;
        transform: rotate(180deg);
        white-space: nowrap;
        display: inline-block;
        line-height: 1;
    }

    /* External / Internal / Total / Grade */
    .result-sheet .subject-heading {
        height: 58px;
        padding: 2px;
        font-size: 8px;
    }

    /* Result summary vertical headings */
    .result-sheet .result-heading {
        min-width: 0;
        padding: 3px 2px;
        font-size: 8px;
        white-space: nowrap;
    }

    /* =========================================
       SUBJECT INFORMATION
    ========================================= */
    .result-sheet .subject-group {
        font-size: 9px;
        padding: 4px 2px;
        white-space: normal;
        overflow-wrap: break-word;
        line-height: 1.2;
    }

    .result-sheet .subject-name {
        font-size: 8px;
        padding: 4px 2px;
        height: 55px;
        white-space: normal;
        overflow-wrap: break-word;
    }

    .result-sheet .subject-marks {
        height: 17px;
        font-size: 8px;
        padding: 2px 1px;
    }

    /* =========================================
       STUDENT DETAILS
    ========================================= */
    .result-sheet .student-heading {
        font-size: 8px;
        white-space: normal;
        padding: 4px 2px;
    }

    .result-sheet .student-cell {
        font-size: 8px;
        padding: 3px 2px;
        overflow-wrap: anywhere;
    }

    .result-sheet .roll-cell {
        white-space: nowrap;
        font-size: 7px;
    }

    /* =========================================
       BODY / MARKS
    ========================================= */
    .result-sheet tbody tr td {
        height: 75px;
        vertical-align: top;
        padding: 4px 2px;
    }

    .result-sheet .marks-cell {
        font-size: 8px;
        text-align: center;
    }

    .result-sheet .grade-cell {
        font-size: 8px;
        line-height: 1.3;
        text-align: center;
    }

    .result-sheet .mini-text {
        font-size: 6px;
        line-height: 1.2;
    }

    .result-sheet .failed-bg {
        background-color: #fd4242;
        color: #fff;
    }

    .result-sheet .text-danger {
        color: #dc3545;
    }

    /* =========================================
       RESULT SUMMARY
    ========================================= */
    .result-sheet .result-cell {
        font-size: 8px;
        text-align: center;
        white-space: nowrap;
    }

    /* =========================================
       TABLE HEADER ROW HEIGHTS
    ========================================= */
    .result-sheet thead tr:first-child th,
    .result-sheet thead tr:nth-child(2) th,
    .result-sheet thead tr:nth-child(3) th {
        height: 17px;
        font-size: 9px;
    }

    .result-sheet thead tr:nth-child(7) th,
    .result-sheet thead tr:nth-child(8) th {
        height: 17px;
    }

    .result-sheet thead tr:last-child th {
        height: 20px;
    }

    /* =========================================
       RESPONSIVE
    ========================================= */
    @media screen and (max-width: 768px) {
        .result-sheet table {
            font-size: 8px;
        }
    }

    @media print {
        .result-sheet {
            overflow: visible;
        }

        .result-sheet table {
            width: 100%;
        }
    }
</style>


<div class="result-sheet">

    <table>

        <!-- =====================================
             FIXED COLUMN WIDTH DEFINITIONS
        ====================================== -->
        <colgroup>

            <!-- Student Details: 5 Columns -->
            <col class="sn-col">
            <col class="roll-col">
            <col class="college-col">
            <col class="name-col">
            <col class="father-col">

            <!-- Subject 1: 4 Columns -->
            <col class="external-col">
            <col class="internal-col">
            <col class="total-col">
            <col class="grade-col">

            <!-- Subject 2: 4 Columns -->
            <col class="external-col">
            <col class="internal-col">
            <col class="total-col">
            <col class="grade-col">

            <!-- Subject 3: 4 Columns -->
            <col class="external-col">
            <col class="internal-col">
            <col class="total-col">
            <col class="grade-col">

            <!-- Result Summary: 8 Columns -->
            <col class="result-col">
            <col class="credit-col">
            <col class="grade-point-col">
            <col class="sgpa-col">
            <col class="credit-col">
            <col class="grade-point-col">
            <col class="sgpa-col">
            <col class="previous-status-col">
            <col class="remark-col">

        </colgroup>


        <thead>

            <!-- UNIVERSITY NAME -->
            <tr>
                <th colspan="26">
                    Himachal Pradesh Technical University, Hamirpur
                </th>
            </tr>

            <!-- EXAMINATION SESSION -->
            <tr>
                <th colspan="26">
                    RESULT TABULATION SHEET OF EXAMINATION SESSION, MAY/JUNE, 2024
                </th>
            </tr>

            <!-- COURSE DETAILS -->
            <tr>
                <th colspan="26">
                    MASTER OF SCIENCE IN YOGA STUDIES | NEP | NA | SEM - II || Regular
                </th>
            </tr>


            <!-- SUBJECT GROUPS + RESULT HEADINGS -->
            <tr>

                <th colspan="5">
                    Name & Particular of Candidate Separate
                </th>

                <th colspan="4">
                    ENV ID-6201(i)
                </th>

                <th colspan="4">
                    IKS-6200
                </th>

                <th colspan="4">
                    MBA ID-6001(ii)
                </th>

                <th rowspan="6">
                    Result
                </th>

                <th rowspan="6">
                    <span class="vertical-text result-heading">
                        Total Current Credit Point
                    </span>
                </th>

                <th rowspan="6">
                    <span class="vertical-text result-heading">
                        Current Grade Point Earn
                    </span>
                </th>

                <th rowspan="6">
                    <span class="vertical-text result-heading">
                        SGPA
                    </span>
                </th>

                <th rowspan="6">
                    <span class="vertical-text result-heading">
                        Previous Credit Point
                    </span>
                </th>

                <th rowspan="6">
                    <span class="vertical-text result-heading">
                        Previous Grade Point Earn
                    </span>
                </th>

                <th rowspan="6">
                    <span class="vertical-text result-heading">
                        CGPA
                    </span>
                </th>

                <th rowspan="6">
                    <span class="vertical-text result-heading">
                        Previous Result Status
                    </span>
                </th>

                <th rowspan="6">
                    <span class="vertical-text result-heading">
                        Remark
                    </span>
                </th>

            </tr>


            <!-- SUBJECT NAMES -->
            <tr>

                <th rowspan="2" colspan="5"></th>

                <th colspan="4" class="subject-name">
                    BASIC CONCEPT OF ENVIRONMENT SCIENCE-Theory
                </th>

                <th colspan="4" class="subject-name">
                    INDIAN KNOWLEDGE SYSTEM-Theory
                </th>

                <th colspan="4" class="subject-name">
                    ENTREPRENEURSHIP AND START-UP MANAGEMENT-Theory
                </th>

            </tr>


            <!-- EXTERNAL / INTERNAL / TOTAL / GRADE -->
            <tr>

                <!-- Subject 1 -->
                <th class="subject-heading">
                    <span class="vertical-text">External</span>
                </th>

                <th class="subject-heading">
                    <span class="vertical-text">Internal</span>
                </th>

                <th class="subject-heading">
                    <span class="vertical-text">Total</span>
                </th>

                <th class="subject-heading">
                    <span class="vertical-text">Grade</span>
                </th>


                <!-- Subject 2 -->
                <th class="subject-heading">
                    <span class="vertical-text">External</span>
                </th>

                <th class="subject-heading">
                    <span class="vertical-text">Internal</span>
                </th>

                <th class="subject-heading">
                    <span class="vertical-text">Total</span>
                </th>

                <th class="subject-heading">
                    <span class="vertical-text">Grade</span>
                </th>


                <!-- Subject 3 -->
                <th class="subject-heading">
                    <span class="vertical-text">External</span>
                </th>

                <th class="subject-heading">
                    <span class="vertical-text">Internal</span>
                </th>

                <th class="subject-heading">
                    <span class="vertical-text">Total</span>
                </th>

                <th class="subject-heading">
                    <span class="vertical-text">Grade</span>
                </th>

            </tr>


            <!-- MAXIMUM MARKS -->
            <tr>

                <th colspan="5"></th>

                <th class="subject-marks">60</th>
                <th class="subject-marks">40</th>
                <th class="subject-marks">100</th>
                <th class="subject-marks"></th>

                <th class="subject-marks">60</th>
                <th class="subject-marks">40</th>
                <th class="subject-marks">100</th>
                <th class="subject-marks"></th>

                <th class="subject-marks">60</th>
                <th class="subject-marks">40</th>
                <th class="subject-marks">100</th>
                <th class="subject-marks"></th>

            </tr>


            <!-- MINIMUM MARKS -->
            <tr>

                <th colspan="5"></th>

                <th class="subject-marks">24</th>
                <th class="subject-marks">16</th>
                <th class="subject-marks">40</th>
                <th class="subject-marks"></th>

                <th class="subject-marks">24</th>
                <th class="subject-marks">16</th>
                <th class="subject-marks">40</th>
                <th class="subject-marks"></th>

                <th class="subject-marks">24</th>
                <th class="subject-marks">16</th>
                <th class="subject-marks">40</th>
                <th class="subject-marks"></th>

            </tr>


            <!-- FINAL COLUMN HEADINGS -->
            <tr>

                <th class="student-heading">SN</th>
                <th class="student-heading">Roll no</th>
                <th class="student-heading">College</th>
                <th class="student-heading">Name</th>
                <th class="student-heading">Father Name</th>

                <!-- Subject 1 -->
                <th></th>
                <th></th>
                <th></th>
                <th>2</th>

                <!-- Subject 2 -->
                <th></th>
                <th></th>
                <th></th>
                <th>2</th>

                <!-- Subject 3 -->
                <th></th>
                <th></th>
                <th></th>
                <th>2</th>

            </tr>

        </thead>


        <tbody>

            <tr>

                <td class="student-cell">1</td>

                <td class="student-cell roll-cell">
                    23015131001
                </td>

                <td class="student-cell">
                    HPTU Humanities
                </td>

                <td class="student-cell">
                    AANCHAL KUMARI
                </td>

                <td class="student-cell">
                    RAJESH / SANTOSH
                </td>


                <!-- SUBJECT 1 -->
                <td class="marks-cell">44</td>

                <td class="marks-cell">38</td>

                <td class="marks-cell">
                    82<br>
                    <span class="vertical-text mini-text text-danger">
                        ENV ID-6201(i)
                    </span>
                </td>

                <td class="grade-cell failed-bg">
                    A+<br>
                    <span class="mini-text">82%</span><br>
                    <span class="mini-text">9</span><br>
                    <span class="mini-text">18</span>
                </td>


                <!-- SUBJECT 2 -->
                <td class="marks-cell">44</td>

                <td class="marks-cell">38</td>

                <td class="marks-cell">82</td>

                <td class="grade-cell">A+</td>


                <!-- SUBJECT 3 -->
                <td class="marks-cell">44</td>

                <td class="marks-cell">38</td>

                <td class="marks-cell">82</td>

                <td class="grade-cell">A+</td>


                <!-- RESULT SUMMARY -->
                <td class="result-cell">Pass</td>

                <td class="result-cell">24</td>

                <td class="result-cell">208</td>

                <td class="result-cell">8.67</td>

                <td class="result-cell">26</td>

                <td class="result-cell">234</td>

                <td class="result-cell">8.84</td>

                <td class="result-cell">Pass</td>

                <td class="result-cell">Pass</td>

            </tr>

        </tbody>

    </table>

</div>
