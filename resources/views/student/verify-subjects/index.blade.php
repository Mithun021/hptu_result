
@extends('student.partial.master')

@section('title', 'Verify Subjects')

@section('sub_title', 'Student Subject Verification')

@section('student-content')

<style>
    /* Main Cards */
    .profile-card,
    .subject-card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
        height: 100%;
    }

    /* Section Heading */
    .section-heading {
        padding: 14px 18px;
        background: #f8f9fa;
        border-bottom: 1px solid #e5e7eb;
        font-size: 16px;
        font-weight: 600;
    }

    /* Profile Details */
    .profile-details {
        padding: 15px;
    }

    .profile-details .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 9px 0;
        border-bottom: 1px solid #eee;
        font-size: 13px;
    }

    .profile-details .detail-row:last-child {
        border-bottom: 0;
    }

    .detail-label {
        color: #6b7280;
        font-weight: 500;
    }

    .detail-value {
        font-weight: 600;
        text-align: right;
    }

    /* Subject List */
    .subject-list {
        padding: 15px;
    }

    /* Subject Item */
    .subject-item {
        padding: 10px 12px;
        border: 1px solid #eee;
        border-radius: 5px;
        margin-bottom: 9px;
        background: #fff;
    }

    /* Checkbox Horizontal Alignment */
    .subject-item .form-check {
        display: flex;
        align-items: center;
        gap: 10px;
        padding-left: 0;
        margin-bottom: 0;
        min-height: 22px;
    }

    .subject-item input[type="checkbox"] {
        position: static;
        float: none;
        flex-shrink: 0;
        margin: 0;
        transform: scale(1.1);
        cursor: pointer;
    }

    .subject-item .form-check-label {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
        cursor: pointer;
        margin: 0;
        width: 100%;
        font-size: 11px;
        line-height: 1.5;
    }

    .subject-item .form-check-label strong {
        font-weight: 600;
    }

    .subject-item .form-check-label small {
        font-size: 10px;
    }

    .subject-item .badge {
        font-size: 9px;
        padding: 4px 6px;
    }

    /* Locked Checkbox */
    .locked-checkbox {
        cursor: not-allowed !important;
        opacity: 1 !important;
    }

    .locked-checkbox:disabled {
        opacity: 1 !important;
    }

    .locked-checkbox:disabled + label {
        color: #333;
        cursor: not-allowed;
    }

    /* Academic History Scroll */
    .subject-card .subject-list {
        max-height: 550px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    /* Academic History Subject Font */
    .subject-card .subject-item {
        font-size: 11px;
        padding: 9px 12px;
    }

    .subject-card .subject-item strong {
        font-size: 11px;
    }

    .subject-card .subject-item small {
        font-size: 11px;
    }

    .subject-card .subject-item .badge {
        font-size: 10px;
        padding: 4px 7px;
    }

    /* Academic History Semester Heading */
    .subject-card .term-heading {
        background: #edf3ff;
        padding: 11px 14px;
        border-left: 4px solid #0d6efd;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 12px;
        border-radius: 4px;
    }

    .term-section {
        margin-bottom: 22px;
    }

    /* Academic History Subject Alignment */
    .subject-card .subject-item .d-flex {
        gap: 10px;
    }

    .subject-card .subject-item .d-flex > div {
        min-width: 0;
    }

    /* Custom Scrollbar */
    .subject-card .subject-list::-webkit-scrollbar {
        width: 5px;
    }

    .subject-card .subject-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .subject-card .subject-list::-webkit-scrollbar-thumb {
        background: #b5b5b5;
        border-radius: 10px;
    }

    .subject-card .subject-list::-webkit-scrollbar-thumb:hover {
        background: #888;
    }

    /* Responsive Design */
    @media (max-width: 767px) {

        .subject-list {
            padding: 10px;
        }

        .section-heading {
            padding: 12px 14px;
            font-size: 15px;
        }

        .subject-item {
            padding: 9px 10px;
        }

        .subject-item .form-check-label {
            font-size: 11px;
        }

        .profile-details .detail-row {
            font-size: 12px;
        }

        .subject-card .subject-list {
            max-height: 450px;
        }
    }
</style>


<div class="container-fluid">

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ERROR MESSAGE --}}
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">

        {{-- ========================================================= --}}
        {{-- COLUMN 1 : STUDENT PROFILE + SUBJECT SELECTION --}}
        {{-- ========================================================= --}}

        <div class="col-md-6 mb-4">

            <div class="profile-card">

                {{-- <div class="section-heading">
                    <i class="fa fa-user me-1"></i>
                    Current Student Profile
                </div> --}}

                {{-- <div class="profile-details">

                    <div class="detail-row">
                        <span class="detail-label">Student ID</span>
                        <span class="detail-value">{{ $student->id }}</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">University Roll No.</span>
                        <span class="detail-value">{{ $student->university_roll_no }}</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Student Name</span>
                        <span class="detail-value">{{ $student->student_name }}</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Institute ID</span>
                        <span class="detail-value">{{ $student->institute->name ?? '' }}</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Course Type</span>
                        <span class="detail-value">{{ $student->course_type }}</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Course</span>
                        <span class="detail-value">{{ $student->course->name ?? '-' }}</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Branch</span>
                        <span class="detail-value">{{ $student->branch->name ?? '-' }}</span>
                    </div>

                    @if($student->course_type === 'Semester Based')

                        <div class="detail-row">
                            <span class="detail-label">Current Semester</span>
                            <span class="detail-value">
                                {{ $student->semester->name ?? '-' }}
                            </span>
                        </div>

                    @else

                        <div class="detail-row">
                            <span class="detail-label">Current Annual Term</span>
                            <span class="detail-value">
                                {{ $student->annual_semester->name ?? '-' }}
                            </span>
                        </div>

                    @endif

                    <div class="detail-row">
                        <span class="detail-label">Scheme</span>
                        <span class="detail-value">{{ $student->scheme->name ?? '-' }}</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Batch / Session</span>
                        <span class="detail-value">{{ $student->batch->name ?? '-' }}</span>
                    </div>

                </div> --}}

                <div class="section-heading">
                    <i class="fa fa-book me-1"></i>
                    Select Your Subjects
                </div>

                <div class="subject-list">

                    @if($isLocked)

                        <div class="alert alert-success">
                            <i class="fa fa-check-circle"></i>
                            <strong>Subjects Submitted Successfully!</strong>
                            <br>
                            Your subject selection has been locked.
                            You cannot change your subjects from the student portal.
                            Contact your institute for any correction.
                        </div>

                    @else

                        <div class="alert alert-info">
                            Please verify your subjects carefully.
                            Once submitted, you cannot change your selection.
                        </div>

                    @endif

                    @if($subjects->isEmpty())

                        <div class="alert alert-warning">
                            No subjects found for your current academic profile.
                            Please contact your institute.
                        </div>

                    @else

                        <form action="{{ route('student.verify-subjects.store') }}"
                              method="POST"
                              id="subjectForm">

                            @csrf

                            @foreach($subjects as $subject)

                                <div class="subject-item">

                                    <div class="form-check">

                                        <input
                                            type="checkbox"
                                            class="form-check-input {{ $isLocked ? 'locked-checkbox' : '' }}"
                                            name="subject_ids[]"
                                            id="subject_{{ $subject->id }}"
                                            value="{{ $subject->id }}"
                                            {{ in_array($subject->id, $selectedSubjectIds) ? 'checked' : '' }}
                                            {{ $isLocked ? 'disabled' : '' }}
                                            {{ !$isLocked && in_array($subject->id, old('subject_ids', [])) ? 'checked' : '' }}
                                        >

                                        <label
                                            class="form-check-label"
                                            for="subject_{{ $subject->id }}">

                                            <strong>{{ $subject->name }}</strong>

                                            @if($subject->subject_code)
                                                <small class="text-muted">
                                                    ({{ $subject->subject_code }})
                                                </small>
                                            @endif

                                            @if($subject->is_nc)
                                                <span class="badge bg-secondary">NC</span>
                                            @endif

                                        </label>

                                    </div>

                                </div>

                            @endforeach

                            @if(!$isLocked)

                                <div class="mt-3">

                                    <button type="submit"
                                            class="btn btn-primary w-100"
                                            id="saveSubjectsBtn">

                                        <i class="fa fa-save me-1"></i>
                                        Submit & Verify Subjects

                                    </button>

                                </div>

                            @endif

                        </form>

                    @endif

                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- COLUMN 2 : SEMESTER / ANNUAL WISE SELECTED SUBJECTS --}}
        {{-- ========================================================= --}}

        <div class="col-md-6 mb-4">

            <div class="subject-card">

                <div class="section-heading">
                    <i class="fa fa-list me-1"></i>
                    Submitted Subjects - Academic History
                </div>

                <div class="subject-list">

                    @if($groupedSubjects->isEmpty())

                        <div class="alert alert-warning">
                            No submitted subjects available.
                        </div>

                    @else

                        @foreach($groupedSubjects as $termId => $items)

                            @php
                                $term = $termNames->get($termId);
                            @endphp

                            <div class="term-section">

                                <div class="term-heading">

                                    @if($student->course_type === 'Semester Based')
                                        {{ $term->name ?? 'Semester' }}
                                    @else
                                        {{ $term->name ?? 'Annual Term' }}
                                    @endif

                                </div>

                                @foreach($items->sortBy(function($item) {
                                    return $item->subject->orders ?? 0;
                                }) as $item)

                                    <div class="subject-item">

                                        <div class="d-flex justify-content-between align-items-center">

                                            <div>
                                                <strong>
                                                    {{ $item->subject->name }}
                                                </strong>

                                                @if($item->subject->subject_code)
                                                    <small class="text-muted">
                                                        ({{ $item->subject->subject_code }})
                                                    </small>
                                                @endif
                                            </div>

                                            <span class="badge bg-success">
                                                Selected
                                            </span>

                                        </div>

                                    </div>

                                @endforeach

                            </div>

                        @endforeach

                    @endif

                </div>

            </div>

        </div>

    </div>

</div>

<script>
    document.getElementById('subjectForm')?.addEventListener('submit', function(e) {

        let checkedSubjects = document.querySelectorAll(
            '#subjectForm input[name="subject_ids[]"]:checked'
        );

        if (checkedSubjects.length === 0) {
            e.preventDefault();
            alert('Please select at least one subject.');
            return false;
        }

        if (!confirm(
            'Are you sure you want to submit these subjects? You cannot change them after submission.'
        )) {
            e.preventDefault();
            return false;
        }

        let button = document.getElementById('saveSubjectsBtn');

        button.disabled = true;
        button.innerHTML = 'Submitting...';

    });
</script>

@endsection
