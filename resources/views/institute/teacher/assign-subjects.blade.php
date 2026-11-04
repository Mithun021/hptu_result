@extends('institute.partial.master')
@section('title', 'Assign Subjects')
@section('sub_title', 'List of Assign Subjects to Teachers')

@section('institute-content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title m-0">List of Assign Subjects to Teachers</h4>

        @if(auth('institute')->user()->account_type == 'institute')
            <a href="{{ route('create.assign.subject') }}" class="btn btn-sm btn-primary">
                Add New
            </a>
        @endif
    </div>

    <div class="card-body">

        <div class="table-responsive database-table">
            <table class="table table-bordered table-striped"  id="datatable-buttons">
                <thead class="table-primary">
                    <tr>
                        <th>SN</th>
                        <th>Teacher Name</th>
                        <th>Course</th>
                        <th>Branch</th>
                        <th>Semester</th>
                        <th>Subject List</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>

                    @forelse($teachers as $index => $teacher)

                        @php
                            $subjects = $teacher->subjects;
                            $firstSubject = $subjects->first();
                        @endphp

                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $teacher->name }}</td>

                            <td>
                                {{ $firstSubject?->course?->name ?? '-' }}
                            </td>

                            <td>
                                {{ $firstSubject?->branch?->name ?? '-' }}
                            </td>

                            <td>
                                @if($firstSubject?->course_type === 'Semester Based')
                                    {{ $firstSubject?->semester?->name ?? '-' }}
                                @else
                                    {{ $firstSubject?->annual_semester?->name ?? '-' }}
                                @endif
                            </td>

                            <td>
                                @foreach($subjects as $subject)
                                    <span class="badge bg-primary">
                                        {{ $subject->name }}
                                    </span>
                                @endforeach
                            </td>
                            @if(auth('institute')->user()->account_type == 'institute')
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-transparent dropdown-toggle"
                                            type="button"
                                            id="actionDropdown{{ $teacher->id }}"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                        <i class="mdi mdi-dots-vertical fs-16"></i>
                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-start"
                                        aria-labelledby="actionDropdown{{ $teacher->id }}">

                                        {{-- Edit --}}
                                        <li>
                                            <a class="dropdown-item"
                                            href="{{ route('edit.assign.subject', $teacher->id) }}">
                                                <i class="mdi mdi-pencil-outline me-2"></i>
                                                Edit
                                            </a>
                                        </li>

                                        {{-- Delete --}}
                                        <li>
                                            <form action="{{ route('delete.assign.subject', $teacher->id) }}"
                                                method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="dropdown-item"
                                                        onclick="return confirm('Are you sure you want to delete this assignment?')">
                                                    <i class="mdi mdi-delete-outline me-2"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </li>

                                    </ul>
                                </div>
                            </td>
                            @endif
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-danger">
                                No Assigned Subjects Found
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection