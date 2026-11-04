@if($students->isEmpty())

    <div class="alert alert-warning text-center mb-0">
        <i data-feather="alert-circle"></i>
        No mapped student records found.
    </div>

@else

    <style>
        .mapped-subject-table th {
            background: #f5f7fb;
            color: #475467;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .mapped-subject-table td {
            font-size: 13px;
            vertical-align: middle;
            color: #344054;
        }

        .mapped-subject-list {
            line-height: 1.9;
        }

        .mapped-subject-list span {
            display: inline-block;
            background: #f2f4f7;
            padding: 2px 8px;
            border-radius: 4px;
            margin: 2px;
            font-size: 12px;
        }
    </style>

    <div class="table-responsive">

        <table class="table table-bordered table-hover mapped-subject-table">

            <thead>
                <tr>
                    <th width="">ID</th>
                    <th width="">Student Roll No.</th>
                    <th width="">Student Name</th>
                    <th>Subject List</th>
                    <th width="100" class="text-center no-export">Action</th>
                </tr>
            </thead>

            <tbody>

                @php
                    $num = 0;
                @endphp

                @foreach($students as $record)

                    <tr id="mapped-row-{{ $record->id }}">

                        {{-- ID --}}
                        <td>
                            {{ ++$num }}
                        </td>

                        {{-- Roll Number --}}
                        <td>
                            <strong>
                                {{ $record->student->university_roll_no ?? 'N/A' }}
                            </strong>
                        </td>

                        {{-- Student Name --}}
                        <td>
                            {{ $record->student->student_name ?? 'N/A' }}
                        </td>

                        {{-- Subject List --}}
                        <td>

                            <div class="mapped-subject-list">

                                @php
                                    $mappedSubjects = $record->subjects
                                        ->filter(fn($item) => $item->subject);
                                @endphp

                                @forelse($mappedSubjects as $mappedSubject)

                                    <div class="mapped-subject-item">
                                        <span class="subject-number">{{ $loop->iteration }}.</span>

                                        <span>
                                            {{ $mappedSubject->subject->name }}
                                            <small class="text-muted">
                                                ({{ $mappedSubject->subject->subject_code ?? 'N/A' }})
                                            </small>
                                        </span>
                                    </div>

                                @empty

                                    <span class="text-danger">No subjects mapped</span>

                                @endforelse

                            </div>

                        </td>

                        {{-- Action --}}
                        <td class="text-center no-export">

                            <button type="button"
                                class="btn btn-sm btn-danger delete-mapped-subject"
                                data-id="{{ $record->id }}"
                                title="Delete">

                                <i data-feather="trash-2"
                                    style="width:14px;height:14px;"></i>

                            </button>

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>

    <div class="text-muted small mt-2">
        Total Mapped Students:
        <strong class="mapped-total-count">{{ $students->count() }}</strong>
    </div>

@endif

<script>
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
</script>
