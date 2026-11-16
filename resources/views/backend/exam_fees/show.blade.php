
@extends('backend.partial.master')

@section('title', 'Exam Fees')
@section('sub_title', 'View Exam Fees')

@section('backend-content')

@php
    $romanOrder = [
        'I' => 1,
        'II' => 2,
        'III' => 3,
        'IV' => 4,
        'V' => 5,
        'VI' => 6,
        'VII' => 7,
        'VIII' => 8,
        'IX' => 9,
        'X' => 10,
        'XI' => 11,
        'XII' => 12,
    ];

    $sortedSemesters = $examFee->semesters->sortBy(function ($semester) use ($romanOrder) {
        return $romanOrder[strtoupper(trim($semester->name))] ?? 999;
    });
@endphp

<div class="row">
    <div class="col-md-12">
        <div class="card">

            {{-- Header --}}
            <div class="card-header d-flex justify-content-between align-items-center mb-3">
                <h4 class="card-title m-0">Exam Fee Details</h4>

                <div>
                    <a href="{{ route('exam-fees.edit', $examFee->id) }}"
                       class="btn btn-sm btn-warning">
                        <i class="mdi mdi-pencil-outline me-1"></i> Edit
                    </a>

                    <a href="{{ route('exam-fees.index') }}"
                       class="btn btn-sm btn-secondary">
                        <i class="mdi mdi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>

            <div class="card-body">

                {{-- Basic Details --}}
                <h5 class="mb-3">Basic Details</h5>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <tbody>

                            <tr>
                                <th width="25%">Course</th>
                                <td>{{ $examFee->course->name ?? 'N/A' }}</td>
                            </tr>

                            <tr>
                                <th>Schemes</th>
                                <td>
                                    {{ $examFee->schemes->pluck('name')->implode(', ') ?: 'N/A' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Branches</th>
                                <td>
                                    {{ $examFee->branches->pluck('name')->implode(', ') ?: 'N/A' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Semesters</th>
                                <td>
                                    {{ $sortedSemesters->pluck('name')->implode(', ') ?: 'N/A' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Sessions / Batches</th>
                                <td>
                                    {{ $examFee->batches->pluck('name')->implode(', ') ?: 'N/A' }}
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                {{-- Exam Fee Details --}}
                <h5 class="mb-3">Exam Fee Details</h5>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered">

                        <thead class="table-light">
                            <tr>
                                <th width="25%">Student Type</th>
                                <th width="25%">Exam Type</th>
                                <th width="25%">Exam Fee</th>
                                <th width="25%">Max Exam Fee</th>
                            </tr>
                        </thead>

                        <tbody>

                            {{-- Regular --}}
                            <tr>
                                <td>Regular</td>
                                <td>All</td>
                                <td>{{ number_format($examFee->regular_exam_fee, 2) }}</td>
                                <td>N/A</td>
                            </tr>

                            {{-- Reappear Theory --}}
                            <tr>
                                <td>Reappear</td>
                                <td>Theory</td>
                                <td>{{ number_format($examFee->reappear_theory_exam_fee, 2) }}</td>
                                <td>{{ number_format($examFee->reappear_theory_max_exam_fee, 2) }}</td>
                            </tr>

                            {{-- Reappear Practical --}}
                            <tr>
                                <td></td>
                                <td>Practical</td>
                                <td>{{ number_format($examFee->reappear_practical_exam_fee, 2) }}</td>
                                <td>{{ number_format($examFee->reappear_practical_max_exam_fee, 2) }}</td>
                            </tr>

                            {{-- Special Chance Theory --}}
                            <tr>
                                <td>Special Chance</td>
                                <td>Theory</td>
                                <td>{{ number_format($examFee->special_chance_theory_exam_fee, 2) }}</td>
                                <td>{{ number_format($examFee->special_chance_theory_max_exam_fee, 2) }}</td>
                            </tr>

                            {{-- Special Chance Practical --}}
                            <tr>
                                <td></td>
                                <td>Practical</td>
                                <td>{{ number_format($examFee->special_chance_practical_exam_fee, 2) }}</td>
                                <td>{{ number_format($examFee->special_chance_practical_max_exam_fee, 2) }}</td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                {{-- Examination Schedule --}}
                <h5 class="mb-3">Examination Schedule</h5>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <tbody>

                            <tr>
                                <th width="25%">Exam Start Date</th>
                                <td>
                                    {{ $examFee->exam_start_date?->format('d-m-Y') ?? 'N/A' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Exam End Date</th>
                                <td>
                                    {{ $examFee->exam_end_date?->format('d-m-Y') ?? 'N/A' }}
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                {{-- Late Fee Details --}}
                <h5 class="mb-3">Late Fee Details</h5>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered">

                        <thead class="table-light">
                            <tr>
                                <th width="10%">S.No.</th>
                                <th width="30%">Start Date</th>
                                <th width="30%">End Date</th>
                                <th width="30%">Late Fee Amount</th>
                            </tr>
                        </thead>

                        <tbody>

                            {{-- Late Fee 1 --}}
                            <tr>
                                <td>1</td>
                                <td>
                                    {{ $examFee->late_fee_start1?->format('d-m-Y') ?? 'N/A' }}
                                </td>
                                <td>
                                    {{ $examFee->late_fee_end1?->format('d-m-Y') ?? 'N/A' }}
                                </td>
                                <td>
                                    {{ number_format($examFee->late_fee_amount1 ?? 0, 2) }}
                                </td>
                            </tr>

                            {{-- Late Fee 2 --}}
                            <tr>
                                <td>2</td>
                                <td>
                                    {{ $examFee->late_fee_start2?->format('d-m-Y') ?? 'N/A' }}
                                </td>
                                <td>
                                    {{ $examFee->late_fee_end2?->format('d-m-Y') ?? 'N/A' }}
                                </td>
                                <td>
                                    {{ number_format($examFee->late_fee_amount2 ?? 0, 2) }}
                                </td>
                            </tr>

                            {{-- Late Fee 3 --}}
                            <tr>
                                <td>3</td>
                                <td>
                                    {{ $examFee->late_fee_start3?->format('d-m-Y') ?? 'N/A' }}
                                </td>
                                <td>
                                    {{ $examFee->late_fee_end3?->format('d-m-Y') ?? 'N/A' }}
                                </td>
                                <td>
                                    {{ number_format($examFee->late_fee_amount3 ?? 0, 2) }}
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                {{-- Record Information --}}
                <h5 class="mb-3">Record Information</h5>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>

                            <tr>
                                <th width="25%">Created At</th>
                                <td>
                                    {{ $examFee->created_at?->format('d-m-Y h:i A') ?? 'N/A' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Last Updated</th>
                                <td>
                                    {{ $examFee->updated_at?->format('d-m-Y h:i A') ?? 'N/A' }}
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection
