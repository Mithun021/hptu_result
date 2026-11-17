
@extends('student.partial.master')
@section('title', 'Examination Form')
@section('sub_title', 'Fill Examination Form')
@section('student-content')

<div class="row">

    <div class="col-12">

        <div class="card">

            <div class="card-header">
                <h4 class="card-title mb-0">Examination Form Debug</h4>
            </div>

            <div class="card-body">

                {{-- Student Current Semester --}}
                <div class="mb-4">

                    <h5>Student Current Semester</h5>

                    <div class="alert alert-info mb-0">
                        <strong>
                            {{ $currentSemester->name ?? 'Not Available' }}
                        </strong>
                    </div>

                </div>


                {{-- Student Type --}}
                <div class="mb-4">

                    <h5>Student Type</h5>

                    @if($studentType)

                        <div class="alert alert-success mb-0">
                            <strong>{{ $studentType }}</strong>
                        </div>

                    @else

                        <div class="alert alert-warning mb-0">
                            No matching Exam Center allocation found for your profile.
                        </div>

                        <div class="mt-2 text-muted">
                            Please contact your institute administration.
                        </div>

                    @endif

                </div>


                {{-- Student Semesters With Exam Type --}}
                <div class="mb-4">

                    <h5>Student Semester With Type</h5>

                    @if($studentSemesters->isNotEmpty())

                        <div class="table-responsive">

                            <table class="table table-bordered table-striped">

                                <thead>
                                    <tr>
                                        <th width="10%">#</th>
                                        <th>Semester</th>
                                        <th>Examination Type</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach($studentSemesters as $key => $semester)

                                        <tr>
                                            <td>{{ $key + 1 }}</td>

                                            <td>
                                                {{ $semester->name }}
                                            </td>

                                            <td>
                                                @if($semester->exam_type === 'Regular')
                                                    <span class="badge bg-success">
                                                        {{ $semester->exam_type }}
                                                    </span>
                                                @elseif($semester->exam_type === 'Reappear')
                                                    <span class="badge bg-warning text-dark">
                                                        {{ $semester->exam_type }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-info">
                                                        {{ $semester->exam_type }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    @else

                        <div class="alert alert-warning">
                            No semesters available for examination.
                        </div>

                    @endif

                </div>


                {{-- Debug: Exam Center Allocation --}}
                @if($examCenter)

                    <div class="mt-4">

                        <h5>Matched Exam Center Allocation (Debug)</h5>

                        <table class="table table-bordered">

                            <tr>
                                <th width="30%">Allocation ID</th>
                                <td>{{ $examCenter->id }}</td>
                            </tr>

                            <tr>
                                <th>Student Type</th>
                                <td>{{ $examCenter->student_type }}</td>
                            </tr>

                            <tr>
                                <th>Course</th>
                                <td>{{ $examCenter->course->name ?? 'N/A' }}</td>
                            </tr>

                            <tr>
                                <th>Institute</th>
                                <td>{{ $examCenter->institute->name ?? 'N/A' }}</td>
                            </tr>

                        </table>

                    </div>

                @endif

            </div>

        </div>

    </div>

</div>

@endsection
