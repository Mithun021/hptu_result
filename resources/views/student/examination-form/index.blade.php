@extends('student.partial.master')
@section('title', 'Examination Form')
@section('sub_title', 'Fill Examination Form')

@section('student-content')

@if(empty($activeExam))

    <div class="row justify-content-center mt-5">
        <div class="col-md-8 col-lg-6">

            <div class="card border-0 shadow-sm text-center">

                <div class="card-body py-5 px-4">

                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10"
                            style="width: 85px; height: 85px;">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                width="42"
                                height="42"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="text-danger">

                                <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                <path d="M12 15v3"></path>

                            </svg>

                        </div>
                    </div>

                    <h4 class="fw-bold text-dark mb-2">
                        Examination Form Closed
                    </h4>

                    <p class="text-muted mb-4">
                        The examination form submission window is currently unavailable.
                        Please contact your college or university administration
                        for further information.
                    </p>

                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2">
                        <i class="fa fa-exclamation-circle me-1"></i>
                        Currently Unavailable
                    </span>

                </div>

            </div>

        </div>
    </div>

@else

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Please correct the following errors:</strong>

        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>

        <button type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="Close"></button>
    </div>
@endif

<form action="{{ route('student.examination-form.store') }}"
      method="POST"
      id="examinationForm">

    @csrf



    <input type="hidden" name="student_type" value="{{ $studentType ?? '' }}" id="student_type">
    <input type="hidden" name="exam_type" id="exam_type">

    <div class="row">

        {{-- STUDENT PROFILE --}}
        <div class="col-12">
            <div class="card">

                <div class="card-header">
                    <h4 class="card-title mb-0">Student Profile</h4>
                </div>

                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">

                            <tbody>

                                <tr>
                                    <td width="50%">
                                        <div class="d-flex">
                                            <strong class="w-50">Student Name</strong>
                                            <span class="w-50">
                                                {{ $student->student_name ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </td>

                                    <td width="50%">
                                        <div class="d-flex">
                                            <strong class="w-50">Branch</strong>
                                            <span class="w-50">
                                                {{ $student->branch->name ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="d-flex">
                                            <strong class="w-50">University Roll Number</strong>
                                            <span class="w-50">
                                                {{ $student->university_roll_no ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex">
                                            <strong class="w-50">Scheme</strong>
                                            <span class="w-50">
                                                {{ $student->scheme->name ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="d-flex">
                                            <strong class="w-50">Father Name</strong>
                                            <span class="w-50">
                                                {{ $student->father_name ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex">
                                            <strong class="w-50">Semester</strong>
                                            <span class="w-50">
                                                {{ $currentSemester->name ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="d-flex">
                                            <strong class="w-50">Course</strong>
                                            <span class="w-50">
                                                {{ $student->course->name ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex">
                                            <strong class="w-50">Session</strong>
                                            <span class="w-50">
                                                {{ $student->batch->name ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="2">
                                        <div class="d-flex">
                                            <strong class="w-25">Institute</strong>
                                            <span class="w-75">
                                                {{ $student->institute->name ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>


        {{-- SELECT SEMESTER - FULL WIDTH --}}
        <div class="col-md-12">
            <div class="card">

                <div class="card-header">
                    <h4 class="card-title mb-0">Examination Details</h4>
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-12">

                            <label for="semester_id" class="form-label">
                                Select Semester <span class="text-danger">*</span>
                            </label>

                            <select name="semester_id"
                                    id="semester_id"
                                    class="form-select"
                                    required>

                                <option value="">Select Semester</option>

                                @foreach($studentSemesters as $semester)

                                    <option
                                        value="{{ $semester->id }}"
                                        data-student-type="{{ $semester->exam_type }}">

                                        Semester - {{ $semester->name }}
                                        ({{ $semester->exam_type }})

                                    </option>

                                @endforeach

                            </select>

                        </div>

                    </div>

                </div>
            </div>
        </div>


        {{-- SUBJECT LIST --}}
        <div class="col-12">
            <div class="card">

                <div class="card-header">
                    <h4 class="card-title mb-0">Subject List</h4>
                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered table-striped mb-0">

                            <thead>
                                <tr>

                                    <th width="8%">
                                        <input type="checkbox"
                                               id="checkAllSubjects"
                                               class="form-check-input">
                                    </th>

                                    <th width="20%">Subject Code</th>

                                    <th>Subject Name</th>

                                    <th width="20%">Subject Type</th>

                                </tr>
                            </thead>

                            <tbody id="subjectList">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        Please select semester to load subjects.
                                    </td>
                                </tr>
                            </tbody>

                        </table>

                    </div>

                </div>
            </div>
        </div>


        {{-- EXAM CENTER - FULL WIDTH --}}
        <div class="col-md-12" id="examCenterWrapper" style="display:none;">

            <div class="card">

                <div class="card-header">
                    <h4 class="card-title mb-0">Examination Center</h4>
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-12">

                            <label for="exam_center_id" class="form-label">
                                Select Exam Center <span class="text-danger">*</span>
                            </label>

                            <select name="exam_center_id"
                                    id="exam_center_id"
                                    class="form-select">

                                <option value="">Select Exam Center</option>

                                @foreach($examCenters as $center)

                                    <option value="{{ $center->id }}">
                                        {{ $center->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                    </div>

                </div>
            </div>

        </div>


        {{-- EXAM FEE DETAILS --}}
        <div class="col-12">
            <div class="card">

                <div class="card-header">
                    <h4 class="card-title mb-0">Fee Details</h4>
                </div>

                <div class="card-body">

                    <div class="row">

                        {{-- EXAM FEE --}}
                        <div class="col-md-4 d-none">

                            <label for="exam_fee" class="form-label">
                                Exam Fee
                                <span id="examFeeNote" class="text-muted"></span>
                            </label>

                            <input type="number"
                                   name="exam_fee"
                                   id="exam_fee"
                                   class="form-control"
                                   placeholder="Exam Fee"
                                   readonly>

                        </div>

                        {{-- LATE FEE --}}
                        <div class="col-md-4">

                            <label for="late_fee" class="form-label">
                                Late Fee
                            </label>

                            <input type="number"
                                   name="late_fee"
                                   id="late_fee"
                                   class="form-control"
                                   placeholder="Late Fee"
                                   readonly>

                        </div>

                        {{-- TOTAL EXAM FEE --}}
                        <div class="col-md-4">

                            <label for="total_exam_fee" class="form-label">
                                Total Exam Fee
                            </label>

                            <input type="number"
                                   name="total_exam_fee"
                                   id="total_exam_fee"
                                   class="form-control"
                                   placeholder="Total Exam Fee"
                                   readonly>

                        </div>

                    </div>

                </div>
            </div>
        </div>


        {{-- LAST APPEARING DETAILS --}}
        <div class="col-12">
            <div class="card">

                <div class="card-header">
                    <h4 class="card-title mb-0">Last Appearing Details</h4>
                </div>

                <div class="card-body">

                    <div class="row">

                        {{-- LAST APPEARING SESSION --}}
                        <div class="col-md-6">

                            <label for="last_appearing_session" class="form-label">
                                Last Appearing Session
                            </label>

                            <select name="last_appearing_session"
                                    id="last_appearing_session"
                                    class="form-select">

                                <option value="">Select Session</option>
                                <option value="NA">NA</option>

                                @foreach(range(1, 12) as $month)

                                    <option value="{{ date('F', mktime(0, 0, 0, $month, 1)) }}">
                                        {{ date('F', mktime(0, 0, 0, $month, 1)) }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        {{-- LAST APPEARING YEAR --}}
                        <div class="col-md-6">

                            <label for="last_appearing_year" class="form-label">
                                Last Appearing Year
                            </label>

                            <select name="last_appearing_year"
                                    id="last_appearing_year"
                                    class="form-select">

                                <option value="">Select Year</option>
                                <option value="NA">NA</option>

                                @foreach(range(2015, now()->year) as $year)

                                    <option value="{{ $year }}">
                                        {{ $year }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                    </div>

                </div>
            </div>
        </div>


        {{-- SUBMIT BUTTON --}}
        <div class="col-12 mb-4">

            <div class="d-flex justify-content-end">

                <button type="submit" id="submitExaminationForm" class="btn btn-primary" disabled> <i class="fa fa-paper-plane me-1"></i> <span id="submitButtonText">Submit Examination Form</span> </button>

            </div>

        </div>

    </div>

</form>



@push('scripts')

<script>
$(document).ready(function () {

    let submissionStarted = false;

    let blockRequest = null;
    let feeRequest = null;
    let subjectRequest = null;

    // Eligibility states
    let activeExamValid = false;
    let studentEligible = false;
    let formDateValid = false;
    let feeCalculationValid = false;
    let subjectsLoaded = false;

    const blockedMessage =
        'You are not eligible! please contact in your college/university administration';


    /*
    |--------------------------------------------------------------------------
    | CHECK SUBMIT BUTTON ELIGIBILITY
    |--------------------------------------------------------------------------
    */

    function updateSubmitButton() {

        let semesterId = $('#semester_id').val();
        let subjectCount = $('.subject-checkbox:checked').length;
        let examCenterId = $('#exam_center_id').val();

        let allFieldsValid =
            semesterId !== '' &&
            subjectCount > 0 &&
            examCenterId !== '' &&
            subjectsLoaded;

        let canSubmit =
            !submissionStarted &&
            activeExamValid &&
            studentEligible &&
            formDateValid &&
            feeCalculationValid &&
            allFieldsValid;

        $('#submitExaminationForm').prop('disabled', !canSubmit);
    }


    /*
    |--------------------------------------------------------------------------
    | RESET FORM ELIGIBILITY
    |--------------------------------------------------------------------------
    */

    function resetEligibility() {

        activeExamValid = false;
        studentEligible = false;
        formDateValid = false;
        feeCalculationValid = false;
        subjectsLoaded = false;

        updateSubmitButton();
    }


    /*
    |--------------------------------------------------------------------------
    | RESET EXAMINATION FEES
    |--------------------------------------------------------------------------
    */

    function resetExamFees() {

        if (feeRequest !== null) {
            feeRequest.abort();
            feeRequest = null;
        }

        $('#exam_fee').val('');
        $('#late_fee').val('');
        $('#total_exam_fee').val('');

        formDateValid = false;
        feeCalculationValid = false;

        updateSubmitButton();
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK BLOCKED STUDENT
    |--------------------------------------------------------------------------
    */

    function checkBlockStatus(semesterId) {

        if (blockRequest !== null) {
            blockRequest.abort();
        }

        activeExamValid = false;
        studentEligible = false;

        updateSubmitButton();

        blockRequest = $.ajax({

            url: "{{ route('student.examination-form.check-block-status') }}",
            type: "GET",

            data: {
                semester_id: semesterId
            },

            success: function (response) {

                blockRequest = null;

                if (response.status === 'no_active_exam') {

                    activeExamValid = false;
                    studentEligible = false;

                    Swal.fire({
                        icon: 'warning',
                        title: 'No Active Examination',
                        text: response.message,
                        confirmButtonText: 'OK'
                    });

                    updateSubmitButton();
                    return;
                }

                if (response.status === 'blocked') {

                    activeExamValid = true;
                    studentEligible = false;

                    Swal.fire({
                        icon: 'error',
                        title: 'Not Eligible',
                        text: blockedMessage,
                        confirmButtonText: 'OK'
                    });

                    updateSubmitButton();
                    return;
                }

                if (response.status === 'success') {

                    activeExamValid = true;
                    studentEligible = true;
                }

                updateSubmitButton();
            },

            error: function (xhr, status) {

                if (status === 'abort') {
                    return;
                }

                blockRequest = null;

                activeExamValid = false;
                studentEligible = false;

                updateSubmitButton();

                Swal.fire({
                    icon: 'error',
                    title: 'Eligibility Check Failed',
                    text: 'Unable to verify your examination eligibility. Please try again.'
                });
            }

        });
    }


    /*
    |--------------------------------------------------------------------------
    | FETCH VERIFIED SUBJECTS
    |--------------------------------------------------------------------------
    */

    function loadSubjects(semesterId) {

        if (subjectRequest !== null) {
            subjectRequest.abort();
        }

        subjectsLoaded = false;

        $('#checkAllSubjects').prop('checked', false);

        $('#subjectList').html(`
            <tr>
                <td colspan="4" class="text-center">
                    Loading subjects...
                </td>
            </tr>
        `);

        updateSubmitButton();

        subjectRequest = $.ajax({

            url: "{{ route('student.examination-form.verified-subjects') }}",
            type: "GET",

            data: {

                semester_id: @json($student->course_type === 'Semester Based') ? semesterId : null,

                annual_semester_id: @json($student->course_type === 'Annual Based') ? semesterId : null

            },

            success: function (subjects) {

                subjectRequest = null;

                // Special Chance: Only Theory subjects
                if ($('#student_type').val() === 'Special Chance') {

                    subjects = subjects.filter(function (subject) {
                        return subject.type === 'Theory';
                    });

                }

                let html = '';

                if (subjects.length > 0) {

                    $.each(subjects, function (index, subject) {

                        html += `
                            <tr>
                                <td>
                                    <input type="checkbox"
                                        class="form-check-input subject-checkbox"
                                        name="subject_ids[]"
                                        value="${subject.id}">
                                </td>

                                <td>${subject.subject_code ?? '-'}</td>

                                <td>${subject.name}</td>

                                <td>${subject.type ?? '-'}</td>
                            </tr>
                        `;

                    });

                } else {

                    html = `
                        <tr>
                            <td colspan="4" class="text-center text-danger">
                                No verified subjects found for this semester.
                            </td>
                        </tr>
                    `;

                }

                $('#subjectList').html(html);

                $('#checkAllSubjects').prop('checked', false);

                subjectsLoaded = true;

                // No fee calculation until at least one subject is selected
                resetExamFees();

                updateSubmitButton();
            },

            error: function (xhr, status) {

                if (status === 'abort') {
                    return;
                }

                subjectRequest = null;

                subjectsLoaded = false;

                $('#subjectList').html(`
                    <tr>
                        <td colspan="4" class="text-center text-danger">
                            Unable to load verified subjects. Please try again.
                        </td>
                    </tr>
                `);

                updateSubmitButton();

            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | EXAMINATION FEE CALCULATION
    |--------------------------------------------------------------------------
    */

    function calculateExamFee() {

        let semesterId = $('#semester_id').val();
        let examType = $('#exam_type').val();

        let subjectIds = [];

        $('.subject-checkbox:checked').each(function () {
            subjectIds.push($(this).val());
        });

        // Per paper note
        if (examType === 'Reappear' || examType === 'Special Chance') {
            $('#examFeeNote').text('(per paper)');
        } else {
            $('#examFeeNote').text('');
        }

        // No semester or exam type
        if (!semesterId || !examType) {
            resetExamFees();
            return;
        }

        // IMPORTANT: No selected subject means no fee calculation
        if (subjectIds.length === 0) {
            resetExamFees();
            return;
        }

        // Reset previous calculation status
        formDateValid = false;
        feeCalculationValid = false;

        updateSubmitButton();

        // Abort previous fee request
        if (feeRequest !== null) {
            feeRequest.abort();
            feeRequest = null;
        }

        feeRequest = $.ajax({

            url: "{{ route('student.examination-form.calculate-fee') }}",
            type: "GET",

            data: {

                exam_type: examType,

                semester_id: @json($student->course_type === 'Semester Based') ? semesterId : null,

                annual_semester_id: @json($student->course_type === 'Annual Based') ? semesterId : null,

                subject_ids: subjectIds

            },

            success: function (response) {

                feeRequest = null;

                if (response.status === 'closed') {

                    $('#exam_fee').val('');
                    $('#late_fee').val('');
                    $('#total_exam_fee').val('');

                    formDateValid = false;
                    feeCalculationValid = false;

                    Swal.fire({
                        icon: 'warning',
                        title: 'Form has been closed',
                        html: `
                            <p>${response.message}</p>
                            <p class="mb-1">
                                <strong>Exam Start Date:</strong>
                                ${response.exam_start_date}
                            </p>
                            <p class="mb-0">
                                <strong>Exam End Date:</strong>
                                ${response.exam_end_date}
                            </p>
                        `,
                        confirmButtonText: 'OK'
                    });

                    updateSubmitButton();
                    return;
                }

                if (response.status === 'success') {

                    $('#exam_fee').val(response.exam_fee);
                    $('#late_fee').val(response.late_fee);
                    $('#total_exam_fee').val(response.total_exam_fee);

                    formDateValid = true;
                    feeCalculationValid = true;

                } else {

                    formDateValid = false;
                    feeCalculationValid = false;

                }

                updateSubmitButton();
            },

            error: function (xhr, status) {

                if (status === 'abort') {
                    return;
                }

                feeRequest = null;

                $('#exam_fee').val('');
                $('#late_fee').val('');
                $('#total_exam_fee').val('');

                formDateValid = false;
                feeCalculationValid = false;

                updateSubmitButton();

                let message = 'Unable to calculate examination fee.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Fee Calculation Error',
                    text: message
                });

            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | SEMESTER CHANGE
    |--------------------------------------------------------------------------
    */

    $('#semester_id').on('change', function () {

        let semesterId = $(this).val();
        let selectedOption = $(this).find(':selected');

        let examType = selectedOption.data('student-type');

        $('#exam_type').val(examType || '');

        // Reset fees
        resetExamFees();

        // Reset exam center
        $('#exam_center_id').val('');

        // Reset eligibility
        resetEligibility();

        if (!semesterId) {

            $('#examCenterWrapper').slideUp();

            $('#exam_center_id').prop('required', false);

            $('#subjectList').html(`
                <tr>
                    <td colspan="4" class="text-center text-muted">
                        Please select semester to load subjects.
                    </td>
                </tr>
            `);

            return;
        }

        // Show exam center
        $('#examCenterWrapper').slideDown();

        $('#exam_center_id').prop('required', true);

        // Check student eligibility
        checkBlockStatus(semesterId);

        // Fetch subjects
        loadSubjects(semesterId);

        // Fee will be calculated only after subject selection

    });


    /*
    |--------------------------------------------------------------------------
    | CHECK / UNCHECK ALL SUBJECTS
    |--------------------------------------------------------------------------
    */

    $('#checkAllSubjects').on('change', function () {

        $('.subject-checkbox').prop(
            'checked',
            $(this).is(':checked')
        );

        calculateExamFee();

        updateSubmitButton();

    });


    /*
    |--------------------------------------------------------------------------
    | INDIVIDUAL SUBJECT CHANGE
    |--------------------------------------------------------------------------
    */

    $(document).on('change', '.subject-checkbox', function () {

        let totalSubjects = $('.subject-checkbox').length;

        let checkedSubjects = $('.subject-checkbox:checked').length;

        $('#checkAllSubjects').prop(
            'checked',
            totalSubjects > 0 && totalSubjects === checkedSubjects
        );

        calculateExamFee();

        updateSubmitButton();

    });


    /*
    |--------------------------------------------------------------------------
    | EXAM CENTER CHANGE
    |--------------------------------------------------------------------------
    */

    $('#exam_center_id').on('change', function () {

        updateSubmitButton();

    });

    /*
    |--------------------------------------------------------------------------
    | FINAL FORM SUBMISSION WITH RANDOM DELAY
    |--------------------------------------------------------------------------
    */

    $('#examinationForm').on('submit', function (e) {

        e.preventDefault();

        if (submissionStarted) {
            return false;
        }

        updateSubmitButton();

        if ($('#submitExaminationForm').prop('disabled')) {

            Swal.fire({
                icon: 'warning',
                title: 'Form Not Ready',
                text: 'Please complete all required examination form details and verify your eligibility.'
            });

            return false;
        }

        if (!this.checkValidity()) {
            this.reportValidity();
            return false;
        }

        let form = this;

        // Random delay between 5 and 10 seconds
        let delay = Math.floor(Math.random() * 5001) + 5000;

        submissionStarted = true;

        $('#submitExaminationForm').prop('disabled', true);

        $('#submitButtonText').html(`
            <i class="fa fa-spinner fa-spin me-1"></i>
            Please wait...
        `);

        setTimeout(function () {

            // Submit without triggering this submit event again
            HTMLFormElement.prototype.submit.call(form);

        }, delay);

    });


    // Initially disabled
    resetEligibility();

});

</script>

@endpush

@endif

@endsection
