<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Student Password Reset</title>

</head>


<body
    style="
        margin:0;
        padding:30px;
        background:#f1f5f9;
        font-family:Arial,Helvetica,sans-serif;
    "
>

<div
    style="
        max-width:600px;
        margin:auto;
        background:#ffffff;
        border-radius:12px;
        border:1px solid #e2e8f0;
        overflow:hidden;
    "
>


    <!-- Header -->

    <div
        style="
            background:#4f46e5;
            padding:28px;
            text-align:center;
        "
    >

        <h1
            style="
                margin:0;
                color:#ffffff;
                font-size:24px;
            "
        >
            Student Portal
        </h1>

    </div>


    <!-- Body -->

    <div style="padding:35px 30px;">

        <h2
            style="
                margin:0 0 18px;
                color:#111827;
            "
        >
            Reset Your Password
        </h2>


        <p
            style="
                color:#374151;
                font-size:15px;
                line-height:1.7;
            "
        >
            Hello {{ $student->student_name }},
        </p>


        <p
            style="
                color:#374151;
                font-size:15px;
                line-height:1.7;
            "
        >
            We received a request to reset the password
            for your student account.
        </p>


        <p
            style="
                color:#374151;
                font-size:15px;
                line-height:1.7;
            "
        >
            Click the button below to create your new password.
        </p>


        <div style="text-align:center; margin:30px 0;">

            <a
                href="{{ $resetUrl }}"
                style="
                    display:inline-block;
                    padding:14px 28px;
                    background:#4f46e5;
                    color:#ffffff;
                    text-decoration:none;
                    border-radius:8px;
                    font-size:15px;
                    font-weight:bold;
                "
            >
                Reset Password
            </a>

        </div>


        <p
            style="
                color:#64748b;
                font-size:13px;
                line-height:1.6;
            "
        >
            This password reset link will expire in
            <strong>30 minutes</strong>.
        </p>


        <p
            style="
                color:#64748b;
                font-size:13px;
                line-height:1.6;
            "
        >
            If you did not request this password reset,
            you can safely ignore this email.
        </p>

    </div>


    <!-- Footer -->

    <div
        style="
            padding:20px;
            background:#f8fafc;
            text-align:center;
        "
    >

        <p
            style="
                margin:0;
                color:#94a3b8;
                font-size:12px;
            "
        >
            This is an automated email. Please do not reply.
        </p>

    </div>


</div>

</body>

</html>