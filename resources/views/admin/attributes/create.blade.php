<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Add attribute') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-3xl mx-auto p-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold">{{ __('Add attribute') }}</h1>
            <a href="{{ route('admin.attributes.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-100">{{ __('Back') }}</a>
        </div>

        <form method="POST" action="{{ route('admin.attributes.store') }}" class="bg-white rounded-xl border p-8 space-y-6">
            @csrf

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium mb-1">{{ __('Name') }} (VI) *</label>
                    <input type="text" name="name[vi]" value="{{ old('name.vi') }}" required class="w-full border rounded-lg px-3 py-2">
                    @error('name.vi')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block font-medium mb-1">{{ __('Name') }} (EN) *</label>
                    <input type="text" name="name[en]" value="{{ old('name.en') }}" required class="w-full border rounded-lg px-3 py-2">
                    @error('name.en')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block font-medium mb-1">{{ __('Type') }}</label>
                <select name="type" class="w-full border rounded-lg px-3 py-2">
                    <option value="select">{{ __('Select') }}</option>
                    <option value="color">{{ __('Color') }}</option>
                    <option value="text">{{ __('Text') }}</option>
                </select>
            </div>

            <div>
                <label class="block font-medium mb-2">{{ __('Values') }}</label>
                <div id="values-container"></div>
                <button type="button" onclick="addValueRow()"
                        class="mt-2 px-4 py-2 border border-dashed rounded-lg text-blue-600 hover:bg-blue-50">
                    + {{ __('Add value') }}
                </button>
                @error('values')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">{{ __('Save') }}</button>
        </form>
    </div>

    <template id="value-row-template">
        <div class="value-row flex gap-2 items-center mb-2">
            <input type="hidden" name="values[__INDEX__][id]" value="">
            <input type="text" name="values[__INDEX__][vi]" placeholder="VI *" required class="border rounded-lg px-3 py-2 flex-1">
            <input type="text" name="values[__INDEX__][en]" placeholder="EN *" required class="border rounded-lg px-3 py-2 flex-1">
            <input type="number" name="values[__INDEX__][sort_order]" value="0" class="border rounded-lg px-3 py-2 w-20" title="{{ __('Sort order') }}">
            <button type="button" onclick="removeValueRow(this)" class="text-red-600 hover:text-red-800 px-2 text-lg font-bold">✕</button>
        </div>
    </template>

    <script>
        let valueIndex = 0;
        function addValueRow() {
            const template = document.getElementById('value-row-template');
            const clone = template.content.cloneNode(true);
            clone.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace('__INDEX__', valueIndex);
            });
            document.getElementById('values-container').appendChild(clone);
            valueIndex++;
        }
        function removeValueRow(btn) {
            btn.closest('.value-row').remove();
        }
        // Start with one empty row
        addValueRow();
    </script>
</body>
</html>
