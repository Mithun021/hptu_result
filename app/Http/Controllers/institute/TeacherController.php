<?php

namespace App\Http\Controllers\institute;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\QueryException;
use App\Models\Institute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    public function index()
    {
        $instituteId = auth('institute')->id();

        $teachers = Institute::with('parentInstitute')
            ->where('account_type', 'teacher')
            ->where('institute_id', $instituteId)
            ->get();

        return view('institute.teacher.index', compact('teachers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required',
            'sort_name'     => 'required',
            'email'    => 'required|email|unique:institutes,email',
            'phone' => ['nullable', 'regex:/^[6-9]\d{9}$/'],
            'password' => 'required|string|min:6',
        ]);

        try {
            $user = Institute::create([
                'name'     => $request->name,
                'sort_name'  => $request->sort_name,
                'email'    => $request->email,
                'phone'     => $request->phone,
                'password' => $request->password,
                'account_type' => 'teacher',
                'institute_id' => auth('institute')->id(),
            ]);

            return to_route('teachers.index')->with('success', 'Teacher created successfully!');
        } catch (QueryException $e) {
            return back()->withInput()->with('error', 'Database error: '.$e->getMessage());
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Something went wrong: '.$e->getMessage());
        }
    }

    public function edit(string $id)
    {
        return view('institute.teacher.edit',[
            'institute' => Institute::findOrFail($id)
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'     => 'required',
            'sort_name' => 'nullable',
            'email'    => 'required|email|unique:institutes,email,' . $id,
            'phone' => ['nullable', 'regex:/^[6-9]\d{9}$/'],
            'password' => 'nullable|string|min:6',
        ]);

        try {
            $institute = Institute::findOrFail($id);

            $institute->name  = $request->name;
            $institute->sort_name  = $request->sort_name;
            $institute->email = $request->email;
            $institute->phone  = $request->phone;
            $institute->account_type  = 'teacher';
            $institute->institute_id  = auth('institute')->id();

            if ($request->filled('password')) {
                $institute->password = $request->password;
            }

            $institute->save();

            return to_route('teachers.index')->with('success', 'Teacher updated successfully!');
        } catch (QueryException $e) {
            // Handle database errors
            return back()->withInput()->with('error', 'Database error: ' . $e->getMessage());
        } catch (Exception $e) {
            // Handle general errors
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $institute = Institute::findOrFail($id);
            if ($institute) {
                Institute::destroy($id);
                return to_route('teachers.index')->with('success', 'Teacher Delete successfully!');
            }else{
                return to_route('teachers.index')->with('error', 'Teacher not found!');
            }
        } catch (QueryException $e) {
            // Handle database errors
            return back()->withInput()->with('error', 'Database error: ' . $e->getMessage());
        } catch (Exception $e) {
            // Handle general errors
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function profile()
    {
        $authUser = auth('institute')->user();

        $instituteId = $authUser->account_type === 'institute'
            ? auth('institute')->id()
            : $authUser->institute_id;

        $isInstitute = $authUser->account_type === 'institute';

        $authUser->load('parentInstitute');

        return view('institute.profile', compact(
            'authUser',
            'instituteId',
            'isInstitute'
        ));
    }


    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password:institute'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'Your current password is incorrect.',
            'password.required' => 'Please enter your new password.',
            'password.min' => 'New password must be at least 8 characters.',
            'password.confirmed' => 'New password and confirm password do not match.',
        ]);

        $authUser = auth('institute')->user();

        /*
        * Institute model mein setPasswordAttribute() mutator
        * password ko automatically bcrypt karta hai.
        * Isliye yahan plain password assign karenge.
        */
        $authUser->password = $request->password;
        $authUser->save();

        return redirect()
            ->route('institute.profile')
            ->with('success', 'Password changed successfully.');
    }

}
