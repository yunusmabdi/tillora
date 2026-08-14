<!DOCTYPE html>
<html>

<head>
    <title>Tillora POS</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="min-h-screen bg-slate-100 text-[#0F172A]">

<div class="p-6">

    <!-- POS Navbar -->
    <nav class="bg-[#0F172A]
                rounded-2xl
                shadow-lg
                p-5
                mb-6
                flex
                items-center
                justify-between">

        <!-- Logo & User -->
        <div class="flex items-center gap-4">

            <div class="bg-white
                        w-14
                        h-14
                        rounded-xl
                        flex
                        items-center
                        justify-center
                        text-3xl">

                🏪

            </div>

            <div>

                <h1 class="text-3xl font-bold text-white">
                    Tillora POS
                </h1>

                <p class="text-[#F8F1E7]">
                    Welcome, {{ auth()->user()->name }}
                </p>

                <span class="inline-block
                             mt-1
                             bg-white
                             text-[#0F172A]
                             px-3
                             py-1
                             rounded-full
                             text-xs
                             font-bold">

                    {{ auth()->user()->getRoleNames()->first() }}

                </span>

            </div>

        </div>

        <!-- Navigation -->
        <div class="flex items-center gap-3">

            <a
                href="{{ route('pos.history') }}"
                class="inline-flex items-center gap-2
                       bg-white
                       text-[#0F172A]
                       px-5
                       py-3
                       rounded-xl
                       font-semibold
                       shadow
                       hover:bg-[#F8F1E7]
                       transition">

                🧾

                <span>Sales History</span>

            </a>

            <form method="POST" action="{{ route('pos.logout') }}">

                @csrf

                <button
                    class="inline-flex items-center gap-2
                           bg-red-600
                           text-white
                           px-5
                           py-3
                           rounded-xl
                           font-semibold
                           shadow
                           hover:bg-red-700
                           transition">

                    🚪

                    <span>Logout</span>

                </button>

            </form>

        </div>

    </nav>

    <!-- POS Workspace -->
    <div class="grid grid-cols-12 gap-6">

        <!-- Products -->
        <div class="col-span-8">

            <div class="bg-white
                        rounded-2xl
                        shadow
                        p-5
                        mb-5">

                <livewire:p-o-s.product-search />

            </div>

            <div class="bg-white
                        rounded-2xl
                        shadow
                        p-5">

                <livewire:p-o-s.product-grid />

            </div>

        </div>

        <!-- Shopping Cart -->
        <div class="col-span-4 space-y-6">

            {{-- Shopping Cart --}}
            <div class="bg-[#0F172A]
                        rounded-2xl
                        shadow-xl
                        p-5
                        text-white">

                <livewire:p-o-s.shopping-cart />

            </div>

            {{-- Held Sales --}}
            <div class="bg-white
                        rounded-2xl
                        shadow
                        p-5">

                <livewire:p-o-s.held-sales />

            </div>

        </div>
    </div>

</div>

@livewireScripts

</body>

</html>