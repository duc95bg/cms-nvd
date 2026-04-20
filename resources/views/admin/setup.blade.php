<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Site setup') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-2xl w-full mx-4">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900">🚀 {{ __('Welcome') }}</h1>
            <p class="text-gray-600 mt-2">{{ __('Set up your website in 2 steps') }}</p>
        </div>

        <form method="POST" action="{{ route('admin.setup.store') }}" class="bg-white rounded-2xl shadow-lg p-8 space-y-8">
            @csrf

            {{-- Step 1: Choose theme --}}
            <div>
                <h2 class="text-xl font-semibold mb-4">1. {{ __('Choose a theme') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach ($themes as $theme)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="theme_id" value="{{ $theme->id }}"
                                   class="peer sr-only" {{ $loop->first ? 'checked' : '' }}>
                            <div class="border-2 rounded-xl p-5 text-center transition
                                        peer-checked:border-blue-500 peer-checked:bg-blue-50
                                        hover:border-gray-300">
                                <div class="text-3xl mb-2">
                                    @if(str_contains($theme->slug, 'product')) 🛍️
                                    @elseif(str_contains($theme->slug, 'service')) 💼
                                    @else 📝
                                    @endif
                                </div>
                                <div class="font-semibold">{{ $theme->t('name') }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ $theme->t('description') }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('theme_id')<p class="text-red-600 text-sm mt-2">{{ $message }}</p>@enderror
            </div>

            {{-- Step 2: Site name --}}
            <div>
                <h2 class="text-xl font-semibold mb-4">2. {{ __('Site name') }}</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('Site name') }} (VI) *</label>
                        <input type="text" name="site_name_vi" required
                               value="{{ old('site_name_vi', 'Website của tôi') }}"
                               class="w-full border rounded-lg px-4 py-3">
                        @error('site_name_vi')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('Site name') }} (EN) *</label>
                        <input type="text" name="site_name_en" required
                               value="{{ old('site_name_en', 'My Website') }}"
                               class="w-full border rounded-lg px-4 py-3">
                        @error('site_name_en')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <button type="submit"
                    class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-lg transition">
                {{ __('Create my website') }} →
            </button>
        </form>
    </div>
</body>
</html>
