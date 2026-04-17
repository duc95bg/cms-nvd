@php $locale = app()->getLocale(); @endphp

<section class="py-20 bg-gray-50">
    <div class="max-w-3xl mx-auto px-6 text-center">
        @if(data_get($block, "heading.{$locale}"))
            <h2 class="text-3xl font-bold mb-8">{{ data_get($block, "heading.{$locale}") }}</h2>
        @endif
        <div class="grid md:grid-cols-3 gap-6">
            @if(!empty($block['email']))
                <div class="p-6 bg-white rounded-xl border">
                    <div class="text-2xl mb-2">📧</div>
                    <div class="font-medium mb-1">Email</div>
                    <a href="mailto:{{ $block['email'] }}" class="text-blue-600 hover:underline">{{ $block['email'] }}</a>
                </div>
            @endif
            @if(!empty($block['phone']))
                <div class="p-6 bg-white rounded-xl border">
                    <div class="text-2xl mb-2">📞</div>
                    <div class="font-medium mb-1">{{ __('Phone') }}</div>
                    <a href="tel:{{ $block['phone'] }}" class="text-blue-600 hover:underline">{{ $block['phone'] }}</a>
                </div>
            @endif
            @if(data_get($block, "address.{$locale}"))
                <div class="p-6 bg-white rounded-xl border">
                    <div class="text-2xl mb-2">📍</div>
                    <div class="font-medium mb-1">{{ __('Shipping address') }}</div>
                    <p class="text-gray-600">{{ data_get($block, "address.{$locale}") }}</p>
                </div>
            @endif
        </div>
    </div>
</section>
