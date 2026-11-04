@extends('backend.partial.master')
@section('title', 'Student')
@section('sub_title', 'Edit Student')

@section('backend-content')
<div class="row">
    <div class="col-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title m-0">Edit Student</h4>
                <a href="{{ route('students.index') }}" class="btn btn-sm btn-danger">
                    <i data-feather="arrow-left" class="me-1" style="width:14px;height:14px;"></i> Back
                </a>
            </div>

            <form action="{{ route('students.update', $student->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="card-body">

                    {{-- ================= ERRORS ================= --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- ================= COURSE DETAILS ================= --}}
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label>Course Type <span class="text-danger">*</span></label>
                            <select name="course_type" id="course_type" class="form-control">
                                <option value="">--Select--</option>
                                <option value="Annual Based"
                                    {{ old('course_type', $student->course_type)=='Annual Based'?'selected':'' }}>
                                    Annual Based
                                </option>
                                <option value="Semester Based"
                                    {{ old('course_type', $student->course_type)=='Semester Based'?'selected':'' }}>
                                    Semester Based
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-2">
                            <label>Select Course <span class="text-danger">*</span></label>
                            <select name="course_id" id="course_id" class="form-control">
                                <option value="">--Select--</option>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}"
                                        {{ old('course_id', $student->course_id)==$course->id?'selected':'' }}>
                                        {{ $course->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-2">
                            <label>Select Scheme</label>
                            <select name="scheme_id" id="scheme_id" class="form-control"></select>
                        </div>

                        <div class="col-md-4 mb-2">
                            <label>Select Branch <span class="text-danger">*</span></label>
                            <select name="branch_id" id="branch_id" class="form-control"></select>
                        </div>

                        <div class="col-md-4 mb-2">
                            <label>Select Batch <span class="text-danger">*</span></label>
                            <select name="batch_id" id="batch_id" class="form-control"></select>
                        </div>

                        <div class="col-md-4 mb-2">
                            <label>Select Institute <span class="text-danger">*</span></label>
                            <select name="institute_id" id="institute_id" class="form-control"></select>
                        </div>

                        <div class="col-md-4 mb-2" id="semester_container" style="display:none;">
                            <label>Select Semester</label>
                            <select name="semester_id" id="semester_id" class="form-control"></select>
                        </div>

                        <div class="col-md-4 mb-2" id="annual_semester_container" style="display:none;">
                            <label>Select Annual Terms</label>
                            <select name="annual_semester_id" id="annual_semester_id" class="form-control"></select>
                        </div>
                    </div>

                    {{-- ================= PERSONAL DETAILS ================= --}}
                    <div class="row">
                        <div class="col-md-6 col-6 form-group">
                            <label for="">University Roll No.<span class="text-danger">*</span></label>
                            <input type="text" name="university_roll_no" value="{{ old('university_roll_no', $student->university_roll_no) }}" class="form-control">
                            @error('university_roll_no') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 col-6 form-group">
                            <label for="">Registration No.</label>
                            <input type="text" name="reg_no" value="{{ old('reg_no', $student->reg_no) }}" class="form-control">
                            @error('reg_no') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 col-6 form-group">
                            <label for="">Admission Year</label>
                            <input type="date" 
                                name="admission_year" 
                                value="{{ old('admission_year', $student->admission_year ? date('Y-m-d', strtotime($student->admission_year)) : '') }}" 
                                class="form-control">
                            @error('admission_year') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6 col-6 form-group">
                            <label for="">Completion Year</label>
                            <input type="date" 
                                name="completion_year" 
                                value="{{ old('completion_year', $student->completion_year ? date('Y-m-d', strtotime($student->completion_year)) : '') }}" 
                                class="form-control">
                            @error('completion_year') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-12 form-group mt-2">
                            <label for="">Student Name<span class="text-danger">*</span></label>
                            <input type="text" name="student_name" value="{{ old('student_name', $student->student_name) }}" class="form-control">
                            @error('student_name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 col-6 form-group mt-2">
                            <label for="">Father's Name<span class="text-danger">*</span></label>
                            <input type="text" name="father_name" value="{{ old('father_name', $student->father_name) }}" class="form-control">
                            @error('father_name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 col-6 form-group mt-2">
                            <label for="">Mother's Name</label>
                            <input type="text" name="mother_name" value="{{ old('mother_name', $student->mother_name) }}" class="form-control">
                            @error('mother_name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 col-6 form-group mt-2">
                            <label for="">ABC Id.</label>
                            <input type="text" name="abcid" value="{{ old('abcid', $student->abcid) }}" class="form-control">
                            @error('abcid') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 col-6 form-group mt-2">
                            <label for="">Gender<span class="text-danger">*</span></label>
                            <select name="gender" class="form-control">
                                <option value="">--Select--</option>
                                <option value="male" {{ old('gender', $student->gender)=='male'?'selected':'' }}>Male</option>
                                <option value="female" {{ old('gender', $student->gender)=='female'?'selected':'' }}>Female</option>
                                <option value="other" {{ old('gender', $student->gender)=='other'?'selected':'' }}>Other</option>
                            </select>
                            @error('gender') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 col-6 form-group mt-2">
                            <label for="">Date of Birth<span class="text-danger">*</span></label>
                           <input type="date"
                                name="dob"
                                value="{{ old('dob', optional($student->dob)->format('Y-m-d')) }}"
                                class="form-control">
                            @error('dob') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 col-6 form-group mt-2">
                            <label for="">Aadhar</label>
                            <input type="text" name="aadhar" value="{{ old('aadhar', $student->aadhar) }}" class="form-control" maxlength="12">
                            @error('aadhar') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 form-group mt-2">
                            <label for="">Current Address</label>
                            <textarea name="current_address" class="form-control">{{ old('current_address', $student->current_address) }}</textarea>
                            @error('current_address') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 form-group mt-2">
                            <label for="">Parmanent Address</label>
                            <textarea name="parmanent_address" class="form-control">{{ old('parmanent_address', $student->parmanent_address) }}</textarea>
                            <label for="for-current-address"><input type="checkbox" id="same_address"> Check if the permanent address is the same as the current address.</label>
                            @error('parmanent_address') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 col-6 form-group mt-2">
                            <label for="">Phone no.<span class="text-danger">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone', $student->phone) }}" class="form-control" maxlength="10">
                            @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 col-6 form-group mt-2">
                            <label for="">Email Address<span class="text-danger">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $student->email) }}" class="form-control">
                            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-3 col-6 form-group mt-2">
                            <label for="">Nationality</label>
                            <input type="text" name="nationality" value="{{ old('nationality', $student->nationality) }}" class="form-control">
                            @error('nationality') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-3 col-6 form-group mt-2">
                            <label for="">State</label>
                            <input type="text" name="state" value="{{ old('state', $student->state) }}" class="form-control">
                            @error('state') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-3 col-6 form-group mt-2">
                            <label for="">City</label>
                            <input type="text" name="city" value="{{ old('city', $student->city) }}" class="form-control">
                            @error('city') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-3 col-6 form-group mt-2">
                            <label for="">Category</label>
                            <input type="text" name="category" value="{{ old('category', $student->category) }}" class="form-control">
                            @error('category') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    {{-- ================= FILES ================= --}}
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label>Profile Image</label>
                            <input type="file" name="profile_image" class="form-control">
                            @if($student->profile_image)
                                <img src="{{ asset($student->profile_image) }}" width="120" class="mt-2">
                            @else
                                <p class="text-muted mt-1">No file available</p>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label>Signature</label>
                            <input type="file" name="signature" class="form-control">
                            @if($student->signature)
                                <img src="{{ asset($student->signature) }}" width="120" class="mt-2">
                            @else
                                <p class="text-muted mt-1">No file available</p>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-6 form-group mt-2">
                            <label for="">Password</label>
                            <input type="password" name="password" class="form-control" maxlength="12">
                            @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 col-6 form-group mt-2">
                            <label for="">Pursuing Status<span class="text-danger">*</span></label>
                            <select name="pursuing_status" class="form-control">
                                <option value="yes" {{ old('pursuing_status',$student->pursuing_status)=='yes'?'selected':'' }}>Yes</option>
                                <option value="no" {{ old('pursuing_status',$student->pursuing_status)=='no'?'selected':'' }}>No</option>
                            </select>
                            @error('pursuing_status') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                </div>

                <div class="card-footer">
                    <button class="btn btn-primary px-5">Update Student</button>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- ================= SCRIPTS ================= --}}
<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>

<script>
$(document).ready(function () {

    let selected = {
        scheme  : "{{ old('scheme_id', $student->scheme_id) }}",
        branch  : "{{ old('branch_id', $student->branch_id) }}",
        batch   : "{{ old('batch_id', $student->batch_id) }}",
        inst    : "{{ old('institute_id', $student->institute_id) }}",
        sem     : "{{ old('semester_id', $student->semester_id) }}",
        asem    : "{{ old('annual_semester_id', $student->annual_semester_id) }}"
    };

    function resetSemesterUI() {
        $('#semester_container').hide();
        $('#annual_semester_container').hide();
        $('#semester_id').html('<option value="">--Select Semester--</option>');
        $('#annual_semester_id').html('<option value="">--Select--</option>');
    }

    function loadCourseData(courseId) {
        if (!courseId) return;

        resetSemesterUI();

        /* =========================
           LOADERS
        ==========================*/
        $('#branch_id').html('<option>Please wait...</option>');
        $('#scheme_id').html('<option>Please wait...</option>');
        $('#batch_id').html('<option>Please wait...</option>');
        $('#institute_id').html('<option>Please wait...</option>');

        /* =========================
           FETCH BRANCHES (FIXED)
        ==========================*/
        $.get("{{ url('branches') }}/" + courseId, function (branches) {
            $('#branch_id').html('<option value="">--Select Branch--</option>');
            $.each(branches, function (_, branch) {
                $('#branch_id').append(
                    `<option value="${branch.id}" ${branch.id==selected.branch?'selected':''}>
                        ${branch.name}
                     </option>`
                );
            });
        });

        /* =========================
           FETCH COURSE DETAILS
        ==========================*/
        $.get("{{ url('courses') }}/" + courseId + "/details", function (response) {

            /* ---------- Schemes ---------- */
            $('#scheme_id').html('<option value="">--Select Scheme--</option>');
            $.each(response.schemes, function (_, scheme) {
                $('#scheme_id').append(
                    `<option value="${scheme.id}" ${scheme.id==selected.scheme?'selected':''}>
                        ${scheme.name}
                     </option>`
                );
            });

            /* ---------- Batches ---------- */
            $('#batch_id').html('<option value="">--Select Batch--</option>');
            $.each(response.batches, function (_, batch) {
                $('#batch_id').append(
                    `<option value="${batch.id}" ${batch.id==selected.batch?'selected':''}>
                        ${batch.name}
                     </option>`
                );
            });

            /* ---------- Institutes ---------- */
            $('#institute_id').html('<option value="">--Select Institute--</option>');
            $.each(response.institutes, function (_, institute) {
                $('#institute_id').append(
                    `<option value="${institute.id}" ${institute.id==selected.inst?'selected':''}>
                        ${institute.name}
                     </option>`
                );
            });

            let courseType = $('#course_type').val();

            /* =========================
               SEMESTER BASED
            ==========================*/
            if (courseType === 'Semester Based') {
                $('#semester_container').show();
                $('#annual_semester_container').hide();

                $('#semester_id').html('<option value="">--Select Semester--</option>');
                $.each(response.semesters, function (_, semester) {
                    $('#semester_id').append(
                        `<option value="${semester.id}" ${semester.id==selected.sem?'selected':''}>
                            ${semester.name}
                         </option>`
                    );
                });
            }

            /* =========================
               ANNUAL BASED
            ==========================*/
            if (courseType === 'Annual Based') {
                $('#annual_semester_container').show();
                $('#semester_container').hide();

                $('#annual_semester_id').html('<option value="">--Select--</option>');
                $.each(response.annual_semesters, function (_, annual) {
                    $('#annual_semester_id').append(
                        `<option value="${annual.id}" ${annual.id==selected.asem?'selected':''}>
                            ${annual.name}
                         </option>`
                    );
                });
            }
        });
    }

    /* =========================
       CHANGE EVENTS
    ==========================*/
    $('#course_id').on('change', function () {
        loadCourseData($(this).val());
    });

    $('#course_type').on('change', function () {
        resetSemesterUI();
        if ($('#course_id').val()) {
            loadCourseData($('#course_id').val());
        }
    });

    /* =========================
       AUTO LOAD (EDIT / OLD)
    ==========================*/
    if ($('#course_id').val() && $('#course_type').val()) {
        loadCourseData($('#course_id').val());
    }

});
</script>

@endsection
