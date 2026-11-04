<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Reset Password - Admin Portal</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center px-4">

    <div class="w-full max-w-md">

        <div class="bg-white rounded-2xl shadow-xl p-8">


            {{-- Header --}}
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
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />

                    </svg>

                </div>


                <h1 class="text-2xl font-bold text-gray-800">

                    Reset Password

                </h1>


                <p class="mt-2 text-sm text-gray-500">

                    Create a new password for your admin account.

                </p>

            </div>


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


            {{-- Reset Form --}}
            <form
                method="POST"
                action="{{ route('user.reset-password.update') }}">

                @csrf


                {{-- Token --}}
                <input
                    type="hidden"
                    name="token"
                    value="{{ $token }}">


                {{-- Email --}}
                <div class="mb-5">

                    <label
                        for="email"
                        class="block text-sm font-medium text-gray-700 mb-2">

                        Email Address

                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ $email }}"
                        readonly
                        class="w-full rounded-lg border border-gray-300
                               bg-gray-100 px-4 py-3
                               text-gray-600 outline-none">

                </div>


                {{-- Password --}}
                <div class="mb-5">

                    <label
                        for="password"
                        class="block text-sm font-medium text-gray-700 mb-2">

                        New Password

                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        placeholder="Enter new password"
                        class="w-full rounded-lg border border-gray-300
                               px-4 py-3 text-gray-800
                               outline-none
                               focus:border-blue-500
                               focus:ring-2 focus:ring-blue-100">

                    <p class="mt-1 text-xs text-gray-400">
                        Minimum 8 characters.
                    </p>

                </div>


                {{-- Confirm Password --}}
                <div class="mb-6">

                    <label
                        for="password_confirmation"
                        class="block text-sm font-medium text-gray-700 mb-2">

                        Confirm New Password

                    </label>

                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        placeholder="Confirm new password"
                        class="w-full rounded-lg border border-gray-300
                               px-4 py-3 text-gray-800
                               outline-none
                               focus:border-blue-500
                               focus:ring-2 focus:ring-blue-100">

                </div>


                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full rounded-lg bg-blue-600 px-4 py-3
                           font-semibold text-white
                           hover:bg-blue-700
                           focus:outline-none
                           focus:ring-2 focus:ring-blue-300
                           transition">

                    Reset Password

                </button>

            </form>


            {{-- Back Login --}}
            <div class="mt-6 text-center">

                <a
                    href="{{ url('/login') }}"
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