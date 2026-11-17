
@extends('student.partial.master')

@section('title', 'Payment Proceed')
@section('sub_title', 'Examination Fee Payment')

@section('student-content')

@php
    $examFee = (float) $examinationForm->exam_fee;
    $lateFee = (float) $examinationForm->late_fee;
    $totalFee = (float) $examinationForm->total_exam_fee;
@endphp

<div class="container-fluid">

    <div class="row justify-content-center">

        <div class="col-lg-8 col-md-10">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0">
                        <i class="fa fa-credit-card me-2"></i>
                        Examination Fee Payment
                    </h5>
                </div>

                <div class="card-body p-4">

                    <div class="text-center mb-4">
                        {{-- <p class="text-muted mb-1">Examination Session</p> --}}
                        <h5 class="fw-bold">
                            {{ $examinationForm->exam?->name ?? '-' }}
                        </h5>
                    </div>

                    <table class="table table-bordered">

                        <tr>
                            <th width="40%">Form ID</th>
                            <td>{{ $examinationForm->form_id }}</td>
                        </tr>

                        <tr>
                            <th>Student Name</th>
                            <td>{{ $student->student_name ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th>University Roll No.</th>
                            <td>{{ $student->university_roll_no ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th>Form Type</th>
                            <td>
                                {{ $examinationForm->exam_type }}
                                (Sem - {{ $examinationForm->semester?->name ?? '-' }})
                            </td>
                        </tr>

                        <tr>
                            <th>Exam Fee</th>
                            <td>
                                ₹{{ number_format($examFee, 2) }}
                            </td>
                        </tr>

                        <tr>
                            <th>Fine / Late Fee</th>
                            <td>
                                ₹{{ number_format($lateFee, 2) }}
                            </td>
                        </tr>

                        <tr class="table-light">
                            <th>Total Exam Fee</th>
                            <td>
                                <strong class="text-primary fs-5">
                                    ₹{{ number_format($totalFee, 2) }}
                                </strong>
                            </td>
                        </tr>

                    </table>

                    <div class="d-flex justify-content-between align-items-center mt-4">

                        <a href="{{ route('student.examination-forms.pending', $examinationForm->id) }}"
                           class="btn btn-outline-secondary">
                            <i class="fa fa-arrow-left me-1"></i>
                            Back
                        </a>

                        <form action="#" method="POST">
                            @csrf

                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-lock me-1"></i>
                                Proceed to Pay
                            </button>
                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
