<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Settings') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-3xl mx-auto p-8">
        <h1 class="text-3xl font-bold mb-8">{{ __('Site settings') }}</h1>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf

            {{-- Branding --}}
            <div class="bg-white rounded-xl border p-6 space-y-5">
                <h2 class="text-xl font-semibold">{{ __('Branding') }}</h2>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium mb-1">{{ __('Logo') }}</label>
                        @if(!empty($settings['logo']))
                            <img src="{{ $settings['logo'] }}" alt="Logo" class="h-12 mb-2">
                        @endif
                        <input type="file" name="logo" accept="image/*" class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block font-medium mb-1">{{ __('Favicon') }}</label>
                        @if(!empty($settings['favicon']))
                            <img src="{{ $settings['favicon'] }}" alt="Favicon" class="h-8 mb-2">
                        @endif
                        <input type="file" name="favicon" accept="image/*" class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium mb-1">{{ __('Site name') }} (VI)</label>
                        <input type="text" name="site_name[vi]" value="{{ data_get($settings, 'site_name.vi', '') }}"
                               class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block font-medium mb-1">{{ __('Site name') }} (EN)</label>
                        <input type="text" name="site_name[en]" value="{{ data_get($settings, 'site_name.en', '') }}"
                               class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium mb-1">{{ __('Tagline') }} (VI)</label>
                        <input type="text" name="tagline[vi]" value="{{ data_get($settings, 'tagline.vi', '') }}"
                               class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block font-medium mb-1">{{ __('Tagline') }} (EN)</label>
                        <input type="text" name="tagline[en]" value="{{ data_get($settings, 'tagline.en', '') }}"
                               class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>
            </div>

            {{-- Contact --}}
            <div class="bg-white rounded-xl border p-6 space-y-5">
                <h2 class="text-xl font-semibold">{{ __('Contact') }}</h2>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium mb-1">{{ __('Email') }}</label>
                        <input type="email" name="email" value="{{ $settings['email'] ?? '' }}"
                               class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block font-medium mb-1">{{ __('Phone') }}</label>
                        <input type="text" name="phone" value="{{ $settings['phone'] ?? '' }}"
                               class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium mb-1">{{ __('Shipping address') }} (VI)</label>
                        <textarea name="address[vi]" rows="2" class="w-full border rounded-lg px-3 py-2">{{ data_get($settings, 'address.vi', '') }}</textarea>
                    </div>
                    <div>
                        <label class="block font-medium mb-1">{{ __('Shipping address') }} (EN)</label>
                        <textarea name="address[en]" rows="2" class="w-full border rounded-lg px-3 py-2">{{ data_get($settings, 'address.en', '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Social --}}
            <div class="bg-white rounded-xl border p-6 space-y-5">
                <h2 class="text-xl font-semibold">{{ __('Social links') }}</h2>
                @foreach(['facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'tiktok' => 'TikTok'] as $key => $label)
                    <div>
                        <label class="block font-medium mb-1">{{ $label }}</label>
                        <input type="url" name="social_{{ $key }}" value="{{ $settings["social_{$key}"] ?? '' }}"
                               placeholder="https://" class="w-full border rounded-lg px-3 py-2">
                    </div>
                @endforeach
            </div>

            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                {{ __('Save') }}
            </button>
        </form>
    </div>
</body>
</html>
