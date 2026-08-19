<div>

    {{-- Categories --}}
    <div class="mb-6 overflow-x-auto">
        <div class="flex gap-3 pb-2">

            {{-- All Categories --}}
            <button
                wire:click="selectCategory(null)"
                class="px-5 py-2 rounded-full font-medium transition whitespace-nowrap
                    {{ is_null($selectedCategory)
                        ? 'bg-[#0F172A] text-[#F8F1E7]'
                        : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-100' }}">
                All
            </button>

            {{-- Category Buttons --}}
            @foreach($this->categories as $category)
                <button
                    wire:click="selectCategory({{ $category->id }})"
                    class="px-5 py-2 rounded-full font-medium transition whitespace-nowrap
                        {{ $selectedCategory === $category->id
                            ? 'bg-[#0F172A] text-[#F8F1E7]'
                            : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-100' }}">

                    {{ $category->name }}

                </button>
            @endforeach

        </div>
    </div>


    {{-- Product Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 2xl:grid-cols-4 gap-6">

        @forelse($this->products as $product)

            <div
                class="bg-white rounded-2xl border border-gray-200 shadow-sm
                       hover:shadow-xl hover:border-[#0F172A]
                       transition p-6 min-h-[220px]
                       flex flex-col justify-between">

                {{-- Top --}}
                <div>

                    {{-- Product Image --}}
                    <div
                        class="w-16 h-16 rounded-xl overflow-hidden
                               bg-[#F8F1E7] flex items-center justify-center mb-5">

                        @if ($product->image)

                            <img
                                src="{{ asset('storage/' . $product->image) }}"
                                alt="{{ $product->name }}"
                                class="w-full h-full object-cover"
                            >

                        @else

                            {{-- Default Product Placeholder --}}
                            <svg
                                class="w-8 h-8 text-[#0F172A]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M20 13V7a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 002 7v6m18 0l-8 5-8-5m16 0l-8-5-8 5"
                                />
                            </svg>

                        @endif

                    </div>


                    {{-- Product Name --}}
                    <h3 class="font-bold text-lg text-[#0F172A]">
                        {{ $product->name }}
                    </h3>


                    {{-- SKU --}}
                    <p class="text-sm text-gray-500 mt-1">
                        SKU: {{ $product->sku }}
                    </p>


                    {{-- Stock --}}
                    <p
                        class="mt-3 text-sm font-medium
                            {{ $product->stock_quantity <= 5
                                ? 'text-red-600'
                                : 'text-green-600' }}">

                        Stock: {{ $product->stock_quantity }}

                    </p>

                </div>


                {{-- Bottom --}}
                <div class="flex items-end justify-between mt-6">

                    {{-- Price --}}
                    <div>

                        <p class="text-xs text-gray-500">
                            Price
                        </p>

                        <p class="text-2xl font-bold text-[#0F172A]">
                            KES {{ number_format($product->selling_price, 2) }}
                        </p>

                    </div>


                    {{-- Add To Cart --}}
                    <button
                        wire:click="addProduct({{ $product->id }})"
                        class="w-12 h-12 rounded-full
                               bg-[#0F172A] text-[#F8F1E7]
                               hover:bg-slate-800 transition
                               flex items-center justify-center
                               shadow-md">

                        <span class="text-2xl font-light leading-none">
                            +
                        </span>

                    </button>

                </div>

            </div>

        @empty

            {{-- Empty State --}}
            <div
                class="col-span-full bg-white rounded-2xl
                       p-12 text-center text-gray-500 shadow-sm">

                <p class="text-lg font-semibold">
                    No products found
                </p>

                <p class="text-sm mt-2">
                    Try selecting another category or searching
                    for a different product.
                </p>

            </div>

        @endforelse

    </div>

</div>