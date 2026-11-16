
@extends('backend.partial.master')
@section('title', 'Exam Fees')
@section('sub_title', 'Exam Fees List')
@section('backend-content')
@php
    function sortSemesters($semesters)
    {
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

        return $semesters->sortBy(function ($semester) use ($romanOrder) {
            return $romanOrder[strtoupper(trim($semester->name))] ?? 999;
        });
    }
@endphp
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between mb-3">
                <h4 class="card-title m-0">Exam Fees List</h4>
                <a href="{{ route('exam-fees.create') }}" class="btn btn-sm btn-primary">+ Add Exam Fee</a>
            </div>

            <div class="card-body">
                <div class="table-responsive database-table">
                    <table class="table table-bordered" id="datatable-buttons">
                        <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>Course</th>
                                <th>Schemes</th>
                                <th>Branches</th>
                                <th>Semesters</th>
                                <th>Sessions</th>
                                <th>Exam Fee</th>
                                <th>Exam Start Date</th>
                                <th>Exam End Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($examFees as $key => $fee)
                                <tr>
                                    <td>{{ $key + 1 }}</td>

                                    <td>
                                        {{ $fee->course->name ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $fee->schemes->pluck('name')->implode(', ') ?: 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $fee->branches->pluck('name')->implode(', ') ?: 'N/A' }}
                                    </td>

                                    <td>
                                        {{ sortSemesters($fee->semesters)->pluck('name')->implode(', ') ?: 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $fee->batches->pluck('name')->implode(', ') ?: 'N/A' }}
                                    </td>

                                    <td>
                                        <div>
                                            <strong>Regular:</strong>
                                            {{ number_format($fee->regular_exam_fee, 2) }}
                                        </div>

                                        <hr class="my-1">

                                        <div>
                                            <strong>Reappear Theory:</strong>
                                            {{ number_format($fee->reappear_theory_exam_fee, 2) }}
                                        </div>

                                        <div>
                                            <strong>Reappear Practical:</strong>
                                            {{ number_format($fee->reappear_practical_exam_fee, 2) }}
                                        </div>

                                        <hr class="my-1">

                                        <div>
                                            <strong>Special Chance Theory:</strong>
                                            {{ number_format($fee->special_chance_theory_exam_fee, 2) }}
                                        </div>

                                        <div>
                                            <strong>Special Chance Practical:</strong>
                                            {{ number_format($fee->special_chance_practical_exam_fee, 2) }}
                                        </div>
                                    </td>

                                    <td>
                                        {{ $fee->exam_start_date?->format('d-m-Y') ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $fee->exam_end_date?->format('d-m-Y') ?? 'N/A' }}
                                    </td>

                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-transparent dropdown-toggle"
                                                    type="button"
                                                    id="actionDropdown{{ $fee->id }}"
                                                    data-bs-toggle="dropdown"
                                                    aria-expanded="false">

                                                <i class="mdi mdi-dots-vertical fs-16"></i>
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-start"
                                                aria-labelledby="actionDropdown{{ $fee->id }}">

                                                {{-- View --}}
                                                <li>
                                                    <a class="dropdown-item"
                                                    href="{{ route('exam-fees.show', $fee->id) }}">

                                                        <i class="mdi mdi-eye-outline me-2"></i> View
                                                    </a>
                                                </li>

                                                {{-- Edit --}}
                                                <li>
                                                    <a class="dropdown-item"
                                                    href="{{ route('exam-fees.edit', $fee->id) }}">

                                                        <i class="mdi mdi-pencil-outline me-2"></i> Edit
                                                    </a>
                                                </li>

                                                {{-- Delete --}}
                                                <li>
                                                    <form action="{{ route('exam-fees.destroy', $fee->id) }}"
                                                        method="POST"
                                                        class="d-inline">

                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit"
                                                                class="dropdown-item"
                                                                onclick="return confirm('Are you sure you want to delete this exam fee?')">

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
                                    <td colspan="10" class="text-center">
                                        No Exam Fees Found.
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
