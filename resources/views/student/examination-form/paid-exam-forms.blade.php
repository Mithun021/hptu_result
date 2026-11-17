@extends('student.partial.master')

@section('title', 'Examination Forms')
@section('sub_title', 'Paid Examination Form')

@section('student-content')

@php
    $formStudent = $examinationForm->student;

    $profileImage = $formStudent?->profile_image;
    $signature = $formStudent?->signature;

    $profileImageExists = !empty($profileImage)
        && file_exists(public_path($profileImage));

    $signatureExists = !empty($signature)
        && file_exists(public_path($signature));

    $examFee = (float) $examinationForm->exam_fee;
    $lateFee = (float) $examinationForm->late_fee;
    $totalFee = (float) $examinationForm->total_exam_fee;
@endphp

{{-- Print Button --}}
<div class="row mb-3 no-print">
    <div class="col-md-12 text-end">
        <button type="button" class="btn btn-primary" onclick="printExamForm()">
            <i class="fa fa-print me-1"></i> Print Examination Form
        </button>
    </div>
</div>

{{-- Printable Area --}}
<div class="row" id="printableArea">
    <div class="col-md-12">
        <div id="exam_form_data">

            <style>
                #printableArea {
                    background: #fff;
                }

                #tabledata-info tr td {
                    color: #4E71A7;
                    padding: 2px 0;
                }

                #tabledata-info tr td b {
                    font-weight: 800;
                }

                #subject-table tr th,
                #subject-table tr td {
                    border: 1px solid black;
                    padding: 2px;
                    color: #4E71A7;
                }

                #subject-table {
                    border-collapse: collapse;
                }

                #rules-info {
                    color: #4E71A7;
                }

                #table-heading {
                    background: #C4C4C4;
                }

                .exam-heading h4 {
                    font-size: 14px;
                    font-weight: 700;
                    margin: 4px 0;
                    white-space: nowrap;
                }

                /* Print CSS */
                @media print {

                    @page {
                        size: A4 portrait;
                        margin: 6mm;
                    }

                    html,
                    body {
                        margin: 0 !important;
                        padding: 0 !important;
                        background: #fff !important;
                        width: 100% !important;
                    }

                    body * {
                        visibility: hidden !important;
                    }

                    #printableArea,
                    #printableArea * {
                        visibility: visible !important;
                    }

                    #printableArea {
                        position: absolute !important;
                        left: 0 !important;
                        top: 0 !important;
                        width: 100% !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        background: #fff !important;
                        zoom: 0.82;
                    }

                    #printableArea .col-md-12 {
                        width: 100% !important;
                        max-width: 100% !important;
                        padding: 0 !important;
                        margin: 0 !important;
                    }

                    #exam_form_data {
                        width: 100% !important;
                        margin: 0 !important;
                        padding: 0 !important;
                    }

                    #exam_form_data table {
                        width: 100% !important;
                    }

                    #exam_form_data tr,
                    #exam_form_data td,
                    #exam_form_data th {
                        page-break-inside: avoid !important;
                        break-inside: avoid !important;
                    }

                    #exam_form_data p {
                        margin-top: 2px !important;
                        margin-bottom: 2px !important;
                    }

                    #exam_form_data h4 {
                        font-size: 15px !important;
                        margin: 3px 0 !important;
                    }

                    #exam_form_data img {
                        print-color-adjust: exact !important;
                        -webkit-print-color-adjust: exact !important;
                    }

                    #exam_form_data td,
                    #exam_form_data th {
                        print-color-adjust: exact !important;
                        -webkit-print-color-adjust: exact !important;
                    }

                    #subject-table th,
                    #subject-table td {
                        padding: 2px !important;
                        font-size: 11px !important;
                    }

                    #tabledata-info td {
                        font-size: 11px !important;
                        padding: 1px 0 !important;
                    }

                    #rules-info {
                        font-size: 11px !important;
                        line-height: 1.3 !important;
                    }

                    .no-print {
                        display: none !important;
                    }
                }
            </style>

            <table width="100%">
                <thead>
                    <tr>
                        <td
                            id="printHead"
                            colspan="2"
                            style="background: #C4C4C4; padding: 10px 0;"
                        >
                            <center>
                                <img
                                    src="{{ asset('assets/images/hptulogo.png') }}"
                                    style="height: 80px; width: auto;"
                                    alt="University Logo"
                                >
                            </center>
                        </td>
                    </tr>
                </thead>

                <tbody>

                    {{-- Examination Session --}}
                    <tr class="exam-heading">
                        <td style="width: 65%;">
                            <h4>
                                Examination Session:
                                {{ $examinationForm->exam?->name ?? 'N/A' }}
                            </h4>
                        </td>

                        <td style="width: 35%; white-space: nowrap; text-align: right;">
                            <h4>
                                Form No.:
                                {{ $examinationForm->form_id ?? 'N/A' }}
                            </h4>
                        </td>
                    </tr>

                    {{-- Personal Details Heading --}}
                    <tr>
                        <td colspan="2" style="background: #C4C4C4;">
                            <b>
                                <p style="margin: 0; padding: 5px 10px;">
                                    :: Personal Details ::
                                </p>
                            </b>
                        </td>
                    </tr>

                    {{-- Personal Details --}}
                    <tr>
                        <td width="70%">
                            <table width="100%" id="tabledata-info">

                                <tr>
                                    <td width="50%">
                                        <b>Student Name</b>
                                    </td>
                                    <td width="50%">
                                        <b>{{ $formStudent?->student_name ?? 'N/A' }}</b>
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>Father Name :</b>
                                    </td>
                                    <td width="50%">
                                        {{ $formStudent?->father_name ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>Aadhaar No.</b>
                                    </td>
                                    <td width="50%">
                                        {{ $formStudent?->aadhar ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>ABC / NAD ID:</b>
                                    </td>
                                    <td width="50%">
                                        {{ $formStudent?->abcid ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>Email ID</b>
                                    </td>
                                    <td width="50%">
                                        {{ $formStudent?->email ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>Mobile No.</b>
                                    </td>
                                    <td width="50%">
                                        {{ $formStudent?->phone ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>College</b>
                                    </td>
                                    <td width="50%">
                                        {{ $examinationForm->institute?->name ?? 'N/A' }}
                                    </td>
                                </tr>

                            </table>
                        </td>

                        <td width="30%" style="vertical-align: top;">
                            @if($profileImageExists)
                                <img
                                    src="{{ asset($profileImage) }}"
                                    style="max-width: 120px; height: 120px; object-fit: contain;"
                                    alt="Student Profile Picture"
                                >
                            @endif
                        </td>
                    </tr>

                    {{-- Form Details Heading --}}
                    <tr>
                        <td colspan="2" style="background: #C4C4C4;">
                            <b>
                                <p style="margin: 0; padding: 5px 10px;">
                                    :: Form Details ::
                                </p>
                            </b>
                        </td>
                    </tr>

                    {{-- Form Details --}}
                    <tr>
                        <td width="80%">
                            <table width="100%" id="tabledata-info">

                                <tr>
                                    <td width="50%">
                                        <b>University Roll No.</b>
                                    </td>
                                    <td width="50%">
                                        <b>{{ $formStudent?->university_roll_no ?? 'N/A' }}</b>
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>Form Type :</b>
                                    </td>
                                    <td width="50%">
                                        {{ $examinationForm->exam_type ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>Course</b>
                                    </td>
                                    <td width="50%">
                                        {{ $examinationForm->course?->name ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>Scheme</b>
                                    </td>
                                    <td width="50%">
                                        {{ $examinationForm->scheme?->name ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>Branch</b>
                                    </td>
                                    <td width="50%">
                                        {{ $examinationForm->branch?->name ?? 'NA' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>Semester</b>
                                    </td>
                                    <td width="50%">
                                        {{ $examinationForm->semester?->name ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>Session / Batch</b>
                                    </td>
                                    <td width="50%">
                                        {{ $examinationForm->batch?->name ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>Fee</b>
                                    </td>
                                    <td width="50%">
                                        {{ number_format($examFee, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>Fine / Late Fee</b>
                                    </td>
                                    <td width="50%">
                                        {{ number_format($lateFee, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>Total Fee Paid</b>
                                    </td>
                                    <td width="50%">
                                        {{ number_format($totalFee, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>Form Status</b>
                                    </td>
                                    <td width="50%">
                                        <b>PAID</b>
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>Exam Centre</b>
                                    </td>
                                    <td width="50%">
                                        {{ $examinationForm->examCenter?->name ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>Last Appeared Session</b>
                                    </td>
                                    <td width="50%">
                                        {{ $examinationForm->last_appearning_session ?? 'NA' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>Last Appeared Year</b>
                                    </td>
                                    <td width="50%">
                                        {{ $examinationForm->last_appearing_year ?? 'NA' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>Transaction ID</b>
                                    </td>
                                    <td width="50%">
                                        {{ $examinationForm->transaction_id ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>Transaction Date</b>
                                    </td>
                                    <td width="50%">
                                        {{ $examinationForm->transaction_date?->format('d-m-Y h:i A') ?? 'N/A' }}
                                    </td>
                                </tr>

                            </table>
                        </td>

                        <td width="20%"></td>
                    </tr>

                    {{-- Subjects Applied Heading --}}
                    <tr>
                        <td
                            colspan="2"
                            id="table-heading"
                            style="background: #C4C4C4;"
                        >
                            <b>
                                <p style="margin: 0; padding: 5px 10px;">
                                    :: Subjects Applied ::
                                </p>
                            </b>
                        </td>
                    </tr>

                    {{-- Subjects Applied --}}
                    <tr>
                        <td colspan="2">
                            <table width="100%" id="subject-table">

                                <tr>
                                    <th>SNo</th>
                                    <th>Subject Type</th>
                                    <th>Subject Code</th>
                                    <th>Subject Title</th>
                                    <th>Fee Status</th>
                                </tr>

                                @forelse($examinationForm->choosedSubjects as $index => $chosenSubject)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>

                                        <td>
                                            {{ $chosenSubject->subject?->type ?? 'N/A' }}
                                        </td>

                                        <td>
                                            {{ $chosenSubject->subject?->subject_code ?? 'N/A' }}
                                        </td>

                                        <td>
                                            {{ $chosenSubject->subject?->name ?? 'N/A' }}
                                        </td>

                                        <td>
                                            <b>PAID</b>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            No subjects found.
                                        </td>
                                    </tr>
                                @endforelse

                            </table>
                        </td>
                    </tr>

                    {{-- Undertaking Heading --}}
                    <tr>
                        <td colspan="2" style="background: #C4C4C4;">
                            <b>
                                <p style="margin: 0; padding: 5px 10px;">
                                    :: Undertaking By the Student ::
                                </p>
                            </b>
                        </td>
                    </tr>

                    {{-- Undertaking Content --}}
                    <tr>
                        <td colspan="2">
                            <p id="rules-info">
                                I, hereby solemnly declare that the information furnished
                                above is true to the best of my knowledge and belief,

                                I also state that I have read the Ordinances &amp;
                                regulations of the university carefully and the provisions
                                made therein are acceptable to me.

                                I also declare that I will undergo such punishment as may
                                be imposed upon me by the Institute Authorities for errors
                                and/or omissions including suppression of

                                I further declare that I have not passed the Examination
                                applied for here-in-before from any other Statutory University.
                            </p>
                        </td>
                    </tr>

                    {{-- Date and Student Signature --}}
                    <tr>
                        <td id="rules-info">
                            <b>
                                Date :
                                <u>{{ $examinationForm->created_at?->format('d-m-Y') }}</u>
                            </b>
                        </td>

                        <td>
                            <div id="std_sign">
                                @if($signatureExists)
                                    <img
                                        src="{{ asset($signature) }}"
                                        style="height: 40px; width: auto; max-width: 150px; object-fit: contain;"
                                        alt="Student Signature"
                                    >
                                @endif
                            </div>

                            <p style="margin: 0;" id="rules-info">
                                <b>Signature of Student</b>
                            </p>
                        </td>
                    </tr>

                </tbody>
            </table>

        </div>
    </div>
</div>

{{-- Custom Print JavaScript --}}
@push('scripts')
<script>
    function printExamForm() {

        const printArea = document.getElementById('printableArea');

        if (!printArea) {
            alert('Examination form not found.');
            return;
        }

        // Wait for images to load before printing
        const images = printArea.querySelectorAll('img');

        const imagePromises = Array.from(images).map(function (img) {

            if (img.complete) {
                return Promise.resolve();
            }

            return new Promise(function (resolve) {
                img.onload = resolve;
                img.onerror = resolve;
            });

        });

        Promise.all(imagePromises).then(function () {

            window.print();

        });
    }
</script>
@endpush

@endsection
