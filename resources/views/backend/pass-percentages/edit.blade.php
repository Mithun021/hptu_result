
@extends('backend.partial.master')
@section('title', 'Edit Pass Percentage')
@section('sub_title', 'Edit Pass Percentage')

@section('backend-content')

<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card">

            <div class="card-header">
                <h4 class="card-title m-0">Edit Pass Percentage</h4>
            </div>

            <form action="{{ route('pass-percentages.update', $passPercentage->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card-body px-3">

                    {{-- Course --}}
                    <div class="form-group mb-3">
                        <label>
                            Course <span class="text-danger">*</span>
                        </label>

                        <select name="course_id" id="course_id" class="form-control">
                            <option value="">--Select Course--</option>

                            @foreach ($courses as $item)
                                <option value="{{ $item->id }}"
                                    {{ old('course_id', $passPercentage->course_id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('course_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Scheme --}}
                    <div class="form-group mb-3">
                        <label>
                            Scheme <span class="text-danger">*</span>
                        </label>

                        <select name="scheme_id" id="scheme_id" class="form-control">
                            <option value="">--Select Scheme--</option>
                        </select>

                        @error('scheme_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Overall Pass Percentage --}}
                    <div class="form-group mb-3">
                        <label>
                            Overall Pass Percentage (%)
                            <span class="text-danger">*</span>
                        </label>

                        <input type="number"
                               name="overall_pass_percentage"
                               class="form-control"
                               min="0"
                               max="100"
                               step="0.01"
                               value="{{ old('overall_pass_percentage', $passPercentage->overall_pass_percentage) }}">

                        @error('overall_pass_percentage')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Percentage Type --}}
                    <div class="form-group mb-3">
                        <label>
                            Percentage Type
                            <span class="text-danger">*</span>
                        </label>

                        <select name="percentage_type" class="form-control">
                            <option value="">--Select Percentage Type--</option>

                            <option value="separate"
                                {{ old('percentage_type', $passPercentage->percentage_type) == 'separate' ? 'selected' : '' }}>
                                Separate
                            </option>

                            <option value="aggregate"
                                {{ old('percentage_type', $passPercentage->percentage_type) == 'aggregate' ? 'selected' : '' }}>
                                Aggregate
                            </option>
                        </select>

                        @error('percentage_type')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                {{-- Buttons --}}
                <div class="card-body pt-0 d-flex justify-content-between">

                    <a href="{{ route('pass-percentages.index') }}"
                       class="btn btn-secondary">
                        Back
                    </a>

                    <button type="submit" class="btn btn-primary">
                        Update Pass Percentage
                    </button>

                </div>

            </form>
        </div>
    </div>
</div>

<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>

<script>
$(document).ready(function () {

    function loadSchemes(courseId, selectedScheme = '') {

        $('#scheme_id').html('<option value="">--Select Scheme--</option>');

        if (!courseId) return;

        $('#scheme_id').html('<option value="">Please wait...</option>');

        $.get("{{ url('courses') }}/" + courseId + "/details")

            .done(function (response) {

                $('#scheme_id').html('<option value="">--Select Scheme--</option>');

                $.each(response.schemes, function (_, scheme) {

                    let selected = selectedScheme == scheme.id ? 'selected' : '';

                    $('#scheme_id').append(
                        `<option value="${scheme.id}" ${selected}>${scheme.name}</option>`
                    );

                });

            })

            .fail(function (xhr) {

                console.log(xhr.responseText);

                $('#scheme_id').html(
                    '<option value="">Unable to load schemes</option>'
                );

            });
    }

    // Course Change
    $('#course_id').on('change', function () {
        loadSchemes($(this).val());
    });

    // Auto Load Existing Scheme
    let defaultCourse = $('#course_id').val();

    let defaultScheme = "{{ old('scheme_id', $passPercentage->scheme_id) }}";

    if (defaultCourse) {
        loadSchemes(defaultCourse, defaultScheme);
    }

});
</script>

@endsection
