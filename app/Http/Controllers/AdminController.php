<?php

namespace App\Http\Controllers;

use App\Models\Institute;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;


class AdminController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('backend.index', compact('user'));
    }
    
    public function mailConfig()
    {
        $envPath = base_path('.env');

        $mailConfig = [
            'MAIL_MAILER' => env('MAIL_MAILER', 'smtp'),
            'MAIL_HOST' => env('MAIL_HOST', ''),
            'MAIL_PORT' => env('MAIL_PORT', '587'),
            'MAIL_USERNAME' => env('MAIL_USERNAME', ''),
            'MAIL_PASSWORD' => env('MAIL_PASSWORD', ''),
            'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION', 'tls'),
            'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS', ''),
            'MAIL_FROM_NAME' => env('MAIL_FROM_NAME', config('app.name')),
        ];

        return view('backend.mail-config.index', compact(
            'mailConfig',
            'envPath'
        ));
    }

    /**
     * Save / Update Mail Configuration in .env
     */
    public function mailConfigUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'MAIL_MAILER' => 'required|string|max:50',
            'MAIL_HOST' => 'required|string|max:255',
            'MAIL_PORT' => 'required|integer|min:1|max:65535',
            'MAIL_USERNAME' => 'required|string|max:255',
            'MAIL_PASSWORD' => 'required|string|max:255',
            'MAIL_ENCRYPTION' => 'nullable|string|in:tls,ssl',
            'MAIL_FROM_ADDRESS' => 'required|email|max:255',
            'MAIL_FROM_NAME' => 'required|string|max:255',
        ], [
            'MAIL_PORT.integer' => 'Mail port must be a valid number.',
            'MAIL_PORT.min' => 'Mail port must be greater than 0.',
            'MAIL_PORT.max' => 'Mail port cannot be greater than 65535.',
            'MAIL_FROM_ADDRESS.email' => 'Please enter a valid from email address.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return redirect()
                ->back()
                ->with('error', '.env file not found.');
        }

        $envContent = file_get_contents($envPath);

        $mailConfig = [
            'MAIL_MAILER' => $request->MAIL_MAILER,
            'MAIL_HOST' => $request->MAIL_HOST,
            'MAIL_PORT' => $request->MAIL_PORT,
            'MAIL_USERNAME' => $request->MAIL_USERNAME,
            'MAIL_PASSWORD' => $request->MAIL_PASSWORD,
            'MAIL_ENCRYPTION' => $request->MAIL_ENCRYPTION,
            'MAIL_FROM_ADDRESS' => $request->MAIL_FROM_ADDRESS,
            'MAIL_FROM_NAME' => $request->MAIL_FROM_NAME,
        ];

        foreach ($mailConfig as $key => $value) {

            // Escape quotes if present
            $value = str_replace('"', '\"', $value);

            // Wrap values containing spaces/special characters
            $formattedValue = '"' . $value . '"';

            $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

            if (preg_match($pattern, $envContent)) {

                // Existing key → Update
                $envContent = preg_replace(
                    $pattern,
                    $key . '=' . $formattedValue,
                    $envContent
                );

            } else {

                // Key does not exist → Add
                $envContent .= PHP_EOL . $key . '=' . $formattedValue;
            }
        }

        file_put_contents($envPath, $envContent);

        /*
        |--------------------------------------------------------------------------
        | Clear Laravel Configuration Cache
        |--------------------------------------------------------------------------
        */

        Artisan::call('config:clear');

        /*
        |--------------------------------------------------------------------------
        | Rebuild Configuration Cache if your project uses it
        |--------------------------------------------------------------------------
        */

        // Uncomment this if you normally use config caching in production.
        // Artisan::call('config:cache');

        return redirect()
            ->route('backend.mail-config.index')
            ->with('success', 'Mail configuration saved successfully.');
    }
    
    public function sendMail()
    {
        return view('backend.send-mail.index');
    }
    
    public function sendMailStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emails' => 'required|string',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ], [
            'emails.required' => 'Please enter at least one email address.',
            'subject.required' => 'Please enter mail subject.',
            'message.required' => 'Please enter mail message.',
        ]);
    
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }
    
    
        /*
        |--------------------------------------------------------------------------
        | Convert comma / newline separated emails into array
        |--------------------------------------------------------------------------
        */
    
        $emailString = $request->emails;
    
        $emails = preg_split(
            '/[\s,;]+/',
            $emailString,
            -1,
            PREG_SPLIT_NO_EMPTY
        );
    
    
        /*
        |--------------------------------------------------------------------------
        | Remove duplicate emails
        |--------------------------------------------------------------------------
        */
    
        $emails = array_values(
            array_unique(
                array_map('strtolower', $emails)
            )
        );
    
    
        /*
        |--------------------------------------------------------------------------
        | Maximum 10 Emails
        |--------------------------------------------------------------------------
        */
    
        if (count($emails) > 10) {
            return redirect()
                ->back()
                ->with('error', 'You can send mail to maximum 10 email addresses at a time.')
                ->withInput();
        }
    
    
        /*
        |--------------------------------------------------------------------------
        | Validate Email Addresses
        |--------------------------------------------------------------------------
        */
    
        $invalidEmails = [];
    
        foreach ($emails as $email) {
    
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $invalidEmails[] = $email;
            }
        }
    
    
        if (!empty($invalidEmails)) {
    
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Invalid email address: ' . implode(', ', $invalidEmails)
                )
                ->withInput();
        }
    
    
        /*
        |--------------------------------------------------------------------------
        | Send Mail
        |--------------------------------------------------------------------------
        */
    
        $successEmails = [];
        $failedEmails = [];
    
        foreach ($emails as $email) {
    
            try {
    
                Mail::html($request->message, function ($mail) use ($email, $request) {
    
                    $mail->to($email)
                        ->subject($request->subject);
                });
    
                $successEmails[] = $email;
    
            } catch (\Throwable $e) {
    
                $failedEmails[] = [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ];
            }
        }
    
    
        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */
    
        if (count($successEmails) > 0 && count($failedEmails) == 0) {
    
            return redirect()
                ->route('backend.send-mail.index')
                ->with(
                    'success',
                    count($successEmails) . ' email(s) sent successfully.'
                );
        }
    
    
        if (count($successEmails) > 0 && count($failedEmails) > 0) {
    
            $failedEmailNames = array_column($failedEmails, 'email');
    
            return redirect()
                ->route('backend.send-mail.index')
                ->with(
                    'success',
                    count($successEmails) . ' email(s) sent successfully.'
                )
                ->with(
                    'error',
                    count($failedEmails) . ' email(s) failed: ' .
                    implode(', ', $failedEmailNames)
                );
        }
    
    
        return redirect()
            ->back()
            ->with(
                'error',
                'Mail could not be sent to any recipient.'
            )
            ->withInput();
    }
    
    // College Forget Password Area ------------------------------------------------------
    
    public function collegeForgotPassword()
    {
        return view('forgot-password.college.index');
    }
    
    public function collegeSendResetLink(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
            ],
        ]);
    
        // Clean email
        $email = strtolower(trim($request->email));
    
    
        /*
        |--------------------------------------------------------------------------
        | Check Email Exists in Institutes Table
        |--------------------------------------------------------------------------
        */
        $institute = Institute::whereRaw(
            'LOWER(email) = ?',
            [$email]
        )->first();
    
    
        /*
        |--------------------------------------------------------------------------
        | Email Not Registered
        |--------------------------------------------------------------------------
        */
        if (!$institute) {
    
            return back()
                ->withInput()
                ->with(
                    'error',
                    'This email address is not registered with any college account.'
                );
        }
    
    
        /*
        |--------------------------------------------------------------------------
        | Delete Old Reset Tokens
        |--------------------------------------------------------------------------
        */
        DB::table('institute_password_reset_tokens')
            ->where('email', $email)
            ->delete();
    
    
        /*
        |--------------------------------------------------------------------------
        | Generate New Reset Token
        |--------------------------------------------------------------------------
        */
        $token = Str::random(64);
    
    
        /*
        |--------------------------------------------------------------------------
        | Save Token
        |--------------------------------------------------------------------------
        */
        DB::table('institute_password_reset_tokens')->insert([
            'email' => $email,
            'token' => hash('sha256', $token),
            'expires_at' => Carbon::now()->addMinutes(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
    
        /*
        |--------------------------------------------------------------------------
        | Generate Reset URL
        |--------------------------------------------------------------------------
        */
        $resetUrl = route('college.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    
    
        /*
        |--------------------------------------------------------------------------
        | Send Reset Email
        |--------------------------------------------------------------------------
        */
        try {
    
            Mail::send(
                'emails.college-password-reset',
                [
                    'institute' => $institute,
                    'resetUrl' => $resetUrl,
                ],
                function ($mail) use ($email) {
    
                    $mail->to($email)
                        ->subject('Reset Your College Account Password');
                }
            );
    
        } catch (\Throwable $e) {
    
            /*
            |--------------------------------------------------------------------------
            | Email Sending Failed
            |--------------------------------------------------------------------------
            */
    
            DB::table('institute_password_reset_tokens')
                ->where('email', $email)
                ->delete();
    
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to send password reset email. Please try again later.'
                );
        }
    
    
        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */
    
        return back()->with(
            'success',
            'Password reset link has been sent successfully to your registered email address.'
        );
    }
    
    public function collegeResetPassword(
        Request $request,
        string $token
    ) {
        $email = strtolower(trim($request->query('email', '')));

        if (!$email) {
            return redirect()
                ->route('college.forgot-password')
                ->with('error', 'Invalid password reset link.');
        }

        /*
        |--------------------------------------------------------------------------
        | Find Token
        |--------------------------------------------------------------------------
        */
        $resetToken = DB::table('institute_password_reset_tokens')
            ->where('email', $email)
            ->where('token', hash('sha256', $token))
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Invalid Token
        |--------------------------------------------------------------------------
        */
        if (!$resetToken) {
            return redirect()
                ->route('college.forgot-password')
                ->with('error', 'Invalid or expired password reset link.');
        }

        /*
        |--------------------------------------------------------------------------
        | Check Expiry
        |--------------------------------------------------------------------------
        */
        if (Carbon::parse($resetToken->expires_at)->isPast()) {

            DB::table('institute_password_reset_tokens')
                ->where('id', $resetToken->id)
                ->delete();

            return redirect()
                ->route('college.forgot-password')
                ->with('error', 'This password reset link has expired.');
        }

        /*
        |--------------------------------------------------------------------------
        | Check Institute
        |--------------------------------------------------------------------------
        */
        $institute = Institute::whereRaw(
            'LOWER(email) = ?',
            [$email]
        )->first();

        if (!$institute) {
            return redirect()
                ->route('college.forgot-password')
                ->with('error', 'Invalid password reset link.');
        }

        return view(
            'forgot-password.college.reset-password',
            compact(
                'token',
                'email',
                'institute'
            )
        );
    }
    
    public function collegeUpdatePassword(Request $request)
    {
        $request->validate([
            'token' => [
                'required',
                'string',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $email = strtolower(trim($request->email));

        /*
        |--------------------------------------------------------------------------
        | Find Token
        |--------------------------------------------------------------------------
        */
        $resetToken = DB::table('institute_password_reset_tokens')
            ->where('email', $email)
            ->where('token', hash('sha256', $request->token))
            ->first();

        if (!$resetToken) {

            return redirect()
                ->route('college.forgot-password')
                ->with(
                    'error',
                    'Invalid or expired password reset link.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Check Expiry
        |--------------------------------------------------------------------------
        */
        if (Carbon::parse($resetToken->expires_at)->isPast()) {

            DB::table('institute_password_reset_tokens')
                ->where('id', $resetToken->id)
                ->delete();

            return redirect()
                ->route('college.forgot-password')
                ->with(
                    'error',
                    'This password reset link has expired.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Find Institute
        |--------------------------------------------------------------------------
        */
        $institute = Institute::whereRaw(
            'LOWER(email) = ?',
            [$email]
        )->first();

        if (!$institute) {

            return redirect()
                ->route('college.forgot-password')
                ->with(
                    'error',
                    'Unable to update password.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Update Password
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | Institute model already has:
        |
        | setPasswordAttribute()
        |
        | which automatically bcrypts the password.
        |
        */
        $institute->password = $request->password;
        $institute->save();

        /*
        |--------------------------------------------------------------------------
        | Delete Used Token
        |--------------------------------------------------------------------------
        */
        DB::table('institute_password_reset_tokens')
            ->where('email', $email)
            ->delete();

        return redirect()
            ->route('college.forgot-password')
            ->with(
                'success',
                'Your password has been reset successfully. You can now login with your new password.'
            );
    }
    
    // Student Forget Password Area ------------------------------------------------------
    
    public function studentForgotPassword()
    {
        return view('forgot-password.student.index');
    }
    
    public function studentSendResetLink(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
            ],
        ]);

        $email = strtolower(trim($request->email));


        /*
        |--------------------------------------------------------------------------
        | Check Student Email Exists
        |--------------------------------------------------------------------------
        */
        $student = Student::whereRaw(
            'LOWER(email) = ?',
            [$email]
        )->first();


        /*
        |--------------------------------------------------------------------------
        | Email Not Registered
        |--------------------------------------------------------------------------
        */
        if (!$student) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'This email address is not registered with any student account.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Delete Old Tokens
        |--------------------------------------------------------------------------
        */
        DB::table('student_password_reset_tokens')
            ->where('email', $email)
            ->delete();


        /*
        |--------------------------------------------------------------------------
        | Generate Token
        |--------------------------------------------------------------------------
        */
        $token = Str::random(64);


        /*
        |--------------------------------------------------------------------------
        | Save Hashed Token
        |--------------------------------------------------------------------------
        */
        DB::table('student_password_reset_tokens')->insert([
            'email' => $email,
            'token' => hash('sha256', $token),
            'expires_at' => Carbon::now()->addMinutes(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        /*
        |--------------------------------------------------------------------------
        | Reset URL
        |--------------------------------------------------------------------------
        */
        $resetUrl = route('student.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Send Email
        |--------------------------------------------------------------------------
        */
        try {

            $emailBody = view(
                'emails.student-password-reset',
                [
                    'student' => $student,
                    'resetUrl' => $resetUrl,
                ]
            )->render();


            Mail::html(
                $emailBody,
                function ($mail) use ($email) {

                    $mail->to($email)
                        ->subject('Reset Your Student Account Password');
                }
            );

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Delete Token If Email Failed
            |--------------------------------------------------------------------------
            */

            DB::table('student_password_reset_tokens')
                ->where('email', $email)
                ->delete();

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to send password reset email. Please try again later.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        return back()->with(
            'success',
            'Password reset link has been sent successfully to your registered email address.'
        );
    }
    
    public function studentResetPassword(
        Request $request,
        string $token
    ) {
        $email = strtolower(
            trim($request->query('email', ''))
        );


        /*
        |--------------------------------------------------------------------------
        | Email Missing
        |--------------------------------------------------------------------------
        */
        if (!$email) {

            return redirect()
                ->route('student.forgot-password')
                ->with(
                    'error',
                    'Invalid password reset link.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Find Token
        |--------------------------------------------------------------------------
        */
        $resetToken = DB::table('student_password_reset_tokens')
            ->where('email', $email)
            ->where(
                'token',
                hash('sha256', $token)
            )
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Invalid Token
        |--------------------------------------------------------------------------
        */
        if (!$resetToken) {

            return redirect()
                ->route('student.forgot-password')
                ->with(
                    'error',
                    'Invalid or expired password reset link.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Check Expiry
        |--------------------------------------------------------------------------
        */
        if (
            Carbon::parse($resetToken->expires_at)->isPast()
        ) {

            DB::table('student_password_reset_tokens')
                ->where('id', $resetToken->id)
                ->delete();

            return redirect()
                ->route('student.forgot-password')
                ->with(
                    'error',
                    'This password reset link has expired.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Check Student
        |--------------------------------------------------------------------------
        */
        $student = Student::whereRaw(
            'LOWER(email) = ?',
            [$email]
        )->first();


        if (!$student) {

            return redirect()
                ->route('student.forgot-password')
                ->with(
                    'error',
                    'Invalid password reset link.'
                );
        }


        return view(
            'forgot-password.student.reset-password',
            compact(
                'token',
                'email',
                'student'
            )
        );
    }
    
    public function studentUpdatePassword(Request $request)
    {
        $request->validate([
            'token' => [
                'required',
                'string',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'university_roll_no' => [
                'required',
                'string',
                'max:255',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);


        $email = strtolower(
            trim($request->email)
        );

        $universityRollNo = trim(
            $request->university_roll_no
        );


        /*
        |--------------------------------------------------------------------------
        | Find Reset Token
        |--------------------------------------------------------------------------
        */
        $resetToken = DB::table('student_password_reset_tokens')
            ->where('email', $email)
            ->where(
                'token',
                hash('sha256', $request->token)
            )
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Invalid Token
        |--------------------------------------------------------------------------
        */
        if (!$resetToken) {

            return redirect()
                ->route('student.forgot-password')
                ->with(
                    'error',
                    'Invalid or expired password reset link.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Check Token Expiry
        |--------------------------------------------------------------------------
        */
        if (
            Carbon::parse($resetToken->expires_at)->isPast()
        ) {

            DB::table('student_password_reset_tokens')
                ->where('id', $resetToken->id)
                ->delete();

            return redirect()
                ->route('student.forgot-password')
                ->with(
                    'error',
                    'This password reset link has expired.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Find Student Using BOTH Email + University Roll No
        |--------------------------------------------------------------------------
        */
        $student = Student::whereRaw(
            'LOWER(email) = ?',
            [$email]
        )
            ->where(
                'university_roll_no',
                $universityRollNo
            )
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Student Not Matched
        |--------------------------------------------------------------------------
        */
        if (!$student) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'University Roll No does not match the registered email address.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Update Password
        |--------------------------------------------------------------------------
        |
        | Student model already has setPasswordAttribute()
        | which automatically bcrypts the password.
        |
        */
        $student->password = $request->password;

        $student->save();


        /*
        |--------------------------------------------------------------------------
        | Delete Used Token
        |--------------------------------------------------------------------------
        */
        DB::table('student_password_reset_tokens')
            ->where('email', $email)
            ->delete();


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */
        return redirect()
            ->route('student.forgot-password')
            ->with(
                'success',
                'Your password has been reset successfully. You can now login with your new password.'
            );
    }
    
    
    // Offial Users Forget Password Area ------------------------------------------------------
    
    public function userForgotPassword()
    {
        return view('forgot-password.user.index');
    }
    
    public function userSendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);
    
        $email = strtolower(trim($request->email));
    
        // Check user exists
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
    
        if (!$user) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'This email address is not registered with any admin account.'
                );
        }
    
        /*
        |--------------------------------------------------------------------------
        | Laravel Default Password Broker
        |--------------------------------------------------------------------------
        */
    
        $status = Password::sendResetLink([
            'email' => $email,
        ]);
    
        if ($status === Password::ResetLinkSent) {
    
            return back()->with(
                'success',
                'Password reset link has been sent successfully to your registered email address.'
            );
        }
    
        return back()
            ->withInput()
            ->with(
                'error',
                __($status)
            );
    }
    
    public function userResetPassword(Request $request, string $token)
    {
        $email = strtolower(trim($request->query('email', '')));
    
        if (!$email) {
            return redirect()
                ->route('user.forgot-password')
                ->with(
                    'error',
                    'Invalid password reset link.'
                );
        }
    
        /*
        |--------------------------------------------------------------------------
        | Check User
        |--------------------------------------------------------------------------
        */
    
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
    
        if (!$user) {
            return redirect()
                ->route('user.forgot-password')
                ->with(
                    'error',
                    'Invalid password reset link.'
                );
        }
    
        return view(
            'forgot-password.user.reset-password',
            [
                'token' => $token,
                'email' => $email,
                'user' => $user,
            ]
        );
    }
    
    public function userUpdatePassword(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    
        $email = strtolower(trim($request->email));
    
        /*
        |--------------------------------------------------------------------------
        | Laravel Default Password Broker
        |--------------------------------------------------------------------------
        */
    
        $status = Password::reset(
            [
                'email' => $email,
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation,
                'token' => $request->token,
            ],
    
            function (User $user, string $password) {
    
                /*
                |--------------------------------------------------------------------------
                | User Model already has:
                | 'password' => 'hashed'
                |--------------------------------------------------------------------------
                */
    
                $user->password = $password;
    
                $user->setRememberToken(
                    Str::random(60)
                );
    
                $user->save();
    
                event(new PasswordReset($user));
            }
        );
    
        if ($status === Password::PasswordReset) {
    
            return redirect()
                ->route('user.forgot-password')
                ->with(
                    'success',
                    'Your password has been reset successfully. You can now login with your new password.'
                );
        }
    
        return back()
            ->withInput()
            ->withErrors([
                'email' => __($status),
            ]);
    }


}
