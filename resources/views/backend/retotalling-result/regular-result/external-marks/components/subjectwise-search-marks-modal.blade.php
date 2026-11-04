<style>
#subjectwiseOffcanvasTop .offcanvas-body {
    min-height: 180px;
}
</style>
<div class="offcanvas offcanvas-top" tabindex="-1" id="subjectwiseOffcanvasTop" aria-labelledby="subjectwiseOffcanvasTopLabel">
    <div class="offcanvas-header">
        <h5 id="subjectwiseOffcanvasTopLabel">Search Retotalling External Marks</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body card">
        <form id="searchIndividualExternalMarksForm">
        <div class="row">
            <div class="col-md-2 form-group mb-2">
                <label for="exam_id">Exam Name<span class="text-danger">*</span></label>
                <select name="exam_id" id="exam_id" class="form-control">
                    <option value="">--Select--</option>
                    @foreach ($exams as $exam)
                        <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 form-group mb-2">
                <label for="course_type">Course Type<span class="text-danger">*</span></label>
                <select name="course_type" id="course_type" class="form-control">
                    <option value="">--Select--</option>
                    <option value="Annual Based">Annual Based</option>
                    <option value="Semester Based">Semester Based</option>
                </select>
                @error('course_type') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-md-2 form-group mb-2">
                <label for="course_id">Select Course<span class="text-danger">*</span></label>
                <select name="course_id" id="course_id" class="form-control">
                    <option value="">--Select--</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}" data-course="{{ $course->course }}">{{ $course->name }}</option>
                @endforeach
                </select>
            </div>
            <div class="col-md-2 form-group mb-2">
                <label for="scheme_id">Select Scheme</label>
                <select name="scheme_id" id="scheme_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
                @error('scheme_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-md-2 form-group mb-2">
                <label for="branch_id">Select Branch<span class="text-danger">*</span></label>
                <select name="branch_id" id="branch_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
            </div>
            <div class="col-md-2 form-group mb-2" id="semester_container"  style="display: none;">
                <label for="semester_id">Select Semester</label>
                <select name="semester_id" id="semester_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
            </div>
            <div class="col-md-2 form-group mb-2" id="annual_semester_container"  style="display: none;">
                <label for="annual_semester_id">Select Annual Terms</label>
                <select name="annual_semester_id" id="annual_semester_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
            </div>
            <div class="col-md-2 form-group mb-2">
                <label>Session / Batch <span class="text-danger">*</span></label>
                <select name="batch_id" id="batch_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
            </div>
            <div class="col-md-2 form-group mb-2">
                <label for="marks_type">Select Marks Type<span class="text-danger">*</span></label>
                <select name="marks_type" id="marks_type" class="form-control">
                    <option value="">--Select--</option>
                    {{-- <option value="Mid-Sem">Mid-Sem</option>
                    <option value="Quiz">Quiz</option>
                    <option value="Practical">Practical</option> --}}
                </select>
            </div>
            <div class="col-md-2 form-group mb-2" id="subject_container">
                <label for="institute_id">Select Institute</label>
                <select name="institute_id" id="institute_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
            </div>

            <div class="col-md-2 form-group mb-2" id="subject_container">
                <label for="subject_id">Select Subject<span class="text-danger">*</span></label>
                <select name="subject_id" id="subject_id" class="form-control">
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
                    {{-- <option value="supplementary">Supplementary / Compartment</option> --}}
                </select>
            </div>
            <div class="col-md-2 form-group mt-3 mb-2">
                <button type="button" id="applyFilter" class="btn btn-primary">Filter</button>
            </div>
        </div>
        </form>
    </div>
</div>
