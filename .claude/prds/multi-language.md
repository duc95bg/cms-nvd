---
name: multi-language
description: Hỗ trợ đa ngôn ngữ (Anh/Việt) cho CMS với URL prefix và session persistence
status: backlog
created: 2026-04-15T00:00:00Z
---

# PRD: multi-language

## Executive Summary
Bổ sung hỗ trợ đa ngôn ngữ cho CMS cms-nvd với hai ngôn ngữ khởi điểm là tiếng Anh (en) và tiếng Việt (vi). Ngôn ngữ được chọn qua URL prefix `/en` hoặc `/vi`, lưu lại trong session để các request tiếp theo tự động áp dụng, và fallback về tiếng Việt nếu người dùng truy cập lần đầu.

## Problem Statement
CMS hiện chỉ hỗ trợ một ngôn ngữ, hạn chế khả năng phục vụ người dùng quốc tế và đối tác nước ngoài. Cần một cơ chế i18n tối giản, tận dụng Laravel core (`lang/`, `__()`, `app()->setLocale()`), không lệ thuộc package bên ngoài.

## User Stories
- **US1 — Chuyển ngôn ngữ qua URL**: Là một khách truy cập, tôi muốn gõ `/en/about` để xem trang About bằng tiếng Anh, hoặc `/vi/about` bằng tiếng Việt.
  - AC: URL prefix `en|vi` định tuyến đúng và hiển thị đúng bản dịch.
- **US2 — Giữ ngôn ngữ đã chọn**: Là người dùng, sau khi chọn tiếng Anh tôi muốn các trang tiếp theo vẫn ở tiếng Anh mà không cần prefix lại.
  - AC: Session lưu `locale`; request không có prefix đọc locale từ session.
- **US3 — Fallback mặc định**: Là khách mới, lần đầu truy cập không có session thì mặc định hiển thị tiếng Việt.
  - AC: Middleware trả về `vi` khi không có segment hợp lệ và chưa có session.
- **US4 — Dev thêm bản dịch**: Là lập trình viên, tôi muốn thêm khoá dịch mới vào một file JSON duy nhất cho mỗi ngôn ngữ.
  - AC: `lang/en.json` và `lang/vi.json` là nguồn duy nhất; dùng `__('key')` trong Blade/PHP.

## Functional Requirements
- FR1: Hỗ trợ hai locale: `en`, `vi`. Danh sách cấu hình được trong middleware.
- FR2: Middleware `SetLocale` chạy trong nhóm `web`, đọc segment đầu tiên của URL.
- FR3: Nếu segment ∈ {en, vi}: set locale + lưu session.
- FR4: Nếu không: đọc session; nếu session rỗng, dùng default `vi`.
- FR5: File dịch: `lang/en.json`, `lang/vi.json`.
- FR6: Route group với constraint `{locale}` prefix để minh hoạ sử dụng.
- FR7: Cấu hình `config/app.php`: `locale=vi`, `fallback_locale=en`.

## Non-Functional Requirements
- NFR1: Không thêm package composer bên ngoài — dùng Laravel core.
- NFR2: Middleware O(1), không query DB.
- NFR3: Tương thích Laravel 12 (bootstrap/app.php style).
- NFR4: Có test feature cho 4 tình huống: `/en`, `/vi`, không prefix có session, không prefix không session.

## Success Criteria
- Truy cập `/en` trả về Blade dùng `__('welcome')` = "Welcome to CMS".
- Truy cập `/vi` trả về "Chào mừng đến với CMS".
- Sau khi truy cập `/en/about`, request kế tiếp tới `/dashboard` dùng tiếng Anh.
- Session không có → locale = `vi`.
- PHPUnit feature test pass 4/4 case.

## Constraints & Assumptions
- Laravel 12, PHP 8.2, thư mục `lang/` ở root (không phải `resources/lang/`).
- Session driver đã cấu hình (mặc định file driver đủ dùng).
- Không xử lý RTL, không xử lý number/date formatting nâng cao trong phạm vi này.

## Out of Scope
- Tự động detect ngôn ngữ từ `Accept-Language` header.
- Lưu locale vào bảng users (per-user preference).
- Dịch nội dung động (bài viết CMS đa ngôn ngữ) — tính năng riêng.
- Ngôn ngữ thứ ba trở lên (chỉ cần mở rộng mảng `SUPPORTED` khi cần).
- UI admin quản lý translation keys.

## Dependencies
- Laravel framework ^12.0 (đã có).
- Session middleware `StartSession` trong nhóm web (mặc định Laravel).
- `gh` CLI chỉ cần khi sync sang GitHub.
