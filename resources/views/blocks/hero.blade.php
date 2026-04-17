@php $locale = app()->getLocale(); @endphp

<section class="relative bg-gray-900 text-white py-24 md:py-32"
         @if(!empty($block['background_image']))
         style="background-image: url('{{ $block['background_image'] }}'); background-size: cover; background-position: center;"
         @endif>
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="relative max-w-5xl mx-auto px-6 text-center">
        <h1 class="text-4xl md:text-6xl font-bold mb-6">
            {{ data_get($block, "title.{$locale}", '') }}
        </h1>
        <p class="text-xl md:text-2xl text-gray-200 mb-10 max-w-3xl mx-auto">
            {{ data_get($block, "subtitle.{$locale}", '') }}
        </p>
        @if(data_get($block, "cta_label.{$locale}"))
            <a href="{{ $block['cta_url'] ?? '#' }}"
               class="inline-block px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-lg transition">
                {{ data_get($block, "cta_label.{$locale}", '') }}
            </a>
        @endif
    </div>
</section>
