
@extends('backend.partial.master')

@section('title', 'Exam Center Allocation')
@section('sub_title', 'Exam Center List')

@section('backend-content')

<div class="row">
    <div class="col-12">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Exam Center Allocation</h4>

                <a href="{{ route('exam-centers.create') }}" class="btn btn-primary">
                    <i class="mdi mdi-plus"></i> Add Exam Center
                </a>
            </div>

            <div class="card-body">

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">

                    <table id="datatable-buttons"
                           class="table table-bordered table-striped nowrap w-100">

                        <thead>
                            <tr>
                                <th width="50">#</th>
                                <th>Student Type</th>
                                <th>Institute</th>
                                <th>Course</th>
                                <th>Branches</th>
                                <th>Schemes</th>
                                <th>Exam Centers</th>
                                <th>Semesters</th>
                                <th>Batches</th>
                                <th width="80">Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($examCenters as $key => $center)

                                <tr>
                                    <td>{{ $key + 1 }}</td>

                                    <td>
                                        {{ $center->student_type }}
                                    </td>

                                    <td>
                                        {{ $center->institute->name ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $center->course->name ?? 'N/A' }}
                                    </td>

                                    {{-- Branches List --}}
                                    <td>
                                        @forelse($center->branches as $branch)
                                            <div>{{ $branch->name }}</div>
                                        @empty
                                            N/A
                                        @endforelse
                                    </td>

                                    {{-- Schemes List --}}
                                    <td>
                                        @forelse($center->schemes as $scheme)
                                            <div>{{ $scheme->name }}</div>
                                        @empty
                                            N/A
                                        @endforelse
                                    </td>

                                    {{-- Exam Centers / Colleges List --}}
                                    <td>
                                        @forelse($center->colleges as $college)
                                            <div>{{ $college->name }}</div>
                                        @empty
                                            N/A
                                        @endforelse
                                    </td>

                                    {{-- Semesters List --}}
                                    <td>
                                        @forelse($center->semesters as $semester)
                                            <div>{{ $semester->name }}</div>
                                        @empty
                                            N/A
                                        @endforelse
                                    </td>

                                    {{-- Batches List --}}
                                    <td>
                                        @forelse($center->batches as $batch)
                                            <div>{{ $batch->name }}</div>
                                        @empty
                                            N/A
                                        @endforelse
                                    </td>

                                    {{-- Action --}}
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-transparent dropdown-toggle"
                                                    type="button"
                                                    id="actionDropdown{{ $center->id }}"
                                                    data-bs-toggle="dropdown"
                                                    aria-expanded="false">

                                                <i class="mdi mdi-dots-vertical fs-16"></i>
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-start"
                                                aria-labelledby="actionDropdown{{ $center->id }}">

                                                <li>
                                                    <a class="dropdown-item"
                                                       href="{{ route('exam-centers.show', $center->id) }}">
                                                        <i class="mdi mdi-eye-outline me-2"></i>
                                                        View
                                                    </a>
                                                </li>

                                                <li>
                                                    <a class="dropdown-item"
                                                       href="{{ route('exam-centers.edit', $center->id) }}">
                                                        <i class="mdi mdi-pencil-outline me-2"></i>
                                                        Edit
                                                    </a>
                                                </li>

                                                <li>
                                                    <form action="{{ route('exam-centers.destroy', $center->id) }}"
                                                          method="POST"
                                                          class="d-inline">

                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit"
                                                                class="dropdown-item"
                                                                onclick="return confirm('Are you sure you want to delete this exam center allocation?')">

                                                            <i class="mdi mdi-delete-outline me-2"></i>
                                                            Delete
                                                        </button>

                                                    </form>
                                                </li>

                                            </ul>
                                        </div>
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="10" class="text-center">
                                        No Exam Center Allocation Found.
                                    </td>
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
