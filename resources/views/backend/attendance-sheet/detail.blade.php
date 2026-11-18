@extends('backend.partial.master')
@section('title', 'Attendance Report')
@section('sub_title', 'Attendance Report')

@section('backend-content')

<style>
    .report-wrapper {
        background: #fff;
        padding: 12px;
        border: 1px solid #ddd;
    }

    .report-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .report-heading h4 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #222;
    }

    .report-info {
        margin-bottom: 12px;
        font-size: 12px;
        color: #222;
    }

    .report-info span {
        display: inline-block;
        margin-right: 18px;
        margin-bottom: 4px;
    }

    .report-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        color: #000;
        font-size: 11px;
    }

    .report-table th,
    .report-table td {
        border: 1px solid #000;
        padding: 5px;
        vertical-align: middle;
    }

    .report-table th {
        text-align: center;
        font-weight: 700;
        background: #f2f2f2;
    }

    .serial-column {
        width: 35px;
        text-align: center;
    }

    .roll-column {
        min-width: 180px;
        font-weight: 600;
    }

    .student-column {
        min-width: 130px;
        font-weight: 600;
    }

    .photo-column {
        width: 90px;
        min-width: 90px;
        text-align: center;
    }

    .student-photo {
        width: 65px;
        height: 70px;
        object-fit: cover;
        display: block;
        margin: 0 auto 5px;
        border: 1px solid #aaa;
    }

    .student-signature {
        width: 65px;
        height: 30px;
        object-fit: contain;
        display: block;
        margin: 0 auto;
    }

    .subject-grid {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .subject-grid td {
        border: 1px solid #000;
        padding: 0;
        vertical-align: top;
    }

    .subject-item {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .subject-item th,
    .subject-item td {
        border: 1px solid #000;
        padding: 4px 2px;
        height: 27px;
        text-align: center;
    }

    .subject-item th {
        background: #f2f2f2;
        font-weight: 700;
        white-space: nowrap;
    }

    .subject-code {
        height: 28px !important;
        font-size: 10px;
        font-weight: 700;
    }

    .subject-empty {
        height: 27px;
    }

    .report-print-btn {
        background: #40565a;
        color: #fff;
        border: 0;
        padding: 6px 14px;
        border-radius: 3px;
        cursor: pointer;
    }

    .report-print-btn:hover {
        background: #293d41;
        color: #fff;
    }

    .college-name{
        font-size: 8px !important;
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        html, body {
            width: 100% !important;
            height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }

        body * {
            visibility: hidden !important;
        }

        #attendanceReport,
        #attendanceReport * {
            visibility: visible !important;
        }

        #attendanceReport {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            background: #fff !important;
            box-shadow: none !important;
        }

        .no-print {
            display: none !important;
        }

        .report-table-wrapper {
            width: 100% !important;
            overflow: visible !important;
        }

        .report-table {
            width: 100% !important;
            font-size: 9px !important;
            border-collapse: collapse !important;
        }

        .report-table th,
        .report-table td,
        .subject-grid td,
        .subject-item th,
        .subject-item td {
            border: 1px solid #000 !important;
            padding: 3px 2px !important;
            color: #000 !important;
        }

        .report-table th,
        .subject-item th {
            background: #f2f2f2 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .subject-grid,
        .subject-item {
            width: 100% !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
        }

        .student-photo {
            width: 48px !important;
            height: 52px !important;
        }

        .student-signature {
            width: 48px !important;
            height: 23px !important;
        }

        .report-table tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .college-name{
            font-size: 8px !important;
        }
    }
</style>

<div class="report-wrapper" id="attendanceReport">

    <div class="report-heading">
        <h4>Attendance Report</h4>

        <button type="button"
                class="report-print-btn no-print"
                onclick="window.print()">
            <i class="fa fa-print"></i> Print
        </button>
    </div>

    <div class="report-info">
        <span><strong>Exam:</strong> {{ $reportDetails['exam_name'] }}</span>
        <span><strong>Exam Type:</strong> {{ $reportDetails['exam_type'] }}</span>
        <span><strong>Course:</strong> {{ $reportDetails['course_name'] }}</span>
        <span><strong>Scheme:</strong> {{ $reportDetails['scheme_name'] }}</span>
        <span><strong>Branch:</strong> {{ $reportDetails['branch_name'] }}</span>
        <span><strong>Semester:</strong> {{ $reportDetails['semester_name'] }}</span>
    </div>

    <div class="report-table-wrapper">
        <table class="report-table">
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Roll No. / Form No.<br>Student College Name</th>
                    <th>Student Name / Father Name</th>
                    <th>Photo / Sign</th>
                    <th>Sub Code | Sheet No | Sign | Date</th>
                </tr>
            </thead>

            <tbody>
                @foreach($forms as $index => $form)

                    @php
                        $student = $form->student;

                        // Controller se already sirf Theory subjects aa rahe hain
                        $subjects = $form->choosedSubjects->values();

                        $photoPath = $student->profile_image ?? null;
                        $signaturePath = $student->signature ?? null;
                    @endphp

                    <tr>
                        <td class="serial-column">
                            {{ $index + 1 }}
                        </td>

                        <td class="roll-column">
                            {{ $student->university_roll_no ?? 'N/A' }}
                            /
                            {{ $form->form_id ?? 'N/A' }}

                            <br>

                            <span class="college-name">{{ $form->institute->name ?? 'N/A' }}</span>
                        </td>

                        <td class="student-column">
                            {{ $student->student_name ?? 'N/A' }}
                            /
                            {{ $student->father_name ?? 'N/A' }}
                        </td>

                        <td class="photo-column">
                            @if($photoPath)
                                <img src="{{ asset($photoPath) }}"
                                     class="student-photo"
                                     alt="Student Photo">
                            @else
                                No Photo
                            @endif

                            @if($signaturePath)
                                <img src="{{ asset($signaturePath) }}"
                                     class="student-signature"
                                     alt="Student Signature">
                            @else
                                No Sign
                            @endif
                        </td>

                        <td style="padding:0;">
                            @if($subjects->count() > 0)

                                <table class="subject-grid">
                                    <tr>
                                        @foreach($subjects as $chosenSubject)
                                            <td>
                                                <table class="subject-item">
                                                    <tr>
                                                        <th class="subject-code">
                                                            {{ $chosenSubject->subject->subject_code ?? 'N/A' }}
                                                        </th>
                                                    </tr>

                                                    @for($blank = 1; $blank <= 3; $blank++)
                                                        <tr>
                                                            <td class="subject-empty"></td>
                                                        </tr>
                                                    @endfor
                                                </table>
                                            </td>
                                        @endforeach
                                    </tr>
                                </table>

                            @else
                                <div style="padding:10px; text-align:center;">
                                    No Theory subject selected
                                </div>
                            @endif
                        </td>
                    </tr>

                @endforeach
            </tbody>
        </table>
    </div>

</div>

@endsection
