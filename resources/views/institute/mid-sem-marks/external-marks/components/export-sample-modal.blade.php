<style>
#exportOffcanvasTop .offcanvas-body {
    min-height: 180px;
}
</style>
<div class="offcanvas offcanvas-top" tabindex="-1" id="exportOffcanvasTop" aria-labelledby="exportOffcanvasTopLabel">
    <div class="offcanvas-header">
        <h5 id="exportOffcanvasTopLabel">Export Result Sample</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body card">
        <form action="{{ route('external.mid-sem-external-marks.export') }}" method="POST"> @csrf
        <div class="row">
            <div class="col-md-2 form-group mb-2 d-none">
                <label for="course_type">Course Type<span class="text-danger">*</span></label>
                <select name="course_type" id="export_course_type" class="form-control">
                    <option value="">--Select--</option>
                    {{-- <option value="Annual Based">Annual Based</option> --}}
                    <option value="Semester Based" selected>Semester Based</option>
                </select>
                @error('course_type') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-4 form-group mb-2">
                <label for="mid_sem_exam_id">Mid-Sem/Internal Exam Name<span class="text-danger">*</span></label>
                <select name="mid_sem_exam_id" id="export_mid_sem_exam_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
            </div>

            <div class="col-md-2 form-group mb-2">
                <label for="course_id">Select Course<span class="text-danger">*</span></label>
                <select name="course_id" id="export_course_id" class="form-control">
                    <option value="">--Select--</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}" data-course="{{ $course->course }}">{{ $course->name }}</option>
                @endforeach
                </select>
            </div>
            <div class="col-md-2 form-group mb-2">
                <label for="scheme_id">Select Scheme</label>
                <select name="scheme_id" id="export_scheme_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
                @error('scheme_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-md-2 form-group mb-2">
                <label for="branch_id">Select Branch<span class="text-danger">*</span></label>
                <select name="branch_id" id="export_branch_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
            </div>
            <div class="col-md-2 form-group mb-2" id="export_semester_container"  style="display: none;">
                <label for="semester_id">Select Semester</label>
                <select name="semester_id" id="export_semester_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
            </div>
            <div class="col-md-2 form-group mb-2" id="export_annual_semester_container"  style="display: none;">
                <label for="annual_semester_id">Select Annual Terms</label>
                <select name="annual_semester_id" id="export_annual_semester_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
            </div>
            <div class="col-md-2 form-group mb-2">
                <label>Session / Batch <span class="text-danger">*</span></label>
                <select name="batch_id" id="export_batch_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
            </div>
            <div class="col-md-2 form-group mb-2">
                <label for="marks_type">Select Marks Type<span class="text-danger">*</span></label>
                <select name="marks_type" id="export_marks_type" class="form-control">
                    <option value="">--Select--</option>
                </select>
            </div>
            <div class="col-md-2 form-group mb-2" id="subject_container">
                <label for="subject_id">Select Subject<span class="text-danger">*</span></label>
                <select name="subject_id" id="export_subject_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
            </div>
            <div class="col-md-2 form-group mt-3 mb-2">
                <button type="submit" id="exportApplyFilter" class="btn btn-primary">Export/Download</button>
            </div>
        </div>
        </form>
    </div>
</div>
