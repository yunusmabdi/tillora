<x-filament-panels::page>

    @php
        $orders = $this->getNewOrders();
    @endphp

    {{-- HEADER --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                New Orders
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Orders waiting for your acceptance.
            </p>
        </div>

        <div class="inline-flex w-fit items-center gap-2 rounded-xl bg-warning-50 px-4 py-2 text-sm font-semibold text-warning-700">
            <x-filament::icon
                icon="heroicon-o-inbox-arrow-down"
                class="h-5 w-5"
            />

            {{ $orders->count() }} Waiting
        </div>

    </div>


    {{-- ORDERS --}}
    <div class="mt-6 space-y-5">

        @forelse($orders as $order)

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5">

                {{-- ORDER HEADER --}}
                <div class="flex flex-col justify-between gap-4 border-b border-gray-100 p-6 sm:flex-row sm:items-center">

                    <div>

                        <div class="flex items-center gap-3">

                            <h2 class="text-lg font-bold text-gray-900">
                                {{ $order->invoice_number }}
                            </h2>

                            <span class="rounded-full bg-warning-50 px-3 py-1 text-xs font-semibold capitalize text-warning-700">
                                New Assignment
                            </span>

                        </div>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ $order->sale_date?->format('d M Y, h:i A') }}
                        </p>

                    </div>

                    <div class="text-left sm:text-right">

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                            Order Total
                        </p>

                        <p class="mt-1 text-xl font-bold text-gray-900">
                            KES {{ number_format($order->total_amount, 2) }}
                        </p>

                    </div>

                </div>


                {{-- ORDER INFORMATION --}}
                <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-3">

                    {{-- CUSTOMER --}}
                    <div>

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                            Customer
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $order->customer?->name ?? 'Customer' }}
                        </p>

                        @if($order->customer?->phone)

                            <p class="mt-1 text-sm text-gray-500">
                                {{ $order->customer->phone }}
                            </p>

                        @endif

                    </div>


                    {{-- LOCATION --}}
                    <div>

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                            Delivery Location
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $order->deliveryZone?->name ?? 'Delivery Zone' }}
                        </p>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ $order->delivery_address ?? 'No address provided' }}
                        </p>

                    </div>


                    {{-- PAYMENT --}}
                    <div>

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                            Payment
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            KES {{ number_format($order->total_amount, 2) }}
                        </p>

                        <p class="mt-1 text-sm">

                            @if($order->payment_status === 'paid')

                                <span class="font-semibold text-success-600">
                                    Fully Paid
                                </span>

                            @else

                                <span class="font-semibold text-warning-600">
                                    Payment Pending
                                </span>

                            @endif

                        </p>

                    </div>

                </div>


                {{-- ITEMS --}}
                <div class="border-t border-gray-100 px-6 py-4">

                    <p class="mb-3 text-xs font-medium uppercase tracking-wide text-gray-400">
                        Order Items
                    </p>

                    <div class="flex flex-wrap gap-2">

                        @foreach($order->saleItems ?? [] as $item)

                            <span class="rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-700">

                                {{ $item->product?->name ?? 'Product' }}

                                <span class="font-semibold">
                                    × {{ $item->quantity }}
                                </span>

                            </span>

                        @endforeach

                    </div>

                </div>


                {{-- ACTIONS --}}
                <div class="flex flex-col gap-3 border-t border-gray-100 bg-gray-50 p-6 sm:flex-row">

                    <a
                        href="{{ \App\Filament\Pages\RiderOrderTracking::getUrl([
                            'record' => $order->id,
                        ]) }}"
                        class="flex-1 rounded-xl border border-gray-300 bg-white px-5 py-3 text-center text-sm font-semibold text-gray-700 transition hover:bg-gray-100"
                    >
                        View Order
                    </a>

                    <button
                        type="button"
                        wire:click="acceptOrder({{ $order->id }})"
                        wire:confirm="Accept this delivery?"
                        class="flex-1 rounded-xl bg-primary-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary-500"
                    >
                        Accept Delivery
                    </button>

                    <button
                        type="button"
                        wire:click="declineOrder({{ $order->id }})"
                        wire:confirm="Are you sure you want to decline this delivery?"
                        class="flex-1 rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-white"
                    >
                        Decline
                    </button>

                </div>

            </div>

        @empty

            <div class="rounded-2xl bg-white p-12 text-center shadow-sm ring-1 ring-gray-950/5">

                <x-filament::icon
                    icon="heroicon-o-inbox"
                    class="mx-auto h-12 w-12 text-gray-300"
                />

                <h2 class="mt-4 text-lg font-semibold text-gray-900">
                    No New Orders
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    New delivery assignments will appear here automatically.
                </p>

            </div>

        @endforelse

    </div>

</x-filament-panels::page>