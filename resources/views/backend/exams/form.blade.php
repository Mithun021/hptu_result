<div class="row">
    <div class="col-md-6 mb-3">
        <label>Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control"
            value="{{ old('name', $exam->name ?? '') }}" required>
        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label>Short Name</label>
        <input type="text" name="sort_name" class="form-control"
            value="{{ old('sort_name', $exam->sort_name ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label>Admit Card Name</label>
        <input type="text" name="admit_card_name" class="form-control"
            value="{{ old('admit_card_name', $exam->admit_card_name ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label>Result Sheet Name</label>
        <input type="text" name="exam_resultsheet_name" class="form-control"
            value="{{ old('exam_resultsheet_name', $exam->exam_resultsheet_name ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label>Supplementary Result Sheet Name</label>
        <input type="text" name="supple_result_sheet" class="form-control"
            value="{{ old('supple_result_sheet', $exam->supple_result_sheet ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label>Reconciliation Result Sheet Name</label>
        <input type="text" name="reconsile_result_sheet" class="form-control"
            value="{{ old('reconsile_result_sheet', $exam->reconsile_result_sheet ?? '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label>DMC Name</label>
        <input type="text" name="dmc_name" class="form-control"
            value="{{ old('dmc_name', $exam->dmc_name ?? '') }}">
    </div>

    <div class="col-md-2 mb-3">
        <label>Examination year</label>
        <input type="text" name="exam_year" class="form-control"
            value="{{ old('exam_year', $exam->exam_year ?? '') }}" placeholder="eg : 2025">
    </div>

    <div class="col-md-2 mb-3">
        <label>Exam Month</label>
        <input type="text" name="exam_month" class="form-control"
            value="{{ old('exam_month', $exam->exam_month ?? '') }}" placeholder="eg : JAN,FEB,APR,MAY,JUN,JUL,AUG,SEP,OCT,NOV,DEC">
    </div>

    <div class="col-md-2 mb-3">
        <label>Is Active <span class="text-danger">*</span></label>
        <select name="is_active" class="form-control" required>
            <option value="1" {{ old('is_active', $exam->is_active ?? 1) == 1 ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ old('is_active', $exam->is_active ?? 1) == 0 ? 'selected' : '' }}>No</option>
        </select>
    </div>

    <div class="col-md-2 mb-3">
        <label>Show Admit Card <span class="text-danger">*</span></label>
        <select name="show_admitcard" class="form-control" required>
            <option value="1" {{ old('show_admitcard', $exam->show_admitcard ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ old('show_admitcard', $exam->show_admitcard ?? 0) == 0 ? 'selected' : '' }}>No</option>
        </select>
    </div>

    <div class="col-md-12 mb-3">
        <label>Description</label>
        <textarea name="description" class="form-control" maxlength="255" rows="3">{{ old('description', $exam->description ?? '') }}</textarea>
    </div>
</div>
