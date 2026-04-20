<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Create site') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    @include('admin.partials.nav')
    <div class="max-w-2xl mx-auto py-10 px-4">
        <div class="bg-white shadow rounded-lg p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ __('Create a new site') }}</h1>
            <p class="text-sm text-gray-600 mb-6">{{ __('Create site') }}</p>

            <form method="POST" action="{{ route('admin.sites.store') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="template_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Template') }}</label>
                    {{-- Theme selection (block-based) --}}
                    @if(isset($themes) && $themes->isNotEmpty())
                        <h3 class="font-semibold mb-2">{{ __('Choose a theme') }} ({{ __('Block editor') }})</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                            @foreach ($themes as $theme)
                                <label class="border rounded-lg p-4 cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition"
                                       :class="{'ring-2 ring-blue-500': false}">
                                    <input type="radio" name="theme_id" value="{{ $theme->id }}" class="mr-2"
                                           onchange="document.getElementById('template_id').removeAttribute('required')">
                                    <span class="font-medium">{{ $theme->t('name') }}</span>
                                    <p class="text-xs text-gray-500 mt-1">{{ $theme->t('description') }}</p>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-400 mb-4">— {{ __('or') }} —</p>
                    @endif

                    {{-- Legacy template selection --}}
                    <h3 class="font-semibold mb-2">{{ __('Template') }} ({{ __('Legacy') }})</h3>
                    <select
                        id="template_id"
                        name="template_id"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border"
                    >
                        <option value="">{{ __('Select') }}</option>
                        @foreach ($templates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }} ({{ $template->type }})</option>
                        @endforeach
                    </select>
                    @error('template_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('theme_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Slug') }}</label>
                    <input
                        type="text"
                        id="slug"
                        name="slug"
                        required
                        maxlength="80"
                        pattern="[a-z0-9\-]+"
                        value="{{ old('slug') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border"
                    >
                    <p class="mt-1 text-xs text-gray-500">{{ __('Lowercase letters, numbers and dashes only.') }}</p>
                    @error('slug')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4">
                    <button
                        type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        {{ __('Create site') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
