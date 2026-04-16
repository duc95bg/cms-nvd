---
name: ecommerce-catalog
description: Product catalog system — categories, products, attributes, variants/SKU, pricing, stock, images, admin CRUD and public listing/detail pages
status: backlog
created: 2026-04-16T00:00:00Z
---

# PRD: ecommerce-catalog

## Executive Summary
Xây dựng hệ thống catalog sản phẩm cho CMS e-commerce single-tenant. Mỗi sản phẩm thuộc danh mục, có nhiều thuộc tính (size, color...), mỗi tổ hợp thuộc tính tạo ra 1 variant với SKU riêng, giá riêng, tồn kho riêng. Admin quản lý toàn bộ CRUD. Public hiển thị danh sách sản phẩm theo danh mục, trang chi tiết với chọn variant, hiển thị giá + stock. Tất cả text hỗ trợ multi-language (vi/en) qua JSON pattern đã thiết lập.

## Problem Statement
Hệ thống CMS hiện tại chỉ render landing page tĩnh từ JSON content. Không có khái niệm sản phẩm, danh mục, variant hay tồn kho. Để trở thành CMS bán hàng cần data layer + UI cho catalog — đây là nền tảng cho cart/checkout (epic sau).

## User Stories

### US-1 — Quản lý danh mục sản phẩm
**As** admin
**I want** tạo/sửa/xóa danh mục sản phẩm với tên multi-language, ảnh đại diện, thứ tự hiển thị
**So that** sản phẩm được phân nhóm có tổ chức trên frontend.

**Acceptance criteria:**
- CRUD danh mục tại `/admin/categories`.
- Mỗi danh mục có: `name` (json vi/en), `slug` (unique), `image` (nullable), `sort_order` (int), `status` (active/inactive).
- Hỗ trợ danh mục cha-con (parent_id nullable, tối đa 2 cấp).
- Slug tự generate từ tên (vi hoặc en) nếu để trống.
- Validation: name bắt buộc, slug unique.

### US-2 — Quản lý thuộc tính sản phẩm
**As** admin
**I want** định nghĩa các thuộc tính (Kích thước, Màu sắc...) và giá trị của chúng
**So that** tôi có thể tạo variant cho sản phẩm.

**Acceptance criteria:**
- CRUD thuộc tính tại `/admin/attributes`.
- Mỗi attribute có: `name` (json vi/en), `type` (select/color/text).
- Mỗi attribute có nhiều `attribute_values`: `value` (json vi/en), `sort_order`.
- Ví dụ: Attribute "Màu sắc" → values ["Đỏ", "Xanh", "Vàng"].
- Xóa attribute chỉ khi chưa gắn vào sản phẩm nào (soft-block).

### US-3 — Quản lý sản phẩm
**As** admin
**I want** tạo sản phẩm với thông tin chi tiết, ảnh, gắn danh mục và thuộc tính
**So that** sản phẩm hiển thị đầy đủ trên frontend.

**Acceptance criteria:**
- CRUD sản phẩm tại `/admin/products`.
- Mỗi product có: `name` (json vi/en), `slug` (unique), `description` (json vi/en, rich text), `short_description` (json vi/en), `base_price` (decimal, giá gốc khi không có variant), `category_id` (FK), `status` (active/draft/inactive), `featured` (bool), `sort_order`.
- Upload nhiều ảnh sản phẩm, chọn ảnh chính (thumbnail).
- Gắn nhiều attributes cho product → tự động hiện form tạo variant.
- Validation: name bắt buộc, slug unique, base_price >= 0, category tồn tại.

### US-4 — Quản lý variant / SKU
**As** admin
**I want** tạo variant cho từng tổ hợp thuộc tính với SKU, giá, tồn kho riêng
**So that** khách hàng chọn đúng phiên bản và thấy giá + stock chính xác.

**Acceptance criteria:**
- Sau khi gắn attributes cho product, admin click "Tạo variant" → hệ thống generate tất cả tổ hợp (cartesian product) hoặc admin tạo thủ công từng variant.
- Mỗi variant có: `sku` (unique), `price` (decimal, nullable → dùng base_price nếu null), `stock` (int >= 0), `status` (active/inactive), `image` (nullable, ảnh riêng cho variant).
- Variant liên kết với product + set attribute_values qua pivot.
- Bảng variant hiển thị dạng table trong form edit product: SKU | Giá | Tồn kho | Trạng thái.
- SKU tự generate dạng `PRODUCT-SLUG-ATTR1-ATTR2` nếu để trống, có thể sửa.

### US-5 — Danh sách sản phẩm công khai
**As** khách truy cập
**I want** xem danh sách sản phẩm theo danh mục, lọc, phân trang
**So that** tôi tìm được sản phẩm cần mua.

**Acceptance criteria:**
- URL: `/{locale}/products` (tất cả) và `/{locale}/category/{slug}` (theo danh mục).
- Hiển thị: ảnh chính, tên, giá (nếu có variant → hiển thị "từ X₫"), trạng thái stock ("Còn hàng" / "Hết hàng").
- Phân trang 12 sản phẩm/trang.
- Filter: theo danh mục (sidebar), sắp xếp (mới nhất, giá tăng/giảm, tên).
- Chỉ hiển thị product `status=active` với ít nhất 1 variant `status=active` (hoặc không có variant và `base_price > 0`).
- Responsive grid (Tailwind): 4 cột desktop, 2 mobile.

### US-6 — Trang chi tiết sản phẩm
**As** khách truy cập
**I want** xem chi tiết sản phẩm, chọn variant, thấy giá + stock realtime
**So that** tôi quyết định mua đúng phiên bản.

**Acceptance criteria:**
- URL: `/{locale}/product/{slug}`.
- Hiển thị: gallery ảnh (ảnh chính + phụ), tên, short_description, description (rich text rendered), giá.
- Nếu có variant: hiển thị dropdown/button cho mỗi attribute. Khi chọn tổ hợp → cập nhật giá + stock bằng JS (fetch API hoặc inline data).
- Nút "Thêm vào giỏ" (disabled nếu hết hàng). Nút này ở UI nhưng logic cart thuộc epic `cart-checkout` → ở đây chỉ render button với `data-product-id` và `data-variant-id`.
- Breadcrumb: Trang chủ > Danh mục > Sản phẩm.
- SEO: `<title>` và `<meta description>` từ product name/short_description.
- Related products (cùng category, tối đa 4) ở cuối trang.

## Functional Requirements

### FR-1 — Database schema

```
categories
  id, parent_id (nullable FK self), name (json), slug (unique), image (nullable), 
  sort_order (int default 0), status (enum: active/inactive), timestamps

attributes
  id, name (json), type (enum: select/color/text), timestamps

attribute_values
  id, attribute_id (FK), value (json), sort_order (int default 0), timestamps

products
  id, category_id (FK), name (json), slug (unique), description (json), 
  short_description (json), base_price (decimal 12,2), status (enum: active/draft/inactive), 
  featured (bool default false), sort_order (int default 0), timestamps

product_images
  id, product_id (FK), path (string), is_primary (bool default false), sort_order, timestamps

product_attributes (pivot)
  product_id (FK), attribute_id (FK), unique together

product_variants
  id, product_id (FK), sku (unique), price (decimal 12,2 nullable), stock (int default 0), 
  status (enum: active/inactive), image (nullable), timestamps

variant_attribute_values (pivot)
  variant_id (FK), attribute_value_id (FK), unique together
```

### FR-2 — Multi-language fields
- `name`, `description`, `short_description` trên product; `name` trên category, attribute, attribute_value — tất cả dùng JSON `{"en": "...", "vi": "..."}`.
- Admin form hiển thị input cho mỗi locale (pattern giống `admin/sites/edit.blade.php` nhưng hardcode fields thay vì auto-discover).
- Public render dùng helper: `$product->t('name')`, `$category->t('name')` — cùng pattern `Site::t()` (tạo trait `HasLocalizedContent`).

### FR-3 — Localized content trait
```php
trait HasLocalizedContent {
    public function t(string $key, ?string $locale = null, string $default = ''): string {
        $locale = $locale ?: app()->getLocale();
        $fallback = config('app.fallback_locale');
        return (string) (data_get($this->{$key}, $locale) ?? data_get($this->{$key}, $fallback) ?? $default);
    }
}
```
Khác `Site::t()` ở chỗ: key là tên column (`name`, `description`), không phải dot-path bên trong JSON content.

### FR-4 — Variant price resolution
```php
// Product model
public function getEffectivePrice(?ProductVariant $variant = null): float {
    if ($variant && $variant->price !== null) return $variant->price;
    return $this->base_price;
}

public function getPriceRange(): array {
    $prices = $this->variants->where('status', 'active')->pluck('price')->filter()->all();
    if (empty($prices)) return ['min' => $this->base_price, 'max' => $this->base_price];
    $prices[] = $this->base_price; // include base if some variants have null price
    return ['min' => min($prices), 'max' => max($prices)];
}
```

### FR-5 — Variant generation
Khi admin gắn attributes A (3 values) và B (2 values) cho product → admin click "Generate variants" → hệ thống tạo 3×2 = 6 variant records, SKU tự sinh, price = null (dùng base_price), stock = 0.

### FR-6 — Image upload
- Product images stored at `storage/app/public/products/{product_id}/`.
- `php artisan storage:link` đã chạy từ epic trước.
- Upload qua form multipart, max 5MB/ảnh, max 10 ảnh/sản phẩm.
- Admin có thể reorder ảnh (sort_order) và chọn ảnh chính.
- Variant image (optional) stored cùng thư mục.

### FR-7 — Routing
Public (trong `{locale}` group đã có):
- `GET /{locale}/products` → `ProductController@index`
- `GET /{locale}/category/{slug}` → `ProductController@byCategory`
- `GET /{locale}/product/{slug}` → `ProductController@show`
- `GET /api/product/{id}/variant-price` → JSON endpoint trả `{price, stock, sku}` khi user chọn variant (dùng cho JS realtime update)

Admin (trong `admin` group, middleware `auth`):
- `admin/categories` — resource routes
- `admin/attributes` — resource routes
- `admin/products` — resource routes + `POST products/{product}/variants/generate`
- `admin/products/{product}/images` — upload/reorder/delete

### FR-8 — Formatting
- Giá hiển thị theo locale: VI → `250.000₫`, EN → `$10.00` (dùng `number_format` + currency symbol config).
- Stock: "Còn hàng" / "In stock", "Hết hàng" / "Out of stock" qua lang files.

## Non-Functional Requirements

- **No new Composer packages.** Laravel core only.
- **Tailwind via CDN** cho admin views (consistent với epic trước). Public views dùng Vite build đã có từ Breeze.
- **Image storage:** local `public` disk. Không resize — giữ file gốc.
- **Shared hosting compatible:** không queue, không WebSocket, không Node server.
- **Test coverage:** feature tests cho product CRUD, variant generation, public listing, public detail, price resolution logic.

## Success Criteria

1. Admin tạo được category "Áo thun" → tạo product "Áo thun cổ tròn" → gắn attributes Size (S/M/L) + Color (Đỏ/Xanh) → generate 6 variants → set giá + stock → publish.
2. Public truy cập `/vi/products` thấy sản phẩm với giá "từ 150.000₫".
3. Public truy cập `/vi/product/ao-thun-co-tron` → chọn Size M + Color Đỏ → giá + stock cập nhật realtime → nút "Thêm vào giỏ" active (stock > 0).
4. Mọi text hiển thị đúng locale: EN tại `/en/products`, VI tại `/vi/products`.
5. Feature tests pass.

## Constraints & Assumptions

- Laravel 11, Blade, Tailwind, MySQL. PHP thuần, không Livewire.
- Single-tenant: 1 shop per deploy, admin quản lý trực tiếp (không multi-shop).
- "Thêm vào giỏ" button chỉ render HTML/data attributes ở đây — logic cart xử lý ở epic `cart-checkout`.
- Rich text description: dùng `<textarea>` có thể paste HTML. Không tích hợp WYSIWYG editor ở epic này (có thể upgrade sau).
- Variant generation tạo tổ hợp mới, không xóa variant đã có (tránh mất data stock/price).
- Hierarchical category tối đa 2 cấp (parent → child) cho đơn giản.

## Out of Scope

- Giỏ hàng, checkout, đơn hàng (→ epic `cart-checkout`).
- Drag-drop block builder (→ epic `cms-block-builder`).
- Admin dashboard analytics (→ epic `admin-dashboard`).
- Payment gateway integration (→ epic `cart-checkout`).
- Product reviews, ratings, wishlist.
- Import/export CSV sản phẩm.
- Discount, coupon, promotion.
- Advanced search (Elasticsearch/Algolia).
- Image resize, watermark, CDN.

## Dependencies

- **Shipped:** `multi-language` epic (SetLocale middleware, lang files, `{locale}` route group), `cms-landing-builder` epic (Site model, templates, Breeze auth).
- **Auth:** Breeze đã cài, admin routes đã có middleware `auth`.
- **Storage:** `php artisan storage:link` đã chạy.
- **Follow-up:** epic `cart-checkout` sẽ consume Product/Variant models từ epic này.
