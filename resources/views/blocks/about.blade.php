@php $locale = app()->getLocale(); @endphp

<section class="py-20">
    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-2 gap-12 items-center">
        @if(!empty($block['image']))
            <div>
                <img src="{{ $block['image'] }}" alt="{{ data_get($block, "title.{$locale}", '') }}"
                     class="w-full rounded-xl shadow-lg">
            </div>
        @endif
        <div>
            @if(data_get($block, "title.{$locale}"))
                <h2 class="text-3xl font-bold mb-6">{{ data_get($block, "title.{$locale}") }}</h2>
            @endif
            <div class="text-gray-600 leading-relaxed text-lg">
                {!! nl2br(e(data_get($block, "body.{$locale}", ''))) !!}
            </div>
        </div>
    </div>
</section>
