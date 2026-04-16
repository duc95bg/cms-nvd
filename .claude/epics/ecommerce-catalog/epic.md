---
name: ecommerce-catalog
status: backlog
created: 2026-04-16T00:00:00Z
updated: 2026-04-16T06:50:37Z
progress: 0%
prd: .claude/prds/ecommerce-catalog.md
github: https://github.com/duc95bg/cms-nvd/issues/17
---

# Epic: ecommerce-catalog

## Overview
Xây dựng hệ thống catalog sản phẩm hoàn chỉnh cho CMS e-commerce single-tenant. Gồm 7 database tables mới, trait localization dùng chung, admin CRUD cho category/attribute/product/variant, public listing + detail pages với variant selection realtime. Đây là nền tảng để epic `cart-checkout` xây dựng lên.

## Architecture Decisions

1. **`HasLocalizedContent` trait thay vì copy-paste `t()`.** Mọi model có field JSON multi-language (Category, Attribute, AttributeValue, Product) đều `use HasLocalizedContent`. Trait expose `t(string $column, ?string $locale, string $default)` — khác `Site::t()` ở chỗ column-level (`$this->name`) thay vì dot-path bên trong 1 JSON blob.

2. **Variant = tổ hợp attribute values.** Mỗi variant link tới product + set attribute_values qua pivot `variant_attribute_values`. SKU unique globally. Price nullable → fallback sang `product.base_price`. Stock tracked per variant.

3. **Cartesian variant generation.** Admin gắn attributes A (n values) + B (m values) → click generate → tạo n×m variant records. Không xóa variant đã có (bảo toàn stock/price data).

4. **Category hierarchy max 2 cấp.** `parent_id` nullable FK self-reference. Query bằng `whereNull('parent_id')` + eager load `children`. Không dùng nested set — 2 cấp đủ đơn giản cho `->where()`.

5. **Realtime variant price via JSON endpoint.** `GET /api/product/{id}/variant-price?values[]=1&values[]=3` trả `{price, stock, sku, image}`. Public detail page gọi endpoint này bằng `fetch()` khi user chọn attribute combo — không cần Alpine.js ở epic này, vanilla JS đủ.

6. **Image upload inline, không package.** `product_images` table + `store()` vào `storage/app/public/products/{id}/`. Max 10 ảnh, max 5MB. Reorder qua `sort_order`, ảnh chính qua `is_primary` flag. Variant image optional (1 ảnh).

7. **Admin views: standalone Blade + Tailwind CDN.** Không extend Breeze layout — admin CMS views tự quản layout để decouple. Consistent với `admin/sites/create.blade.php` đã ship.

8. **Public views: extend `templates/layouts/base.blade.php`.** Product listing/detail pages render qua layout đã có từ `cms-landing-builder` epic, reuse lang-switcher + SEO meta.

9. **Price formatting helper.** `formatPrice(float $price, ?string $locale = null): string` — VI: `250.000₫`, EN: `$10.00`. Config-driven, không hardcode.

10. **"Add to cart" button = dumb HTML.** Render `<button data-product-id="X" data-variant-id="Y" disabled>` khi stock = 0, `enabled` khi > 0. Cart logic hoàn toàn thuộc epic sau — ở đây chỉ cần data attributes đúng.

## Technical Approach

### Backend — Models & Relationships
```
Category: hasMany(Product), belongsTo(Category, 'parent_id'), hasMany(Category, 'parent_id', 'id') as children
Attribute: hasMany(AttributeValue), belongsToMany(Product) via product_attributes
AttributeValue: belongsTo(Attribute)
Product: belongsTo(Category), belongsToMany(Attribute), hasMany(ProductVariant), hasMany(ProductImage)
ProductVariant: belongsTo(Product), belongsToMany(AttributeValue) via variant_attribute_values
ProductImage: belongsTo(Product)
```
All models with JSON fields → `use HasLocalizedContent`.

### Backend — Controllers
- `Admin\CategoryController` — resource CRUD, image upload for category.
- `Admin\AttributeController` — resource CRUD, inline attribute values management.
- `Admin\ProductController` — resource CRUD + `generateVariants()` + image upload/reorder + variant inline edit.
- `ProductController` (public) — `index()`, `byCategory()`, `show()`.
- `Api\VariantPriceController` — single endpoint returning `{price, stock, sku, image}`.

### Frontend — Admin
- `admin/categories/index|create|edit` — table listing + form with parent select + multilingual name inputs.
- `admin/attributes/index|create|edit` — form with dynamic "add value" rows (vanilla JS `appendChild`).
- `admin/products/index|create|edit` — complex form: basic info tabs, attribute multi-select, variant table, image gallery with drag reorder.
- All admin views use Tailwind CDN, standalone `<html>`.

### Frontend — Public
- `products/index.blade.php` — grid cards, sidebar category filter, sort dropdown, pagination.
- `products/show.blade.php` — gallery, info, attribute selectors, price/stock display, add-to-cart button, related products.
- Inline `<script>` for variant selection → fetch `/api/product/{id}/variant-price`.

### Infrastructure
- 7 new migrations (order: categories → attributes → attribute_values → products → product_images → product_attributes pivot → product_variants → variant_attribute_values pivot).
- Seeder: demo category "Áo thun" + product "Áo thun cổ tròn" + 2 attributes (Size S/M/L, Color Đỏ/Xanh) + 6 variants + stock/price.
- No new Composer packages. No queue. No WebSocket.

## Implementation Strategy

**Phase 1 — Data layer (task 1-2):** Trait `HasLocalizedContent`, 7 migrations, 6 Eloquent models, `formatPrice` helper. Verified by `migrate:fresh`.

**Phase 2 — Admin CRUD (task 3-5, parallelizable):**
- Task 3: Category admin (simple resource CRUD).
- Task 4: Attribute admin (resource + inline values).
- Task 5: Product admin (complex: attributes pivot, variant generation, images, variant inline edit).

**Phase 3 — Public pages (task 6-7):**
- Task 6: Product listing + category filter + shared product card partial.
- Task 7: Product detail + variant selector JS + API endpoint.

**Phase 4 — Routes + seeder + tests (task 8-10):**
- Task 8: Wire all routes (admin resource routes + public + API).
- Task 9: Demo seeder with realistic product data.
- Task 10: Feature tests.

## Task Breakdown Preview

1. **Data layer** — trait, migrations, models, formatPrice helper (S, 2h)
2. **Seeder** — demo category + product + attributes + variants (S, 1h)
3. **Admin Category CRUD** — controller + 3 views (M, 2h)
4. **Admin Attribute CRUD** — controller + 3 views + inline values JS (M, 2.5h)
5. **Admin Product CRUD** — controller + 3 views + variant generation + image upload + variant table (L, 4h)
6. **Public product listing** — controller index/byCategory + views + product card partial (M, 2h)
7. **Public product detail** — controller show + view + variant selector JS + API endpoint (M, 3h)
8. **Wire all routes** — admin resource routes + public + API (XS, 0.5h)
9. **Demo seeder** — realistic data: 2 categories, 3 products, attributes, variants (S, 1h)
10. **Feature tests** — admin CRUD + public listing/detail + variant price API + price formatting (M, 2.5h)

**Parallelization:**
- Task 1 unblocks all.
- Tasks 2, 3, 4, 5 parallelizable after task 1 (different files).
- Task 5 conflicts with 3, 4 only on routes → task 8 serializes route wiring.
- Tasks 6, 7 depend on task 1 + 8.
- Task 9 depends on 1.
- Task 10 depends on all.

## Dependencies

- **Shipped:** `multi-language` (SetLocale, lang files, `{locale}` group), `cms-landing-builder` (Breeze auth, `templates/layouts/base.blade.php`, Site model).
- **Auth:** Breeze installed, admin middleware `auth` wired.
- **Storage:** `storage:link` done.
- **Downstream:** epic `cart-checkout` will consume `Product`, `ProductVariant`, `Category` models + the public detail page's add-to-cart button.

## Success Criteria (Technical)

- `php artisan migrate:fresh --seed` creates 7 new tables + demo data cleanly.
- Admin can CRUD categories/attributes/products/variants/images without errors.
- Public listing at `/vi/products` shows products with price range, pagination, category filter.
- Public detail at `/vi/product/ao-thun-co-tron` shows gallery, attribute selectors, variant price updates via JS fetch.
- `formatPrice(250000)` returns `250.000₫` for VI locale, `$250,000.00` for EN.
- `HasLocalizedContent::t()` works on Category, Attribute, AttributeValue, Product.
- Feature tests pass.
- Zero new Composer packages.

## Estimated Effort

- **Serial:** ~20.5 hours (~3 days).
- **Parallel (with agents):** ~10 hours wall clock (~1.5 days).
- **Critical path:** Task 1 → Task 5 (product admin, largest) → Task 8 → Task 7 → Task 10.

## Tasks Created
- [ ] 001.md - Data layer: trait + migrations + models + formatPrice (parallel: true)
- [ ] 002.md - Demo seeder with realistic product data (parallel: true)
- [ ] 003.md - Admin Category CRUD (parallel: true)
- [ ] 004.md - Admin Attribute CRUD with inline values (parallel: true)
- [ ] 005.md - Admin Product CRUD with variant gen + images + variant table (parallel: true)
- [ ] 006.md - Public product listing and category filter (parallel: true)
- [ ] 007.md - Public product detail + variant selector + API (parallel: true)
- [ ] 008.md - Wire all routes (parallel: false)
- [ ] 009.md - Add lang keys for catalog views (parallel: true)
- [ ] 010.md - Feature tests (parallel: false)

Total tasks: 10
Parallel groups:
  - Phase 1: 001 (unblocks all)
  - Phase 2: 002, 003, 004, 005, 009 (5 parallel after 001)
  - Phase 3: 008 (serializes on 003+004+005)
  - Phase 4: 006, 007 (parallel after 008)
  - Phase 5: 010 (serializes on all)
Estimated total effort: ~20.5h serial, ~10h wall with parallel execution
