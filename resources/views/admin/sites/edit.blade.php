@php
    $fields = \App\Support\FlattenContent::flatten($site->content, $locales);
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Edit site') }} — {{ $site->slug }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">
<div class="max-w-5xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">
            {{ __('Edit') }} <span class="font-mono text-base text-gray-600">{{ $site->slug }}</span>
        </h1>
        <div class="flex gap-3 text-sm">
            <a href="{{ route('admin.sites.preview', $site) }}" target="_blank"
               class="px-3 py-1.5 bg-white border border-gray-300 rounded hover:bg-gray-50">
                {{ __('Preview') }}
            </a>
            @if($site->published)
                <a href="/{{ app()->getLocale() }}/site/{{ $site->slug }}" target="_blank"
                   class="px-3 py-1.5 bg-white border border-gray-300 rounded hover:bg-gray-50">
                    {{ __('View live') }}
                </a>
            @endif
        </div>
    </div>

    @if(session('status'))
        <div class="mb-4 p-3 rounded bg-green-100 border border-green-300 text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.sites.update', $site) }}" class="space-y-5">
        @csrf
        @method('PUT')

        @foreach($fields as $key => $value)
            <div class="bg-white border border-gray-200 rounded p-4">
                @if(is_array($value) && !array_is_list($value))
                    {{-- Translatable leaf --}}
                    <label class="block font-mono text-xs text-gray-700 mb-2">{{ $key }}</label>
                    <div class="grid grid-cols-1 md:grid-cols-{{ max(1, count($locales)) }} gap-3">
                        @foreach($locales as $lang)
                            <div>
                                <div class="text-xs uppercase text-gray-500 mb-1">{{ $lang }}</div>
                                <input type="text"
                                       name="content[{{ $key }}][{{ $lang }}]"
                                       value="{{ $value[$lang] ?? '' }}"
                                       class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                            </div>
                        @endforeach
                    </div>
                @elseif(is_array($value))
                    {{-- Indexed list --}}
                    <label class="block font-mono text-xs text-gray-700 mb-2">{{ $key }}</label>
                    <textarea name="content[{{ $key }}]" rows="8"
                              class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs font-mono">{{ json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">{{ __('Raw JSON list — edit carefully.') }}</p>
                @else
                    {{-- Scalar --}}
                    <label class="block font-mono text-xs text-gray-700 mb-2">{{ $key }}</label>
                    <input type="text"
                           name="content[{{ $key }}]"
                           value="{{ $value }}"
                           class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                @endif
            </div>
        @endforeach

        <div class="bg-white border border-gray-200 rounded p-4">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="published" value="1" {{ $site->published ? 'checked' : '' }}>
                <span>{{ __('Published') }}</span>
            </label>
        </div>

        <div>
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                {{ __('Save') }}
            </button>
        </div>
    </form>
</div>
</body>
</html>
