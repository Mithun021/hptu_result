
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

            <table class="table table-bordered table-striped" id="datatable-buttons">

                <thead class="table-primary">
                    <tr>
                        <th>SN</th>
                        <th>Teacher Name</th>
                        <th>Course</th>
                        <th>Scheme</th>
                        <th>Branch</th>
                        <th>Semester / Annual Terms</th>
                        <th>Batch</th>
                        <th>Subject List</th>

                        @if(auth('institute')->user()->account_type == 'institute')
                            <th>Action</th>
                        @endif
                    </tr>
                </thead>

                <tbody>

                    @forelse($assignments as $key => $assignment)

                        <tr>

                            {{-- Serial Number --}}
                            <td>{{ $key + 1 }}</td>

                            {{-- Teacher Name --}}
                            <td>
                                {{ $assignment->teacher?->name ?? '-' }}
                            </td>

                            {{-- Course --}}
                            <td>
                                {{ $assignment->course?->name ?? '-' }}
                            </td>

                            {{-- Scheme --}}
                            <td>
                                {{ $assignment->scheme?->name ?? '-' }}
                            </td>

                            {{-- Branch --}}
                            <td>
                                {{ $assignment->branch?->name ?? '-' }}
                            </td>

                            {{-- Semester / Annual Terms --}}
                            <td>
                                {{ $assignment->semester?->name
                                    ?? $assignment->annualSemester?->name
                                    ?? '-' }}
                            </td>

                            {{-- Batch --}}
                            <td>
                                {{ $assignment->batch?->name ?? '-' }}
                            </td>

                            {{-- Subject List --}}
                            <td>

                                @forelse($assignment->subjects as $subject)

                                    <span class="badge bg-primary mb-1">
                                        {{ $subject->name }}
                                    </span>

                                @empty

                                    <span class="text-danger">
                                        No Subjects Found
                                    </span>

                                @endforelse

                            </td>

                            {{-- Action --}}
                            @if(auth('institute')->user()->account_type == 'institute')

                                <td>

                                    <div class="dropdown">

                                        <button class="btn btn-sm btn-transparent dropdown-toggle"
                                                type="button"
                                                id="actionDropdown{{ $assignment->id }}"
                                                data-bs-toggle="dropdown"
                                                aria-expanded="false">

                                            <i class="mdi mdi-dots-vertical fs-16"></i>

                                        </button>

                                        <ul class="dropdown-menu dropdown-menu-start"
                                            aria-labelledby="actionDropdown{{ $assignment->id }}">

                                            {{-- Edit --}}
                                            <li>
                                                <a class="dropdown-item"
                                                   href="{{ route('edit.assign.subject', $assignment->id) }}">

                                                    <i class="mdi mdi-pencil-outline me-2"></i>
                                                    Edit

                                                </a>
                                            </li>

                                            {{-- Delete --}}
                                            <li>

                                                <form action="{{ route('delete.assign.subject', $assignment->id) }}"
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
                            <td colspan="{{ auth('institute')->user()->account_type == 'institute' ? 9 : 8 }}"
                                class="text-center text-danger">

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
