---
name: multi-language
status: backlog
created: 2026-04-15T00:00:00Z
updated: 2026-04-15T02:48:42Z
progress: 0%
prd: .claude/prds/multi-language.md
github: https://github.com/duc95bg/cms-nvd/issues/1
---

# Epic: multi-language

## Overview
Triển khai i18n tối giản tận dụng Laravel core: thêm middleware `SetLocale`, file dịch JSON, route prefix `{locale}`, cấu hình default locale. Không thêm dependency.

## Architecture Decisions
- **Dùng JSON translation files** (`lang/en.json`, `lang/vi.json`) thay vì PHP array files → một key, một dòng, dễ bảo trì.
- **Session-first, URL override**: URL prefix có quyền cao nhất; nếu không có prefix, đọc session; cuối cùng fallback `vi`.
- **Middleware trong nhóm `web`**: đảm bảo `StartSession` đã chạy trước.
- **Đăng ký qua `bootstrap/app.php`**: Laravel 12 không còn `Kernel.php`.
- **Không auto-detect `Accept-Language`**: giữ hành vi deterministic, tránh confuse user.
- **Không package bên ngoài** (mcamara/laravel-localization...): scope hiện tại đơn giản, core đủ dùng; mở rộng sau nếu cần.

## Technical Approach

### Frontend Components
- Blade partial `resources/views/partials/lang-switcher.blade.php`: 2 link EN/VI giữ nguyên path hiện tại.
- Cập nhật Blade demo (`home`, `about`) dùng `{{ __('welcome') }}`, `{{ __('about') }}`.

### Backend Services
- `app/Http/Middleware/SetLocale.php`: class duy nhất xử lý toàn bộ logic locale.
- `bootstrap/app.php`: append middleware vào web group + alias `locale`.
- `config/app.php`: set `locale=vi`, `fallback_locale=en`.
- `routes/web.php`: route group `prefix({locale})->where(locale=en|vi)`.

### Infrastructure
- Không thay đổi session driver, DB, cache.
- Không thay đổi build (vite/npm).

## Implementation Strategy
1. Nền tảng (file dịch + config) trước — không phụ thuộc gì.
2. Middleware song song với routes (hai luồng độc lập về file).
3. Blade demo + language switcher sau khi middleware + route đã sẵn sàng.
4. Test feature cuối cùng để chốt.

Mỗi bước đều có thể chạy `php artisan serve` test tay trước khi chuyển bước.

## Task Breakdown Preview
- **001 — Lang files & config**: Tạo `lang/en.json`, `lang/vi.json`, sửa `config/app.php`. *[parallel]*
- **002 — Middleware SetLocale**: Class middleware + unit test. *[parallel, depends: 001]*
- **003 — Đăng ký middleware & routes**: Sửa `bootstrap/app.php`, `routes/web.php`. *[depends: 002]*
- **004 — Blade demo + language switcher**: Views mẫu dùng `__()`. *[depends: 003]*
- **005 — Feature tests**: PHPUnit cho 4 kịch bản locale. *[depends: 003]*

5 task, 2 cặp có thể chạy song song (001 với 002 sau khi 001 xong; 004 với 005 sau khi 003 xong).

## Dependencies
- Không có dependency ngoài Laravel core hiện có.
- Không block bởi feature khác.

## Success Criteria (Technical)
- 4 feature test pass.
- `php artisan route:list` hiển thị route prefix `{locale}`.
- Không warning/error khi `php artisan config:cache`.
- Thay đổi locale chỉ bằng cách sửa mảng `SUPPORTED` trong middleware và thêm file JSON.

## Estimated Effort
- **Tổng**: ~2–3 giờ lập trình thuần.
- **Critical path**: 001 → 002 → 003 → 005.
- **Tài nguyên**: 1 dev (có thể parallel 2 agent nếu dùng CCPM execute).
