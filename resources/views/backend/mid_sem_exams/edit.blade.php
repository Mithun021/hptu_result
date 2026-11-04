@extends('backend.partial.master')
@section('title', 'Exam')
@section('sub_title', 'Exam List')
@section('backend-content')

<div class="container">

    <h3>Edit Mid-Sem Exam</h3>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('mid-sem-exams.update',$midSemExam->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-4">
                        <label>Exam</label>
                        <select name="exam_id" class="form-control">
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}"
                                    {{ old('exam_id', $midSemExam->exam_id) == $exam->id ? 'selected' : '' }}>
                                    {{ $exam->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('exam_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label>Mid-Sem Exam Name</label>
                        <input type="text"
                            name="mid_sem_exam_name"
                            class="form-control"
                            value="{{ old('mid_sem_exam_name', $midSemExam->mid_sem_exam_name) }}">
                        @error('mid_sem_exam_name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="1"
                                {{ old('status', $midSemExam->status) == 1 ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="0"
                                {{ old('status', $midSemExam->status) == 0 ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                        @error('status')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-2 mt-2">
                        <button class="btn btn-success mt-2">Update</button>
                    </div>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection