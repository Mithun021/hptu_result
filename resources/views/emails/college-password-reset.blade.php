<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>Password Reset</title>

</head>


<body
    style="
        margin:0;
        padding:0;
        background:#f1f5f9;
        font-family:Arial,Helvetica,sans-serif;
    "
>

<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    style="padding:40px 15px;"
>

<tr>

<td align="center">


<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    style="
        max-width:560px;
        background:#ffffff;
        border-radius:12px;
        overflow:hidden;
        border:1px solid #e2e8f0;
    "
>


<!-- Header -->

<tr>

<td
    style="
        background:#4f46e5;
        padding:30px;
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
    College Portal
</h1>

</td>

</tr>


<!-- Content -->

<tr>

<td style="padding:35px 30px;">


<h2
    style="
        margin:0 0 15px;
        color:#0f172a;
        font-size:22px;
    "
>
    Reset Your Password
</h2>


<p
    style="
        margin:0 0 15px;
        color:#475569;
        font-size:15px;
        line-height:1.7;
    "
>
    Hello {{ $institute->name }},
</p>


<p
    style="
        margin:0 0 20px;
        color:#475569;
        font-size:15px;
        line-height:1.7;
    "
>
    We received a request to reset the password
    for your college account.
</p>


<p
    style="
        margin:0 0 25px;
        color:#475569;
        font-size:15px;
        line-height:1.7;
    "
>
    Click the button below to create a new password.
</p>


<!-- Button -->

<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
>

<tr>

<td align="center">

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

</td>

</tr>

</table>


<p
    style="
        margin:30px 0 0;
        color:#64748b;
        font-size:13px;
        line-height:1.7;
    "
>
    This password reset link will expire in
    <strong>30 minutes</strong>.
</p>


<p
    style="
        margin:15px 0 0;
        color:#64748b;
        font-size:13px;
        line-height:1.7;
    "
>
    If you did not request a password reset,
    you can safely ignore this email.
</p>


</td>

</tr>


<!-- Footer -->

<tr>

<td
    style="
        padding:20px 30px;
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

</td>

</tr>


</table>


</td>

</tr>

</table>

</body>

</html>
