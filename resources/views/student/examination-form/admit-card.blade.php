
@extends('student.partial.master')

@section('title', 'Admit Card')
@section('sub_title', 'Admit Card')

@section('student-content')

@php
    $formStudent = $examinationForm->student;

    $profileImage = !empty($formStudent->profile_image)
        && file_exists(public_path($formStudent->profile_image))
            ? asset($formStudent->profile_image)
            : null;

    $studentSignature = !empty($formStudent->signature)
        && file_exists(public_path($formStudent->signature))
            ? asset($formStudent->signature)
            : null;

    $examName = $examinationForm->exam?->name ?? 'N/A';
    $formId = $examinationForm->form_id ?? 'N/A';

    $semesterName = $examinationForm->semester?->name ?? 'N/A';
    $examType = strtoupper($examinationForm->exam_type ?? '');

    $semesterDetails = $semesterName;

    if ($examType) {
        $semesterDetails .= ' ' . $examType;
    }

    $collegeName = $examinationForm->institute?->name ?? 'N/A';
    $examCenterName = $examinationForm->examCenter?->name ?? 'N/A';

    $branchName = $examinationForm->branch?->name ?? 'NA';
@endphp

{{-- Notification --}}
<div class="alert alert-warning no-print mb-3" role="alert">
    <i class="fa fa-exclamation-triangle me-2"></i>
    <strong>
        (If the student's image and signature are not displayed, please update the image and signature.)
    </strong>
</div>

{{-- Print Button --}}
<div class="row mb-3 no-print">
    <div class="col-md-12 text-end">
        <button type="button" class="btn btn-primary" onclick="printAdmitCard()">
            <i class="fa fa-print me-1"></i>
            Print Admit Card
        </button>
    </div>
</div>

{{-- ADMIT CARD PRINT AREA --}}
<div class="row" id="admitcard_print">
    <div class="col-md-12">
        <div id="exam_form_data">

            <style>
                #admitcard_print {
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

                .margin80 {
                    margin-top: 120px;
                }

                #table-heading {
                    background: #C4C4C4;
                }

                #stdImage {
                    width: 90px;
                    border: 2px solid #000;
                    padding: 2px;
                    text-align: center;
                }

                #stdImage img.profile {
                    width: 100%;
                    height: 95px;
                    object-fit: cover;
                    display: block;
                }

                #stdImage img.sign {
                    width: 100%;
                    height: 30px;
                    object-fit: contain;
                    display: block;
                    margin-top: 3px;
                }

                #tabledata_head {
                    font-size: 10px !important;
                }

                .admit-heading td {
                    padding: 5px 4px;
                    vertical-align: middle;
                }

                .admit-heading h4 {
                    font-size: 15px;
                    font-weight: 700;
                    margin: 3px 0;
                }

                #institute_name {
                    margin: 0;
                    padding: 5px 10px;
                }

                #exam_form_data p {
                    margin-bottom: 5px;
                }

                @media print {
                    @page {
                        size: A4 portrait;
                        margin: 5mm;
                    }

                    html,
                    body {
                        width: 100% !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        background: #fff !important;
                    }

                    body * {
                        visibility: hidden !important;
                    }

                    #admitcard_print,
                    #admitcard_print * {
                        visibility: visible !important;
                    }

                    #admitcard_print {
                        position: absolute !important;
                        left: 0 !important;
                        top: 0 !important;
                        width: 100% !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        background: #fff !important;
                    }

                    #admitcard_print .col-md-12 {
                        width: 100% !important;
                        max-width: 100% !important;
                        padding: 0 !important;
                        margin: 0 !important;
                    }

                    #exam_form_data {
                        width: 100% !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        zoom: 0.78;
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
                        font-size: 14px !important;
                        margin: 3px 0 !important;
                    }

                    #exam_form_data #clgLogo {
                        height: 65px !important;
                        width: auto !important;
                    }

                    #stdImage {
                        width: 75px !important;
                    }

                    #stdImage img.profile {
                        height: 75px !important;
                    }

                    #stdImage img.sign {
                        height: 25px !important;
                    }

                    #subject-table th,
                    #subject-table td {
                        padding: 2px !important;
                        font-size: 10px !important;
                    }

                    #tabledata-info tr td {
                        padding: 1px 0 !important;
                        font-size: 10px !important;
                    }

                    #rules-info {
                        font-size: 9px !important;
                        line-height: 1.2 !important;
                    }

                    #exam_form_data .instruction-text {
                        font-size: 10px !important;
                        line-height: 1.2 !important;
                    }

                    #exam_form_data .section-heading {
                        padding: 3px 0 !important;
                    }

                    #exam_form_data .section-heading p {
                        padding: 3px 8px !important;
                    }

                    .no-print {
                        display: none !important;
                    }

                    * {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                }
            </style>

            <table width="100%">
                <thead>
                    <tr>
                        <td colspan="2" style="background: #C4C4C4; padding: 10px 0;">
                            <center>
                                <img
                                    src="{{ asset('assets/images/hptulogo.png') }}"
                                    style="height: 80px; width: auto;"
                                    id="clgLogo"
                                    alt="University Logo"
                                >
                            </center>
                        </td>
                    </tr>
                </thead>

                <tbody>

                    {{-- ADMIT CARD HEADING --}}
                    <tr class="admit-heading">
                        <td width="65%">
                            <h4>
                                Admit Card for Examination Session,
                                {{ $examName }}
                            </h4>
                        </td>

                        <td width="35%" style="text-align: right; white-space: nowrap;">
                            <h4>
                                Form No. : {{ $formId }}
                            </h4>
                        </td>
                    </tr>

                    {{-- PERSONAL DETAILS HEADING --}}
                    <tr>
                        <td colspan="2" class="section-heading" style="background: #C4C4C4;">
                            <b>
                                <p style="margin: 0; padding: 5px 10px;">
                                    :: Personal Details ::
                                </p>
                            </b>
                        </td>
                    </tr>

                    {{-- STUDENT PERSONAL DETAILS --}}
                    <tr>
                        <td width="70%" style="vertical-align: top;">
                            <table width="100%" id="tabledata-info">

                                <tr>
                                    <td width="50%">
                                        <b>Semester</b>
                                    </td>
                                    <td width="50%">
                                        <b>{{ $semesterDetails }}</b>
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>Student Name</b>
                                    </td>
                                    <td width="50%">
                                        <b>{{ $formStudent->student_name ?? 'N/A' }}</b>
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>Father Name :</b>
                                    </td>
                                    <td width="50%">
                                        {{ $formStudent->father_name ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <b>University Roll No.</b>
                                    </td>
                                    <td width="50%">
                                        <b>{{ $formStudent->university_roll_no ?? 'N/A' }}</b>
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
                                        {{ $branchName }}
                                    </td>
                                </tr>

                            </table>
                        </td>

                        {{-- STUDENT PHOTO AND SIGNATURE --}}
                        <td width="30%" style="vertical-align: top;">
                            <div id="stdImage">

                                @if($profileImage)
                                    <img
                                        class="profile"
                                        src="{{ $profileImage }}"
                                        alt="Student Profile Image"
                                    >
                                @else
                                    <div style="height: 95px; display: flex; align-items: center; justify-content: center; font-size: 10px;">
                                        Photo Not Available
                                    </div>
                                @endif

                                @if($studentSignature)
                                    <img
                                        class="sign"
                                        src="{{ $studentSignature }}"
                                        alt="Student Signature"
                                    >
                                @else
                                    <div style="height: 30px; display: flex; align-items: center; justify-content: center; font-size: 9px;">
                                        Signature Not Available
                                    </div>
                                @endif

                            </div>
                        </td>
                    </tr>

                    {{-- COLLEGE NAME --}}
                    <tr>
                        <td colspan="2" style="background: #C4C4C4;">
                            <b>
                                <p id="institute_name">
                                    :: College Name :
                                    {{ $collegeName }}
                                </p>
                            </b>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" style="margin: 0; padding: 5px 10px;"></td>
                    </tr>

                    {{-- EXAM CENTRE --}}
                    <tr>
                        <td colspan="2" style="background: #C4C4C4;">
                            <b>
                                <p id="institute_name">
                                    :: Exam Centre :
                                    {{ $examCenterName }}
                                </p>
                            </b>
                        </td>
                    </tr>

                    {{-- SUBJECTS APPLIED HEADING --}}
                    <tr>
                        <td colspan="2" style="background: #C4C4C4;" id="table-heading">
                            <b>
                                <p style="margin: 0; padding: 5px 10px;">
                                    :: Subjects Applied ::
                                </p>
                            </b>
                        </td>
                    </tr>

                    {{-- SUBJECTS TABLE --}}
                    <tr>
                        <td colspan="2">

                            <table width="100%" id="subject-table">

                                <thead>
                                    <tr>
                                        <th>SNo</th>
                                        <th>Subject Type</th>
                                        <th>Subject Code</th>
                                        <th>Subject Title</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($examinationForm->choosedSubjects as $index => $chosenSubject)
                                        @php
                                            $subject = $chosenSubject->subject;
                                        @endphp

                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $subject?->type ?? 'N/A' }}</td>
                                            <td>{{ $subject?->subject_code ?? 'N/A' }}</td>
                                            <td>{{ $subject?->name ?? 'N/A' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" style="text-align: center;">
                                                No subjects found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                            </table>

                        </td>
                    </tr>

                    {{-- INSTRUCTIONS HEADING --}}
                    <tr>
                        <td colspan="2" style="background: #C4C4C4;">
                            <b>
                                <p style="margin: 0; padding: 5px 10px;">
                                    :: Instructions for Student ::
                                </p>
                            </b>
                        </td>
                    </tr>

                    {{-- STUDENT INSTRUCTIONS --}}
                    <tr>
                        <td colspan="2" class="instruction-text">

                            <p>
                                <b>
                                    Dear student, please read the instructions for the end semester
                                    examinations very carefully: -
                                </b>
                            </p>

                            <p id="rules-info">
                                1. The Student is advised to reach their examination hall 30 minutes
                                before the scheduled time of the examination mentioned in the date
                                sheet.
                            </p>

                            <p id="rules-info">
                                2. The Student is advised to bring their admit card at the examination
                                centre along with their identity proof such as driving licence,
                                Aadhar card, PAN card, voter identification card etc. issued by the
                                government authorities. Please do not write anything on the front &
                                back side of the admit card and if found so the same shall be treated
                                as unfair mean and action as per university rules shall be taken.
                            </p>

                            <p id="rules-info">
                                3. The Student is advised to place their personal belongings such as
                                mobile phones, ear/headphones, any electronic devices, bags, pouches,
                                printed or handwritten written material, books etc. outside the
                                examination hall and if anything found in the examination hall shall
                                be considered as violation of examination regulations and be treated
                                as unfair mean and action as per university rules shall be taken.
                            </p>

                            <p id="rules-info">
                                4. The Student is advised to write their roll number, subject code,
                                subject title etc. in their answer scripts (OMR sheet) very carefully
                                and counter check the filled-in details, his/her signatures,
                                invigilator signatures etc. after the completion of examination and
                                before the submission of answer script to the invigilator.
                                In case an incorrect detail of the student is found in the answer
                                script, the result shall not be declared.
                            </p>

                            <p id="rules-info">
                                5. The Student is advised to sign the attendance sheet in a prescribed
                                column in front of his/her name and roll number otherwise he/she
                                shall be treated as absent.
                            </p>

                            <p id="rules-info">
                                6. Do not write anything on the question paper except your roll number.
                                Further, no student is allowed to take away the answer scripts outside
                                the examination hall and if found so, the case in the nearest police
                                station shall be registered against him/her.
                            </p>

                        </td>
                    </tr>

                    {{-- CONTROLLER OF EXAMINATIONS SIGNATURE --}}
                    <tr>
                        <td id="rules-info"></td>

                        <td style="text-align: right; vertical-align: bottom;">
                            <img
                                src="https://hptuexam.com/uploads/teacher_images/coe.png"
                                height="60"
                                width="auto"
                                alt="Controller of Examinations Signature"
                            >

                            <p style="margin: 0;" id="rules-info">
                                <b>Controller of Examinations</b>
                            </p>
                        </td>
                    </tr>

                </tbody>
            </table>

        </div>
    </div>
</div>

<script>
    function printAdmitCard() {
        window.print();
    }
</script>

@endsection
