<x-filament-panels::page>

    @php
        $order = $this->order;
        $status = $order->fulfillment_status;

        $mapQuery = trim(
            ($order->delivery_address ?? '') . ', ' .
            ($order->deliveryZone?->name ?? '') . ', Nairobi, Kenya'
        );
    @endphp


    {{-- HEADER --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

        <div>

            <a
                href="{{ \App\Filament\Pages\RiderNewOrders::getUrl() }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary-600"
            >
                <x-filament::icon
                    icon="heroicon-o-arrow-left"
                    class="h-4 w-4"
                />

                Back to New Orders
            </a>

            <h1 class="mt-3 text-2xl font-bold text-gray-900">
                {{ $order->invoice_number }}
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Order Tracking
            </p>

        </div>

        <span class="w-fit rounded-full bg-primary-50 px-4 py-2 text-sm font-semibold capitalize text-primary-700">
            {{ str_replace('_', ' ', $status) }}
        </span>

    </div>


    {{-- CUSTOMER --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 lg:col-span-2">

            <h2 class="text-lg font-bold text-gray-900">
                Customer
            </h2>

            <div class="mt-5 flex flex-col justify-between gap-5 sm:flex-row sm:items-center">

                <div class="flex items-center gap-4">

                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-50">

                        <x-filament::icon
                            icon="heroicon-o-user"
                            class="h-6 w-6 text-primary-600"
                        />

                    </div>

                    <div>

                        <p class="font-semibold text-gray-900">
                            {{ $order->customer?->name ?? 'Customer' }}
                        </p>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ $order->customer?->phone ?? 'No phone number' }}
                        </p>

                    </div>

                </div>


                <div class="flex gap-2">

                    @if($order->customer?->phone)

                        <a
                            href="tel:{{ $order->customer->phone }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-primary-50 px-4 py-2.5 text-sm font-semibold text-primary-700 hover:bg-primary-100"
                        >
                            <x-filament::icon
                                icon="heroicon-o-phone"
                                class="h-5 w-5"
                            />

                            Call
                        </a>

                        <a
                            href="sms:{{ $order->customer->phone }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-200"
                        >
                            <x-filament::icon
                                icon="heroicon-o-chat-bubble-left"
                                class="h-5 w-5"
                            />

                            Message
                        </a>

                    @endif

                </div>

            </div>

        </div>


        {{-- PAYMENT --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5">

            <h2 class="text-lg font-bold text-gray-900">
                Payment
            </h2>

            <p class="mt-4 text-2xl font-bold text-gray-900">
                KES {{ number_format($order->total_amount, 2) }}
            </p>

            <div class="mt-4 space-y-2 text-sm">

                <div class="flex justify-between">
                    <span class="text-gray-500">Advance</span>
                    <span class="font-semibold">
                        KES {{ number_format($order->advance_amount ?? 0, 2) }}
                    </span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Balance</span>
                    <span class="font-semibold">
                        KES {{ number_format($this->getBalance(), 2) }}
                    </span>
                </div>

            </div>

            <div class="mt-4">

                @if($order->payment_status === 'paid')

                    <span class="inline-flex rounded-full bg-success-50 px-3 py-1.5 text-xs font-semibold text-success-700">
                        Fully Paid
                    </span>

                @else

                    <span class="inline-flex rounded-full bg-warning-50 px-3 py-1.5 text-xs font-semibold text-warning-700">
                        Payment Pending
                    </span>

                @endif

            </div>

        </div>

    </div>


    {{-- DELIVERY LOCATION --}}
    <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5">

        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

            <div>

                <h2 class="text-lg font-bold text-gray-900">
                    Delivery Location
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $order->deliveryZone?->name }}
                </p>

                <p class="mt-2 text-sm text-gray-700">
                    {{ $order->delivery_address ?? 'No address provided.' }}
                </p>

            </div>


            @if($order->delivery_address)

                <a
                    href="https://www.google.com/maps/search/?api=1&query={{ urlencode($mapQuery) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-3 text-sm font-semibold text-white hover:bg-primary-500"
                >
                    <x-filament::icon
                        icon="heroicon-o-map"
                        class="h-5 w-5"
                    />

                    Navigate
                </a>

            @endif

        </div>

    </div>


    {{-- TRACKING --}}
    <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5">

        <h2 class="text-lg font-bold text-gray-900">
            Delivery Progress
        </h2>

        <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-4">

            @php
                $steps = [
                    'accepted' => 'Accepted',
                    'picked_up' => 'Picked Up',
                    'out_for_delivery' => 'Out for Delivery',
                    'delivered' => 'Delivered',
                ];

                $numbers = [
                    'accepted' => 1,
                    'picked_up' => 2,
                    'out_for_delivery' => 3,
                    'delivered' => 4,
                ];

                $current = $numbers[$status] ?? 0;
            @endphp

            @foreach($steps as $key => $label)

                <div class="rounded-xl border p-4 text-center
                    {{ ($numbers[$key] <= $current)
                        ? 'border-primary-200 bg-primary-50'
                        : 'border-gray-200 bg-gray-50'
                    }}"
                >

                    <div class="text-sm font-bold">
                        {{ $numbers[$key] }}
                    </div>

                    <div class="mt-1 text-xs font-medium capitalize">
                        {{ $label }}
                    </div>

                </div>

            @endforeach

        </div>

    </div>


    {{-- ORDER ITEMS --}}
    <div class="mt-6 rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5">

        <div class="border-b border-gray-100 p-6">

            <h2 class="text-lg font-bold text-gray-900">
                Order Items
            </h2>

        </div>

        <div class="divide-y divide-gray-100">

            @forelse($order->saleItems ?? [] as $item)

                <div class="flex justify-between gap-4 p-5">

                    <div>

                        <p class="font-semibold text-gray-900">
                            {{ $item->product?->name ?? 'Product' }}
                        </p>

                        <p class="mt-1 text-sm text-gray-500">
                            Quantity: {{ $item->quantity }}
                        </p>

                    </div>

                    <p class="font-semibold text-gray-900">
                        KES {{ number_format(
                            $item->total ?? ($item->quantity * $item->unit_price),
                            2
                        ) }}
                    </p>

                </div>

            @empty

                <div class="p-6 text-center text-sm text-gray-500">
                    No items found.
                </div>

            @endforelse

        </div>

    </div>


    {{-- ACTION --}}
    <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5">

        <h2 class="text-lg font-bold text-gray-900">
            Delivery Action
        </h2>

        <div class="mt-5">

            @if($status === 'accepted')

                <button
                    type="button"
                    wire:click="startRide"
                    class="w-full rounded-xl bg-primary-600 px-5 py-3 text-sm font-semibold text-white hover:bg-primary-500"
                >
                    Start Ride
                </button>


            @elseif($status === 'picked_up')

                <button
                    type="button"
                    wire:click="startDelivery"
                    class="w-full rounded-xl bg-primary-600 px-5 py-3 text-sm font-semibold text-white hover:bg-primary-500"
                >
                    Start Delivery
                </button>


            @elseif($status === 'out_for_delivery')

                @if($order->payment_status === 'paid')

                    <button
                        type="button"
                        wire:click="markDelivered"
                        wire:confirm="Confirm that this order has been delivered?"
                        class="w-full rounded-xl bg-success-600 px-5 py-3 text-sm font-semibold text-white hover:bg-success-500"
                    >
                        Mark as Delivered
                    </button>

                @else

                    <div class="rounded-xl bg-warning-50 p-4 text-sm text-warning-800">

                        <p class="font-semibold">
                            Payment Pending
                        </p>

                        <p class="mt-1">
                            Waiting for the customer to complete payment.
                        </p>

                        <p class="mt-2 font-bold">
                            Balance:
                            KES {{ number_format($this->getBalance(), 2) }}
                        </p>

                    </div>

                @endif


            @elseif($status === 'delivered')

                <div class="rounded-xl bg-success-50 p-5 text-center">

                    <x-filament::icon
                        icon="heroicon-o-check-circle"
                        class="mx-auto h-10 w-10 text-success-600"
                    />

                    <p class="mt-2 font-semibold text-success-700">
                        Order Completed
                    </p>

                </div>

            @endif

        </div>

    </div>

</x-filament-panels::page>