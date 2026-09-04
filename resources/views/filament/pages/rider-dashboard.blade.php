<x-filament-panels::page>

    @php
        $driver = $this->getDriver();
        $pendingOrders = $this->getPendingOrders();
        $activeOrder = $this->getActiveOrder();
        $deliveredToday = $this->getDeliveredToday();
    @endphp


    {{-- HEADER --}}
    <div class="rounded-2xl bg-gradient-to-r from-primary-600 to-primary-700 p-6 text-white shadow-sm">

        <div class="flex flex-col justify-between gap-6 md:flex-row md:items-center">

            <div>
                <p class="text-sm font-medium text-white/80">
                    Good day
                </p>

                <h1 class="mt-1 text-2xl font-bold">
                    {{ Auth::user()->name }}
                </h1>

                <p class="mt-2 text-sm text-white/80">
                    Here is your delivery overview for today.
                </p>
            </div>

            <div class="rounded-xl bg-white/10 px-5 py-4 backdrop-blur">

                <div class="text-sm text-white/70">
                    Today's Deliveries
                </div>

                <div class="mt-1 text-3xl font-bold">
                    {{ $this->getTodayCount() }}
                </div>

            </div>

        </div>

    </div>


    {{-- STATISTICS --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Assigned --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Waiting for Acceptance
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $pendingOrders->count() }}
                    </p>
                </div>

                <div class="rounded-xl bg-warning-50 p-3">
                    <x-filament::icon
                        icon="heroicon-o-clock"
                        class="h-6 w-6 text-warning-600"
                    />
                </div>

            </div>

        </div>


        {{-- Active --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Active Deliveries
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $this->getActiveCount() }}
                    </p>
                </div>

                <div class="rounded-xl bg-primary-50 p-3">
                    <x-filament::icon
                        icon="heroicon-o-truck"
                        class="h-6 w-6 text-primary-600"
                    />
                </div>

            </div>

        </div>


        {{-- Out for delivery --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Out for Delivery
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $this->getAssignedOrders()->where('fulfillment_status', 'out_for_delivery')->count() }}
                    </p>
                </div>

                <div class="rounded-xl bg-orange-50 p-3">
                    <x-filament::icon
                        icon="heroicon-o-map-pin"
                        class="h-6 w-6 text-orange-600"
                    />
                </div>

            </div>

        </div>


        {{-- Delivered --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Delivered Today
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $this->getDeliveredCount() }}
                    </p>
                </div>

                <div class="rounded-xl bg-success-50 p-3">
                    <x-filament::icon
                        icon="heroicon-o-check-circle"
                        class="h-6 w-6 text-success-600"
                    />
                </div>

            </div>

        </div>

    </div>


    {{-- ACTIVE DELIVERY --}}
    @if($activeOrder)

        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5">

            <div class="border-b border-gray-100 p-6">

                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">

                    <div>
                        <p class="text-sm font-medium text-primary-600">
                            CURRENT DELIVERY
                        </p>

                        <h2 class="mt-1 text-xl font-bold text-gray-900">
                            {{ $activeOrder->invoice_number }}
                        </h2>
                    </div>

                    <span class="inline-flex w-fit rounded-full bg-primary-50 px-3 py-1 text-sm font-semibold text-primary-700">
                        {{ str_replace('_', ' ', ucfirst($activeOrder->fulfillment_status)) }}
                    </span>

                </div>

            </div>


            <div class="p-6">

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                    {{-- Customer --}}
                    <div>

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                            Customer
                        </p>

                        <p class="mt-2 font-semibold text-gray-900">
                            {{ $activeOrder->customer?->name ?? 'Customer' }}
                        </p>

                    </div>


                    {{-- Address --}}
                    <div>

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                            Delivery Address
                        </p>

                        <p class="mt-2 font-semibold text-gray-900">
                            {{ $activeOrder->delivery_address ?? 'No address provided' }}
                        </p>

                        @if($activeOrder->deliveryZone)
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $activeOrder->deliveryZone->name }}
                            </p>
                        @endif

                    </div>


                    {{-- Amount --}}
                    <div>

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                            Order Total
                        </p>

                        <p class="mt-2 text-xl font-bold text-gray-900">
                            KES {{ number_format($activeOrder->total_amount, 2) }}
                        </p>

                        <p class="mt-1 text-sm">
                            @if($activeOrder->payment_status === 'paid')
                                <span class="font-medium text-success-600">
                                    Fully Paid
                                </span>
                            @else
                                <span class="font-medium text-warning-600">
                                    Payment Pending
                                </span>
                            @endif
                        </p>

                    </div>

                </div>


                {{-- Progress --}}
                <div class="mt-8">

                    <div class="flex items-center justify-between">

                        @php
                            $steps = [
                                'accepted' => 'Accepted',
                                'picked_up' => 'Picked Up',
                                'out_for_delivery' => 'Out for Delivery',
                                'delivered' => 'Delivered',
                            ];

                            $statusOrder = [
                                'accepted' => 1,
                                'picked_up' => 2,
                                'out_for_delivery' => 3,
                                'delivered' => 4,
                            ];

                            $currentStep = $statusOrder[$activeOrder->fulfillment_status] ?? 1;
                        @endphp

                        @foreach($steps as $step => $label)

                            @php
                                $stepNumber = $statusOrder[$step];
                                $completed = $stepNumber <= $currentStep;
                            @endphp

                            <div class="flex flex-col items-center">

                                <div class="
                                    flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold
                                    {{ $completed
                                        ? 'bg-primary-600 text-white'
                                        : 'bg-gray-100 text-gray-400'
                                    }}
                                ">
                                    {{ $stepNumber }}
                                </div>

                                <span class="mt-2 hidden text-xs font-medium text-gray-500 sm:block">
                                    {{ $label }}
                                </span>

                            </div>

                            @if(!$loop->last)

                                <div class="mx-2 h-1 flex-1 rounded
                                    {{ $stepNumber < $currentStep
                                        ? 'bg-primary-600'
                                        : 'bg-gray-100'
                                    }}">
                                </div>

                            @endif

                        @endforeach

                    </div>

                </div>


                {{-- Main action --}}
                <div class="mt-8 border-t border-gray-100 pt-6">

                    @if($activeOrder->fulfillment_status === 'accepted')

                        <button
                            type="button"
                            wire:click="updateStatus({{ $activeOrder->id }}, 'picked_up')"
                            class="w-full rounded-xl bg-primary-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 sm:w-auto"
                        >
                            Mark Order as Picked Up
                        </button>

                    @elseif($activeOrder->fulfillment_status === 'picked_up')

                        <button
                            type="button"
                            wire:click="updateStatus({{ $activeOrder->id }}, 'out_for_delivery')"
                            class="w-full rounded-xl bg-primary-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 sm:w-auto"
                        >
                            Start Delivery
                        </button>

                    @elseif($activeOrder->fulfillment_status === 'out_for_delivery')

                        @if($activeOrder->payment_status === 'paid')

                            <button
                                type="button"
                                wire:click="updateStatus({{ $activeOrder->id }}, 'delivered')"
                                wire:confirm="Confirm that this order has been delivered?"
                                class="w-full rounded-xl bg-success-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-success-500 sm:w-auto"
                            >
                                Mark as Delivered
                            </button>

                        @else

                            <div class="rounded-xl bg-warning-50 p-4 text-sm text-warning-800">
                                <strong>Payment required.</strong>
                                This order must be fully paid before it can be marked as delivered.
                            </div>

                        @endif

                    @endif

                </div>

            </div>

        </div>

    @endif


    {{-- PENDING ORDERS --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5">

        <div class="border-b border-gray-100 p-6">

            <div>
                <h2 class="text-lg font-bold text-gray-900">
                    New Delivery Assignments
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Orders waiting for your response.
                </p>
            </div>

        </div>


        <div class="divide-y divide-gray-100">

            @forelse($pendingOrders as $order)

                <div class="p-6">

                    <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-center">

                        <div class="flex-1">

                            <div class="flex items-center gap-3">

                                <h3 class="font-bold text-gray-900">
                                    {{ $order->invoice_number }}
                                </h3>

                                <span class="rounded-full bg-warning-50 px-2.5 py-1 text-xs font-medium text-warning-700">
                                    New
                                </span>

                            </div>

                            <p class="mt-2 text-sm font-medium text-gray-700">
                                {{ $order->customer?->name ?? 'Customer' }}
                            </p>

                            <p class="mt-1 text-sm text-gray-500">
                                {{ $order->delivery_address ?? 'No address provided' }}
                            </p>

                            @if($order->deliveryZone)

                                <p class="mt-1 text-xs text-gray-400">
                                    {{ $order->deliveryZone->name }}
                                </p>

                            @endif

                        </div>


                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

                            <div class="mr-4 text-left sm:text-right">

                                <p class="text-xs uppercase tracking-wide text-gray-400">
                                    Order Total
                                </p>

                                <p class="mt-1 text-lg font-bold text-gray-900">
                                    KES {{ number_format($order->total_amount, 2) }}
                                </p>

                            </div>


                            <button
                                type="button"
                                wire:click="acceptOrder({{ $order->id }})"
                                wire:confirm="Accept this delivery?"
                                class="rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-500"
                            >
                                Accept
                            </button>


                            <button
                                type="button"
                                wire:click="declineOrder({{ $order->id }})"
                                wire:confirm="Decline this delivery?"
                                class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                Decline
                            </button>

                        </div>

                    </div>

                </div>

            @empty

                <div class="p-10 text-center">

                    <x-filament::icon
                        icon="heroicon-o-inbox"
                        class="mx-auto h-10 w-10 text-gray-300"
                    />

                    <p class="mt-3 font-medium text-gray-900">
                        No new assignments
                    </p>

                    <p class="mt-1 text-sm text-gray-500">
                        You are all caught up.
                    </p>

                </div>

            @endforelse

        </div>

    </div>


    {{-- TODAY'S COMPLETED --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5">

        <div class="border-b border-gray-100 p-6">

            <h2 class="text-lg font-bold text-gray-900">
                Completed Today
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Your recently completed deliveries.
            </p>

        </div>


        <div class="divide-y divide-gray-100">

            @forelse($deliveredToday->take(5) as $order)

                <div class="flex flex-col justify-between gap-3 p-5 sm:flex-row sm:items-center">

                    <div>

                        <p class="font-semibold text-gray-900">
                            {{ $order->invoice_number }}
                        </p>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ $order->customer?->name ?? 'Customer' }}
                        </p>

                    </div>


                    <div class="flex items-center gap-5">

                        <p class="font-semibold text-gray-900">
                            KES {{ number_format($order->total_amount, 2) }}
                        </p>

                        <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-3 py-1 text-xs font-medium text-success-700">

                            <x-filament::icon
                                icon="heroicon-o-check"
                                class="h-3.5 w-3.5"
                            />

                            Delivered

                        </span>

                    </div>

                </div>

            @empty

                <div class="p-8 text-center text-sm text-gray-500">
                    No deliveries completed today.
                </div>

            @endforelse

        </div>

    </div>

</x-filament-panels::page>