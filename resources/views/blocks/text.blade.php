@php $locale = app()->getLocale(); @endphp

<section class="py-16">
    <div class="max-w-3xl mx-auto px-6 text-center">
        @if(data_get($block, "heading.{$locale}"))
            <h2 class="text-3xl font-bold mb-6">{{ data_get($block, "heading.{$locale}") }}</h2>
        @endif
        <div class="text-gray-600 leading-relaxed text-lg">
            {!! nl2br(e(data_get($block, "body.{$locale}", ''))) !!}
        </div>
    </div>
</section>
