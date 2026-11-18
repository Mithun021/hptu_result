
@extends('institute.partial.master')

@section('title', 'Cut List Report')
@section('sub_title', 'Cut List Report')

@section('institute-content')

<style>
    .cutlist-wrapper {
        background: #fff;
        border: 1px solid #e5e7eb;
        padding: 10px;
        border-radius: 3px;
    }

    .cutlist-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .cutlist-top h4 {
        font-size: 18px;
        margin: 0;
        color: #333;
        font-weight: 500;
    }

    .cutlist-print-btn {
        background: #40565a;
        color: #fff;
        border: 0;
        padding: 5px 14px;
        border-radius: 3px;
        font-size: 15px;
        cursor: pointer;
    }

    .cutlist-print-btn:hover {
        background: #293d41;
        color: #fff;
    }

    .cutlist-group {
        margin-bottom: 14px;
        page-break-inside: avoid;
        break-inside: avoid;
    }

    .cutlist-group-title {
        background: #1717c9;
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        padding: 5px 10px;
        margin-bottom: 9px;
        text-transform: uppercase;
    }

    .cutlist-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
        font-size: 12px;
        color: #172033;
    }

    .cutlist-table td,
    .cutlist-table th {
        border: 1px solid #222;
        padding: 6px 7px;
        text-align: left;
        font-weight: 400;
        vertical-align: middle;
    }

    .cutlist-table th {
        font-weight: 700;
        background: #f2f2f2;
    }

    .cutlist-table .serial-col {
        width: 10%;
    }

    .cutlist-table .subject-col {
        width: 60%;
    }

    .cutlist-table .code-col {
        width: 20%;
    }

    .cutlist-table .count-col {
        width: 10%;
    }

    .cutlist-empty {
        text-align: center;
        padding: 25px 10px;
        color: #777;
        border: 1px dashed #ccc;
        font-size: 14px;
    }

    .cutlist-disabled {
        text-align: center;
        padding: 35px 15px;
        border: 1px solid #f0d7a5;
        background: #fff9eb;
        border-radius: 4px;
    }

    .cutlist-disabled h4 {
        color: #8a5a00;
        font-size: 18px;
        margin-bottom: 8px;
    }

    .cutlist-disabled p {
        color: #6b5a3e;
        margin: 0;
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

        /* Hide complete portal content */
        body * {
            visibility: hidden !important;
        }

        /* Show only cutlist */
        #printArea,
        #printArea * {
            visibility: visible !important;
        }

        #printArea {
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

        .cutlist-top {
            margin-bottom: 12px;
        }

        .cutlist-print-btn,
        .no-print {
            display: none !important;
        }

        .cutlist-group-title {
            background: #1717c9 !important;
            color: #fff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .cutlist-table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 12px !important;
        }

        .cutlist-table td,
        .cutlist-table th {
            border: 1px solid #222 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .cutlist-table th {
            background: #f2f2f2 !important;
        }

        .cutlist-group {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .cutlist-table tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .cutlist-empty,
        .cutlist-disabled {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>

<div class="cutlist-wrapper" id="printArea">

    @if($isDisabled)

        <div class="cutlist-disabled">
            <h4>Cut List Report Temporarily Unavailable</h4>

            <p>
                This facility is temporarily unavailable for your institute.
                For any enquiry or assistance, please contact the
                University Administrator.
            </p>
        </div>

    @else

        <div class="cutlist-top">
            <h4>Cut List Report</h4>

            <button type="button"
                    class="cutlist-print-btn"
                    onclick="printCutlist()">
                <i class="fa fa-print"></i> Print
            </button>
        </div>

        @forelse($groups as $group)

            <div class="cutlist-group">

                <div class="cutlist-group-title">
                    {{ $group['course_name'] }}
                    |
                    {{ $group['scheme_name'] }}
                    |
                    {{ $group['branch_name'] }}
                </div>

                <table class="cutlist-table">
                    <tbody>

                        @forelse($group['subjects'] as $index => $item)

                            <tr>
                                <td class="serial-col">
                                    {{ $index + 1 }}
                                </td>

                                <td class="subject-col">
                                    {{ $item['subject']->name }}
                                </td>

                                <td class="code-col">
                                    {{ $item['subject']->subject_code ?? 'N/A' }}
                                </td>

                                <td class="count-col">
                                    {{ $item['student_count'] }}
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="text-center">
                                    No subjects found.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>
                </table>

            </div>

        @empty

            <div class="cutlist-empty">
                No paid examination forms found for this institute.
            </div>

        @endforelse

    @endif

</div>

<script>
    function printCutlist() {

        const printArea = document.getElementById('printArea');

        if (!printArea) {
            alert('Cutlist report not found!');
            return;
        }

        window.print();
    }
</script>

@endsection
