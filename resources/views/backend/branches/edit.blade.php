@extends('backend.partial.master')
@section('title', 'Edit Branch')
@section('sub_title', 'Edit Branch')
@section('backend-content')

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4>Edit Branch</h4>
            </div>

            <form action="{{ route('branches.update', $branch->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card-body px-1">

                    {{-- Course --}}
                    <div class="form-group mb-3">
                        <label>Course <span class="text-danger">*</span></label>
                        <select name="course_id"
                                class="form-control @error('course_id') is-invalid @enderror">
                            <option value="">-- Select --</option>
                            @foreach ($courses as $item)
                                <option value="{{ $item->id }}"
                                    {{ old('course_id', $branch->course_id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Branch Name --}}
                    <div class="form-group mb-3">
                        <label>Branch Name <span class="text-danger">*</span></label>
                        <input type="text"
                               name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $branch->name) }}">
                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Branch Code --}}
                    <div class="form-group mb-3">
                        <label>Branch Code</label>
                        <input type="text"
                               name="branch_code"
                               class="form-control @error('branch_code') is-invalid @enderror"
                               value="{{ old('branch_code', $branch->branch_code) }}">
                        @error('branch_code')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group mb-2">
                        <label>Institute <span class="text-danger">*</span></label>
                        <div class="scroll-box">
                            @foreach($institutes as $institute)
                                <div>
                                    <input type="checkbox" name="institute[]" value="{{ $institute->id }}"
                                        {{ in_array($institute->id, old('institute', $selectedInstitutes)) ? 'checked' : '' }}>
                                    {{ $institute->name }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>

                <div class="card-body">
                    <button type="submit" class="btn btn-primary">
                        Update Branch
                    </button>
                    <a href="{{ route('branches.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>

@endsection
