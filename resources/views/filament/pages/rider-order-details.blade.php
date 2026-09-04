<x-filament-panels::page>

    @php
        $status = $this->order->fulfillment_status;

        $steps = [
            'accepted' => 'Accepted',
            'picked_up' => 'Picked Up',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
        ];

        $stepNumbers = [
            'accepted' => 1,
            'picked_up' => 2,
            'out_for_delivery' => 3,
            'delivered' => 4,
        ];

        $currentStep = $stepNumbers[$status] ?? 0;

        $mapQuery = trim(
            ($order->delivery_address ?? '') . ', ' .
            ($order->deliveryZone?->name ?? '') . ', Nairobi, Kenya'
        );
    @endphp


    {{-- BACK TO DASHBOARD --}}
    <div>
        <a
            href="{{ \App\Filament\Pages\RiderDashboard::getUrl() }}"
            class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition hover:text-primary-600"
        >
            <x-filament::icon
                icon="heroicon-o-arrow-left"
                class="h-4 w-4"
            />

            Back to Dashboard
        </a>
    </div>


    {{-- ORDER HEADER --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5">

        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">

            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-primary-600">
                    Delivery Order
                </p>

                <h1 class="mt-1 text-2xl font-bold text-gray-900">
                    {{ $order->invoice_number }}
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $order->sale_date?->format('d M Y, h:i A') }}
                </p>
            </div>

            <span class="inline-flex w-fit rounded-full bg-primary-50 px-4 py-2 text-sm font-semibold capitalize text-primary-700">
                {{ $this->getStatusLabel() }}
            </span>

        </div>

    </div>


    {{-- DELIVERY PROGRESS --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5">

        <h2 class="text-lg font-bold text-gray-900">
            Delivery Progress
        </h2>

        <div class="mt-6 flex items-center">

            @foreach($steps as $step => $label)

                @php
                    $number = $stepNumbers[$step];
                    $completed = $number <= $currentStep;
                @endphp

                <div class="flex flex-1 items-center">

                    <div class="flex flex-col items-center">

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold
                            {{ $completed
                                ? 'bg-primary-600 text-white'
                                : 'bg-gray-100 text-gray-400'
                            }}"
                        >

                            @if($number < $currentStep)

                                <x-filament::icon
                                    icon="heroicon-o-check"
                                    class="h-5 w-5"
                                />

                            @else

                                {{ $number }}

                            @endif

                        </div>

                        <span class="mt-2 hidden text-xs font-medium text-gray-500 sm:block">
                            {{ $label }}
                        </span>

                    </div>

                    @if(!$loop->last)

                        <div
                            class="mx-3 h-1 flex-1 rounded-full
                            {{ $number < $currentStep
                                ? 'bg-primary-600'
                                : 'bg-gray-100'
                            }}"
                        ></div>

                    @endif

                </div>

            @endforeach

        </div>

    </div>


    {{-- MAIN CONTENT --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">


        {{-- LEFT SIDE --}}
        <div class="space-y-6 lg:col-span-2">


            {{-- CUSTOMER INFORMATION --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5">

                <div class="flex items-center justify-between">

                    <h2 class="text-lg font-bold text-gray-900">
                        Customer Information
                    </h2>

                    @if($order->customer?->phone)

                        <a
                            href="tel:{{ $order->customer->phone }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-primary-50 px-3 py-2 text-sm font-semibold text-primary-700 transition hover:bg-primary-100"
                        >
                            <x-filament::icon
                                icon="heroicon-o-phone"
                                class="h-4 w-4"
                            />

                            Call Customer
                        </a>

                    @endif

                </div>


                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">

                    <div>

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                            Customer
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $order->customer?->name ?? 'Customer' }}
                        </p>

                    </div>


                    @if($order->customer?->phone)

                        <div>

                            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                                Phone
                            </p>

                            <p class="mt-1 font-semibold text-gray-900">
                                {{ $order->customer->phone }}
                            </p>

                        </div>

                    @endif

                </div>

            </div>


            {{-- DELIVERY LOCATION --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5">

                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">

                    <h2 class="text-lg font-bold text-gray-900">
                        Delivery Location
                    </h2>

                    @if($order->delivery_address)

                        <a
                            href="https://www.google.com/maps/search/?api=1&query={{ urlencode($mapQuery) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex w-fit items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-500"
                        >
                            <x-filament::icon
                                icon="heroicon-o-map"
                                class="h-5 w-5"
                            />

                            Navigate to Customer
                        </a>

                    @endif

                </div>


                <div class="mt-5 rounded-xl bg-gray-50 p-5">

                    <div class="flex gap-4">

                        <div class="shrink-0 rounded-xl bg-primary-50 p-3">

                            <x-filament::icon
                                icon="heroicon-o-map-pin"
                                class="h-6 w-6 text-primary-600"
                            />

                        </div>

                        <div class="min-w-0">

                            <p class="font-semibold text-gray-900">
                                {{ $order->deliveryZone?->name ?? 'Delivery Zone' }}
                            </p>

                            <p class="mt-1 text-sm leading-6 text-gray-600">
                                {{ $order->delivery_address ?? 'No delivery address provided.' }}
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ORDER ITEMS --}}
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5">

                <div class="border-b border-gray-100 p-6">

                    <div class="flex items-center justify-between">

                        <div>

                            <h2 class="text-lg font-bold text-gray-900">
                                Order Items
                            </h2>

                            <p class="mt-1 text-sm text-gray-500">
                                Items included in this delivery
                            </p>

                        </div>

                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                            {{ $order->saleItems?->count() ?? 0 }} items
                        </span>

                    </div>

                </div>


                <div class="divide-y divide-gray-100">

                    @forelse($order->saleItems ?? [] as $item)

                        <div class="flex items-center justify-between gap-4 p-5">

                            <div class="min-w-0">

                                <p class="font-semibold text-gray-900">
                                    {{ $item->product?->name ?? 'Product' }}
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    Quantity: {{ $item->quantity }}
                                </p>

                            </div>


                            <div class="shrink-0 text-right">

                                <p class="font-semibold text-gray-900">
                                    KES
                                    {{ number_format(
                                        $item->total ?? ($item->quantity * $item->unit_price),
                                        2
                                    ) }}
                                </p>

                            </div>

                        </div>

                    @empty

                        <div class="p-6 text-center text-sm text-gray-500">
                            No order items found.
                        </div>

                    @endforelse

                </div>

            </div>

        </div>


        {{-- RIGHT SIDEBAR --}}
        <div class="space-y-6">


            {{-- PAYMENT --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5">

                <h2 class="text-lg font-bold text-gray-900">
                    Payment
                </h2>

                <div class="mt-5 space-y-4">

                    <div class="flex justify-between text-sm">

                        <span class="text-gray-500">
                            Order Total
                        </span>

                        <span class="font-semibold text-gray-900">
                            KES {{ number_format($order->total_amount, 2) }}
                        </span>

                    </div>


                    <div class="flex justify-between text-sm">

                        <span class="text-gray-500">
                            Advance Paid
                        </span>

                        <span class="font-semibold text-gray-900">
                            KES {{ number_format($order->advance_amount ?? 0, 2) }}
                        </span>

                    </div>


                    <div class="border-t pt-4">

                        <div class="flex justify-between">

                            <span class="font-medium text-gray-700">
                                Balance
                            </span>

                            <span
                                class="font-bold
                                {{ $this->getBalance() > 0
                                    ? 'text-warning-600'
                                    : 'text-success-600'
                                }}"
                            >
                                KES {{ number_format($this->getBalance(), 2) }}
                            </span>

                        </div>

                    </div>


                    @if($order->payment_status === 'paid')

                        <div class="flex items-center gap-2 rounded-xl bg-success-50 p-3 text-sm font-medium text-success-700">

                            <x-filament::icon
                                icon="heroicon-o-check-circle"
                                class="h-5 w-5"
                            />

                            Fully Paid

                        </div>

                    @else

                        <div class="rounded-xl bg-warning-50 p-3 text-sm font-medium text-warning-700">

                            <p class="font-semibold">
                                Payment Pending
                            </p>

                            <p class="mt-1 text-xs">
                                Outstanding balance:
                                KES {{ number_format($this->getBalance(), 2) }}
                            </p>

                        </div>

                    @endif

                </div>

            </div>


            {{-- DELIVERY ACTION --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5">

                <h2 class="text-lg font-bold text-gray-900">
                    Delivery Action
                </h2>

                <div class="mt-5">


                    {{-- ASSIGNED --}}
                    @if($status === 'assigned')

                        <div class="space-y-3">

                            <button
                                type="button"
                                wire:click="acceptOrder"
                                wire:confirm="Accept this delivery?"
                                class="w-full rounded-xl bg-primary-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary-500"
                            >
                                Accept Order
                            </button>


                            <button
                                type="button"
                                wire:click="declineOrder"
                                wire:confirm="Are you sure you want to decline this delivery?"
                                class="w-full rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                            >
                                Decline Order
                            </button>

                        </div>


                    {{-- ACCEPTED --}}
                    @elseif($status === 'accepted')

                        <button
                            type="button"
                            wire:click="updateStatus('picked_up')"
                            class="w-full rounded-xl bg-primary-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary-500"
                        >
                            Mark as Picked Up
                        </button>


                    {{-- PICKED UP --}}
                    @elseif($status === 'picked_up')

                        <button
                            type="button"
                            wire:click="updateStatus('out_for_delivery')"
                            class="w-full rounded-xl bg-primary-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary-500"
                        >
                            Start Delivery
                        </button>


                    {{-- OUT FOR DELIVERY --}}
                    @elseif($status === 'out_for_delivery')

                        @if($order->payment_status === 'paid')

                            <button
                                type="button"
                                wire:click="updateStatus('delivered')"
                                wire:confirm="Confirm that this order has been delivered?"
                                class="w-full rounded-xl bg-success-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-success-500"
                            >
                                Mark as Delivered
                            </button>

                        @else

                            <div class="rounded-xl bg-warning-50 p-4 text-sm text-warning-800">

                                <div class="flex items-start gap-3">

                                    <x-filament::icon
                                        icon="heroicon-o-exclamation-triangle"
                                        class="mt-0.5 h-5 w-5 shrink-0"
                                    />

                                    <div>

                                        <p class="font-semibold">
                                            Payment Required
                                        </p>

                                        <p class="mt-1">
                                            Outstanding balance:
                                            <strong>
                                                KES {{ number_format($this->getBalance(), 2) }}
                                            </strong>
                                        </p>

                                    </div>

                                </div>

                            </div>

                        @endif


                    {{-- DELIVERED --}}
                    @elseif($status === 'delivered')

                        <div class="rounded-xl bg-success-50 p-4 text-center">

                            <x-filament::icon
                                icon="heroicon-o-check-circle"
                                class="mx-auto h-10 w-10 text-success-600"
                            />

                            <p class="mt-2 font-semibold text-success-700">
                                Delivery Completed
                            </p>

                            <p class="mt-1 text-xs text-success-600">
                                This order has been successfully delivered.
                            </p>

                        </div>


                    {{-- OTHER --}}
                    @else

                        <div class="rounded-xl bg-gray-50 p-4 text-center text-sm text-gray-500">
                            No action available for this order.
                        </div>

                    @endif

                </div>

            </div>


        </div>

    </div>

</x-filament-panels::page>