@php $locale = app()->getLocale(); @endphp

<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-6">
        @if(data_get($block, "heading.{$locale}"))
            <h2 class="text-3xl font-bold text-center mb-12">{{ data_get($block, "heading.{$locale}") }}</h2>
        @endif
        <div class="grid md:grid-cols-3 gap-8">
            @foreach(($block['items'] ?? []) as $item)
                <div class="text-center p-6 rounded-xl border hover:shadow-lg transition">
                    @if(!empty($item['icon']))
                        <div class="text-4xl mb-4">{{ $item['icon'] }}</div>
                    @endif
                    <h3 class="text-xl font-semibold mb-3">
                        {{ data_get($item, "title.{$locale}", '') }}
                    </h3>
                    <p class="text-gray-600">
                        {{ data_get($item, "body.{$locale}", '') }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>
