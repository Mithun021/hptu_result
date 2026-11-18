
@extends('backend.partial.master')
@section('title', 'Exam')
@section('sub_title', 'Exam List')
@section('backend-content')

<div class="container">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header">Create Mid-Sem Exam</div>
        <div class="card-body">
            <form action="{{ route('mid-sem-exams.store') }}" method="POST">
                @csrf

                <div class="row">

                    <div class="col-md-4 d-none">
                        <label>Course Type</label>
                        <select name="course_type" class="form-control">
                            <option value="">Select Course Type</option>
                            <option value="Semester Based" {{ old('course_type') == 'Semester Based' ? 'selected' : '' }} default selected>Semester Based</option>
                            {{-- <option value="Annual Based" {{ old('course_type') == 'Annual Based' ? 'selected' : '' }}>Annual Based</option> --}}
                        </select>
                        @error('course_type')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label>Exam</label>
                        <select name="exam_id" class="form-control">
                            <option value="">Select Exam</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}" {{ old('exam_id') == $exam->id ? 'selected' : '' }}>{{ $exam->name }}</option>
                            @endforeach
                        </select>
                        @error('exam_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-5">
                        <label>Mid-Sem Exam Name</label>
                        <input type="text" name="mid_sem_exam_name" class="form-control" value="{{ old('mid_sem_exam_name') }}">
                        @error('mid_sem_exam_name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">Select</option>
                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-1 mt-2">
                        <button class="btn btn-primary mt-2">Save</button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-header">All Records</div>
        <div class="card-body">
            <div class="table-responsive database-table">
                <table class="table table-bordered" id="datatable-buttons">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Course Type</th>
                            <th>Exam</th>
                            <th>Mid-Sem Name</th>
                            <th>Status</th>
                            <th width="150">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($midSemExams as $key => $row)
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td>{{ $row->course_type }}</td>
                                <td>{{ $row->exam->name ?? '' }}</td>
                                <td>{{ $row->mid_sem_exam_name }}</td>
                                <td>
                                    @if($row->status)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('mid-sem-exams.edit',$row->id) }}" class="btn btn-sm btn-warning">Edit</a>

                                    <form action="{{ route('mid-sem-exams.destroy',$row->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Delete?')" class="btn btn-sm btn-danger">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
