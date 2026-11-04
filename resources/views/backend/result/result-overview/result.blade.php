<style>

.result-table-a4 {
    width: 100%;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
}

.result-table-a4 th,
.result-table-a4 td {
    border: 1px solid #ddd;
    padding: 8px;
    font-size: 12px;
    text-align: center;
}

/* Table Header */
.result-table-a4 thead th {
    background: #f5f5f5;
    font-weight: 600;
}

/* Table Footer */
.result-table-a4 tfoot td {
    font-weight: 600;
    padding: 25px 8px 10px;
    text-align: center;
    vertical-align: bottom;
}

/* Footer top spacing for signature */
.result-table-a4 tfoot td::before {
    content: "";
    display: block;
    height: 40px;
}

/* Header section styling */
.result-header h4 {
    margin: 2px 0;
}

.result-header p {
    margin: 4px 0;
    text-align: left
}

</style>
<div class="table-responsive">
    <table class="result-table-a4">
        <thead>
            <tr>
                <td colspan="7" class="result-header">
                    <center>
                        <h4>CONTROLLER OF EXAMINATIONS</h4>

                        <h4>
                            GURU ANGAD DEV VETERINARY AND ANIMAL SCIENCE UNIVERSITY,
                            LUDHIANA (INDIA)
                        </h4>
                        <h4>{{ $exam->exam_resultsheet_name ?? '' }}</h4>
                    </center>

                    @if(!empty($institute?->name))
                    <p>
                        <b>College : {{ $institute->name ?? '' }}</b>
                    </p>
                    @endif

                    <p>
                        <b>{{ $course->name ?? '' }} </b>
                    </p>

                    {{-- @dump($className) --}}

                    <p>
                        {{ $className ?? '' }} | 
                        {{ (!empty($branch?->name) && $branch->name !== 'NA') ? $branch->name." | " : '' }} 
                        {{ (!empty($scheme?->name) && $scheme->name !== 'NA') ? $scheme->name." | " : '' }} 
                        Batch : {{ $batch->name ?? '' }}
                    </p>

                </td>
            </tr>


            <!-- ===============================
                    TABLE COLUMN HEADER
            ================================ -->

            <tr>
                <th><input type="checkbox" id="select-all" class="no-export"> #</th>
                <th>Admission Number / Roll No</th>
                <th>Student Name</th>
                <th>GPA</th>
                <th>CGPA</th>
                <th class="no-export">Grade</th>
                <th class="no-export">Remarks</th>
            </tr>

        </thead>
        <tbody>
            @forelse($results as $key => $row)

            @php
                $finalGrade = '';

                foreach($grades as $g){
                    if($row->cgpa >= $g->percent_from && $row->cgpa <= $g->percent_upto){
                        $finalGrade = $g->grade_description;
                        break;
                    }
                }
            @endphp
            <tr>
                {{-- <td>
                    @if($row->result_declare == 0)
                    <input type="checkbox" class="row-checkbox" value="{{ $row->id }}">
                    @endif
                    {{ $key + 1 }}
                </td> --}}
                <td>
                    <input type="checkbox" class="row-checkbox no-export" value="{{ $row->id }}">
                    {{ $key + 1 }}

                    @if($row->result_declare == 1)
                        <br>
                        <small class="text-success no-export">Declared</small>
                    @else
                        <br>
                        <small class="text-danger no-export">Not Declared</small>
                    @endif
                </td>

                <td>
                    {{ $row->student->university_roll_no ?? '' }}
                </td>

                <td>
                    {{ $row->student->student_name ?? '' }}
                </td>

                <td>{{ $row->gpa }}</td>

                <td>{{ $row->cgpa }}</td>

                <td class="no-export">{{ $finalGrade }}</td>

                <td class="no-export">
                    <b>{{-- $row->result_status --}}</b>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-danger">
                    No Result Found
                </td>
            </tr>
            @endforelse
            </tbody>
    </table>
</div>

{{-- <button class="btn btn-success my-3" id="declare-result">Declare Result</button> --}}
<div class="d-flex gap-2 my-3">

    <select class="form-control w-auto" id="result-action">
        <option value="1">Declare Result</option>
        <option value="0">Undo Declaration</option>
    </select>

    <button class="btn btn-success" id="declare-result">
        Submit
    </button>

</div>

<script>
    // Select All
    $('#select-all').on('click', function () {
        $('.row-checkbox').prop('checked', this.checked);
    });

    // If one unchecked → uncheck select-all
    $(document).on('change', '.row-checkbox', function () {
        if (!this.checked) {
            $('#select-all').prop('checked', false);
        }

        if ($('.row-checkbox:checked').length === $('.row-checkbox').length) {
            $('#select-all').prop('checked', true);
        }
    });


    // $('#declare-result').click(function () {

    //     let ids = [];

    //     $('.row-checkbox:checked').each(function () {
    //         ids.push($(this).val());
    //     });

    //     if (ids.length === 0) {
    //         Swal.fire({
    //             icon: 'warning',
    //             title: 'Please select at least one student'
    //         });
    //         return;
    //     }

    //     $.ajax({
    //         url: "{{ route('results.declare') }}", 
    //         type: "POST",
    //         data: {
    //             ids: ids,
    //             _token: "{{ csrf_token() }}"
    //         },

    //         beforeSend: function () {
    //             $('#declare-result').prop('disabled', true);

    //             Swal.fire({
    //                 title: 'Please Wait...',
    //                 text: 'Declaring Result...',
    //                 allowOutsideClick: false,
    //                 didOpen: () => {
    //                     Swal.showLoading();
    //                 }
    //             });
    //         },

    //         success: function (response) {
    //             $('#declare-result').prop('disabled', false);

    //             Swal.fire({
    //                 icon: 'success',
    //                 title: response.message
    //             });

    //             $('.row-checkbox:checked').each(function () {
    //                 $(this).closest('td').html('<span class="text-success">Declared</span>');
    //             });

    //             // Optional: refresh or uncheck
    //             $('.row-checkbox').prop('checked', false);
    //             $('#select-all').prop('checked', false);
    //         },

    //         error: function () {
    //             $('#declare-result').prop('disabled', false);

    //             Swal.fire({
    //                 icon: 'error',
    //                 title: 'Something went wrong!'
    //             });
    //         }
    //     });  
    // });

    $('#declare-result').click(function () {

        let ids = [];

        $('.row-checkbox:checked').each(function () {
            ids.push($(this).val());
        });

        if (ids.length === 0) {

            Swal.fire({
                icon: 'warning',
                title: 'Please select at least one student'
            });

            return;
        }

        let resultDeclare = $('#result-action').val();

        $.ajax({

            url: "{{ route('results.declare') }}",

            type: "POST",

            data: {
                ids: ids,
                result_declare: resultDeclare,
                _token: "{{ csrf_token() }}"
            },

            beforeSend: function () {

                $('#declare-result').prop('disabled', true);

                Swal.fire({
                    title: 'Please Wait...',
                    text: 'Updating Result Status...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },

            success: function (response) {

                $('#declare-result').prop('disabled', false);

                Swal.fire({
                    icon: 'success',
                    title: response.message
                });

                location.reload();
            },

            error: function () {

                $('#declare-result').prop('disabled', false);

                Swal.fire({
                    icon: 'error',
                    title: 'Something went wrong!'
                });
            }
        });
    });

</script>

