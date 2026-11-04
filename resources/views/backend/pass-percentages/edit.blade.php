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

                <div class="card-body px-2">
                    <div class="form-group mb-3">
                        <label>Course <span class="text-danger">*</span></label>
                        <select name="course_id" class="form-control">
                            <option value="">--Select--</option>
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

                    <div class="form-group mb-3">
                        <label>Percentage <span class="text-danger">*</span></label>
                        <input type="number" name="overall_pass_percentage" class="form-control"
                               value="{{ old('overall_pass_percentage', $passPercentage->overall_pass_percentage) }}">
                        @error('overall_pass_percentage')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label>Theory min Percentage <span class="text-danger">*</span></label>
                        <input type="number" name="theory_min_percentage" class="form-control"
                               value="{{ old('theory_min_percentage', $passPercentage->theory_min_percentage) }}">
                        @error('theory_min_percentage')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="card-body d-flex justify-content-between">
                    <a href="{{ route('pass-percentages.index') }}" class="btn btn-secondary">
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

@endsection
