@php
    $locale = app()->getLocale();
    $heading = data_get($block, "heading.{$locale}", '');
    $categoryId = $block['category_id'] ?? null;
    $count = $block['count'] ?? 4;

    $query = \App\Models\Product::active()->with(['images', 'variants' => fn($q) => $q->active()]);
    if ($categoryId) {
        $query->where('category_id', $categoryId);
    }
    $products = $query->limit($count)->latest()->get();
@endphp

<section class="py-20 bg-gray-50">
    <div class="max-w-6xl mx-auto px-6">
        @if($heading)
            <h2 class="text-3xl font-bold text-center mb-12">{{ $heading }}</h2>
        @endif

        @if($products->isNotEmpty())
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($products as $product)
                    @include('products._card', ['product' => $product])
                @endforeach
            </div>
        @else
            <p class="text-center text-gray-500">{{ __('No products found.') }}</p>
        @endif
    </div>
</section>
