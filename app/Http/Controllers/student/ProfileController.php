<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProfileController extends Controller
{


    public function update(Request $request)
    {
        $student = auth('student')->user();

        if (!$student) {
            abort(403);
        }

        /* ================= VALIDATION ================= */

        $validated = $request->validate([
            'father_name'       => 'nullable|string|max:255',
            'mother_name'       => 'nullable|string|max:255',
            'abcid'             => 'nullable|string|max:50',
            'phone'             => 'nullable|digits_between:10,15',
            'email'             => 'nullable|email|max:255',
            'dob'               => 'nullable|date',
            'current_address'   => 'nullable|string|max:1000',
            'parmanent_address' => 'nullable|string|max:1000',

            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:200',
            'signature'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:200',
        ]);

        $oldFiles = [];

        /* ================= PROFILE IMAGE ================= */

        if ($request->hasFile('profile_image')) {

            $file = $request->file('profile_image');

            $folder = public_path('students/profile');

            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }

            $filename = $student->university_roll_no
                . '_profile_'
                . Str::uuid()
                . '.' . $file->getClientOriginalExtension();

            $file->move($folder, $filename);

            if (!empty($student->profile_image)) {
                $oldFiles[] = public_path($student->profile_image);
            }

            $student->profile_image = 'students/profile/' . $filename;
        }

        /* ================= SIGNATURE ================= */

        if ($request->hasFile('signature')) {

            $file = $request->file('signature');

            $folder = public_path('students/signature');

            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }

            $filename = $student->university_roll_no
                . '_signature_'
                . Str::uuid()
                . '.' . $file->getClientOriginalExtension();

            $file->move($folder, $filename);

            if (!empty($student->signature)) {
                $oldFiles[] = public_path($student->signature);
            }

            $student->signature = 'students/signature/' . $filename;
        }

        /* ================= UPDATE OTHER FIELDS ================= */

        $student->father_name       = $validated['father_name'] ?? null;
        $student->mother_name       = $validated['mother_name'] ?? null;
        $student->abcid             = $validated['abcid'] ?? null;
        $student->phone             = $validated['phone'] ?? null;
        $student->email             = $validated['email'] ?? null;
        $student->dob               = $validated['dob'] ?? null;
        $student->current_address   = $validated['current_address'] ?? null;
        $student->parmanent_address = $validated['parmanent_address'] ?? null;

        $student->kyc_status = 1;

        /* ================= SAVE ONCE ================= */

        $student->save();

        /* ================= DELETE OLD FILES ================= */

        foreach ($oldFiles as $oldFile) {
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        /* ================= REDIRECT ================= */

        return redirect()
            ->route('student.dashboard')
            ->with('success', 'Profile updated successfully');
    }



}
