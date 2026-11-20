
@extends('backend.partial.master')
@section('title', 'Pass Percentage')
@section('sub_title', 'Pass Percentage List')

@section('backend-content')

<div class="row">

    {{-- Add Pass Percentage --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4>Add New Pass Percentage</h4>
            </div>

            <form action="{{ route('pass-percentages.store') }}" method="post">
                @csrf

                <div class="card-body px-3">

                    {{-- Course --}}
                    <div class="form-group mb-3">
                        <label>Course <span class="text-danger">*</span></label>

                        <select name="course_id" id="course_id" class="form-control">
                            <option value="">--Select Course--</option>

                            @foreach ($courses as $item)
                                <option value="{{ $item->id }}"
                                    {{ old('course_id') == $item->id ? 'selected' : '' }}>
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
                        <label>Scheme <span class="text-danger">*</span></label>

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
                               value="{{ old('overall_pass_percentage') }}">

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
                                {{ old('percentage_type') == 'separate' ? 'selected' : '' }}>
                                Separate
                            </option>

                            <option value="aggregate"
                                {{ old('percentage_type') == 'aggregate' ? 'selected' : '' }}>
                                Aggregate
                            </option>
                        </select>

                        @error('percentage_type')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                <div class="card-body pt-0">
                    <button type="submit" class="btn btn-primary">
                        Add Pass Percentage
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- Pass Percentage List --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Pass Percentage List</h4>
            </div>

            <div class="card-body">
                <div class="table-responsive database-table">

                    <table class="table table-bordered"
                           id="responsive-datatable">

                        <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>Course</th>
                                <th>Scheme</th>
                                <th>Overall Percentage (%)</th>
                                <th>Percentage Type</th>
                                <th scope="col" id="no-export">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($passPercentages as $index => $item)
                                <tr>
                                    <td>{{ ++$index }}</td>

                                    <td>{{ $item->course->name ?? '' }}</td>

                                    <td>{{ $item->scheme->name ?? '' }}</td>

                                    <td>{{ $item->overall_pass_percentage }}</td>

                                    <td>
                                        {{ ucfirst($item->percentage_type) }}
                                    </td>

                                    <td>
                                        <div class="dropdown">

                                            <button class="btn btn-sm btn-transparent dropdown-toggle"
                                                    type="button"
                                                    id="actionDropdown{{ $item->id }}"
                                                    data-bs-toggle="dropdown"
                                                    aria-expanded="false">

                                                <i class="mdi mdi-dots-vertical fs-16"></i>
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-start"
                                                aria-labelledby="actionDropdown{{ $item->id }}">

                                                <li>
                                                    <a class="dropdown-item"
                                                       href="{{ route('pass-percentages.edit', $item->id) }}">

                                                        <i class="mdi mdi-pencil-outline me-2"></i>
                                                        Edit
                                                    </a>
                                                </li>

                                                <li>
                                                    <form action="{{ route('pass-percentages.destroy', $item->id) }}"
                                                          method="POST"
                                                          class="d-inline">

                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit"
                                                                class="dropdown-item"
                                                                onclick="return confirm('Delete this pass percentage?')">

                                                            <i class="mdi mdi-delete-outline me-2"></i>
                                                            Delete
                                                        </button>

                                                    </form>
                                                </li>

                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>

                </div>
            </div>
        </div>
    </div>

</div>

<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
<script>
$(document).ready(function () {

    function loadSchemes(courseId) {

        $('#scheme_id').html('<option value="">--Select Scheme--</option>');

        if (!courseId) return;

        $('#scheme_id').html('<option value="">Please wait...</option>');

        $.get("{{ url('courses') }}/" + courseId + "/details", function (response) {

            $('#scheme_id').html('<option value="">--Select Scheme--</option>');

            $.each(response.schemes, function (_, scheme) {
                $('#scheme_id').append(
                    `<option value="${scheme.id}">${scheme.name}</option>`
                );
            });

        });
    }

    // Course Change
    $('#course_id').on('change', function () {
        loadSchemes($(this).val());
    });

    // Auto Load (Edit / Old)
    let defaultCourse = $('#course_id').val();

    if (defaultCourse) {
        loadSchemes(defaultCourse);
    }

});
</script>

@endsection
