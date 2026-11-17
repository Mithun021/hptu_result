
@extends('student.partial.master')

@section('title', 'Examination Form')
@section('sub_title', 'Pending Examination Form')

@section('student-content')

@php
    $profileImage = $student->profile_image ?? null;
    $imagePath = $profileImage ? public_path($profileImage) : null;

    $profileImageUrl = (
        !empty($profileImage) && file_exists($imagePath)
    )
        ? asset($profileImage)
        : asset('assets/images/users/blank-profile-picture.webp');

    // Stored fees from examination_form table.
    $storedExamFee = (float) $examinationForm->exam_fee;
    $storedLateFee = (float) $examinationForm->late_fee;
    $storedTotalFee = (float) $examinationForm->total_exam_fee;

    // Current recalculated fees.
    $currentExamFee = (float) ($feeDetails['exam_fee'] ?? 0);
    $currentLateFee = (float) ($feeDetails['late_fee'] ?? 0);
    $currentTotalFee = (float) ($feeDetails['total_exam_fee'] ?? 0);

    // Show current calculated fee when fee window is open.
    // Otherwise show the fee already stored in the form.
    if (!empty($feeDetails['available']) && !empty($feeDetails['is_open'])) {
        $displayExamFee = $currentExamFee;
        $displayLateFee = $currentLateFee;
        $displayTotalFee = $currentTotalFee;
    } else {
        $displayExamFee = $storedExamFee;
        $displayLateFee = $storedLateFee;
        $displayTotalFee = $storedTotalFee;
    }
@endphp

<div class="row" id="printableArea">

    <div class="col-md-12">

        <div id="exam_form_data">

            <style>
                #tabledata-info tr td {
                    color: #4E71A7;
                    padding: 2px 0;
                    vertical-align: top;
                }

                #tabledata-info tr td b {
                    font-weight: 800;
                }

                #subject-table tr th,
                #subject-table tr td {
                    border: 1px solid black;
                    padding: 4px;
                    color: #4E71A7;
                }

                #subject-table {
                    border-collapse: collapse;
                }

                #rules-info {
                    color: #4E71A7;
                }

                .margin80 {
                    margin-top: 50px;
                }

                #table-heading {
                    background: #C4C4C4;
                }

                #exam_form_data {
                    background: #fff;
                    padding: 10px;
                }

                #exam_form_data h4 {
                    font-size: 16px;
                    font-weight: 700;
                }

                #exam_form_data p {
                    margin-bottom: 5px;
                }

                @media print {
                    body {
                        background: #fff !important;
                    }

                    #printableArea {
                        width: 100%;
                    }
                }
            </style>

            <table width="100%">

                <thead>
                    <tr>
                        <td id="printHead"
                            style="background: #C4C4C4; padding: 10px 0;"
                            colspan="2">

                            <center>
                                <img
                                    src="{{ asset('assets/images/hptulogo.png') }}"
                                    style="height: 80px; width: auto;"
                                    alt="University Logo">
                            </center>

                        </td>
                    </tr>
                </thead>

                <tbody>

                    {{-- Examination Session --}}
                    <tr>
                        <td>
                            <h4>
                                Examination Session:
                                {{ $examinationForm->exam?->name ?? '-' }}
                            </h4>
                        </td>

                        <td>
                            <h4>
                                Form No. :
                                {{ $examinationForm->form_id }}
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
                                        <b>{{ $student->student_name ?? '-' }}</b>
                                    </td>
                                </tr>

                                <tr>
                                    <td><b>Father Name :</b></td>
                                    <td>{{ $student->father_name ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td><b>Adhar No.</b></td>
                                    <td>{{ $student->aadhar ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td><b>ABC / NAD ID:</b></td>
                                    <td>{{ $student->abcid ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td><b>Email ID</b></td>
                                    <td>{{ $student->email ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td><b>Mobile No.</b></td>
                                    <td>{{ $student->phone ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td><b>College</b></td>
                                    <td>
                                        {{ $examinationForm->institute?->name ?? '-' }}
                                    </td>
                                </tr>

                            </table>

                        </td>

                        <td width="30%" style="vertical-align: top; padding: 5px;">
                            <img
                                src="{{ $profileImageUrl }}"
                                style="width: auto; height: 90px; max-width: 110px;"
                                alt="Student Profile Picture">
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
                                        <b>
                                            {{ $student->university_roll_no ?? '-' }}
                                        </b>
                                    </td>
                                </tr>

                                <tr>
                                    <td><b>Form Type :</b></td>
                                    <td>{{ $examinationForm->exam_type }}</td>
                                </tr>

                                <tr>
                                    <td><b>Course</b></td>
                                    <td>{{ $examinationForm->course?->name ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td><b>Scheme</b></td>
                                    <td>{{ $examinationForm->scheme?->name ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td><b>Branch</b></td>
                                    <td>{{ $examinationForm->branch?->name ?? 'NA' }}</td>
                                </tr>

                                <tr>
                                    <td><b>Semester</b></td>
                                    <td>{{ $examinationForm->semester?->name ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td><b>Fee</b></td>
                                    <td>
                                        ₹{{ number_format($displayExamFee, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td><b>Fine / Late Fee</b></td>
                                    <td>
                                        ₹{{ number_format($displayLateFee, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td><b>Total Fee</b></td>
                                    <td>
                                        <b>
                                            ₹{{ number_format($displayTotalFee, 2) }}
                                        </b>
                                    </td>
                                </tr>

                                <tr>
                                    <td><b>Form Status</b></td>
                                    <td>
                                        <b>
                                            <span class="text-danger">Pending</span>
                                        </b>
                                    </td>
                                </tr>

                                <tr>
                                    <td><b>Exam Centre</b></td>
                                    <td>
                                        {{ $examinationForm->examCenter?->name ?? '-' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td><b>Last Appeared Session</b></td>
                                    <td>
                                        {{ $examinationForm->last_appearning_session ?? 'NA' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td><b>Last Appeared Year</b></td>
                                    <td>
                                        {{ $examinationForm->last_appearing_year ?? 'NA' }}
                                    </td>
                                </tr>

                            </table>

                        </td>

                        <td width="20%"></td>
                    </tr>

                    {{-- Subjects Applied Heading --}}
                    <tr>
                        <td colspan="2"
                            style="background: #C4C4C4;"
                            id="table-heading">

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

                                <thead>
                                    <tr>
                                        <th>SNo</th>
                                        <th>Subject Type</th>
                                        <th>Subject Code</th>
                                        <th>Subject Title</th>
                                        <th>Fee Status</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @forelse($examinationForm->choosedSubjects as $index => $chosenSubject)

                                        <tr>
                                            <td>{{ $index + 1 }}</td>

                                            <td>
                                                {{ $chosenSubject->subject?->type ?? '-' }}
                                            </td>

                                            <td>
                                                {{ $chosenSubject->subject?->subject_code ?? '-' }}
                                            </td>

                                            <td>
                                                {{ $chosenSubject->subject?->name ?? '-' }}
                                            </td>

                                            <td>
                                                <b>
                                                    <span class="text-danger">UNPAID</span>
                                                </b>
                                            </td>
                                        </tr>

                                    @empty

                                        <tr>
                                            <td colspan="5" class="text-center">
                                                No subjects found.
                                            </td>
                                        </tr>

                                    @endforelse

                                </tbody>

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
                                I, hereby solemnly declare that the information furnished above
                                is true to the best of my knowledge and belief, I also state that
                                I have read the Ordinances &amp; regulations of the university
                                carefully and the provisions made therein are acceptable to me.
                                I also declare that I will undergo such punishment as may be
                                imposed upon me by the Institute Authorities for errors and/or
                                omissions including suppression of information. I further declare
                                that I have not passed the Examination applied for here-in-before
                                from any other Statutory University.
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

                        <td style="vertical-align: top;">

                            @if(!empty($student->signature) && file_exists(public_path($student->signature)))

                                <img
                                    src="{{ asset($student->signature) }}"
                                    alt="Student Signature"
                                    style="height: 60px; max-width: 150px; object-fit: contain; display: block; margin-bottom: 5px;">

                            @endif

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


{{-- Fee Update and Payment Section --}}
<div class="box-footer">

    <div class="margin80">

        @if($feeDetails['available'] && $feeDetails['is_open'])

            <div class="card border shadow-sm">

                <div class="card-header bg-light">
                    <strong>Examination Fee Details</strong>
                </div>

                <div class="card-body">

                    <div class="row g-3">

                        <div class="col-lg-4 col-md-6">
                            <label class="form-label">Exam Fee</label>
                            <input
                                type="text"
                                class="form-control"
                                value="{{ number_format($currentExamFee, 2) }}"
                                readonly>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <label class="form-label">Fine / Late Fee</label>
                            <input
                                type="text"
                                class="form-control"
                                value="{{ number_format($currentLateFee, 2) }}"
                                readonly>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <label class="form-label">Total Exam Fee</label>
                            <input
                                type="text"
                                class="form-control fw-bold"
                                value="{{ number_format($currentTotalFee, 2) }}"
                                readonly>
                        </div>

                    </div>

                    <form
                        method="POST"
                        action="{{ route('student.examination-forms.update-fee', $examinationForm->id) }}"
                        class="mt-3">

                        @csrf

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-refresh me-1"></i>
                            Update Fee &amp; Pay
                        </button>

                    </form>

                </div>

            </div>

        @else

            <div class="alert alert-warning">
                <i class="fa fa-exclamation-circle me-1"></i>

                {{ $feeDetails['message'] ?? 'Examination fee update window is closed.' }}

                <p class="mb-0 mt-1">
                    The fee update and payment option is currently unavailable.
                </p>
            </div>

        @endif

        <div class="col-lg-12 mt-3" id="messages">

            <p class="text-capitalize p-3 text-danger">
                If your payment has been deducted but the status of your examination
                form has not been updated, please contact the university examination
                department with proof of payment, Transaction ID and Examination Form ID.
            </p>

        </div>

    </div>

</div>

@endsection
