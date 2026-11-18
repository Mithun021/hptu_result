
@extends('institute.partial.master')

@section('title', 'Attendance Sheet')
@section('sub_title', 'Attendance Sheet')

@section('institute-content')

<style>
    .attendance-wrapper {
        background: #fff;
        border: 1px solid #e5e7eb;
        padding: 10px;
        border-radius: 3px;
    }

    .attendance-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .attendance-top h4 {
        font-size: 18px;
        margin: 0;
        color: #333;
        font-weight: 500;
    }

    .attendance-print-btn {
        background: #40565a;
        color: #fff;
        border: 0;
        padding: 5px 14px;
        border-radius: 3px;
        font-size: 14px;
        cursor: pointer;
    }

    .attendance-print-btn:hover {
        background: #293d41;
        color: #fff;
    }

    .attendance-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .attendance-table {
        width: 100%;
        min-width: 950px;
        border-collapse: collapse;
        font-size: 12px;
        color: #172033;
    }

    .attendance-table th,
    .attendance-table td {
        border: 1px solid #222;
        padding: 7px 6px;
        text-align: center;
        vertical-align: middle;
    }

    .attendance-table th {
        background: #f2f2f2;
        font-weight: 700;
        white-space: nowrap;
    }

    .attendance-table td.course-column {
        text-align: left;
        min-width: 260px;
        font-weight: 600;
    }

    .attendance-table td.exam-type-column {
        white-space: nowrap;
        font-weight: 600;
    }

    .semester-badge {
        display: inline-block;
        min-width: 27px;
        padding: 4px 7px;
        background: #1717c9;
        color: #fff;
        border-radius: 4px;
        text-decoration: none;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.2;
    }

    .semester-badge:hover {
        background: #09098c;
        color: #fff;
    }

    .attendance-empty {
        text-align: center;
        padding: 25px 10px;
        color: #777;
        border: 1px dashed #ccc;
        font-size: 14px;
    }

    /* ================= PRINT CSS ================= */

    @media print {

        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        html,
        body {
            width: 100% !important;
            height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }

        /* Hide complete page */
        body * {
            visibility: hidden !important;
        }

        /* Show only print area */
        #attendancePrintArea,
        #attendancePrintArea * {
            visibility: visible !important;
        }

        #attendancePrintArea {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            border-radius: 0 !important;
            background: #fff !important;
            box-shadow: none !important;
            overflow: visible !important;
        }

        .attendance-print-btn,
        .no-print {
            display: none !important;
        }

        .attendance-table-wrapper {
            width: 100% !important;
            overflow: visible !important;
        }

        .attendance-table {
            width: 100% !important;
            min-width: 0 !important;
            border-collapse: collapse !important;
            font-size: 9px !important;
        }

        .attendance-table th,
        .attendance-table td {
            border: 1px solid #222 !important;
            padding: 5px 3px !important;
            color: #000 !important;
        }

        .attendance-table th {
            background: #f2f2f2 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .attendance-table td.course-column {
            min-width: 0 !important;
        }

        .semester-badge {
            background: #1717c9 !important;
            color: #fff !important;
            text-decoration: none !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .attendance-table tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
    }
</style>

<div class="attendance-wrapper" id="attendancePrintArea">

    <div class="attendance-top">
        <h4>Attendance Sheet</h4>

        <button type="button"
                class="attendance-print-btn"
                onclick="printAttendanceSheet()">
            <i class="fa fa-print"></i> Print
        </button>
    </div>

    @if($groups->count() > 0)

        <div class="attendance-table-wrapper">

            <table class="attendance-table">

                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Exam Type</th>
                        <th>Course</th>

                        @foreach($semesters as $semester)
                            <th>{{ $semester->name }}</th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>

                    @foreach($groups as $index => $group)

                        <tr>

                            <td>{{ $index + 1 }}</td>

                            <td class="exam-type-column">
                                {{ $group['exam_type'] }}
                            </td>

                            <td class="course-column">
                                {{ $group['course_name'] }}
                                |
                                {{ $group['scheme_name'] }}
                                |
                                {{ $group['branch_name'] }}
                            </td>

                            @foreach($semesters as $semester)

                                @php
                                    $semesterInfo = $group['semesters'][$semester->id] ?? null;
                                    $count = $semesterInfo['count'] ?? 0;
                                @endphp

                                <td>

                                    @if($count > 0)

                                        <a href="{{ route('institute.attendance-sheet.detail', [
                                            'exam_id' => $group['exam_id'],
                                            'exam_type' => $group['exam_type'],
                                            'course_id' => $group['course_id'],
                                            'scheme_id' => $group['scheme_id'],
                                            'branch_id' => $group['branch_id'],
                                            'semester_id' => $semester->id,
                                        ]) }}"
                                        class="semester-badge" target="_blank"
                                        title="View {{ $semester->name }} Attendance">
                                            {{ $count }}
                                        </a>

                                    @endif

                                </td>

                            @endforeach

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    @else

        <div class="attendance-empty">
            No paid examination forms found for active exams.
        </div>

    @endif

</div>

<script>
    function printAttendanceSheet() {

        const printArea = document.getElementById('attendancePrintArea');

        if (!printArea) {
            alert('Attendance sheet not found!');
            return;
        }

        window.print();
    }
</script>

@endsection
