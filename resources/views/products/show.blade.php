@extends('layouts.catalog')

@section('title', $product->t('name') . ' — ' . config('app.name'))
@section('description', $product->t('short_description'))

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-8">

        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-500 mb-6">
            <a href="/{{ app()->getLocale() }}" class="hover:text-blue-600">{{ __('home') }}</a>
            <span class="mx-1">/</span>
            <a href="/{{ app()->getLocale() }}/products" class="hover:text-blue-600">{{ __('Products') }}</a>
            <span class="mx-1">/</span>
            @if ($product->category)
                @if ($product->category->parent)
                    <a href="/{{ app()->getLocale() }}/category/{{ $product->category->parent->slug }}" class="hover:text-blue-600">
                        {{ $product->category->parent->t('name') }}
                    </a>
                    <span class="mx-1">/</span>
                @endif
                <a href="/{{ app()->getLocale() }}/category/{{ $product->category->slug }}" class="hover:text-blue-600">
                    {{ $product->category->t('name') }}
                </a>
                <span class="mx-1">/</span>
            @endif
            <span class="text-gray-900">{{ $product->t('name') }}</span>
        </nav>

        <div class="grid md:grid-cols-2 gap-10">
            {{-- Gallery --}}
            <div>
                @php $images = $product->images; $primary = $product->primaryImage(); @endphp
                <div class="mb-4">
                    <img id="main-image"
                         src="{{ $primary?->url ?? '' }}"
                         alt="{{ $product->t('name') }}"
                         class="w-full h-96 object-contain border rounded-xl bg-gray-50">
                </div>
                @if ($images->count() > 1)
                    <div class="flex gap-2 overflow-x-auto">
                        @foreach ($images as $img)
                            <button type="button" onclick="document.getElementById('main-image').src='{{ $img->url }}'"
                                    class="shrink-0 w-20 h-20 border rounded-lg overflow-hidden hover:ring-2 hover:ring-blue-500 {{ $img->is_primary ? 'ring-2 ring-blue-500' : '' }}">
                                <img src="{{ $img->url }}" alt="" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Product info --}}
            <div>
                <h1 class="text-3xl font-bold mb-2">{{ $product->t('name') }}</h1>

                @if ($product->t('short_description'))
                    <p class="text-gray-600 mb-4">{{ $product->t('short_description') }}</p>
                @endif

                {{-- Price display --}}
                @php $priceRange = $product->getPriceRange(); @endphp
                <div class="text-2xl font-bold text-blue-600 mb-6" id="price-display">
                    @if ($priceRange['min'] !== $priceRange['max'])
                        {{ __('From') }} {{ \App\Support\PriceFormatter::format($priceRange['min']) }}
                    @else
                        {{ \App\Support\PriceFormatter::format($priceRange['min']) }}
                    @endif
                </div>

                {{-- Stock badge --}}
                <div class="mb-6" id="stock-display">
                    @if ($product->isInStock())
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">{{ __('In stock') }}</span>
                    @else
                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">{{ __('Out of stock') }}</span>
                    @endif
                </div>

                {{-- SKU display --}}
                <div class="text-sm text-gray-500 mb-6 hidden" id="sku-display">
                    {{ __('SKU') }}: <span id="sku-value"></span>
                </div>

                {{-- Attribute selectors --}}
                @if ($product->attributes->isNotEmpty())
                    <div class="space-y-4 mb-6" id="attribute-selectors">
                        @foreach ($product->attributes as $attr)
                            <div>
                                <label class="block font-medium mb-2">{{ $attr->t('name') }}</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($attr->values as $val)
                                        <button type="button"
                                                data-attribute-id="{{ $attr->id }}"
                                                data-value-id="{{ $val->id }}"
                                                onclick="selectAttribute(this)"
                                                class="attr-btn px-4 py-2 border rounded-lg hover:border-blue-500 hover:bg-blue-50 transition text-sm">
                                            {{ $val->t('value') }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Add to cart --}}
                <button id="add-to-cart-btn"
                        data-product-id="{{ $product->id }}"
                        data-variant-id=""
                        disabled
                        class="w-full md:w-auto px-8 py-3 bg-blue-600 text-white font-medium rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-blue-700 transition">
                    {{ __('Add to cart') }}
                </button>

                @if ($product->variants->isEmpty())
                    {{-- No variants — enable button if product has stock --}}
                    <script>
                        document.getElementById('add-to-cart-btn').disabled = {{ $product->isInStock() ? 'false' : 'true' }};
                    </script>
                @endif
            </div>
        </div>

        {{-- Description --}}
        @if ($product->t('description'))
            <div class="mt-12 prose max-w-none">
                <h2 class="text-2xl font-bold mb-4">{{ __('Description') }}</h2>
                <div class="text-gray-700 leading-relaxed">
                    {!! nl2br(e($product->t('description'))) !!}
                </div>
            </div>
        @endif

        {{-- Related products --}}
        @if ($related->isNotEmpty())
            <div class="mt-16">
                <h2 class="text-2xl font-bold mb-6">{{ __('Related products') }}</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach ($related as $relProduct)
                        @include('products._card', ['product' => $relProduct])
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Variant selector JS --}}
    <script>
        const productId = {{ $product->id }};
        const selectedValues = {};
        const totalAttributes = {{ $product->attributes->count() }};

        function selectAttribute(btn) {
            const attrId = btn.dataset.attributeId;
            const valueId = btn.dataset.valueId;

            // Toggle selection in this attribute group
            btn.parentElement.querySelectorAll('.attr-btn').forEach(b => {
                b.classList.remove('border-blue-500', 'bg-blue-50', 'ring-2', 'ring-blue-500');
            });
            btn.classList.add('border-blue-500', 'bg-blue-50', 'ring-2', 'ring-blue-500');

            selectedValues[attrId] = parseInt(valueId);

            // Check if all attributes are selected
            if (Object.keys(selectedValues).length === totalAttributes) {
                fetchVariantPrice();
            }
        }

        async function fetchVariantPrice() {
            const values = Object.values(selectedValues);
            const params = values.map(v => 'values[]=' + v).join('&');

            try {
                const res = await fetch('/api/product/' + productId + '/variant-price?' + params);
                const data = await res.json();

                const priceEl = document.getElementById('price-display');
                const stockEl = document.getElementById('stock-display');
                const skuEl = document.getElementById('sku-display');
                const skuVal = document.getElementById('sku-value');
                const cartBtn = document.getElementById('add-to-cart-btn');
                const mainImg = document.getElementById('main-image');

                if (res.ok) {
                    priceEl.textContent = data.price;
                    skuVal.textContent = data.sku;
                    skuEl.classList.remove('hidden');

                    if (data.in_stock) {
                        stockEl.innerHTML = '<span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">{{ __("In stock") }} (' + data.stock + ')</span>';
                        cartBtn.disabled = false;
                    } else {
                        stockEl.innerHTML = '<span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">{{ __("Out of stock") }}</span>';
                        cartBtn.disabled = true;
                    }

                    cartBtn.dataset.variantId = data.variant_id;

                    if (data.image) {
                        mainImg.src = data.image;
                    }
                } else {
                    priceEl.textContent = '{{ __("Select variant") }}';
                    cartBtn.disabled = true;
                    cartBtn.dataset.variantId = '';
                    skuEl.classList.add('hidden');
                }
            } catch (err) {
                console.error('Failed to fetch variant price:', err);
            }
        }
    </script>
@endsection
