
<style>
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

    /* Print Header/Footer - Hidden on screen */
    .print-header,
    .print-footer {
        display: none;
    }

    @media print {

        @page {
            size: A4 portrait;
            margin: 20mm 10mm 18mm 10mm;
        }

        body * {
            visibility: hidden !important;
        }

        #exam-form-print-area,
        #exam-form-print-area * {
            visibility: visible !important;
        }

        #exam-form-print-area {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            padding: 0;
            margin: 0;
            border: none;
        }

        /* Hide no-export elements */
        #exam-form-print-area .no-export {
            display: none !important;
        }

        /* Remove responsive scrolling */
        #exam-form-print-area.table-responsive {
            overflow: visible !important;
        }

        .exam-form-table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 9px !important;
            white-space: normal !important;
        }

        .exam-form-table th,
        .exam-form-table td {
            font-size: 9px !important;
            padding: 5px 4px !important;
            border: 1px solid #333 !important;
            color: #000 !important;
            background: #fff !important;
        }

        .exam-form-table thead {
            display: table-header-group;
        }

        .exam-form-table tfoot {
            display: table-footer-group;
        }

        .exam-form-table tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .exam-form-table .father-name {
            color: #333 !important;
        }

        /* Repeating header */
        .print-header {
            display: block !important;
            position: fixed;
            top: -15mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            padding-bottom: 5px;
            border-bottom: 1px solid #333;
        }

        /* Repeating footer */
        .print-footer {
            display: block !important;
            position: fixed;
            bottom: -13mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            padding-top: 5px;
            border-top: 1px solid #333;
        }

        a {
            color: #000 !important;
            text-decoration: none !important;
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
                            {{ $form->student->father_name ?? '-' }}
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
                        <a href="{{ route('admin.examination-form.edit', $form->id) }}"
                           class="btn btn-primary btn-sm btn-edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
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
