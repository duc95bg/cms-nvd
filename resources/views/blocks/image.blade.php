@php $locale = app()->getLocale(); @endphp

<section class="py-12">
    <div class="max-w-4xl mx-auto px-6">
        @if(!empty($block['src']))
            <figure class="text-center">
                <img src="{{ $block['src'] }}"
                     alt="{{ data_get($block, "alt.{$locale}", '') }}"
                     class="w-full rounded-xl shadow-lg">
                @if(data_get($block, "caption.{$locale}"))
                    <figcaption class="mt-3 text-sm text-gray-500">
                        {{ data_get($block, "caption.{$locale}") }}
                    </figcaption>
                @endif
            </figure>
        @endif
    </div>
</section>
