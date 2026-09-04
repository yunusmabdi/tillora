<x-filament-panels::page>

    @php
        $orders = $this->getDeliveredOrders();
        $todayDeliveries = $this->getTodayDeliveries();
    @endphp

    {{-- HEADER --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5">

        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">

            <div>
                <p class="text-sm font-medium text-primary-600">
                    RIDER ACTIVITY
                </p>

                <h1 class="mt-1 text-2xl font-bold text-gray-900">
                    Delivery History
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    View your completed deliveries.
                </p>
            </div>

            <div class="flex gap-3">

                <div class="rounded-xl bg-gray-50 px-5 py-3 text-center">
                    <p class="text-2xl font-bold text-gray-900">
                        {{ $this->getTotalDeliveries() }}
                    </p>

                    <p class="text-xs text-gray-500">
                        Total Deliveries
                    </p>
                </div>

                <div class="rounded-xl bg-primary-50 px-5 py-3 text-center">
                    <p class="text-2xl font-bold text-primary-700">
                        {{ $this->getTodayCount() }}
                    </p>

                    <p class="text-xs text-primary-600">
                        Today
                    </p>
                </div>

            </div>

        </div>

    </div>


    {{-- TODAY --}}
    @if($todayDeliveries->isNotEmpty())

        <div class="mt-6">

            <div class="mb-3">
                <h2 class="text-lg font-bold text-gray-900">
                    Today's Deliveries
                </h2>
            </div>

            <div class="space-y-3">

                @foreach($todayDeliveries as $order)

                    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5">

                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                            <div class="flex items-start gap-4">

                                <div class="rounded-xl bg-success-50 p-3">

                                    <x-filament::icon
                                        icon="heroicon-o-check-circle"
                                        class="h-6 w-6 text-success-600"
                                    />

                                </div>

                                <div>

                                    <div class="flex items-center gap-2">

                                        <h3 class="font-bold text-gray-900">
                                            {{ $order->invoice_number }}
                                        </h3>

                                        <span class="rounded-full bg-success-50 px-2.5 py-1 text-xs font-semibold text-success-700">
                                            Delivered
                                        </span>

                                    </div>

                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ $order->sale_date?->format('d M Y, h:i A') }}
                                    </p>

                                </div>

                            </div>


                            <div class="grid grid-cols-2 gap-6 sm:grid-cols-3">

                                <div>
                                    <p class="text-xs text-gray-400">
                                        Customer
                                    </p>

                                    <p class="mt-1 text-sm font-semibold text-gray-900">
                                        {{ $order->customer?->name ?? 'Customer' }}
                                    </p>
                                </div>


                                <div>
                                    <p class="text-xs text-gray-400">
                                        Zone
                                    </p>

                                    <p class="mt-1 text-sm font-semibold text-gray-900">
                                        {{ $order->deliveryZone?->name ?? '—' }}
                                    </p>
                                </div>


                                <div>
                                    <p class="text-xs text-gray-400">
                                        Total
                                    </p>

                                    <p class="mt-1 text-sm font-bold text-gray-900">
                                        KES {{ number_format($order->total_amount, 2) }}
                                    </p>
                                </div>

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        </div>

    @endif


    {{-- ALL DELIVERIES --}}
    <div class="mt-8">

        <div class="mb-4">

            <h2 class="text-lg font-bold text-gray-900">
                All Completed Deliveries
            </h2>

            <p class="text-sm text-gray-500">
                Your complete delivery history.
            </p>

        </div>


        @if($orders->isEmpty())

            <div class="rounded-2xl bg-white p-10 text-center shadow-sm ring-1 ring-gray-950/5">

                <x-filament::icon
                    icon="heroicon-o-clipboard-document-list"
                    class="mx-auto h-12 w-12 text-gray-300"
                />

                <h3 class="mt-4 font-semibold text-gray-900">
                    No completed deliveries
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Your completed deliveries will appear here.
                </p>

            </div>

        @else

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5">

                <div class="divide-y divide-gray-100">

                    @foreach($orders as $order)

                        <div class="p-5 transition hover:bg-gray-50">

                            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

                                <div class="flex items-center gap-4">

                                    <div class="rounded-xl bg-success-50 p-3">

                                        <x-filament::icon
                                            icon="heroicon-o-check"
                                            class="h-5 w-5 text-success-600"
                                        />

                                    </div>

                                    <div>

                                        <p class="font-bold text-gray-900">
                                            {{ $order->invoice_number }}
                                        </p>

                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ $order->sale_date?->format('d M Y, h:i A') }}
                                        </p>

                                    </div>

                                </div>


                                <div class="grid grid-cols-2 gap-6 sm:grid-cols-3">

                                    <div>
                                        <p class="text-xs text-gray-400">
                                            Customer
                                        </p>

                                        <p class="mt-1 text-sm font-medium text-gray-900">
                                            {{ $order->customer?->name ?? '—' }}
                                        </p>
                                    </div>


                                    <div>
                                        <p class="text-xs text-gray-400">
                                            Delivery Zone
                                        </p>

                                        <p class="mt-1 text-sm font-medium text-gray-900">
                                            {{ $order->deliveryZone?->name ?? '—' }}
                                        </p>
                                    </div>


                                    <div>
                                        <p class="text-xs text-gray-400">
                                            Order Total
                                        </p>

                                        <p class="mt-1 text-sm font-bold text-gray-900">
                                            KES {{ number_format($order->total_amount, 2) }}
                                        </p>
                                    </div>

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>

            </div>

        @endif

    </div>

</x-filament-panels::page>