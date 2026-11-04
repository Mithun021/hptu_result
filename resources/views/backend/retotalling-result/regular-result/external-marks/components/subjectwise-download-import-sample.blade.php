<style>
#externalMarksImportExportOffcanvasTop .offcanvas-body {
    min-height: 320px;
}
</style>
<div class="offcanvas offcanvas-top" tabindex="-1" id="externalMarksImportExportOffcanvasTop" aria-labelledby="externalMarksImportExportOffcanvasTopLabel">
    <div class="offcanvas-header">
        <h5 id="externalMarksImportExportOffcanvasTopLabel">Download/Import Retotalling External Marks</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body card">
        <form id="exportIndividualExternalMarksForm">
        <div class="row">
            <div class="col-md-2 form-group mb-2">
                <label for="export_exam_id">Exam Name<span class="text-danger">*</span></label>
                <select name="export_exam_id" id="export_exam_id" class="form-control">
                    <option value="">--Select--</option>
                    @foreach ($exams as $exam)
                        <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 form-group mb-2">
                <label for="export_course_type">Course Type<span class="text-danger">*</span></label>
                <select name="export_course_type" id="export_course_type" class="form-control">
                    <option value="">--Select--</option>
                    <option value="Annual Based">Annual Based</option>
                    <option value="Semester Based">Semester Based</option>
                </select>
                @error('export_course_type') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-md-2 form-group mb-2">
                <label for="export_course_id">Select Course<span class="text-danger">*</span></label>
                <select name="export_course_id" id="export_course_id" class="form-control">
                    <option value="">--Select--</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}" data-course="{{ $course->course }}">{{ $course->name }}</option>
                @endforeach
                </select>
            </div>
            <div class="col-md-2 form-group mb-2">
                <label for="export_scheme_id">Select Scheme</label>
                <select name="export_scheme_id" id="export_scheme_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
                @error('export_scheme_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-md-2 form-group mb-2">
                <label for="export_branch_id">Select Branch<span class="text-danger">*</span></label>
                <select name="export_branch_id" id="export_branch_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
            </div>
            <div class="col-md-2 form-group mb-2" id="semester_container_export"  style="display: none;">
                <label for="export_semester_id">Select Semester</label>
                <select name="export_semester_id" id="export_semester_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
            </div>
            <div class="col-md-2 form-group mb-2" id="annual_semester_container_export"  style="display: none;">
                <label for="export_annual_semester_id">Select Annual Terms</label>
                <select name="export_annual_semester_id" id="export_annual_semester_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
            </div>
            <div class="col-md-2 form-group mb-2">
                <label>Session / Batch <span class="text-danger">*</span></label>
                <select name="export_batch_id" id="export_batch_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
            </div>
            <div class="col-md-2 form-group mb-2">
                <label for="export_marks_type">Select Marks Type<span class="text-danger">*</span></label>
                <select name="export_marks_type" id="export_marks_type" class="form-control">
                    <option value="">--Select--</option>
                    {{-- <option value="Mid-Sem">Mid-Sem</option>
                    <option value="Quiz">Quiz</option>
                    <option value="Practical">Practical</option> --}}
                </select>
            </div>
            <div class="col-md-2 form-group mb-2" id="subject_container">
                <label for="export_institute_id">Select Institute</label>
                <select name="export_institute_id" id="export_institute_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
            </div>

            <div class="col-md-2 form-group mb-2" id="subject_container">
                <label for="export_subject_id">Select Subject<span class="text-danger">*</span></label>
                <select name="export_subject_id" id="export_subject_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
            </div>
            <div class="col-md-2 form-group mb-2">
                <label for="result_type">
                    Result Type <span class="text-danger">*</span>
                </label>

                <select name="result_type" id="result_type" class="form-control">
                    <option value="">--Select--</option>
                    <option value="regular">Regular / End-Sem</option>
                    <option value="supplementary">Supplementary / Compartment</option>
                </select>
            </div>
            <div class="col-md-2 form-group mt-3 mb-2">
                <button type="button" id="applyExportFilter" class="btn btn-primary">Export Sample</button>
            </div>
        </div>
        </form>

        <hr>

        <form action="{{ route('retotal-result.external-marks.import') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-12">
                <h4 class="text-center">Import/Upload External Marks</h4>
            </div>
            <div class="col-md-6">
                <input type="file" name="import_file" class="form-control">
            </div>
            <div class="col-md-6">
                <button type="submit" class="btn btn-primary form-control">Upload/Import</button>
            </div>
        </div>
        </form>
    </div>
</div>
