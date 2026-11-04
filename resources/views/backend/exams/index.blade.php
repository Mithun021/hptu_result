@extends('backend.partial.master')
@section('title', 'Exam')
@section('sub_title', 'Exam List')
@section('backend-content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between mb-3">
                <h4 class="card-title m-0">Exams List</h4>
                <a href="{{ route('exams.create') }}" class="btn btn-sm btn-primary">+ Add Exam</a>
            </div>
            <div class="card-body">
                <div class="table-responsive database-table">
                    <table class="table table-bordered table-bordered"  id="datatable-buttons">
                        <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Short</th>
                                <th>Academic Year</th>
                                <th>Active</th>
                                <th>Show Admit</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($exams as $exam)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $exam->name }}</td>
                                    <td>{{ $exam->sort_name }}</td>
                                    <td>{{ $exam->exam_month }}</td>
                                    <td>
                                        <span class="badge {{ $exam->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $exam->is_active ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $exam->show_admitcard ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $exam->show_admitcard ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-transparent dropdown-toggle" type="button" 
                                                    id="actionDropdown{{ $exam->id }}" data-bs-toggle="dropdown" 
                                                    aria-expanded="false">
                                                <i class="mdi mdi-dots-vertical fs-16"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-start" aria-labelledby="actionDropdown{{ $exam->id }}">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('exams.edit', $exam->id) }}">
                                                        <i class="mdi mdi-pencil-outline me-2"></i> Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('exams.destroy', $exam->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item" onclick="return confirm('Delete this branch?')">
                                                            <i class="mdi mdi-delete-outline me-2"></i> Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center">No records found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
