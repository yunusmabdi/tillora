<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <title>
        Receipt {{ $sale->invoice_number }}
    </title>

    @vite(['resources/css/app.css'])

</head>

<body class="bg-slate-100 py-10">

<div class="max-w-xl mx-auto">

    <div class="bg-white rounded-3xl shadow-xl p-10">

        <div class="text-center">

            <div class="text-5xl mb-4">
                🏪
            </div>

            <h1 class="text-3xl font-bold text-[#0F172A]">
                Tillora
            </h1>

            <p class="text-slate-500">
                Point of Sale Receipt
            </p>

        </div>

        <hr class="my-8">

        <div class="grid grid-cols-2 gap-4 text-sm">

            <div>

                <p class="text-slate-500">Invoice</p>

                <p class="font-semibold">
                    {{ $sale->invoice_number }}
                </p>

            </div>

            <div>

                <p class="text-slate-500">Date</p>

                <p class="font-semibold">
                    {{ $sale->sale_date->format('d M Y H:i') }}
                </p>

            </div>

            <div>

                <p class="text-slate-500">Cashier</p>

                <p class="font-semibold">
                    {{ auth()->user()->name ?? 'System'}}
                </p>

            </div>

            <div>

                <p class="text-slate-500">Customer</p>

                <p class="font-semibold">
                    {{ $sale->customer?->name ?? 'Walk-in Customer' }}
                </p>

            </div>

        </div>

        <hr class="my-8">

        <table class="w-full">

            <thead>

            <tr class="text-left text-slate-500 border-b">

                <th class="py-2">Item</th>

                <th class="text-center">Qty</th>

                <th class="text-right">Price</th>

                <th class="text-right">Total</th>

            </tr>

            </thead>

            <tbody>

            @foreach($sale->items as $item)

                <tr class="border-b">

                    <td class="py-3">
                        {{ $item->product->name }}
                    </td>

                    <td class="text-center">
                        {{ $item->quantity }}
                    </td>

                    <td class="text-right">
                        {{ number_format($item->unit_price,2) }}
                    </td>

                    <td class="text-right font-semibold">
                        {{ number_format($item->line_total,2) }}
                    </td>

                </tr>

            @endforeach

            </tbody>

        </table>

        <div class="mt-8 space-y-2">

            <div class="flex justify-between">

                <span>Subtotal</span>

                <span>KES {{ number_format($sale->subtotal,2) }}</span>

            </div>

            <div class="flex justify-between">

                <span>Tax</span>

                <span>KES {{ number_format($sale->tax,2) }}</span>

            </div>

            <div class="flex justify-between text-xl font-bold border-t pt-4">

                <span>Total</span>

                <span>KES {{ number_format($sale->total_amount,2) }}</span>

            </div>

            <div class="flex justify-between">

                <span>Amount Paid</span>

                <span>KES {{ number_format($sale->amount_paid,2) }}</span>

            </div>

            <div class="flex justify-between">

                <span>Change</span>

                <span>KES {{ number_format($sale->change_amount,2) }}</span>

            </div>

        </div>

        <div class="mt-10 text-center text-slate-500">

            Thank you for shopping with us!

        </div>

        <div class="mt-8 flex gap-4">

            <button
                onclick="window.print()"
                class="flex-1 bg-[#0F172A] text-white py-3 rounded-xl hover:bg-slate-800">

                Print Receipt

            </button>

            @if(request()->routeIs('pos.receipt'))

            <a
                href="{{ route('pos') }}"
                class="flex-1 text-center bg-slate-200 py-3 rounded-xl">

                New Sale

            </a>

            @endif
            @if(request()->is('admin/*'))

            <a
                href="{{ url()->previous() }}"
                class="flex-1 text-center bg-slate-200 py-3 rounded-xl">

                Back

            </a>

            @endif

        </div>

    </div>

</div>

</body>
</html>