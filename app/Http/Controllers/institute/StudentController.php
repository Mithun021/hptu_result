<?php

namespace App\Http\Controllers\institute;

use App\Http\Controllers\Controller;
use App\Models\AnnualSemester;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Institute;
use App\Models\Scheme;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends Controller
{
    public function index()
    {
        return view('institute.students.index');
    }

    public function data()
    {
        $authUser = auth('institute')->user();

        // Determine institute id
        $institute_id = $authUser->account_type === 'institute'
                            ? $authUser->id
                            : $authUser->institute_id;

        $students = Student::with([
            'course',
            'branch',
            'semester',
            'annual_semester',
            'scheme',
            'batch',
            'institute'
        ])->where('students.institute_id', $institute_id)
        ->select('students.*')
        ->orderBy('students.institute_id')
        ->orderBy('students.course_id')
        ->orderBy('students.branch_id')
        ->orderBy('students.semester_id')
        ->orderBy('students.annual_semester_id')
        ->orderBy('students.scheme_id')
        ->orderBy('students.scheme_id')
        ->orderByDesc('students.batch_id');

        return DataTables::of($students)
            ->addIndexColumn()

            ->addColumn('student', function ($row) {
                return '<strong>'.$row->student_name.'</strong><br>
                        <small class="text-primary">Reg: '.$row->reg_no.'</small>';
            })

            ->addColumn('father', fn ($row) => $row->father_name ?? '-')
            ->addColumn('university_roll', fn ($row) => $row->university_roll_no ?? '-')
            ->addColumn('course', fn ($row) => $row->course->name ?? '-')
            ->addColumn('branch', fn ($row) => $row->branch->name ?? '-')
            // ->addColumn('semester', fn ($row) => $row->semester->name ?? '-')
            ->addColumn('semester', function ($row) {
                // Fallbacks if course_type missing or null
                if (isset($row->course) && $row->course->course_type === 'Semester Based') {
                    return $row->semester->name ?? '-';
                } elseif (isset($row->course) && $row->course->course_type === 'Annual Based') {
                    return $row->annual_semester->name ?? '-';
                }

                // If course_type is missing, try to infer
                return $row->semester->name ?? $row->annual_semester->name ?? '-';
            })
            ->addColumn('scheme', fn ($row) => $row->scheme->name ?? '-')
            ->addColumn('batch', fn ($row) => $row->batch->name ?? '-')
            ->addColumn('institute', fn ($row) => $row->institute->name ?? '-')
            ->addColumn('phone', fn ($row) => $row->phone ?? '-')

            ->addColumn('status', function ($row) {
                return $row->pursuing_status === 'yes'
                    ? '<span class="badge bg-info">Pursuing</span>'
                    : '<span class="badge bg-success">Complete</span>';
            })

            ->addColumn('checkbox', function ($row) {
                return '
                    <div class="d-flex align-items-center">
                        <input type="checkbox" class="student_checkbox me-2" value="'.$row->id.'">
                        <span>'.$row->DT_RowIndex.'</span>
                    </div>
                ';
            })


            ->addColumn('action', function ($row) {
                return '
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" type="button"
                        id="actionDropdown'.$row->id.'"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="mdi mdi-dots-vertical fs-16"></i>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end"
                        aria-labelledby="actionDropdown'.$row->id.'">

                        <li>
                            <a class="dropdown-item" href="'.route('institute.students.edit', $row->id).'">
                                <i class="mdi mdi-pencil-outline me-2 text-primary"></i> Edit
                            </a>
                        </li>

                        <li>
                            <form action="'.route('institute.students.destroy', $row->id).'"
                                method="POST"
                                class="d-inline delete-form">
                                '.csrf_field().'
                                '.method_field('DELETE').'
                                <button type="submit" class="dropdown-item text-danger"
                                    onclick="return confirm(\'Are you sure you want to delete this student?\')">
                                    <i class="mdi mdi-delete-outline me-2"></i> Delete
                                </button>
                            </form>
                        </li>

                    </ul>
                </div>';
            })

            ->rawColumns(['checkbox','student', 'status', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('institute.students.create',[
            'courses' => Course::all(),
            'institutes' => Institute::all()
        ]);
    }

    public function store(Request $request)
    {
        // ============================
        // VALIDATION RULES
        // ============================
        $validator = Validator::make($request->all(), [
            'course_type'         => 'required|in:Annual Based,Semester Based',
            'course_id'           => 'required|exists:courses,id',
            'branch_id'           => 'required|exists:branches,id',
            'semester_id'         => 'required_if:course_type,Semester Based|nullable',
            'annual_semester_id'  => 'required_if:course_type,Annual Based|nullable',
            'scheme_id'           => 'nullable|exists:schemes,id',
            'batch_id'            => 'required|exists:batches,id',

            'university_roll_no'  => 'required|string|max:50|unique:students,university_roll_no',
            'admission_year'      => 'nullable|string|max:50',
            'dob'                 => 'required|date',
            'student_name'        => 'required|string|max:255',
            'father_name'         => 'required|string|max:255',
            'mother_name'         => 'required|string|max:255',

            'abcid'               => 'nullable|string|max:50',

            'gender'              => 'required|in:male,female,other',
            'dob'                 => 'required|date',

            'aadhar'              => ['nullable','regex:/^[2-9]{1}[0-9]{11}$/','unique:students,aadhar'],

            'current_address'     => 'nullable|string|max:500',
            'parmanent_address'   => 'nullable|string|max:500',

            'phone'               => ['required','regex:/^[6-9]\d{9}$/','unique:students,phone'],
            //'parent_phone'        => ['nullable','regex:/^[6-9]\d{9}$/','unique:students,parent_phone'],
            'email'               => 'required|email|unique:students,email',

            'nationality'         => 'nullable|string|max:100',
            'state'               => 'nullable|string|max:100',
            'city'               => 'nullable|string|max:100',
            'category'            => 'nullable|string|max:50',

            'profile_image'       => 'nullable',
            'signature'           => 'nullable',

            'pursuing_status'     => 'required|in:yes,no',
        ]);


        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $authUser = auth('institute')->user();

        // Determine institute id
        $institute_id = $authUser->account_type === 'institute'
                            ? $authUser->id
                            : $authUser->institute_id;

        $profileImagePath = null;
        $signaturePath = null;

        // sanitize roll no (remove spaces & special chars)
        $profileNewName = preg_replace('/[^A-Za-z0-9\-]/', '_', $request->university_roll_no);

        // ================= PROFILE IMAGE =================
        if ($request->hasFile('profile_image')) {
            $path = public_path('students/profile');

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $file = $request->file('profile_image');
            $extension = $file->getClientOriginalExtension();

            $filename = $profileNewName.'_profile_'.time().'.'.$extension;

            $file->move($path, $filename);

            $profileImagePath = 'students/profile/'.$filename;
        }

        // ================= SIGNATURE =================
        if ($request->hasFile('signature')) {
            $path = public_path('students/signatures');

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $file = $request->file('signature');
            $extension = $file->getClientOriginalExtension();

            $filename = $profileNewName.'_signature_'.time().'.'.$extension;

            $file->move($path, $filename);

            $signaturePath = 'students/signatures/'.$filename;
        }



        // ============================
        // SAVE DATA
        // ============================
        $student = new Student();
        $student->institute_id = trim($institute_id);
        $student->course_type = trim($request->course_type);
        $student->course_id = trim($request->course_id);
        $student->branch_id = trim($request->branch_id);
        $student->semester_id = $request->semester_id;
        $student->annual_semester_id = $request->annual_semester_id;
        // $student->scheme_id = trim($request->scheme_id) ?? null;
        $student->scheme_id = $request->filled('scheme_id') 
                        ? $request->scheme_id 
                        : null;
        $student->batch_id = trim($request->batch_id);
        $student->university_roll_no = trim($request->university_roll_no);
        $student->reg_no = trim($request->reg_no);
        $student->admission_year = trim($request->admission_year);
        $student->student_name = trim($request->student_name);
        $student->father_name = trim($request->father_name);
        $student->mother_name = trim($request->mother_name);
        $student->abcid = trim($request->abcid);
        $student->gender = trim($request->gender);
        $student->dob = trim($request->dob);
        $student->aadhar = trim($request->aadhar);
        $student->current_address = trim($request->current_address);
        $student->parmanent_address = trim($request->parmanent_address);
        $student->phone = trim($request->phone);
        //$student->parent_phone = trim($request->parent_phone);
        $student->email = trim($request->email);
        $student->nationality = trim($request->nationality);
        $student->state = trim($request->state);
        $student->city = trim($request->city);
        $student->category = trim($request->category);
        $student->pursuing_status = trim($request->pursuing_status);
        $student->profile_image = $profileImagePath;
        $student->signature = $signaturePath;
        $student->password = $request->filled('password')
                            ? $request->password
                            : '123456';

        $student->save();
        return redirect()->route('institute.students.index')->with('success', 'Student added successfully!');
    }

    public function edit(string $id)
    {
        $student = Student::with([
            'course',
            'branch',
            'semester',
            'annual_semester',
            'scheme',
            'batch',
            'institute'
        ])->findOrFail($id);

        return view('institute.students.edit', [
            'courses' => Course::all(),
            'student' => $student
        ]);
    }

    public function update(Request $request, String $id)
    {
        // dd($request->all());
        $student = Student::findOrFail($id);

        $validator = Validator::make($request->all(), [
            // 'institute_id'        => 'required|exists:institutes,id',
            'course_id'           => 'required|exists:courses,id',
            'branch_id'           => 'required|exists:branches,id',
            'semester_id'         => 'required_if:course_type,Semester Based|nullable',
            'annual_semester_id'  => 'required_if:course_type,Annual Based|nullable',
            'scheme_id'           => 'nullable|exists:schemes,id',
            'batch_id'            => 'required|exists:batches,id',
            'university_roll_no'  => 'required|string|max:50|unique:students,university_roll_no,' . $student->id,
            'reg_no'              => 'nullable|string|max:50',
            'student_name'        => 'required|string|max:255',
            'father_name'         => 'required|string|max:255',
            'mother_name'         => 'required|string|max:255',
            'abcid'               => 'nullable|string|max:50',
            'gender'              => 'required|in:male,female,other',
            'dob'                 => 'required|date',
            'aadhar'              => ['nullable', 'regex:/^[2-9]{1}[0-9]{11}$/', 'unique:students,aadhar,' . $student->id],
            'current_address'     => 'required|string|max:500',
            'parmanent_address'   => 'required|string|max:500',
            'phone'               => ['required', 'regex:/^[6-9]\d{9}$/', 'unique:students,phone,' . $student->id],
            //'parent_phone'        => ['nullable', 'regex:/^[6-9]\d{9}$/', 'unique:students,parent_phone,' . $student->id],
            'email'               => 'required|email|unique:students,email,' . $student->id,
            'nationality'         => 'required|string|max:100',
            'state'               => 'required|string|max:100',
            'city'               => 'required|string|max:100',
            'category'            => 'required|string|max:50',
            'profile_image'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'signature'           => 'nullable|image|mimes:jpeg,png,jpg|max:1024',
            'password'            => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $authUser = auth('institute')->user();

        // Determine institute id
        $institute_id = $authUser->account_type === 'institute'
                            ? $authUser->id
                            : $authUser->institute_id;

        $profileImagePath = null;
        $signaturePath    = null;

        // sanitize roll no (remove spaces & special chars)
        $profileNewName = preg_replace('/[^A-Za-z0-9\-]/', '_', $request->university_roll_no);

        /* ================= PROFILE IMAGE ================= */
        if ($request->hasFile('profile_image')) {

            $path = public_path('students/profile');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            // delete old file if exists
            if ($student->profile_image && file_exists(public_path($student->profile_image))) {
                unlink(public_path($student->profile_image));
            }

            $file      = $request->file('profile_image');
            $extension = $file->getClientOriginalExtension();
            $filename  = $profileNewName . '_profile_' . time() . '.' . $extension;

            $file->move($path, $filename);

            $profileImagePath = 'students/profile/' . $filename;
        }

        /* ================= SIGNATURE ================= */
        if ($request->hasFile('signature')) {

            $path = public_path('students/signatures');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            // delete old file if exists
            if ($student->signature && file_exists(public_path($student->signature))) {
                unlink(public_path($student->signature));
            }

            $file      = $request->file('signature');
            $extension = $file->getClientOriginalExtension();
            $filename  = $profileNewName . '_signature_' . time() . '.' . $extension;

            $file->move($path, $filename);

            $signaturePath = 'students/signatures/' . $filename;
        }

        // Update other fields
        $student->institute_id = $institute_id;
        $student->course_id = trim($request->course_id);
        $student->branch_id = trim($request->branch_id);
        $student->semester_id = $request->semester_id;
        $student->annual_semester_id = $request->annual_semester_id;
        // $student->scheme_id = trim($request->scheme_id);
        $student->scheme_id = $request->filled('scheme_id') 
                        ? $request->scheme_id 
                        : null;
        $student->batch_id = trim($request->batch_id);
        $student->university_roll_no = trim($request->university_roll_no);
        $student->reg_no = trim($request->reg_no);
        $student->student_name = trim($request->student_name);
        $student->father_name = trim($request->father_name);
        $student->mother_name = trim($request->mother_name);
        $student->abcid = trim($request->abcid);
        $student->gender = trim($request->gender);
        $student->dob = trim($request->dob);
        $student->aadhar = trim($request->aadhar);
        $student->current_address = trim($request->current_address);
        $student->parmanent_address = trim($request->parmanent_address);
        $student->phone = trim($request->phone);
        //$student->parent_phone = trim($request->parent_phone);
        $student->email = trim($request->email);
        $student->nationality = trim($request->nationality);
        $student->state = trim($request->state);
        $student->city = trim($request->city);
        $student->category = trim($request->category);

        if ($request->filled('password')) {
            $student->password = $request->password;
        }

        if ($profileImagePath !== null) {
            $student->profile_image = $profileImagePath;
        }

        if ($signaturePath !== null) {
            $student->signature = $signaturePath;
        }

        $student->save();

        return redirect()->route('institute.students.index')->with('success', 'Student updated successfully!');
    }

    public function destroy(String $id)
    {
        $student = Student::findOrFail($id);

        if ($student->profile_image && Storage::disk('public')->exists($student->profile_image)) {
            Storage::disk('public')->delete($student->profile_image);
        }

        // Delete signature if exists
        if ($student->signature && Storage::disk('public')->exists($student->signature)) {
            Storage::disk('public')->delete($student->signature);
        }

        $student->delete();

        return redirect()->route('institute.students.index')
            ->with('success', 'Student deleted successfully.');
    }

    public function promote(Request $request)
    {
        $students = Student::with('course')->whereIn('id', $request->ids)->get();

        foreach ($students as $student) {

            if ($student->course_type === 'Semester Based') {

                // All semesters of course
                $semesters = $student->course->semesters()->orderBy('id')->pluck('id')->toArray();

                $currentIndex = array_search($student->semester_id, $semesters);

                if ($currentIndex === false) continue;

                // Last semester check
                if ($currentIndex == count($semesters) - 1) {
                    $student->pursuing_status = 'no';
                } else {
                    $student->semester_id = $semesters[$currentIndex + 1];
                }

            } else {

                // Annual Based
                $annuals = $student->course->annual_semesters()->orderBy('id')->pluck('id')->toArray();

                $currentIndex = array_search($student->annual_semester_id, $annuals);

                if ($currentIndex === false) continue;

                if ($currentIndex == count($annuals) - 1) {
                    $student->pursuing_status = 'no';
                } else {
                    $student->annual_semester_id = $annuals[$currentIndex + 1];
                }
            }

            $student->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Students promoted successfully!'
        ]);
    }

    public function importStudent(Request $request){
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:2048',
        ]);

        try {
            $path = $request->file('file')->getRealPath();

            // Read CSV lines safely
            $rawData = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (!$rawData || count($rawData) <= 1) {
                return back()->with('error', 'CSV file is empty or invalid.');
            }

            // Convert CSV to array
            $data = array_map('str_getcsv', $rawData);

            // Normalize and extract headers
            $header = array_map(fn($h) => strtolower(trim($h)), array_shift($data));

            $insertCount = 0;

            DB::beginTransaction();

            foreach ($data as $row) {

                if (count(array_filter($row)) === 0) continue;

                $row = array_combine($header, $row);
                if (!$row) continue;

                $course   = Course::where('name', trim($row['course_id'] ?? ''))->first();
                // $branch   = Branch::where('name', trim($row['branch_id'] ?? ''))->first();
                if ($course) {
                    $branch = Branch::where('course_id', $course->id)
                                    ->where('name', trim($row['branch_id'] ?? ''))
                                    ->first();
                } else {
                    $branch = null;
                }
                $semester = Semester::where('name', trim($row['semester_id'] ?? ''))->first();
                $annual_semester = AnnualSemester::where('name', trim($row['annual_semester_id'] ?? ''))->first();
                // $scheme   = Scheme::where('name', trim($row['scheme_id'] ?? ''))->first();
                $schemeName = trim($row['scheme_id'] ?? '');
                $scheme = !empty($schemeName)
                    ? Scheme::where('name', $schemeName)->first()
                    : null;
                $batch   = Batch::where('name', trim($row['batch_id'] ?? ''))->first();
                $institute   = Institute::where('name', trim($row['institute_id'] ?? ''))->first();


                $exists = Student::where('university_roll_no', trim($row['university_roll_no'] ?? ''))
                    ->where('course_id', $course?->id)
                    ->where('branch_id', $branch?->id)
                    ->where('semester_id', $semester?->id)
                    ->where('annual_semester_id', $annual_semester?->id)
                    ->where('scheme_id', $scheme?->id)
                    ->where('batch_id', $batch?->id)
                    ->where('institute_id', $institute?->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                // Create new Student
                Student::create([
                    'course_type'    => trim($row['course_type'] ?? ''),
                    'course_id'      => $course?->id,
                    'branch_id'      => $branch?->id,
                    'semester_id'    => $semester?->id ?? null,
                    'annual_semester_id'    => $annual_semester?->id ?? null,
                    'scheme_id'      => $scheme?->id ?? null,
                    'batch_id'       => $batch?->id ?? null,
                    'institute_id'   => $institute?->id,
                    'university_roll_no'   => trim($row['university_roll_no'] ?? ''),
                    'reg_no'        => trim($row['reg_no'] ?? ''),
                    'student_name' => trim($row['student_name'] ?? ''),
                    'father_name' => trim($row['father_name'] ?? ''),
                    'mother_name'          => trim($row['mother_name'] ?? ''),
                    'abcid'          => trim($row['abcid'] ?? ''),
                    'gender'           => strtolower(trim($row['gender'] ?? 'male')),
                    'dob'   => trim($row['dob'] ?? ''),
                    'aadhar'        => trim($row['aadhar'] ?? ''),
                    'current_address' => trim($row['current_address'] ?? ''),
                    'parmanent_address' => trim($row['parmanent_address'] ?? ''),
                    'phone'          => trim($row['phone'] ?? ''),
                    'email'        => trim($row['email'] ?? ''),
                    'nationality' => trim($row['nationality'] ?? ''),
                    'state' => trim($row['state'] ?? ''),
                    'city' => trim($row['city'] ?? ''),
                    'category'          => trim($row['category'] ?? ''),
                    'profile_image' => trim($row['profile_image'] ?? ''),
                    'signature' => trim($row['signature'] ?? ''),
                    'password'          => "123456",

                ]);

                $insertCount++;
            }

            DB::commit();

            return back()->with('success', "Successfully imported {$insertCount} students!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }


}
