<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{

    public function update(Request $request)
    {
        $student = auth('student')->user();

        /* ================= VALIDATION ================= */
        $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|digits_between:10,15',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:500',
            'signature' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:500',
        ]);

        /* ================= PROFILE IMAGE ================= */
        if ($request->hasFile('profile_image')) {

            $path = public_path('students/profile');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            // delete old file
            if (!empty($student->profile_image) && file_exists(public_path($student->profile_image))) {
                unlink(public_path($student->profile_image));
            }

            $file = $request->file('profile_image');
            $extension = $file->getClientOriginalExtension();

            $filename = $student->university_roll_no
                . '_profile_'
                . time()
                . '.' . $extension;

            $file->move($path, $filename);

            $student->profile_image = 'students/profile/' . $filename;
        }

        /* ================= SIGNATURE ================= */
        if ($request->hasFile('signature')) {

            $path = public_path('students/signature');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            // delete old file
            if (!empty($student->signature) && file_exists(public_path($student->signature))) {
                unlink(public_path($student->signature));
            }

            $file = $request->file('signature');
            $extension = $file->getClientOriginalExtension();

            $filename = $student->university_roll_no
                . '_signature_'
                . time()
                . '.' . $extension;

            $file->move($path, $filename);

            $student->signature = 'students/signature/' . $filename;
        }

        /* ================= UPDATE OTHER FIELDS ================= */
        $student->father_name = $request->father_name;
        $student->mother_name = $request->mother_name;
        $student->abcid = $request->abcid;
        $student->phone = $request->phone;
        $student->email = $request->email;
        $student->dob = $request->dob;
        $student->current_address = $request->current_address;
        $student->parmanent_address = $request->parmanent_address; // DB spelling

        /* ================= SAVE ================= */
        $student->save();

        /* ================= REDIRECT ================= */
        return redirect()
            ->route('student.dashboard')
            ->with('success', 'Profile updated successfully');
    }

}
