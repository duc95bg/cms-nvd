@php $locale = app()->getLocale(); @endphp

<section class="py-16 bg-gray-50">
    <div class="max-w-6xl mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @foreach(($block['images'] ?? []) as $img)
                <div class="overflow-hidden rounded-xl">
                    <img src="{{ $img['src'] ?? '' }}"
                         alt="{{ data_get($img, "alt.{$locale}", '') }}"
                         class="w-full h-48 md:h-64 object-cover hover:scale-105 transition duration-300">
                </div>
            @endforeach
        </div>
    </div>
</section>
