<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold tracking-tight">
                Rider Dashboard
            </h1>

            <p class="text-sm text-gray-500">
                Manage your deliveries and orders.
            </p>
        </div>

        {{-- Statistics --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

            <x-filament::section>
                <div class="text-sm text-gray-500">
                    New Orders
                </div>

                <div class="mt-2 text-3xl font-bold">
                    {{ $this->getNewOrdersCount() }}
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-sm text-gray-500">
                    Active Deliveries
                </div>

                <div class="mt-2 text-3xl font-bold">
                    {{ $this->getActiveOrdersCount() }}
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-sm text-gray-500">
                    Completed Deliveries
                </div>

                <div class="mt-2 text-3xl font-bold">
                    {{ $this->getCompletedOrdersCount() }}
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-sm text-gray-500">
                    Delivered Today
                </div>

                <div class="mt-2 text-3xl font-bold">
                    {{ $this->getTodayCount() }}
                </div>
            </x-filament::section>

        </div>

        {{-- Active Deliveries --}}
        <x-filament::section>
            <x-slot name="heading">
                Active Deliveries
            </x-slot>

            @php
                $activeOrders = $this->getActiveOrders();
            @endphp

            @if ($activeOrders->isEmpty())
                <div class="py-8 text-center text-sm text-gray-500">
                    No active deliveries.
                </div>
            @else
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($activeOrders as $order)
                        <div class="flex flex-col gap-4 py-4 sm:flex-row sm:items-center sm:justify-between">

                            <div>
                                <div class="font-semibold">
                                    {{ $order->invoice_number }}
                                </div>

                                <div class="text-sm text-gray-500">
                                    {{ $order->customer?->name ?? 'Customer' }}
                                </div>

                                @if ($order->deliveryZone)
                                    <div class="mt-1 text-sm text-gray-500">
                                        {{ $order->deliveryZone->name }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-3">
                                <x-filament::badge>
                                    {{ str_replace('_', ' ', ucfirst($order->fulfillment_status)) }}
                                </x-filament::badge>

                                <x-filament::button
                                    wire:click="viewOrder({{ $order->id }})"
                                >
                                    View Order
                                </x-filament::button>
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        {{-- New Orders --}}
        <x-filament::section>
            <x-slot name="heading">
                New Orders
            </x-slot>

            @php
                $newOrders = $this->getNewOrders();
            @endphp

            @if ($newOrders->isEmpty())
                <div class="py-8 text-center text-sm text-gray-500">
                    No new orders waiting for acceptance.
                </div>
            @else
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($newOrders as $order)
                        <div class="flex flex-col gap-4 py-4 lg:flex-row lg:items-center lg:justify-between">

                            <div>
                                <div class="font-semibold">
                                    {{ $order->invoice_number }}
                                </div>

                                <div class="text-sm text-gray-500">
                                    {{ $order->customer?->name ?? 'Customer' }}
                                </div>

                                @if ($order->deliveryZone)
                                    <div class="mt-1 text-sm text-gray-500">
                                        {{ $order->deliveryZone->name }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex gap-2">
                                <x-filament::button
                                    wire:click="acceptOrder({{ $order->id }})"
                                >
                                    Accept
                                </x-filament::button>

                                <x-filament::button
                                    color="danger"
                                    wire:click="declineOrder({{ $order->id }})"
                                >
                                    Decline
                                </x-filament::button>
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        {{-- Completed Today --}}
        <x-filament::section>
            <x-slot name="heading">
                Completed Today
            </x-slot>

            @php
                $deliveredToday = $this->getDeliveredToday();
            @endphp

            @if ($deliveredToday->isEmpty())
                <div class="py-8 text-center text-sm text-gray-500">
                    No deliveries completed today.
                </div>
            @else
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($deliveredToday as $order)
                        <div class="flex items-center justify-between py-4">

                            <div>
                                <div class="font-semibold">
                                    {{ $order->invoice_number }}
                                </div>

                                <div class="text-sm text-gray-500">
                                    {{ $order->customer?->name ?? 'Customer' }}
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <x-filament::badge color="success">
                                    Delivered
                                </x-filament::badge>

                                <x-filament::button
                                    size="sm"
                                    wire:click="viewOrder({{ $order->id }})"
                                >
                                    View
                                </x-filament::button>
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

    </div>
</x-filament-panels::page>