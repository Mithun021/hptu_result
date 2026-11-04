@extends('backend.partial.master')
@section('title', 'Courses')
@section('sub_title', 'Course List')
@section('backend-content')
<style>
    tr th, tr td{
        font-size: 12px;
    }
     tr td ul{
        margin: 0;
        padding: 8px;
     }
</style>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="m-0">Course List</h4>
                <a href="{{ route('courses.create') }}" class="btn btn-primary">Add New Course</a>
            </div>
            <div class="card-body">
                {{-- <pre>
                    @php
                        print_r($courses);
                    @endphp
                </pre> --}}
                <div class="table-responsive database-table">
                    <table class="table table-bordered table-bordered"  id="datatable-buttons">
                        <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                {{-- <th>Course</th> --}}
                                <th>Name</th>
                                {{-- <th>Course Type</th> --}}
                                <th>Schemes</th>
                                <th>Semester</th>
                                <th>Session</th>
                                <th>Institute</th>
                                <th scope="col" id="no-export">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($courses as $index => $course)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                {{-- <td>
                                    {{ $course->course }}
                                </td> --}}
                                <td>
                                    <strong>{{ $course->name }}</strong><br>
                                    <small class="text-primary">{{ $course->course_code }}</small>
                                </td>

                                {{-- <td>
                                    {{ $course->course_type }}
                                </td> --}}

                                {{-- Schemes --}}
                                <td>
                                    @if($course->schemes->count())
                                        <ul class="mb-0 pl-1">
                                            @foreach($course->schemes as $scheme)
                                                <li>{{ $scheme->name }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>

                                {{-- Semester --}}
                                {{-- <td>
                                    @if($course->semesters->count())
                                        <ul class="mb-0 pl-1">
                                            @foreach($course->semesters as $semester)
                                                <li>{{ $semester->name }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td> --}}

                                <td>
                                    {{-- Semester Based --}}
                                    @if($course->course_type === 'Semester Based')
                                        @if($course->semesters && $course->semesters->count())
                                            <ul class="mb-0 pl-1">
                                                @foreach($course->semesters as $semester)
                                                    <li>{{ $semester->name }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif

                                    {{-- Annual Based --}}
                                    @elseif($course->course_type === 'Annual Based')
                                        @if($course->annual_semesters && $course->annual_semesters->count())

                                        </pre>
                                            <ul class="mb-0 pl-1">
                                                @foreach($course->annual_semesters as $annual)
                                                    <li>{{ $annual->name }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>


                                {{-- Session / Batch --}}
                                <td>
                                    @if($course->batches->count())
                                        <ul class="mb-0 pl-1">
                                            @foreach($course->batches as $batch)
                                                <li>{{ $batch->name }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>

                                {{-- Institute --}}
                                <td>
                                    @if($course->institutes->count())
                                        <ul class="mb-0 pl-1">
                                            @foreach($course->institutes as $institute)
                                                <li>{{ $institute->name }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>

                                {{-- Action --}}
                                <td id="no-export">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-transparent dropdown-toggle" type="button"
                                                id="actionDropdown{{ $course->id }}" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                            <i class="mdi mdi-dots-vertical fs-16"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-start" aria-labelledby="actionDropdown{{ $course->id }}">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('courses.edit', $course->id) }}">
                                                    <i class="mdi mdi-pencil-outline me-2"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <form action="{{ route('courses.destroy', $course->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item" onclick="return confirm('Delete this course?')">
                                                        <i class="mdi mdi-delete-outline me-2"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No courses found</td>
                            </tr>
                            @endforelse
                            </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
