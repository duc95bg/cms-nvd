<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('description', '')">
</head>
<body class="bg-gray-50 text-gray-900 antialiased min-h-screen flex flex-col">
    <header class="border-b bg-white">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="/{{ app()->getLocale() }}/products" class="text-xl font-bold hover:text-blue-600">
                {{ config('app.name') }}
            </a>
            <nav class="flex items-center gap-4">
                <a href="/{{ app()->getLocale() }}/products" class="text-sm hover:text-blue-600">{{ __('Products') }}</a>
                @include('partials.lang-switcher')
            </nav>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t bg-white mt-auto">
        <div class="max-w-6xl mx-auto px-4 py-6 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} {{ config('app.name') }}
        </div>
    </footer>
</body>
</html>
