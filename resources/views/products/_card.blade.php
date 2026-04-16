@php
    $primaryImage = $product->primaryImage();
    $priceRange = $product->getPriceRange();
    $inStock = $product->isInStock();
@endphp

<div class="bg-white border rounded-xl overflow-hidden hover:shadow-lg transition group">
    <a href="/{{ app()->getLocale() }}/product/{{ $product->slug }}">
        @if ($primaryImage)
            <img src="{{ $primaryImage->url }}" alt="{{ $product->t('name') }}"
                 class="w-full h-48 object-cover group-hover:scale-105 transition">
        @else
            <div class="w-full h-48 bg-gray-100 flex items-center justify-center text-gray-400">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        @endif
    </a>

    <div class="p-4">
        <a href="/{{ app()->getLocale() }}/product/{{ $product->slug }}"
           class="block font-semibold text-gray-900 mb-1 hover:text-blue-600 truncate">
            {{ $product->t('name') }}
        </a>

        <div class="text-sm text-gray-500 mb-2">
            {{ $product->category?->t('name') }}
        </div>

        <div class="flex items-center justify-between">
            <div class="font-bold text-blue-600">
                @if ($priceRange['min'] !== $priceRange['max'])
                    {{ __('From') }} {{ \App\Support\PriceFormatter::format($priceRange['min']) }}
                @else
                    {{ \App\Support\PriceFormatter::format($priceRange['min']) }}
                @endif
            </div>

            <span class="text-xs px-2 py-1 rounded-full {{ $inStock ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                {{ $inStock ? __('In stock') : __('Out of stock') }}
            </span>
        </div>
    </div>
</div>
