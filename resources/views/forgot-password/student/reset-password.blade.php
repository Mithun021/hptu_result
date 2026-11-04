<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Reset Password - Student Portal</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>


<body class="min-h-screen bg-slate-100">

    <div class="min-h-screen flex items-center justify-center px-4 py-8">

        <div class="w-full max-w-md">


            <!-- Card -->

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">


                <!-- Header -->

                <div class="px-7 pt-8 pb-6 text-center">

                    <div
                        class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50"
                    >

                        <svg
                            class="h-8 w-8 text-indigo-600"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            viewBox="0 0 24 24"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-7a2 2 0 002-2H6a2 2 0 00-2 2v7a2 2 0 002 2zm10-11V7a4 4 0 00-8 0v3h8z"
                            />

                        </svg>

                    </div>


                    <h1 class="text-2xl font-bold text-slate-900">
                        Create New Password
                    </h1>


                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Enter your university roll number and
                        create a new password.
                    </p>

                </div>


                <!-- Email -->

                <div class="mx-7 mb-6 rounded-xl bg-slate-50 px-4 py-3">

                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                        Registered Email
                    </p>

                    <p class="mt-1 truncate text-sm font-semibold text-slate-700">
                        {{ $email }}
                    </p>

                </div>


                <!-- Errors -->

                @if($errors->any())

                    <div
                        class="mx-7 mb-6 rounded-xl border border-red-200 bg-red-50 p-4"
                    >

                        <ul class="space-y-1 text-sm text-red-700">

                            @foreach($errors->all() as $error)

                                <li>
                                    • {{ $error }}
                                </li>

                            @endforeach

                        </ul>

                    </div>

                @endif


                <!-- Form -->

                <form
                    method="POST"
                    action="{{ route('student.reset-password.update') }}"
                    class="px-7 pb-8"
                >

                    @csrf


                    <input
                        type="hidden"
                        name="token"
                        value="{{ $token }}"
                    >


                    <input
                        type="hidden"
                        name="email"
                        value="{{ $email }}"
                    >


                    <!-- University Roll No -->

                    <div>

                        <label
                            for="university_roll_no"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            University Roll No
                        </label>


                        <input
                            type="text"
                            id="university_roll_no"
                            name="university_roll_no"
                            value="{{ old('university_roll_no') }}"
                            placeholder="Enter university roll no"
                            autocomplete="off"
                            required
                            class="block w-full rounded-xl border border-slate-300 px-4 py-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >

                    </div>


                    <!-- New Password -->

                    <div class="mt-5">

                        <label
                            for="password"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            New Password
                        </label>


                        <div class="relative">

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Enter new password"
                                autocomplete="new-password"
                                minlength="8"
                                required
                                class="block w-full rounded-xl border border-slate-300 px-4 py-3.5 pr-12 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            >


                            <button
                                type="button"
                                onclick="togglePassword('password', 'passwordEye')"
                                class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 hover:text-slate-600"
                            >

                                <svg
                                    id="passwordEye"
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    viewBox="0 0 24 24"
                                >

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    />

                                </svg>

                            </button>

                        </div>

                    </div>


                    <!-- Confirm Password -->

                    <div class="mt-5">

                        <label
                            for="password_confirmation"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Confirm New Password
                        </label>


                        <div class="relative">

                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="Confirm new password"
                                autocomplete="new-password"
                                minlength="8"
                                required
                                class="block w-full rounded-xl border border-slate-300 px-4 py-3.5 pr-12 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            >


                            <button
                                type="button"
                                onclick="togglePassword('password_confirmation', 'confirmPasswordEye')"
                                class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 hover:text-slate-600"
                            >

                                <svg
                                    id="confirmPasswordEye"
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    viewBox="0 0 24 24"
                                >

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268 2.943-9.542 7-1.274 4.057-5.065 7-9.542-7z"
                                    />

                                </svg>

                            </button>

                        </div>

                    </div>


                    <!-- Submit -->

                    <button
                        type="submit"
                        class="mt-7 flex w-full items-center justify-center rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200"
                    >

                        Reset Password

                    </button>


                    <div class="mt-6 text-center">

                        <a
                            href="{{ route('student.forgot-password') }}"
                            class="text-sm font-semibold text-indigo-600 hover:text-indigo-700"
                        >
                            ← Back to Forgot Password
                        </a>

                    </div>

                </form>

            </div>


            <p class="mt-6 text-center text-xs text-slate-400">
                Student Portal
            </p>

        </div>

    </div>


    <script>

        function togglePassword(inputId, iconId)
        {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === 'password') {

                input.type = 'text';

                icon.innerHTML = `
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 012.26-3.934M6.228 6.228A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.05 10.05 0 01-4.132 5.411M6.228 6.228L3 3m3.228 3.228l3.072 3.072m0 0a3 3 0 01-4.243 4.243M9.3 9.3l5.4 5.4M14.7 14.7L21 21"
                    />
                `;

            } else {

                input.type = 'password';

                icon.innerHTML = `
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                    />

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268-2.943 9.542-7-1.274-4.057-5.065-7-9.542-7-4.477 0-8.268 2.943-9.542 7z"
                    />
                `;
            }
        }

    </script>

</body>

</html>