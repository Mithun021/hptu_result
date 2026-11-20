
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

    /* Subject Grid */
    .subject-grid {
        width: auto;
        border-collapse: collapse;
        table-layout: auto;
    }

    .subject-grid td {
        border: 0;
        padding: 0;
        vertical-align: top;
        width: 80px;
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

    .college-name {
        font-size: 8px !important;
    }

    /* =========================================
       PRINT SETTINGS - A4 LANDSCAPE
    ========================================= */
    @media print {

        @page {
            size: A4 landscape;
            margin: 6mm;
        }

        html,
        body {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }

        .no-print {
            display: none !important;
        }

        #attendanceReport {
            position: static !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            background: #fff !important;
            box-shadow: none !important;
        }

        .report-heading {
            margin-bottom: 5px !important;
        }

        .report-heading h4 {
            font-size: 12px !important;
        }

        .report-info {
            margin-bottom: 5px !important;
            font-size: 8px !important;
            line-height: 1.2 !important;
        }

        .report-info span {
            margin-right: 10px !important;
            margin-bottom: 2px !important;
        }

        .report-table-wrapper {
            width: 100% !important;
            overflow: visible !important;
        }

        .report-table {
            width: 100% !important;
            font-size: 8px !important;
            border-collapse: collapse !important;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #000 !important;
            padding: 2px 1px !important;
            color: #000 !important;
        }

        .report-table th {
            font-size: 8px !important;
            line-height: 1.1 !important;
            background: #f2f2f2 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Subject outer grid */
        .subject-grid {
            width: auto !important;
            border-collapse: collapse !important;
            table-layout: auto !important;
        }

        .subject-grid td {
            width: 80px !important;
            border: 0 !important;
            padding: 0 !important;
        }

        /* Individual subject boxes */
        .subject-item {
            width: 100% !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
        }

        .subject-item th {
            background: #f2f2f2 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .subject-item th,
        .subject-item td {
            border: 1px solid #000 !important;
            height: 14px !important;
            padding: 1px !important;
            font-size: 7px !important;
            line-height: 1 !important;
            color: #000 !important;
        }

        /* Student photo */
        .student-photo {
            width: 38px !important;
            height: 40px !important;
            margin: 0 auto 2px !important;
        }

        /* Student signature */
        .student-signature {
            width: 38px !important;
            height: 17px !important;
            margin: 0 auto !important;
        }

        /* Subject code */
        .subject-code {
            height: 18px !important;
            font-size: 7px !important;
            line-height: 1 !important;
            padding: 1px !important;
        }

        /* Blank rows */
        .subject-empty {
            height: 14px !important;
        }

        /* College name */
        .college-name {
            font-size: 7px !important;
            line-height: 1.1 !important;
        }

        .report-table tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
    }
</style>

<div class="report-wrapper" id="attendanceReport">

    <div class="report-heading">
        <h4>Attendance Report</h4>

        <button type="button"
                class="report-print-btn no-print"
                onclick="printAttendanceReport()">
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


{{-- =========================================
     CUSTOM PRINT JAVASCRIPT
========================================= --}}
<script>
    function printAttendanceReport() {

        const report = document.getElementById('attendanceReport');

        if (!report) {
            alert('Attendance report not found!');
            return;
        }

        // Open separate print window
        const printWindow = window.open(
            '',
            '_blank',
            'width=1200,height=850'
        );

        if (!printWindow) {
            alert('Please allow pop-ups for printing this report.');
            return;
        }

        // Copy page stylesheets
        const stylesheets = Array.from(
            document.querySelectorAll('link[rel="stylesheet"]')
        ).map(link => link.outerHTML).join('\n');

        // Copy inline styles
        const inlineStyles = Array.from(
            document.querySelectorAll('style')
        ).map(style => style.outerHTML).join('\n');

        // Clone report content
        const reportClone = report.cloneNode(true);

        // Remove print button from cloned report
        const printButton = reportClone.querySelector('.no-print');

        if (printButton) {
            printButton.remove();
        }

        // Write print document
        printWindow.document.open();

        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Attendance Report</title>

                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">

                ${stylesheets}
                ${inlineStyles}

                <style>
                    @page {
                        size: A4 landscape;
                        margin: 6mm;
                    }

                    html, body {
                        width: 100%;
                        margin: 0 !important;
                        padding: 0 !important;
                        background: #fff !important;
                    }

                    body {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }

                    #attendanceReport {
                        position: static !important;
                        width: 100% !important;
                        max-width: 100% !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        border: 0 !important;
                        background: #fff !important;
                        box-shadow: none !important;
                    }

                    .report-table-wrapper {
                        width: 100% !important;
                        overflow: visible !important;
                    }

                    .report-table {
                        width: 100% !important;
                        border-collapse: collapse !important;
                        font-size: 8px !important;
                    }

                    .report-table th,
                    .report-table td {
                        border: 1px solid #000 !important;
                        padding: 2px 1px !important;
                        color: #000 !important;
                    }

                    .report-table th {
                        font-size: 8px !important;
                        line-height: 1.1 !important;
                        background: #f2f2f2 !important;
                    }

                    /* Remove only outer subject grid border */
                    .subject-grid {
                        width: auto !important;
                        border-collapse: collapse !important;
                        table-layout: auto !important;
                    }

                    .subject-grid td {
                        width: 80px !important;
                        border: 0 !important;
                        padding: 0 !important;
                    }

                    /* Keep individual subject boxes */
                    .subject-item {
                        width: 100% !important;
                        border-collapse: collapse !important;
                        table-layout: fixed !important;
                    }

                    .subject-item th,
                    .subject-item td {
                        border: 1px solid #000 !important;
                        height: 14px !important;
                        padding: 1px !important;
                        font-size: 7px !important;
                        line-height: 1 !important;
                    }

                    .subject-item th {
                        background: #f2f2f2 !important;
                    }

                    .subject-code {
                        height: 18px !important;
                        font-size: 7px !important;
                        padding: 1px !important;
                    }

                    .subject-empty {
                        height: 14px !important;
                    }

                    .student-photo {
                        width: 38px !important;
                        height: 40px !important;
                        margin: 0 auto 2px !important;
                        object-fit: cover !important;
                    }

                    .student-signature {
                        width: 38px !important;
                        height: 17px !important;
                        margin: 0 auto !important;
                        object-fit: contain !important;
                    }

                    .college-name {
                        font-size: 7px !important;
                    }

                    .report-heading {
                        margin-bottom: 5px !important;
                    }

                    .report-heading h4 {
                        font-size: 12px !important;
                    }

                    .report-info {
                        margin-bottom: 5px !important;
                        font-size: 8px !important;
                    }

                    .report-info span {
                        margin-right: 10px !important;
                        margin-bottom: 2px !important;
                    }

                    .report-table tr {
                        page-break-inside: avoid !important;
                        break-inside: avoid !important;
                    }

                    img {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                </style>

            </head>

            <body>
                ${reportClone.outerHTML}
            </body>
            </html>
        `);

        printWindow.document.close();

        // Wait for all images and styles to load
        const waitForImages = () => {

            const images = Array.from(
                printWindow.document.images
            );

            return Promise.all(
                images.map(img => {

                    if (img.complete) {
                        return Promise.resolve();
                    }

                    return new Promise(resolve => {

                        img.onload = resolve;
                        img.onerror = resolve;

                    });
                })
            );
        };

        // Print after document and images are ready
        const startPrinting = async () => {

            try {

                await waitForImages();

                if (printWindow.document.fonts) {
                    await printWindow.document.fonts.ready;
                }

                // Small delay for final layout calculation
                setTimeout(() => {

                    printWindow.focus();

                    printWindow.print();

                    // Close after print dialog is completed
                    printWindow.onafterprint = function () {
                        printWindow.close();
                    };

                }, 500);

            } catch (error) {

                console.error('Print error:', error);

                printWindow.focus();
                printWindow.print();

            }
        };

        // Wait until print document is ready
        if (printWindow.document.readyState === 'complete') {

            startPrinting();

        } else {

            printWindow.onload = startPrinting;

        }
    }
</script>

@endsection
