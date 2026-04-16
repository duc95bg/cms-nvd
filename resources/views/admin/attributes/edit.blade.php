<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Edit attribute') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-3xl mx-auto p-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold">{{ __('Edit attribute') }} — {{ $attribute->t('name', 'vi') }}</h1>
            <a href="{{ route('admin.attributes.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-100">{{ __('Back') }}</a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.attributes.update', $attribute) }}" class="bg-white rounded-xl border p-8 space-y-6">
            @csrf @method('PUT')

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium mb-1">{{ __('Name') }} (VI) *</label>
                    <input type="text" name="name[vi]" value="{{ old('name.vi', $attribute->name['vi'] ?? '') }}" required class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium mb-1">{{ __('Name') }} (EN) *</label>
                    <input type="text" name="name[en]" value="{{ old('name.en', $attribute->name['en'] ?? '') }}" required class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block font-medium mb-1">{{ __('Type') }}</label>
                <select name="type" class="w-full border rounded-lg px-3 py-2">
                    <option value="select" {{ $attribute->type === 'select' ? 'selected' : '' }}>{{ __('Select') }}</option>
                    <option value="color" {{ $attribute->type === 'color' ? 'selected' : '' }}>{{ __('Color') }}</option>
                    <option value="text" {{ $attribute->type === 'text' ? 'selected' : '' }}>{{ __('Text') }}</option>
                </select>
            </div>

            <div>
                <label class="block font-medium mb-2">{{ __('Values') }}</label>
                <div id="values-container">
                    @foreach ($attribute->values as $i => $val)
                        <div class="value-row flex gap-2 items-center mb-2">
                            <input type="hidden" name="values[{{ $i }}][id]" value="{{ $val->id }}">
                            <input type="text" name="values[{{ $i }}][vi]" value="{{ $val->value['vi'] ?? '' }}" placeholder="VI *" required class="border rounded-lg px-3 py-2 flex-1">
                            <input type="text" name="values[{{ $i }}][en]" value="{{ $val->value['en'] ?? '' }}" placeholder="EN *" required class="border rounded-lg px-3 py-2 flex-1">
                            <input type="number" name="values[{{ $i }}][sort_order]" value="{{ $val->sort_order }}" class="border rounded-lg px-3 py-2 w-20">
                            <button type="button" onclick="removeValueRow(this)" class="text-red-600 hover:text-red-800 px-2 text-lg font-bold">✕</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" onclick="addValueRow()"
                        class="mt-2 px-4 py-2 border border-dashed rounded-lg text-blue-600 hover:bg-blue-50">
                    + {{ __('Add value') }}
                </button>
            </div>

            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">{{ __('Save') }}</button>
        </form>
    </div>

    <template id="value-row-template">
        <div class="value-row flex gap-2 items-center mb-2">
            <input type="hidden" name="values[__INDEX__][id]" value="">
            <input type="text" name="values[__INDEX__][vi]" placeholder="VI *" required class="border rounded-lg px-3 py-2 flex-1">
            <input type="text" name="values[__INDEX__][en]" placeholder="EN *" required class="border rounded-lg px-3 py-2 flex-1">
            <input type="number" name="values[__INDEX__][sort_order]" value="0" class="border rounded-lg px-3 py-2 w-20">
            <button type="button" onclick="removeValueRow(this)" class="text-red-600 hover:text-red-800 px-2 text-lg font-bold">✕</button>
        </div>
    </template>

    <script>
        let valueIndex = {{ count($attribute->values) }};
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
    </script>
</body>
</html>
