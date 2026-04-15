<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>{{ $site->t('seo.title', null, $site->slug) }}</title>
    <meta name="description" content="{{ $site->t('seo.description') }}">
</head>
<body class="bg-white text-gray-900 antialiased">
    <header class="border-b border-gray-200 bg-white">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="/{{ app()->getLocale() }}/site/{{ $site->slug }}" class="text-xl font-bold text-gray-900 hover:text-indigo-600">
                {{ $site->t('brand.name', null, $site->slug) }}
            </a>
            @include('partials.lang-switcher')
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="border-t border-gray-200 bg-gray-50 mt-16">
        <div class="max-w-6xl mx-auto px-4 py-8 text-center text-sm text-gray-600">
            &copy; {{ date('Y') }} {{ $site->t('brand.name', null, $site->slug) }}
        </div>
    </footer>
</body>
</html>
