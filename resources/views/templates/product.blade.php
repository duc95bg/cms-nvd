@extends('templates.layouts.base')

@section('content')
    <section class="bg-gradient-to-b from-indigo-50 to-white">
        <div class="max-w-6xl mx-auto px-4 py-24 text-center">
            <h1 class="text-5xl font-extrabold tracking-tight text-gray-900 mb-6">
                {{ $site->t('hero.title') }}
            </h1>
            <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                {{ $site->t('hero.subtitle') }}
            </p>
            <a href="{{ $site->t('hero.cta_url', null, '#features') }}"
               class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-8 py-3 rounded-lg shadow-md transition">
                {{ $site->t('hero.cta_label') }}
            </a>
        </div>
    </section>

    <section id="features" class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">
                {{ $site->t('features.heading') }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach(data_get($site->content, 'features.items', []) as $feature)
                    <div class="p-6 border border-gray-200 rounded-lg hover:shadow-lg transition">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">
                            {{ data_get($feature, 'title.'.app()->getLocale()) }}
                        </h3>
                        <p class="text-gray-600">
                            {{ data_get($feature, 'body.'.app()->getLocale()) }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-20 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">
                {{ $site->t('pricing.heading') }}
            </h2>
            <p class="text-lg text-gray-600 mb-8">
                {{ $site->t('pricing.subheading') }}
            </p>
            <a href="{{ $site->t('pricing.cta_url', null, '#') }}"
               class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-8 py-3 rounded-lg shadow-md transition">
                {{ $site->t('pricing.cta_label') }}
            </a>
        </div>
    </section>
@endsection
