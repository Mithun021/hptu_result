<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Forgot Password - Admin Portal</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center px-4">

    <div class="w-full max-w-md">

        <div class="bg-white rounded-2xl shadow-xl p-8">

            {{-- Logo / Heading --}}
            <div class="text-center mb-8">

                <div
                    class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-100">

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-8 w-8 text-blue-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 7a3 3 0 11-6 0 3 3 0 016 0z" />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 20a7 7 0 0114 0" />

                    </svg>

                </div>

                <h1 class="text-2xl font-bold text-gray-800">
                    Forgot Password?
                </h1>

                <p class="mt-2 text-sm text-gray-500">
                    Enter your registered email address and
                    we will send you a password reset link.
                </p>

            </div>


            {{-- Success --}}
            @if(session('success'))

                <div class="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3">

                    <p class="text-sm text-green-700">
                        {{ session('success') }}
                    </p>

                </div>

            @endif


            {{-- Error --}}
            @if(session('error'))

                <div class="mb-5 rounded-lg bg-red-50 border border-red-200 px-4 py-3">

                    <p class="text-sm text-red-700">
                        {{ session('error') }}
                    </p>

                </div>

            @endif


            {{-- Validation Errors --}}
            @if($errors->any())

                <div class="mb-5 rounded-lg bg-red-50 border border-red-200 px-4 py-3">

                    <ul class="list-disc list-inside text-sm text-red-700">

                        @foreach($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- Form --}}
            <form
                method="POST"
                action="{{ route('user.forgot-password.send') }}">

                @csrf

                <div class="mb-5">

                    <label
                        for="email"
                        class="block text-sm font-medium text-gray-700 mb-2">

                        Registered Email Address

                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="Enter your email address"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3
                               text-gray-800 outline-none
                               focus:border-blue-500 focus:ring-2 focus:ring-blue-100">

                </div>


                <button
                    type="submit"
                    class="w-full rounded-lg bg-blue-600 px-4 py-3
                           font-semibold text-white
                           hover:bg-blue-700
                           focus:outline-none
                           focus:ring-2 focus:ring-blue-300
                           transition">

                    Send Password Reset Link

                </button>

            </form>


            {{-- Login --}}
            <div class="mt-6 text-center">

                <a
                    href="{{ route('login') }}"
                    class="text-sm font-medium text-blue-600 hover:text-blue-800">

                    ← Back to Login

                </a>

            </div>

        </div>


        <p class="mt-6 text-center text-xs text-gray-400">

            Admin Portal

        </p>

    </div>

</body>

</html>