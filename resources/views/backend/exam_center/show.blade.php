
@extends('backend.partial.master')

@section('title', 'View Exam Center Allocation')
@section('sub_title', 'Exam Center Details')

@section('backend-content')

<div class="row">
    <div class="col-12">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Exam Center Allocation Details</h4>

                <div>
                    <a href="{{ route('exam-centers.edit', $examCenter->id) }}"
                       class="btn btn-primary btn-sm">
                        <i class="mdi mdi-pencil"></i> Edit
                    </a>

                    <a href="{{ route('exam-centers.index') }}"
                       class="btn btn-secondary btn-sm">
                        <i class="mdi mdi-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <div class="card-body">

                {{-- Basic Information --}}
                <h5 class="mb-3">Basic Information</h5>

                <div class="table-responsive">
                    <table class="table table-bordered">

                        <tr>
                            <th width="25%">Student Type</th>
                            <td>{{ $examCenter->student_type ?? 'N/A' }}</td>
                        </tr>

                        <tr>
                            <th>Course</th>
                            <td>{{ $examCenter->course->name ?? 'N/A' }}</td>
                        </tr>

                        <tr>
                            <th>Institute</th>
                            <td>
                                {{ $examCenter->institute->name ?? 'N/A' }}
                            </td>
                        </tr>

                        <tr>
                            <th>Created At</th>
                            <td>
                                {{ $examCenter->created_at
                                    ? $examCenter->created_at->format('d-m-Y h:i A')
                                    : 'N/A' }}
                            </td>
                        </tr>

                    </table>
                </div>

                {{-- Schemes --}}
                <h5 class="mt-4 mb-3">Allocated Schemes</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th width="10%">#</th>
                                <th>Scheme Name</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($examCenter->schemes as $key => $scheme)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $scheme->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">
                                        No schemes allocated.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                {{-- Branches --}}
                <h5 class="mt-4 mb-3">Allocated Branches</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th width="10%">#</th>
                                <th>Branch Name</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($examCenter->branches as $key => $branch)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $branch->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">
                                        No branches allocated.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                {{-- Semesters --}}
                <h5 class="mt-4 mb-3">Allocated Semesters</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th width="10%">#</th>
                                <th>Semester Name</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($examCenter->semesters as $key => $semester)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $semester->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">
                                        No semesters allocated.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                {{-- Batches --}}
                <h5 class="mt-4 mb-3">Allocated Batches</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th width="10%">#</th>
                                <th>Batch Name</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($examCenter->batches as $key => $batch)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $batch->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">
                                        No batches allocated.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                {{-- Exam Centers --}}
                <h5 class="mt-4 mb-3">Allocated Exam Centers</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th width="10%">#</th>
                                <th>Exam Center / Institute Name</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($examCenter->colleges as $key => $college)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $college->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">
                                        No exam centers allocated.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                {{-- Footer Buttons --}}
                <div class="mt-4">

                    <a href="{{ route('exam-centers.edit', $examCenter->id) }}"
                       class="btn btn-primary">
                        <i class="mdi mdi-pencil"></i> Edit Allocation
                    </a>

                    <a href="{{ route('exam-centers.index') }}"
                       class="btn btn-secondary">
                        Back to List
                    </a>

                </div>

            </div>
        </div>

    </div>
</div>

@endsection
