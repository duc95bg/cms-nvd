<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Block editor') }} — {{ $site->slug }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15/Sortable.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 min-h-screen" x-data="blockEditor()" x-init="initSortable()">
    @include('admin.partials.nav')

    {{-- Top bar --}}
    <header class="bg-white border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.sites.index') }}" class="text-gray-500 hover:text-gray-700">← {{ __('Back') }}</a>
                <h1 class="font-bold text-lg">{{ __('Block editor') }}: {{ $site->slug }}</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.sites.preview', $site) }}" target="_blank"
                   class="px-4 py-2 border rounded-lg text-sm hover:bg-gray-50">{{ __('Preview') }}</a>
                <button @click="save()" :disabled="saving"
                        class="px-5 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 disabled:opacity-50">
                    <span x-show="!saving">{{ __('Save') }}</span>
                    <span x-show="saving">{{ __('Saving...') }}</span>
                </button>
                <span x-show="saved" x-transition class="text-green-600 text-sm">✓ {{ __('Saved') }}</span>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-6 flex gap-6">

        {{-- Left: Block palette --}}
        <aside class="w-56 shrink-0">
            <h2 class="font-semibold mb-3 text-sm text-gray-600 uppercase">{{ __('Block palette') }}</h2>
            <div class="space-y-2">
                <template x-for="(bt, type) in blockTypes" :key="type">
                    <button @click="addBlock(type)"
                            class="w-full flex items-center gap-3 p-3 bg-white border rounded-lg hover:shadow-md hover:border-blue-300 transition text-left text-sm">
                        <span class="text-xl" x-text="bt.icon"></span>
                        <span x-text="bt.label['{{ app()->getLocale() }}'] || bt.label['en']"></span>
                    </button>
                </template>
            </div>
        </aside>

        {{-- Main: Blocks list --}}
        <main class="flex-1">
            <div x-show="blocks.length === 0" class="text-center py-16 bg-white rounded-xl border text-gray-500">
                <p class="text-lg mb-2">{{ __('No blocks yet') }}</p>
                <p class="text-sm">{{ __('Click a block type from the palette to add it') }}</p>
            </div>

            <div id="blocks-list" class="space-y-3">
                <template x-for="(block, index) in blocks" :key="block.id">
                    <div class="bg-white border rounded-xl overflow-hidden"
                         :class="activeBlockId === block.id ? 'ring-2 ring-blue-500' : ''">

                        {{-- Block header --}}
                        <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border-b cursor-pointer"
                             @click="toggleBlock(block.id)">
                            <span class="drag-handle cursor-grab text-gray-400 hover:text-gray-600 text-lg" title="{{ __('Drag to reorder') }}">≡</span>
                            <span class="text-lg" x-text="blockTypes[block.type]?.icon || '📦'"></span>
                            <span class="font-medium text-sm" x-text="blockTypes[block.type]?.label['{{ app()->getLocale() }}'] || block.type"></span>
                            <span class="text-xs text-gray-400 ml-auto" x-text="'#' + (index + 1)"></span>
                            <button @click.stop="removeBlock(block.id)"
                                    class="text-red-400 hover:text-red-600 text-sm px-2" title="{{ __('Remove block') }}">✕</button>
                        </div>

                        {{-- Block content form (expanded) --}}
                        <div x-show="activeBlockId === block.id" x-transition class="p-5 space-y-4">
                            <template x-for="field in (blockTypes[block.type]?.fields || [])" :key="field.name">
                                <div>
                                    {{-- text_i18n --}}
                                    <template x-if="field.type === 'text_i18n'">
                                        <div>
                                            <label class="block text-sm font-medium mb-1 capitalize" x-text="field.name.replace(/_/g, ' ')"></label>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <span class="text-xs text-gray-500">VI</span>
                                                    <input type="text" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"
                                                           :value="block.content?.[field.name]?.vi || ''"
                                                           @input="block.content[field.name] = {...(block.content[field.name] || {}), vi: $event.target.value}">
                                                </div>
                                                <div>
                                                    <span class="text-xs text-gray-500">EN</span>
                                                    <input type="text" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"
                                                           :value="block.content?.[field.name]?.en || ''"
                                                           @input="block.content[field.name] = {...(block.content[field.name] || {}), en: $event.target.value}">
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- textarea_i18n --}}
                                    <template x-if="field.type === 'textarea_i18n'">
                                        <div>
                                            <label class="block text-sm font-medium mb-1 capitalize" x-text="field.name.replace(/_/g, ' ')"></label>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <span class="text-xs text-gray-500">VI</span>
                                                    <textarea rows="3" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"
                                                              :value="block.content?.[field.name]?.vi || ''"
                                                              @input="block.content[field.name] = {...(block.content[field.name] || {}), vi: $event.target.value}"></textarea>
                                                </div>
                                                <div>
                                                    <span class="text-xs text-gray-500">EN</span>
                                                    <textarea rows="3" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"
                                                              :value="block.content?.[field.name]?.en || ''"
                                                              @input="block.content[field.name] = {...(block.content[field.name] || {}), en: $event.target.value}"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- text --}}
                                    <template x-if="field.type === 'text'">
                                        <div>
                                            <label class="block text-sm font-medium mb-1 capitalize" x-text="field.name.replace(/_/g, ' ')"></label>
                                            <input type="text" class="w-full border rounded-lg px-3 py-2 text-sm"
                                                   :value="block.content?.[field.name] || ''"
                                                   @input="block.content[field.name] = $event.target.value">
                                        </div>
                                    </template>

                                    {{-- textarea --}}
                                    <template x-if="field.type === 'textarea'">
                                        <div>
                                            <label class="block text-sm font-medium mb-1 capitalize" x-text="field.name.replace(/_/g, ' ')"></label>
                                            <textarea rows="5" class="w-full border rounded-lg px-3 py-2 text-sm font-mono"
                                                      :value="block.content?.[field.name] || ''"
                                                      @input="block.content[field.name] = $event.target.value"></textarea>
                                        </div>
                                    </template>

                                    {{-- number --}}
                                    <template x-if="field.type === 'number'">
                                        <div>
                                            <label class="block text-sm font-medium mb-1 capitalize" x-text="field.name.replace(/_/g, ' ')"></label>
                                            <input type="number" class="w-full border rounded-lg px-3 py-2 text-sm max-w-xs"
                                                   :value="block.content?.[field.name] || 0"
                                                   @input="block.content[field.name] = parseInt($event.target.value) || 0">
                                        </div>
                                    </template>

                                    {{-- image --}}
                                    <template x-if="field.type === 'image'">
                                        <div>
                                            <label class="block text-sm font-medium mb-1 capitalize" x-text="field.name.replace(/_/g, ' ')"></label>
                                            <div class="flex items-center gap-3">
                                                <img x-show="block.content?.[field.name]"
                                                     :src="block.content?.[field.name]"
                                                     class="h-16 w-16 object-cover rounded border">
                                                <input type="file" accept="image/*" class="text-sm"
                                                       @change="uploadBlockImage($event, block, field.name)">
                                            </div>
                                            <input type="text" class="w-full border rounded-lg px-3 py-2 text-sm mt-2"
                                                   placeholder="Or paste URL"
                                                   :value="block.content?.[field.name] || ''"
                                                   @input="block.content[field.name] = $event.target.value">
                                        </div>
                                    </template>

                                    {{-- select (for category_id in products block) --}}
                                    <template x-if="field.type === 'select'">
                                        <div>
                                            <label class="block text-sm font-medium mb-1 capitalize" x-text="field.name.replace(/_/g, ' ')"></label>
                                            <select class="w-full border rounded-lg px-3 py-2 text-sm"
                                                    @change="block.content[field.name] = $event.target.value ? parseInt($event.target.value) : null">
                                                <option value="">{{ __('Select a category') }}</option>
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat->id }}" :selected="block.content?.[field.name] == {{ $cat->id }}">
                                                        {{ $cat->t('name') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </template>

                                    {{-- repeater --}}
                                    <template x-if="field.type === 'repeater'">
                                        <div>
                                            <label class="block text-sm font-medium mb-2 capitalize" x-text="field.name.replace(/_/g, ' ')"></label>
                                            <div class="space-y-3">
                                                <template x-for="(item, itemIdx) in (block.content?.[field.name] || [])" :key="itemIdx">
                                                    <div class="border rounded-lg p-3 bg-gray-50 relative">
                                                        <button @click="block.content[field.name].splice(itemIdx, 1)"
                                                                class="absolute top-2 right-2 text-red-400 hover:text-red-600 text-xs">✕</button>
                                                        <template x-for="subField in field.fields" :key="subField.name">
                                                            <div class="mb-2">
                                                                <template x-if="subField.type === 'text_i18n'">
                                                                    <div class="grid grid-cols-2 gap-2">
                                                                        <input type="text" class="border rounded px-2 py-1 text-xs" placeholder="VI"
                                                                               :value="item?.[subField.name]?.vi || ''"
                                                                               @input="item[subField.name] = {...(item[subField.name] || {}), vi: $event.target.value}">
                                                                        <input type="text" class="border rounded px-2 py-1 text-xs" placeholder="EN"
                                                                               :value="item?.[subField.name]?.en || ''"
                                                                               @input="item[subField.name] = {...(item[subField.name] || {}), en: $event.target.value}">
                                                                    </div>
                                                                </template>
                                                                <template x-if="subField.type === 'textarea_i18n'">
                                                                    <div class="grid grid-cols-2 gap-2">
                                                                        <textarea rows="2" class="border rounded px-2 py-1 text-xs" placeholder="VI"
                                                                                  :value="item?.[subField.name]?.vi || ''"
                                                                                  @input="item[subField.name] = {...(item[subField.name] || {}), vi: $event.target.value}"></textarea>
                                                                        <textarea rows="2" class="border rounded px-2 py-1 text-xs" placeholder="EN"
                                                                                  :value="item?.[subField.name]?.en || ''"
                                                                                  @input="item[subField.name] = {...(item[subField.name] || {}), en: $event.target.value}"></textarea>
                                                                    </div>
                                                                </template>
                                                                <template x-if="subField.type === 'text'">
                                                                    <input type="text" class="w-full border rounded px-2 py-1 text-xs"
                                                                           :placeholder="subField.name"
                                                                           :value="item?.[subField.name] || ''"
                                                                           @input="item[subField.name] = $event.target.value">
                                                                </template>
                                                                <template x-if="subField.type === 'image'">
                                                                    <input type="text" class="w-full border rounded px-2 py-1 text-xs"
                                                                           placeholder="Image URL"
                                                                           :value="item?.[subField.name] || ''"
                                                                           @input="item[subField.name] = $event.target.value">
                                                                </template>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                            <button @click="addRepeaterItem(block, field)"
                                                    class="mt-2 px-3 py-1.5 border border-dashed rounded-lg text-blue-600 hover:bg-blue-50 text-xs">
                                                + {{ __('Add value') }}
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </main>
    </div>

    <script>
        function blockEditor() {
            return {
                blocks: {!! $blocksJson !!},
                blockTypes: {!! $blockTypesJson !!},
                activeBlockId: null,
                saving: false,
                saved: false,
                siteId: {{ $site->id }},

                initSortable() {
                    this.$nextTick(() => {
                        const el = document.getElementById('blocks-list');
                        if (!el) return;
                        const self = this;
                        Sortable.create(el, {
                            handle: '.drag-handle',
                            animation: 150,
                            ghostClass: 'opacity-30',
                            onEnd(evt) {
                                const item = self.blocks.splice(evt.oldIndex, 1)[0];
                                self.blocks.splice(evt.newIndex, 0, item);
                                self.blocks.forEach((b, i) => b.order = i);
                            }
                        });
                    });
                },

                addBlock(type) {
                    const bt = this.blockTypes[type];
                    if (!bt) return;
                    const id = 'block_' + Math.random().toString(36).substr(2, 9);
                    this.blocks.push({
                        id: id,
                        type: type,
                        order: this.blocks.length,
                        content: JSON.parse(JSON.stringify(bt.default_content))
                    });
                    this.activeBlockId = id;
                },

                removeBlock(id) {
                    if (!confirm('{{ __("Are you sure?") }}')) return;
                    this.blocks = this.blocks.filter(b => b.id !== id);
                    this.blocks.forEach((b, i) => b.order = i);
                    if (this.activeBlockId === id) this.activeBlockId = null;
                },

                toggleBlock(id) {
                    this.activeBlockId = this.activeBlockId === id ? null : id;
                },

                addRepeaterItem(block, field) {
                    if (!block.content[field.name]) block.content[field.name] = [];
                    const newItem = {};
                    (field.fields || []).forEach(sf => {
                        if (sf.type === 'text_i18n' || sf.type === 'textarea_i18n') {
                            newItem[sf.name] = {vi: '', en: ''};
                        } else {
                            newItem[sf.name] = '';
                        }
                    });
                    block.content[field.name].push(newItem);
                },

                async uploadBlockImage(event, block, fieldName) {
                    const file = event.target.files[0];
                    if (!file) return;
                    const formData = new FormData();
                    formData.append('image', file);
                    try {
                        const res = await fetch(`/admin/sites/${this.siteId}/blocks/upload`, {
                            method: 'POST',
                            body: formData,
                            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}
                        });
                        const data = await res.json();
                        if (data.url) {
                            block.content[fieldName] = data.url;
                        }
                    } catch (err) {
                        alert('{{ __("Upload failed") }}');
                    }
                },

                async save() {
                    this.saving = true;
                    this.saved = false;
                    try {
                        const res = await fetch(`/admin/sites/${this.siteId}/blocks`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                            },
                            body: JSON.stringify({blocks: this.blocks})
                        });
                        if (res.ok) {
                            this.saved = true;
                            setTimeout(() => this.saved = false, 3000);
                        }
                    } catch (err) {
                        alert('{{ __("Save failed") }}');
                    }
                    this.saving = false;
                }
            };
        }
    </script>
</body>
</html>
