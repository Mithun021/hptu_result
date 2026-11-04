<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Reset Password - College Portal</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui']
                    }
                }
            }
        }
    </script>

</head>


<body class="min-h-screen bg-slate-100 font-sans">

    <div class="min-h-screen flex items-center justify-center px-4 py-8">

        <div class="w-full max-w-md">

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
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-7a2 2 0 00-2-2H6a2 2 0 00-2 2v7a2 2 0 002 2zm10-11V7a4 4 0 00-8 0v3h8z"
                            />

                        </svg>

                    </div>


                    <h1 class="text-2xl font-bold text-slate-900">
                        Create New Password
                    </h1>


                    <p class="mt-2 text-sm leading-6 text-slate-500">

                        Set a new password for your college account.

                    </p>

                </div>


                <!-- Institute Email -->
                <div class="mx-7 mb-6 rounded-xl bg-slate-50 px-4 py-3">

                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                        Account
                    </p>

                    <p class="mt-1 truncate text-sm font-semibold text-slate-700">
                        {{ $email }}
                    </p>

                </div>


                <!-- Validation Errors -->
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
                    action="{{ route('college.reset-password.update') }}"
                    class="px-7 pb-8"
                >

                    @csrf


                    <!-- Hidden Token -->
                    <input
                        type="hidden"
                        name="token"
                        value="{{ $token }}"
                    >


                    <!-- Hidden Email -->
                    <input
                        type="hidden"
                        name="email"
                        value="{{ $email }}"
                    >


                    <!-- New Password -->
                    <div>

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
                                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3.5 pr-12 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
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


                        <p class="mt-2 text-xs text-slate-400">
                            Password must be at least 8 characters.
                        </p>

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
                                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3.5 pr-12 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
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
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    />

                                </svg>

                            </button>

                        </div>

                    </div>


                    <!-- Submit -->
                    <button
                        type="submit"
                        class="mt-7 flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200 active:scale-[0.99]"
                    >

                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.291 9 11.622C17.176 19.291 21 14.591 21 9c0-1.412-.243-2.766-.69-4.016z"
                            />

                        </svg>

                        Reset Password

                    </button>


                    <!-- Back -->
                    <div class="mt-6 text-center">

                        <a
                            href="{{ route('college.forgot-password') }}"
                            class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-700"
                        >

                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"
                                />

                            </svg>

                            Back to Forgot Password

                        </a>

                    </div>

                </form>

            </div>


            <p class="mt-6 text-center text-xs text-slate-400">
                College Portal
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
                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 012.26-3.934M6.228 6.228A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.05 10.05 0 01-4.132 5.411M6.228 6.228L3 3m3.228 3.228l3.072 3.072m0 0a3 3 0 104.243 4.243M9.3 9.3l5.4 5.4M14.7 14.7L21 21"
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
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                    />
                `;
            }
        }

    </script>

</body>

</html>