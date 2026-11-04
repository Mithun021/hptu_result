@extends('backend.partial.master')

@section('title', 'Retotalling Result')
@section('sub_title', 'End Semester Result Prepare')

@section('backend-content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">End Semester Result Card</h3>
                <div class="export-button">
                    <button type="button" id="export-excel-btn" class="btn btn-sm btn-success">Excel</button>
                    <button type="button" id="print-btn" class="btn btn-sm btn-danger">Print PDF</button>
                </div>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#endSemOffcanvasTop">
                        <i data-feather="filter" style="width:14px; height:14px;"></i> Search End Sem Result
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="endSemMarksResult"></div>
            </div>
        </div>
    </div>
</div>

<!-- ✅ ADDON: RESULT DETAIL MODAL -->
<div class="modal fade" id="resultDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Result Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="resultDetailContent">
                <center>Loading...</center>
            </div>

        </div>
    </div>
</div>

<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/sweetalert.js') }}"></script>
<script src="{{ asset('assets/libs/jquery-validate/jquery.validate.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

@include('backend.retotalling-result.regular-result.result-preview.overall-result.components.search-final-marks-modal')
@include('backend.result.js.search-final-result')
@include('backend.retotalling-result.regular-result.result-preview.overall-result.js.fetch-final-result')
@include('backend.result.js.export-button')


<script>
$(document).on('click', '.result-detail-btn', function () {

    let student_id = $(this).data('student_id');
    let subject_id = $(this).data('subject_id');

    // alert(student_id + "-" + subject_id); return false;

    $('#resultDetailModal').modal('show');
    $('#resultDetailContent').html('<center>Loading...</center>');

    $.ajax({
        url: "{{ route('retotal-result.regular.result-preview.subjectwise.fetch-details') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            exam_id: $('#exam_id').val(),
            course_type: $('#course_type').val(),
            course_id: $('#course_id').val(),
            branch_id: $('#branch_id').val(),
            batch_id: $('#batch_id').val(),
            scheme_id: $('#scheme_id').val(),
            semester_id: $('#semester_id').val(),
            annual_semester_id: $('#annual_semester_id').val(),
            institute_id: $('#institute_id').val(),
            subject_id: subject_id,
            student_id: student_id
        },
        success: function (response) {
            $('#resultDetailContent').html(response);
        }
    });

});
</script>


@endsection
