
<style>
    /* =========================================================
    SCREEN CSS
    ========================================================= */

    .exam-form-table {
        width: 100%;
        font-size: 11px;
        white-space: nowrap;
        margin-bottom: 0;
    }

    .exam-form-table th {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        background: #f1f3f5;
        color: #343a40;
        padding: 7px 8px;
        vertical-align: middle;
    }

    .exam-form-table td {
        font-size: 11px;
        padding: 6px 8px;
        vertical-align: middle;
    }

    .exam-form-table .student-name {
        font-size: 11px;
        font-weight: 600;
        color: #212529;
    }

    .exam-form-table .father-name {
        display: block;
        font-size: 9px;
        font-weight: 400;
        color: #6c757d;
        margin-top: 2px;
    }

    .exam-form-table .btn-edit {
        font-size: 10px;
        padding: 3px 8px;
        white-space: nowrap;
    }

    .exam-form-table tbody tr:hover {
        background-color: #f8f9fa;
    }


    /* =========================================================
    PRINT CSS
    ========================================================= */

    @media print {

        /* A4 Landscape */
        @page {
            size: A4 landscape;
            margin: 8mm 6mm 8mm 6mm;
        }

        html,
        body {
            width: 100% !important;
            height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }

        /* Hide everything */
        body * {
            visibility: hidden !important;
        }

        /* Show only examination form table */
        #exam-form-print-area,
        #exam-form-print-area * {
            visibility: visible !important;
        }

        /*
        IMPORTANT:
        Do not use absolute/fixed positioning here.
        Static positioning allows the table to flow across pages.
        */

        #exam-form-print-area {
            display: block !important;
            position: static !important;
            width: 100% !important;
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            background: #fff !important;
        }

        /* Hide action buttons and other unwanted columns */
        #exam-form-print-area .no-export {
            display: none !important;
        }

        /* Remove responsive table scrolling */
        .table-responsive {
            display: block !important;
            width: 100% !important;
            height: auto !important;
            overflow: visible !important;
        }

        /* Table layout */
        .exam-form-table {
            display: table !important;
            width: 100% !important;
            table-layout: auto !important;
            border-collapse: collapse !important;
            border-spacing: 0 !important;
            font-size: 7px !important;
            white-space: normal !important;
            margin: 0 !important;
            background: #fff !important;
        }

        /* Repeat table heading on every page */
        .exam-form-table thead {
            display: table-header-group !important;
        }

        .exam-form-table tbody {
            display: table-row-group !important;
        }

        .exam-form-table tfoot {
            display: table-footer-group !important;
        }

        /* Table heading and cells */
        .exam-form-table th,
        .exam-form-table td {
            font-size: 7px !important;
            line-height: 1.2 !important;
            padding: 3px 3px !important;
            border: 1px solid #333 !important;
            color: #000 !important;
            background: #fff !important;
            vertical-align: middle !important;
            white-space: normal !important;
            overflow-wrap: break-word !important;
            word-wrap: break-word !important;
        }

        .exam-form-table th {
            font-weight: bold !important;
            text-transform: uppercase;
            background: #e9ecef !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Keep each record together when possible */
        .exam-form-table tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .exam-form-table .student-name {
            font-size: 7px !important;
            font-weight: bold !important;
            color: #000 !important;
        }

        .exam-form-table .father-name {
            display: block !important;
            font-size: 6px !important;
            color: #333 !important;
            margin-top: 1px !important;
        }

        /* Remove links styling */
        #exam-form-print-area a {
            color: #000 !important;
            text-decoration: none !important;
        }

        /* Hide unnecessary elements */
        .print-header,
        .print-footer,
        .no-print,
        .card-header,
        .card-tools,
        .export-button {
            display: none !important;
        }

        /* Prevent unwanted backgrounds */
        body,
        #exam-form-print-area,
        .exam-form-table,
        .exam-form-table tr,
        .exam-form-table td {
            background-color: #fff !important;
        }

    }
</style>

<div class="table-responsive" id="exam-form-print-area">

    <!-- PRINT HEADER -->
    {{-- <div class="print-header">
        Examination Forms
    </div> --}}

    <table class="table table-bordered table-striped table-hover exam-form-table">

        <thead>
            <tr>
                <th>SN</th>
                <th>Exam Type</th>
                <th>Form ID</th>
                <th>Student Name</th>
                <th>Course Name</th>
                <th>Scheme</th>
                <th>Branch</th>
                <th>Semester</th>
                <th>Batch</th>
                <th>Institute</th>
                <th>Exam Center</th>
                <th class="no-export">Action</th>
            </tr>
        </thead>

        <tbody>

            @forelse ($examinationForms as $form)

                <tr>
                    <td>{{ $loop->iteration }}</td>

                    <td>{{ $form->exam_type ?? '-' }}</td>

                    <td>{{ $form->form_id ?? '-' }}</td>

                    <td>
                        <span class="student-name">
                            {{ $form->student->student_name ?? '-' }}
                        </span>

                        <small class="father-name">
                            {{ $form->student->university_roll_no ?? '-' }} / {{ $form->student->father_name ?? '-' }}
                        </small>
                    </td>

                    <td>{{ $form->course->name ?? '-' }}</td>

                    <td>{{ $form->scheme->name ?? '-' }}</td>

                    <td>{{ $form->branch->name ?? '-' }}</td>

                    <td>{{ $form->semester->name ?? '-' }}</td>

                    <td>{{ $form->batch->name ?? '-' }}</td>

                    <td>{{ $form->institute->name ?? '-' }}</td>

                    <td>{{ $form->examCenter->name ?? '-' }}</td>

                    <td class="no-export">
                        @if (strtolower($form->exam_type ?? '') === 'regular')
                            <a href="{{ route('admin.examination-form.edit', $form->id) }}"
                            class="btn btn-primary btn-sm btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @else
                            -
                        @endif
                    </td>
                </tr>

            @empty

                <tr>
                    <td colspan="12" class="text-center text-danger py-3">
                        No paid examination forms found.
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

    <!-- PRINT FOOTER -->
    {{-- <div class="print-footer">
        Examination Forms - Generated on {{ now()->format('d-m-Y h:i A') }}
    </div> --}}

</div>
