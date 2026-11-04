<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Forgot Password - College Portal</title>

    <!-- Tailwind CSS -->
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

            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">

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
                        Forgot Password?
                    </h1>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Enter your registered college email address and
                        we'll send you a password reset link.
                    </p>

                </div>


                <!-- Messages -->
                <div class="px-7">

                    @if(session('success'))

                        <div
                            class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4"
                        >

                            <svg
                                class="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                />

                            </svg>

                            <p class="text-sm text-emerald-700">
                                {{ session('success') }}
                            </p>

                        </div>

                    @endif


                    @if(session('error'))

                        <div
                            class="mb-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4"
                        >

                            <svg
                                class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />

                            </svg>

                            <p class="text-sm text-red-700">
                                {{ session('error') }}
                            </p>

                        </div>

                    @endif


                    @if($errors->any())

                        <div
                            class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4"
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

                </div>


                <!-- Form -->
                <form
                    method="POST"
                    action="{{ route('college.forgot-password.send') }}"
                    class="px-7 pb-8"
                >

                    @csrf


                    <div>

                        <label
                            for="email"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Registered Email Address
                        </label>


                        <div class="relative">

                            <div
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4"
                            >

                                <svg
                                    class="h-5 w-5 text-slate-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                    />

                                </svg>

                            </div>


                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="college@example.com"
                                autocomplete="email"
                                required
                                class="block w-full rounded-xl border border-slate-300 bg-white py-3.5 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            >

                        </div>

                    </div>


                    <button
                        type="submit"
                        class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200 active:scale-[0.99]"
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
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                            />

                        </svg>

                        Send Password Reset Link

                    </button>


                    <!-- Login Link -->
                    <div class="mt-6 text-center">

                        <a
                            href="{{ route('institute.login') }}"
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

                            Back to Login

                        </a>

                    </div>

                </form>

            </div>


            <!-- Footer -->
            <p class="mt-6 text-center text-xs text-slate-400">
                College Portal
            </p>

        </div>

    </div>

</body>

</html>