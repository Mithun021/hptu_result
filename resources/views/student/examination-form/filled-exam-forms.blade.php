
@extends('student.partial.master')

@section('title', 'My Examination Forms')
@section('sub_title', 'My Examination Forms')

@section('student-content')

<div class="container-fluid">

    {{-- <div class="mb-3">
        <p class="fw-semibold mb-1">My Examination Forms</p>
        <p class="text-muted small mb-0">
            View your submitted examination forms and payment status.
        </p>
    </div> --}}

    @if($examinationForms->isEmpty())

        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <i class="fa fa-file-alt fa-2x text-muted mb-2"></i>

                <p class="fw-semibold mb-1">No Examination Forms Found</p>

                <p class="text-muted small mb-0">
                    You have not submitted any examination form yet.
                </p>
            </div>
        </div>

    @else

        <div class="row g-3">

            @foreach($examinationForms as $form)

                <div class="col-xl-4 col-lg-4 col-md-6 col-12">

                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header py-2 px-2" style="background-color: antiquewhite">
                            {{-- Examination Session --}}
                            {{-- <p class="text-muted small mb-1">
                                Examination Session
                            </p> --}}

                            <p class="fw-semibold mb-0">
                                {{ $form->exam?->name ?? '-' }}
                            </p>
                        </div>

                        <div class="card-body p-2">

                            {{-- Form Number --}}
                            <div class="mb-2">
                                <p class="text-muted small mb-1">
                                    Form No.
                                </p>

                                <p class="text-primary fw-semibold mb-0">
                                    {{ $form->form_id }}
                                </p>
                            </div>

                            {{-- Form Type --}}
                            <div class="mb-2">
                                <p class="text-muted small mb-1">
                                    Form Type
                                </p>

                                <p class="fw-medium mb-0">
                                    {{ $form->exam_type }}
                                    (Sem - {{ $form->semester?->name ?? '-' }})
                                </p>
                            </div>

                            {{-- Exam Fee --}}
                            <div class="mb-2">
                                <p class="text-muted small mb-1">
                                    Exam Fee
                                </p>

                                <p class="fw-semibold mb-0">
                                    ₹{{ number_format((float) $form->exam_fee + (float) $form->late_fee, 2) }}
                                </p>
                            </div>

                            <hr class="my-2">
                            {{-- Status and Action --}}
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">

                                @if($form->payment_status == 1)

                                    <span class="badge bg-success px-2 py-2">
                                        <i class="fa fa-check-circle me-1"></i>
                                        Paid
                                    </span>

                                    <a href="{{ route('student.examination-forms.paid', $form->id) }}"
                                    class="btn btn-outline-primary btn-sm">
                                        <i class="fa fa-print me-1"></i>
                                        Print Form
                                    </a>

                                @else

                                    <span class="badge bg-warning text-dark px-2 py-2">
                                        <i class="fa fa-clock me-1"></i>
                                        Unpaid
                                    </span>

                                    @if($form->exam && $form->exam->is_active == 1)
                                        <a href="{{ route('student.examination-forms.pending', $form->id) }}"
                                        class="btn btn-primary btn-sm">
                                            <i class="fa fa-credit-card me-1"></i>
                                            Pay and Print
                                        </a>
                                    @endif

                                @endif

                            </div>

                            {{-- Download Admit Card --}}
                            @if($form->payment_status == 1 && $form->exam && $form->exam->show_admitcard == 1)

                                <a href="{{ route('student.admit-cards.show', $form->id) }}"
                                class="btn btn-outline-success btn-sm w-100">
                                    <i class="fa fa-download me-1"></i>
                                    Download Admit Card
                                </a>

                            @endif

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    @endif

</div>

@endsection
