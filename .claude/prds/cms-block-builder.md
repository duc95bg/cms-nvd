---
name: cms-block-builder
description: Drag-and-drop block editor for site pages — admin configures logo, banner, about, product showcase, and custom blocks via Alpine.js + Sortable.js
status: backlog
created: 2026-04-16T00:00:00Z
---

# PRD: cms-block-builder

## Executive Summary
Thay thế form JSON hiện tại của `cms-landing-builder` bằng block editor kéo thả thật sự. Admin chọn theme (bộ block preset), kéo thả sắp xếp vị trí các block (hero banner, logo, about, product showcase, text, image gallery, contact form, footer...), chỉnh nội dung từng block qua form inline. Mỗi lần deploy là install 1 layout khác nhau — theme = preset blocks JSON. Dùng Alpine.js + Sortable.js qua CDN, chạy được trên shared hosting, không thêm Composer package.

## Problem Statement
Hệ thống `cms-landing-builder` hiện tại chỉ cho phép edit content qua form JSON phẳng — không kéo thả, không thay đổi thứ tự block, không thêm/xóa block. Admin muốn dàn trang linh hoạt kiểu NukeViet: chọn theme → kéo thả block → chỉnh nội dung → publish. Hiện tại mỗi template là 1 Blade file cứng — cần chuyển sang render động từ JSON block list.

## User Stories

### US-1 — Chọn theme khi tạo site
**As** admin
**I want** chọn một theme (bộ block preset) khi tạo site mới
**So that** site được khởi tạo với layout phù hợp mà tôi chỉ cần chỉnh nội dung.

**Acceptance criteria:**
- Danh sách theme hiển thị tại `/admin/sites/create` với preview thumbnail.
- Mỗi theme = 1 record trong `themes` table với `blocks_preset` JSON (danh sách block type + default content + order).
- Khi tạo site, `sites.blocks` được clone từ `themes.blocks_preset`.
- Ship ít nhất 3 themes: `landing-product` (hero + features + CTA), `landing-service` (hero + about + services + contact), `landing-blog` (header + posts grid + footer).

### US-2 — Kéo thả sắp xếp block
**As** admin
**I want** kéo thả để thay đổi thứ tự các block trên trang
**So that** tôi dàn layout theo ý mà không cần code.

**Acceptance criteria:**
- Trang edit site hiển thị danh sách block dạng sortable list.
- Kéo thả bằng Sortable.js (CDN) — smooth, touch-friendly.
- Thứ tự mới lưu vào `sites.blocks` JSON mỗi khi save.
- Block có handle kéo, label type, preview nội dung rút gọn.

### US-3 — Thêm / xóa block
**As** admin
**I want** thêm block mới từ palette và xóa block không cần
**So that** tôi customize layout ngoài preset.

**Acceptance criteria:**
- Nút "Thêm block" mở palette hiển thị tất cả block types có sẵn.
- Click block type → block mới được thêm cuối danh sách với default content.
- Mỗi block có nút xóa (confirm trước khi xóa).
- Block types: `hero`, `text`, `image`, `gallery`, `features`, `products`, `about`, `contact`, `cta`, `spacer`, `html`.

### US-4 — Chỉnh nội dung block inline
**As** admin
**I want** click vào block để mở form chỉnh nội dung (tiêu đề, text, ảnh, link...) ngay trong editor
**So that** tôi thấy thay đổi trước khi save.

**Acceptance criteria:**
- Click block → expand form editor bên dưới block (accordion-style, Alpine.js).
- Form fields tùy theo block type:
  - `hero`: title (vi/en), subtitle (vi/en), cta_label (vi/en), cta_url, background_image
  - `text`: heading (vi/en), body (vi/en, textarea)
  - `image`: src (upload/URL), alt (vi/en), caption (vi/en)
  - `gallery`: list of images [{src, alt}] — add/remove inline
  - `features`: heading (vi/en), items [{icon, title vi/en, body vi/en}] — add/remove
  - `products`: heading (vi/en), category_id (select), count (number) — renders products from catalog
  - `about`: title (vi/en), body (vi/en), image
  - `contact`: heading (vi/en), email, phone, address (vi/en)
  - `cta`: title (vi/en), description (vi/en), button_label (vi/en), button_url
  - `spacer`: height (number px)
  - `html`: raw HTML content
- Tất cả text fields hỗ trợ multi-language (vi/en input cạnh nhau).
- Sau khi chỉnh xong → click Save toàn bộ page → lưu `sites.blocks` JSON.

### US-5 — Render public site từ blocks
**As** khách truy cập
**I want** xem trang landing page đẹp với layout đúng thứ tự block admin đã dàn
**So that** tôi có trải nghiệm tốt.

**Acceptance criteria:**
- URL: `/{locale}/site/{slug}` (reuse route hiện có từ `cms-landing-builder`).
- Renderer đọc `sites.blocks` JSON, loop qua từng block, render Blade partial tương ứng: `blocks/{type}.blade.php`.
- Mỗi block partial nhận `$block` (content data) và render theo locale hiện tại.
- Block `products` query catalog realtime (products theo category, limit count).
- Responsive (Tailwind), đẹp trên mobile.

### US-6 — Setup ban đầu (logo, thông tin chung)
**As** admin
**I want** cấu hình logo, favicon, site name, contact info chung cho toàn site
**So that** header/footer tự động hiển thị đúng.

**Acceptance criteria:**
- Settings page tại `/admin/settings` lưu: logo (upload), favicon (upload), site_name (vi/en), tagline (vi/en), email, phone, address (vi/en), social links (facebook, instagram, youtube, tiktok).
- Settings lưu trong `settings` table (key-value) hoặc 1 JSON file.
- Header/footer blocks tự đọc settings: logo, site name, contact info, social links.
- Settings seed với defaults khi `db:seed`.

## Functional Requirements

### FR-1 — Database changes

```
themes
  id, name (json vi/en), slug (unique), description (json vi/en),
  thumbnail (nullable), blocks_preset (json), status (active/inactive),
  timestamps

-- Modify existing sites table:
ALTER TABLE sites ADD COLUMN blocks JSON NULLABLE AFTER content;
ALTER TABLE sites ADD COLUMN theme_id NULLABLE REFERENCES themes(id);

settings
  id, key (unique string), value (json), timestamps
```

### FR-2 — Block data structure
```json
// sites.blocks = ordered array of block objects
[
  {
    "id": "block_abc123",
    "type": "hero",
    "order": 0,
    "content": {
      "title": {"vi": "...", "en": "..."},
      "subtitle": {"vi": "...", "en": "..."},
      "cta_label": {"vi": "...", "en": "..."},
      "cta_url": "#",
      "background_image": "/storage/..."
    }
  },
  {
    "id": "block_def456",
    "type": "features",
    "order": 1,
    "content": {
      "heading": {"vi": "...", "en": "..."},
      "items": [
        {"icon": "🚀", "title": {"vi":"...", "en":"..."}, "body": {"vi":"...", "en":"..."}}
      ]
    }
  }
]
```
- `id`: unique string per block, generated client-side (`'block_' + Math.random().toString(36).substr(2, 9)`).
- `type`: matches a Blade partial filename at `resources/views/blocks/{type}.blade.php`.
- `order`: integer, determined by position in array (Sortable.js updates this).
- `content`: type-specific data, all text fields as `{vi, en}` objects.

### FR-3 — Block type registry
```php
// config/blocks.php
return [
    'hero' => ['label' => ['vi' => 'Banner chính', 'en' => 'Hero Banner'], 'icon' => '🖼️', 'fields' => [...]],
    'text' => ['label' => ['vi' => 'Văn bản', 'en' => 'Text'], 'icon' => '📝', 'fields' => [...]],
    'image' => [...],
    'gallery' => [...],
    'features' => [...],
    'products' => [...],
    'about' => [...],
    'contact' => [...],
    'cta' => [...],
    'spacer' => [...],
    'html' => [...],
];
```
Mỗi entry define: label (multi-lang), icon (emoji), fields (schema cho form generation), default_content (clone khi thêm block mới).

### FR-4 — Block editor UI (Alpine.js + Sortable.js)
- Editor page: `/admin/sites/{site}/editor`
- Alpine.js component `x-data="blockEditor()"` manages state: `blocks` array, `activeBlock` (expanded), methods `addBlock(type)`, `removeBlock(id)`, `moveBlock(oldIndex, newIndex)`, `save()`.
- Sortable.js initialized on the blocks list container: `Sortable.create(el, { handle: '.drag-handle', onEnd: ... })`.
- Save = POST `sites.blocks` JSON to server.
- No page reload during editing — SPA-like experience within the editor.

### FR-5 — Block rendering engine
```php
// In SiteController@show or a dedicated BlockRenderer service
public function renderBlocks(array $blocks): string {
    $html = '';
    foreach ($blocks as $block) {
        $view = "blocks.{$block['type']}";
        if (view()->exists($view)) {
            $html .= view($view, ['block' => $block['content']])->render();
        }
    }
    return $html;
}
```
- Public site render: if `sites.blocks` is not null, render from blocks. If null, fall back to old template-based render (backward compatible).
- Each block partial: `resources/views/blocks/{type}.blade.php` — receives `$block` (content array), uses `data_get($block, 'title.'.app()->getLocale())` pattern.

### FR-6 — Settings service
```php
class SettingService {
    public static function get(string $key, mixed $default = null): mixed
    public static function set(string $key, mixed $value): void
    public static function all(): array
}
```
- Cached in memory per request. No file cache (shared hosting simplicity).
- Keys: `site_name`, `tagline`, `logo`, `favicon`, `email`, `phone`, `address`, `social_facebook`, `social_instagram`, `social_youtube`, `social_tiktok`.

### FR-7 — Routing
Admin:
- `GET /admin/sites/{site}/editor` → BlockEditorController@edit
- `PUT /admin/sites/{site}/blocks` → BlockEditorController@update (AJAX, receives JSON)
- `POST /admin/sites/{site}/blocks/upload` → BlockEditorController@uploadImage (block images)
- `GET /admin/settings` → SettingsController@edit
- `POST /admin/settings` → SettingsController@update

Public (existing, modified):
- `GET /{locale}/site/{slug}` → SiteController@show (modified to render blocks if present)

### FR-8 — Theme seeder
Seed 3 themes with realistic `blocks_preset`:
1. **landing-product**: hero (product image BG) → features (3 items) → products (from catalog) → CTA → contact
2. **landing-service**: hero → about (company info) → features (services) → contact → CTA
3. **landing-blog**: hero → text (intro) → gallery → CTA

## Non-Functional Requirements

- **No Composer packages.** Alpine.js + Sortable.js via CDN only.
- **Shared hosting compatible.** No Node server, no build step for the editor. CDN scripts load at runtime.
- **Backward compatible.** Sites created before this epic (with `content` JSON) continue to work — renderer falls back to template if `blocks` is null.
- **Touch-friendly.** Sortable.js supports touch by default — verify on mobile viewport.
- **Performance.** Block list JSON typically < 50KB. No lazy-loading needed.
- **Test coverage.** Feature tests for block CRUD, render, settings CRUD. Unit test for block type registry.

## Success Criteria

1. Admin creates site with "landing-product" theme → editor shows preset blocks → drags "features" above "hero" → saves → public page reflects new order.
2. Admin adds a "products" block → selects category "Áo thun" → count 4 → save → public page shows 4 products from that category.
3. Admin uploads logo + sets site name in settings → all site headers show the logo.
4. Existing sites (created via old `cms-landing-builder` flow) still render correctly at `/{locale}/site/{slug}`.
5. Feature tests pass, no regression.

## Constraints & Assumptions

- Alpine.js v3 + Sortable.js v1.15 via CDN (unpkg/cdnjs). No npm/Vite integration for these.
- Block editor is admin-only — no public-facing editor.
- `html` block type renders raw HTML — admin responsibility for XSS. No sanitization (power user feature).
- Image uploads in blocks reuse the existing `storage/app/public/sites/{site_id}/` path.
- Settings are global (not per-site) — single-tenant model.
- No undo/redo in the editor (can be added later with history stack).
- No block nesting (no columns/rows layout) — blocks are flat, full-width. Grid layouts via the `features` block's internal items.

## Out of Scope

- Visual page builder (WYSIWYG inline editing on the rendered page).
- Block nesting / column layout editor.
- Undo/redo.
- Block animations/transitions config.
- Per-block visibility rules (show/hide on mobile).
- A/B testing for blocks.
- Block templates marketplace.
- Custom CSS per block.

## Dependencies

- **Shipped:** `cms-landing-builder` (Site model, SiteController@show, templates, Breeze auth, admin routes).
- **Shipped:** `ecommerce-catalog` (Product/Category models — used by `products` block type).
- **External CDN:** Alpine.js (`https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js`), Sortable.js (`https://cdn.jsdelivr.net/npm/sortablejs@1.15/Sortable.min.js`).
