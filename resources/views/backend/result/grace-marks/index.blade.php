@extends('backend.partial.master')

@section('title', 'Result')
@section('sub_title', 'Grace Marks')

@section('backend-content')

<div class="row">
    <div class="col-md-12">
        <div class="card">

            <div class="card-header">
                <h4 class="card-title m-0">Grace Mark Policy</h4>
            </div>

            {{-- SUCCESS MESSAGE --}}
            @if(session('success'))
                <div class="alert alert-success m-3">
                    {{ session('success') }}
                </div>
            @endif

            {{-- ERROR MESSAGE --}}
            @if(session('error'))
                <div class="alert alert-danger m-3">
                    {{ session('error') }}
                </div>
            @endif

            {{-- VALIDATION ERRORS --}}
            @if ($errors->any())
                <div class="alert alert-danger m-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="graceMarkForm" action="{{ route('results.save-grace') }}" method="post">
                @csrf

                <div class="card-body">

                    <div class="row">

                        <div class="form-group col-md-6 mb-2">
                            <label>Student University Roll no.<span class="text-danger">*</span></label>
                            <input type="text" name="university_roll" class="form-control" value="{{ old('university_roll') }}">
                            @error('university_roll')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
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

                        {{-- COURSE TYPE --}}
                        <div class="form-group col-md-6 mb-2">
                            <label>Course Type<span class="text-danger">*</span></label>

                            <select name="course_type" id="course_type" class="form-control">
                                <option value="">-select--</option>

                                <!--<option value="Semester Based"-->
                                <!--    {{ old('course_type') == 'Semester Based' ? 'selected' : '' }}>-->
                                <!--    Semester Based-->
                                <!--</option>-->

                                <option value="Annual Based"
                                    {{ old('course_type') == 'Annual Based' ? 'selected' : '' }}>
                                    Annual Based
                                </option>
                            </select>

                            @error('course_type')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- COURSE --}}
                        <div class="form-group col-md-6 mb-2">
                            <label>Select Course <span class="text-danger">*</span></label>

                            <select name="course_id" id="course_id" class="form-control">
                                <option value="">--Select--</option>

                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}"
                                        {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('course_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- SCHEME --}}
                        <div class="form-group col-md-6 mb-2">
                            <label>Select Scheme</label>

                            <select name="scheme_id" id="scheme_id" class="form-control">
                                <option value="">--Select--</option>
                            </select>

                            @error('scheme_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- BRANCH --}}
                        <div class="form-group col-md-6 mb-2">
                            <label>Select Branch <span class="text-danger">*</span></label>

                            <select name="branch_id" id="branch_id" class="form-control">
                                <option value="">--Select--</option>
                            </select>

                            @error('branch_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- SEMESTER --}}
                        <div class="form-group col-md-6 mb-2"
                             id="semester_container"
                             style="display: none;">

                            <label>Select Semester</label>

                            <select name="semester_id" id="semester_id" class="form-control">
                                <option value="">--Select--</option>
                            </select>

                            @error('semester_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- ANNUAL TERM --}}
                        <div class="form-group col-md-6 mb-2"
                             id="annual_semester_container"
                             style="display: none;">

                            <label>Select Annual Terms</label>

                            <select name="annual_semester_id"
                                    id="annual_semester_id"
                                    class="form-control">

                                <option value="">--Select--</option>
                            </select>

                            @error('annual_semester_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- BATCH --}}
                        <div class="form-group col-md-6 mb-2">
                            <label>Session / Batch <span class="text-danger">*</span></label>

                            <select name="batch_id" id="batch_id" class="form-control">
                                <option value="">--Select--</option>
                            </select>

                            @error('batch_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- <div class="col-md-6 form-group mb-2" id="subject_container">
                            <label for="subject_id">Select Subject<span class="text-danger">*</span></label>
                            <select name="subject_id" id="subject_id" class="form-control">
                                <option value="">--Select--</option>
                            </select>
                        </div> --}}

                        {{-- GRACE ACTION --}}
                        <div class="form-group col-md-6 mb-2">
                            <label>Grace Action <span class="text-danger">*</span></label>

                            <select name="grace_action" id="grace_action" class="form-control">

                                <option value="">--Select--</option>

                                <option value="apply"
                                    {{ old('grace_action') == 'apply' ? 'selected' : '' }}>
                                    Apply Grace
                                </option>

                                <option value="revert"
                                    {{ old('grace_action') == 'revert' ? 'selected' : '' }}>
                                    Revert Grace
                                </option>

                            </select>

                            @error('grace_action')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- GRACE MARKS --}}
                        <div class="form-group col-md-6 mb-2">
                            <label>
                                Grace Marks
                                <span class="text-danger">*</span>
                            </label>

                            <input type="number"
                                   name="grace_marks"
                                   class="form-control"
                                   step="any"
                                   value="{{ old('grace_marks') }}">
                            @error('grace_marks')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- BUTTON --}}
                        <div class="form-group col-12 mb-2">
                            <button type="submit" id="graceSubmitBtn" class="btn btn-primary">
                                Submit
                            </button>
                        </div>

                    </div>

                </div>

            </form>

        </div>
    </div>
</div>

<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
@include('backend.result.grace-marks.script')

@endsection
