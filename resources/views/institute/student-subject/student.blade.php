{{-- <div class="row mb-3">
    <div class="col-md-6"><strong>Student Name</strong></div>
    <div class="col-md-6">{{ $student->student_name }}</div>

    <div class="col-md-6"><strong>Student Roll No.</strong></div>
    <div class="col-md-6">{{ $student->university_roll_no }}</div>

    <div class="col-md-6"><strong>Father's Name</strong></div>
    <div class="col-md-6">{{ $student->father_name }}</div>
</div>

<div class="table-responsive">
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>
                    <input type="checkbox" id="select_all">
                </th>
                <th>Subject Name</th>
                <th>Subject Code</th>
            </tr>
        </thead>
        <tbody>
            @forelse($subjects as $subject)
                <tr>
                    <td>
                        <input type="checkbox"
                            class="subject-checkbox"
                            value="{{ $subject->id }}"
                            {{ in_array($subject->id, $selectedSubjects) ? 'checked' : '' }}>
                    </td>
                    <td>{{ $subject->name }}</td>
                    <td>{{ $subject->subject_code }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-danger">
                        No Subjects Found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div> --}}

@forelse($students as $student)

    @php
        $studentSelectedSubjects = $selectedSubjects[$student->id] ?? [];
    @endphp

    <div class="card mb-4 student-subject-card"
         data-student="{{ $student->id }}">

        <div class="card-header bg-light">
            <div class="row">

                <div class="col-md-6">
                    <strong>Student Name:</strong>
                    {{ $student->student_name }}
                </div>

                <div class="col-md-6">
                    <strong>Student Roll No:</strong>
                    {{ $student->university_roll_no }}
                </div>

                <div class="col-md-6 mt-2">
                    <strong>Father's Name:</strong>
                    {{ $student->father_name }}
                </div>

                <div class="col-md-6 mt-2">
                    <strong>Student ID:</strong>
                    {{ $student->id }}
                </div>

            </div>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered">

                    <thead class="table-light">
                        <tr>
                            <th>
                                <input type="checkbox"
                                    class="select-all-student-subjects">
                            </th>

                            <th>Subject Name</th>
                            <th>Subject Code</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($subjects as $subject)

                            <tr>
                                <td>
                                    <input type="checkbox"
                                        class="subject-checkbox"
                                        value="{{ $subject->id }}"
                                        {{ in_array($subject->id, $studentSelectedSubjects) ? 'checked' : '' }}>
                                </td>

                                <td>{{ $subject->name }}</td>

                                <td>{{ $subject->subject_code }}</td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="3"
                                    class="text-center text-danger">
                                    No Subjects Found
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@empty

    <div class="alert alert-danger">
        No Students Found
    </div>

@endforelse

@if($students->isNotEmpty())

    <button type="button"
        id="saveSelectionSubject"
        class="btn btn-primary">
        Save All Students Selection
    </button>

@endif
<!--<button type="button" id="saveSelectionSubject" class="btn btn-primary">Save Selection</button>-->