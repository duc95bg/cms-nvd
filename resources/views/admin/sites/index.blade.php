<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Your sites') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    @include('admin.partials.nav')
    <div class="max-w-5xl mx-auto p-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold">{{ __('Your sites') }}</h1>
            <a href="{{ route('admin.sites.create') }}"
               class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                {{ __('Create site') }}
            </a>
        </div>

        @if ($sites->isEmpty())
            <div class="p-10 bg-white rounded-xl border text-center text-gray-500">
                {{ __('No sites yet.') }}
                <a href="{{ route('admin.sites.create') }}" class="text-blue-600 underline">
                    {{ __('Create your first one') }}
                </a>.
            </div>
        @else
            <div class="bg-white rounded-xl border divide-y">
                @foreach ($sites as $site)
                    <div class="p-5 flex items-center justify-between">
                        <div>
                            <div class="font-semibold">{{ $site->slug }}</div>
                            <div class="text-sm text-gray-500">
                                {{ $site->template->name ?? '—' }}
                                · {{ $site->published ? __('Published') : __('Draft') }}
                                · {{ $site->updated_at->diffForHumans() }}
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.sites.edit', $site) }}"
                               class="px-4 py-2 border rounded-lg hover:bg-gray-50">{{ __('Edit') }}</a>
                            <a href="{{ route('admin.sites.preview', $site) }}" target="_blank"
                               class="px-4 py-2 border rounded-lg hover:bg-gray-50">{{ __('Preview') }}</a>
                            @if ($site->published)
                                <a href="/{{ app()->getLocale() }}/site/{{ $site->slug }}" target="_blank"
                                   class="px-4 py-2 border rounded-lg hover:bg-gray-50">{{ __('View live') }}</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $sites->links() }}
            </div>
        @endif
    </div>
</body>
</html>
