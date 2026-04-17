---
name: cms-block-builder
status: backlog
created: 2026-04-16T00:00:00Z
updated: 2026-04-17T04:01:57Z
progress: 0%
prd: .claude/prds/cms-block-builder.md
github: https://github.com/duc95bg/cms-nvd/issues/40
---

# Epic: cms-block-builder

## Overview
Thay thế form JSON cũ bằng block editor kéo thả (Alpine.js + Sortable.js CDN). Admin chọn theme → kéo thả sắp xếp block → chỉnh nội dung inline → publish. Public renderer đọc `sites.blocks` JSON rồi render chuỗi Blade partials `blocks/{type}.blade.php`. Backward compatible — site cũ vẫn render từ `content` nếu `blocks` null. Thêm settings system cho logo/favicon/contact.

## Architecture Decisions

1. **Block = JSON array trong `sites.blocks`.** Mỗi block: `{id, type, order, content}`. Content multi-lang. Array order = render order. Không dùng table riêng cho blocks — 1 JSON column đủ cho single-tenant, < 50KB.

2. **Theme = `blocks_preset` JSON.** Clone vào `sites.blocks` khi tạo site. Theme là template layout, không phải Blade file — 1 record DB với preset data.

3. **Alpine.js + Sortable.js via CDN.** Không npm, không build. Script load runtime trên editor page. Sortable handles drag, Alpine handles state + form binding. SPA-like experience trong 1 trang admin.

4. **Block type registry = `config/blocks.php`.** Define label, icon, fields schema, default_content cho mỗi type. Form editor auto-generate từ schema. Thêm block type mới = thêm 1 entry config + 1 Blade partial.

5. **Block rendering = Blade partial loop.** `@foreach ($blocks as $block) @include("blocks.{$block['type']}", ['block' => $block['content']]) @endforeach`. View exists check → skip unknown types gracefully.

6. **Backward compatible render.** `SiteController@show`: if `$site->blocks` not null → render blocks. Else → render old `$site->template->view` with `$site->content`. Zero migration cho existing sites.

7. **Settings = key-value table.** `SettingService::get('logo')`, `::set('site_name', [...])`. Simple, no cache file, shared hosting safe. Header/footer blocks read settings directly.

8. **Products block = realtime catalog query.** Block content stores `category_id` + `count`. Render partial queries `Product::active()->where('category_id', ...)->limit($count)` at render time — always fresh data.

9. **Image upload in blocks = reuse existing path.** `storage/app/public/sites/{site_id}/` — same as `cms-landing-builder`. Upload via AJAX from editor, return URL, store in block content.

10. **No block nesting.** Blocks are flat, full-width. Complex layouts via internal items (features items, gallery items). Column/grid layouts out of scope.

## Technical Approach

### Backend — Models & Services
- `Theme` model: `name` (json), `slug`, `description` (json), `thumbnail`, `blocks_preset` (json cast array).
- `Setting` model: `key` (unique), `value` (json cast).
- `SettingService`: static `get(key, default)`, `set(key, value)`, `all()`.
- `BlockRenderer` service: `render(array $blocks): string` — loops blocks, renders partials.
- Modify `Site` model: add `blocks` (json cast) and `theme_id` (FK nullable).

### Backend — Controllers
- `BlockEditorController`: `edit(Site)` shows editor, `update(Request, Site)` saves blocks JSON (AJAX PUT), `uploadImage(Request, Site)` handles block image uploads.
- `SettingsController`: `edit()` shows settings form, `update(Request)` saves settings.
- Modify `SiteController@show`: check blocks first, fallback to content.
- Modify `Admin\SiteController@create/store`: add theme selection, clone blocks_preset.

### Frontend — Block Editor
- Single Blade page: `admin/sites/editor.blade.php`.
- Alpine.js component `blockEditor()`:
  - State: `blocks` (array from server), `activeBlockId` (expanded), `blockTypes` (from config).
  - Methods: `addBlock(type)`, `removeBlock(id)`, `toggleBlock(id)`, `save()` (PUT JSON to server).
- Sortable.js: init on blocks container, `onEnd` updates `blocks` array order.
- Block palette: grid of block types with icon + label, click to add.
- Block card: drag handle + type label + content preview + expand/collapse + delete.
- Expanded block: type-specific form fields (auto-generated from `blockTypes[type].fields`).
- Multi-lang inputs: 2 columns (VI | EN) per text field.
- List fields (features items, gallery images): add/remove rows inline.

### Frontend — Block Partials (Public Render)
11 partials at `resources/views/blocks/`:
- `hero.blade.php` — full-width banner with title, subtitle, CTA, background image
- `text.blade.php` — heading + body paragraph
- `image.blade.php` — single image with alt + caption
- `gallery.blade.php` — responsive image grid
- `features.blade.php` — heading + 3-col feature cards with icon
- `products.blade.php` — heading + product cards grid (query catalog)
- `about.blade.php` — 2-col: image + text
- `contact.blade.php` — heading + contact info (email, phone, address)
- `cta.blade.php` — colored section with title, description, button
- `spacer.blade.php` — vertical space (configurable height)
- `html.blade.php` — raw HTML output

All partials use Tailwind, responsive, read locale via `app()->getLocale()`.

### Infrastructure
- 2 new migrations: `themes`, `settings`.
- 1 alter migration: add `blocks` + `theme_id` to `sites`.
- `config/blocks.php` — block type registry.
- Theme seeder: 3 themes with realistic blocks_preset.
- Settings seeder: defaults for site_name, contact info.

## Implementation Strategy

**Phase 1 — Data layer (task 1):** Migrations + models + config/blocks.php + SettingService.

**Phase 2 — Block partials + renderer (task 2-3):**
- Task 2: 11 block Blade partials.
- Task 3: BlockRenderer service + modify SiteController@show for dual render.

**Phase 3 — Block editor UI (task 4, largest):**
- Task 4: Alpine.js + Sortable.js editor page + BlockEditorController.

**Phase 4 — Theme + settings + site create flow (task 5-6):**
- Task 5: Theme seeder + modify site create to include theme selection.
- Task 6: Settings CRUD (controller + view).

**Phase 5 — Routes + lang + tests (task 7-9):**
- Task 7: Wire routes.
- Task 8: Lang keys.
- Task 9: Feature tests.

## Task Breakdown Preview

1. **Data layer** — migrations (themes, settings, alter sites), models (Theme, Setting), SettingService, config/blocks.php, Site model update (S, 2h)
2. **Block Blade partials** — 11 partials at resources/views/blocks/ (M, 2.5h)
3. **Block renderer + SiteController update** — BlockRenderer service, modify show() for blocks-first render (S, 1h)
4. **Block editor UI** — BlockEditorController + Alpine.js/Sortable.js editor page (L, 5h)
5. **Theme system + site create update** — ThemeSeeder + SettingsSeeder + modify admin site create/store (M, 2h)
6. **Settings CRUD** — SettingsController + admin settings view (S, 1.5h)
7. **Wire routes** — new routes for editor + settings (XS, 0.5h)
8. **Lang keys** — all __() keys (XS, 0.5h)
9. **Feature tests** — block CRUD, render, settings, theme clone (M, 2.5h)

**Parallelization:**
- Task 1 unblocks all.
- Tasks 2, 5, 6, 8 can run in parallel after 1.
- Task 3 depends on 2.
- Task 4 depends on 1 (uses config/blocks.php + models).
- Task 7 depends on 3, 4, 6.
- Task 9 depends on all.

## Dependencies

- **Shipped:** `cms-landing-builder` (Site model, SiteController, admin sites routes, templates layout).
- **Shipped:** `ecommerce-catalog` (Product/Category — used by `products` block type).
- **External CDN:** Alpine.js v3 (`cdn.jsdelivr.net`), Sortable.js v1.15 (`cdn.jsdelivr.net`).

## Success Criteria (Technical)

- Admin creates site with "landing-product" theme → editor shows 5 preset blocks → drags to reorder → saves → public page reflects order.
- Admin adds "products" block → selects category → public renders real products.
- Admin uploads logo in settings → header block shows logo on all sites.
- Existing sites (blocks=null) still render from old template+content.
- `config('blocks.hero')` returns schema with label, icon, fields, default_content.
- Feature tests pass, no regression on existing 56+ tests.

## Estimated Effort

- **Serial:** ~17.5h (~2.5 days).
- **Parallel:** ~9h wall clock (~1 day).
- **Critical path:** Task 1 → Task 4 (editor, largest) → Task 7 → Task 9.

## Tasks Created
- [ ] 001.md - Data layer: migrations, models, config/blocks.php, SettingService (parallel: true)
- [ ] 002.md - Block Blade partials (11 files) (parallel: true)
- [ ] 003.md - BlockRenderer service + SiteController update (parallel: false)
- [ ] 004.md - Block editor UI — Alpine.js + Sortable.js (parallel: true)
- [ ] 005.md - Theme system + site create update (parallel: true)
- [ ] 006.md - Settings CRUD (parallel: true)
- [ ] 007.md - Wire routes (parallel: false)
- [ ] 008.md - Lang keys (parallel: true)
- [ ] 009.md - Feature tests (parallel: false)

Total tasks: 9
Parallel groups:
  - Phase 1: 001 (unblocks all)
  - Phase 2: 002, 004, 005, 006, 008 (5 parallel after 001)
  - Phase 3: 003 (serial on 002)
  - Phase 4: 007 (serial on 003 + 004 + 006)
  - Phase 5: 009 (serial on all)
Estimated total effort: ~17.5h serial, ~9h wall with parallel execution
