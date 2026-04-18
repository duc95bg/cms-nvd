@php $locale = app()->getLocale(); @endphp

<section class="py-20 bg-blue-600 text-white">
    <div class="max-w-4xl mx-auto px-6 text-center">
        <h2 class="text-4xl font-bold mb-4">
            {{ data_get($block, "title.{$locale}", '') }}
        </h2>
        <p class="text-xl opacity-90 mb-8 max-w-2xl mx-auto">
            {{ data_get($block, "description.{$locale}", '') }}
        </p>
        @if(data_get($block, "button_label.{$locale}"))
            <a href="{{ $block['button_url'] ?? '#' }}"
               class="inline-block px-8 py-4 bg-white text-blue-600 font-medium rounded-lg hover:bg-gray-100 transition text-lg">
                {{ data_get($block, "button_label.{$locale}") }}
            </a>
        @endif
    </div>
</section>
