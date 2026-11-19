@extends('backend.partial.master')
@section('title', 'Examination Form')
@section('sub_title', 'Examination Form List')
@section('backend-content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Examination Form</h3>
            </div>
            <div class="card-body">
                <form method="get" action="{{ route('admin.download-exam-form-sheet') }}">
                    <div class="row">
                        <div class="col-md-6 form-group mb-2">
                            <label for="student_roll_number">Student Roll Number</label>
                            <input type="text" name="student_roll_number" id="student_roll_number" class="form-control">
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label for="exam_id">Exam Name<span class="text-danger">*</span></label>
                            <select name="exam_id" id="exam_id" class="form-control">
                                <option value="">--Select--</option>
                                @foreach ($exams as $exam)
                                    <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-2 d-none">
                            <label for="course_type">Course Type<span class="text-danger">*</span></label>
                            <select name="course_type" id="course_type" class="form-control">
                                <option value="">--Select--</option>
                                {{-- <option value="Annual Based">Annual Based</option> --}}
                                <option value="Semester Based" default selected>Semester Based</option>
                            </select>
                            @error('course_type') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label for="course_id">Select Course</label>
                            <select name="course_id" id="course_id" class="form-control">
                                <option value="">--Select--</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label for="scheme_id">Select Scheme</label>
                            <select name="scheme_id" id="scheme_id" class="form-control">
                                <option value="">--Select--</option>
                            </select>
                            @error('scheme_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label for="branch_id">Select Branch</label>
                            <select name="branch_id" id="branch_id" class="form-control">
                                <option value="">--Select--</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-2" id="semester_container"  style="display: none;">
                            <label for="semester_id">Select Semester</label>
                            <select name="semester_id" id="semester_id" class="form-control">
                                <option value="">--Select--</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-2" id="annual_semester_container"  style="display: none;">
                            <label for="annual_semester_id">Select Annual Terms</label>
                            <select name="annual_semester_id" id="annual_semester_id" class="form-control">
                                <option value="">--Select--</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label>Session / Batch </label>
                            <select name="batch_id" id="batch_id" class="form-control">
                                <option value="">--Select--</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-2" id="subject_container">
                            <label for="institute_id">Select Institute</label>
                            <select name="institute_id" id="institute_id" class="form-control">
                                <option value="">--Select--</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-2" id="subject_container">
                            <label for="form_type">Form Type<span class="text-danger">*</span></label>
                            <select name="form_type" id="form_type" class="form-control">
                                <option value="row-wise">Row Wise</option>
                                <option value="column-wise">Column Wise</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group mt-3 mb-2">
                            <button type="submit"  class="btn btn-primary">Download</button>
                        </div>
                    </div>
                    </form>
            </div>
        </div>
    </div>
</div>


<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>

@include('backend.examination-form.components.search-modal-data-js')
@endsection
