<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tillora Login</title>

    @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen bg-slate-100 flex items-center justify-center px-6">

<div class="w-full max-w-md">

    <!-- Logo -->

    <div class="text-center mb-8">

        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-[#0F172A] shadow-xl">

            <span class="text-4xl">🏪</span>

        </div>

        <h1 class="mt-6 text-4xl font-bold tracking-tight text-[#0F172A]">
            Tillora
        </h1>

        <p class="mt-2 text-slate-500">
            Retail Management & Point of Sale System
        </p>

    </div>


    <!-- Login Card -->

    <div class="rounded-3xl bg-white border border-slate-200 shadow-2xl p-8">

        <div class="mb-8">

            <h2 class="text-2xl font-bold text-slate-900">
                Welcome Back
            </h2>

            <p class="mt-1 text-slate-500">
                Sign in to continue to Tillora.
            </p>

        </div>


        <!-- Demo Access -->

        <div class="mb-8">

            <p class="mb-3 text-sm font-semibold text-slate-700">
                Try the Demo
            </p>

            <div class="grid grid-cols-2 gap-3">

                <!-- Cashier Demo -->

                <form method="POST" action="{{ route('demo.cashier') }}">
                    @csrf

                    <button
                        type="submit"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-left transition duration-200 hover:border-[#0F172A] hover:bg-slate-100">

                        <div class="flex items-center gap-3">

                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#0F172A] text-lg text-white">
                                🛒
                            </div>

                            <div>
                                <p class="font-semibold text-slate-900">
                                    Cashier
                                </p>

                                <p class="text-xs text-slate-500">
                                    Open POS
                                </p>
                            </div>

                        </div>

                    </button>

                </form>


                <!-- Admin Demo -->

                <form method="POST" action="{{ route('demo.admin') }}">
                    @csrf

                    <button
                        type="submit"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-left transition duration-200 hover:border-[#0F172A] hover:bg-slate-100">

                        <div class="flex items-center gap-3">

                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#0F172A] text-lg text-white">
                                ⚙️
                            </div>

                            <div>
                                <p class="font-semibold text-slate-900">
                                    Admin
                                </p>

                                <p class="text-xs text-slate-500">
                                    Open Dashboard
                                </p>
                            </div>

                        </div>

                    </button>

                </form>

            </div>

        </div>


        <!-- Divider -->

        <div class="relative mb-8">

            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-200"></div>
            </div>

            <div class="relative flex justify-center">
                <span class="bg-white px-4 text-sm text-slate-400">
                    or sign in normally
                </span>
            </div>

        </div>


        <!-- Normal Login -->

        <form method="POST" action="{{ route('pos.login.submit') }}" class="space-y-6">

            @csrf


            <!-- Email -->

            <div>

                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Email Address
                </label>

                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="you@example.com"
                    autocomplete="email"
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 placeholder-slate-400 outline-none transition focus:border-[#0F172A] focus:ring-4 focus:ring-slate-200">

                @error('email')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <!-- Password -->

            <div>

                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 placeholder-slate-400 outline-none transition focus:border-[#0F172A] focus:ring-4 focus:ring-slate-200">

                @error('password')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <!-- Sign In -->

            <button
                type="submit"
                class="w-full rounded-xl bg-[#0F172A] py-3 font-semibold text-white shadow-lg transition duration-200 hover:bg-slate-800 hover:shadow-xl">

                Sign In

            </button>

        </form>

    </div>


    <!-- Footer -->

    <p class="mt-8 text-center text-sm text-slate-500">
        © {{ date('Y') }} Tillora
        <span class="mx-1">•</span>
        Powered by @Abdi
    </p>

</div>

</body>

</html>