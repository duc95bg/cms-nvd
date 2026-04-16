---
name: cart-checkout
status: backlog
created: 2026-04-16T00:00:00Z
updated: 2026-04-16T08:58:39Z
progress: 0%
prd: .claude/prds/cart-checkout.md
github: https://github.com/duc95bg/cms-nvd/issues/28
---

# Epic: cart-checkout

## Overview
Biến catalog sản phẩm thành e-commerce hoạt động: cart session-based → checkout → order + stock decrement → payment (COD/bank/VNPay/PayPal). Admin quản lý đơn hàng. User xem lịch sử. Không thêm Composer package — VNPay + PayPal gọi trực tiếp qua `Http::`.

## Architecture Decisions

1. **Cart = session, không phải DB table.** Guest mua hàng không cần đăng nhập. Cart key `"cart"` trong session, mỗi item keyed `"{productId}-{variantId}"`. `CartService` class xử lý logic, inject vào controller qua constructor.

2. **CartService là plain PHP class, không phải Eloquent.** Không cần migration. `add()` / `update()` validate stock realtime bằng query DB. `items()` lazy-load Product/Variant relationships lần đầu, cache trong instance.

3. **Stock locking = `DB::transaction()` + `lockForUpdate()`.** Checkout lock variant rows trước khi decrement. Nếu stock < quantity → rollback toàn bộ, trả lỗi. Tránh negative stock dưới concurrent requests.

4. **Order snapshots product data.** `order_items.product_name` (JSON vi/en) và `variant_info` (JSON attributes) lưu tại thời điểm order — sản phẩm có thể đổi tên/xóa sau đó mà đơn hàng vẫn đúng.

5. **VNPay = URL building + HMAC-SHA512.** Không dùng SDK. `PaymentService::createVnpayUrl(Order $order): string` build query params, sort, append hash. IPN/return verify bằng `hash_hmac()`. All config từ `.env`.

6. **PayPal = REST API v2 via `Http::`.** `PaymentService::createPaypalOrder(Order $order): string` gọi `POST /v2/checkout/orders` với Basic Auth. Return URL capture payment. USD rate cấu hình tĩnh (không gọi exchange rate API).

7. **Payment callbacks tách CSRF.** VNPay IPN + PayPal return/cancel là external GET → phải exclude khỏi `VerifyCsrfToken`. Cart add/update/remove là internal POST → giữ CSRF.

8. **`config/payment.php`** tập trung tất cả payment config: bank info, VNPay credentials, PayPal credentials. Đọc từ `.env`.

9. **Order status pipeline.** `pending → confirmed → shipping → delivered` hoặc `pending → cancelled` / `confirmed → cancelled`. Mỗi transition ghi `order_status_logs` row. Admin chỉ chuyển tiến, không lùi (trừ cancel).

10. **Cart badge = Blade partial.** `partials/cart-badge.blade.php` include trong `layouts/catalog.blade.php`. Đọc `CartService::count()` mỗi page load. Không cần AJAX/WebSocket.

## Technical Approach

### Backend — Services
- **`App\Services\CartService`**: session CRUD, stock validation, total calculation. Registered in `AppServiceProvider` as singleton (shares session across request).
- **`App\Services\PaymentService`**: `createVnpayUrl()`, `verifyVnpayHash()`, `createPaypalOrder()`, `capturePaypalPayment()`, `getPaypalAccessToken()`. Static methods, stateless.
- **`App\Services\OrderService`**: `createOrder(array $customerData, string $paymentMethod): Order`. Wraps DB transaction: create order + items + decrement stock + clear cart. Throws `InsufficientStockException`.

### Backend — Models
- `Order`: fillable, casts, relationships `items()`, `statusLogs()`, `user()`. Scopes `forUser()`, `byStatus()`. Helper `canTransitionTo(string $status): bool`.
- `OrderItem`: fillable, casts `product_name => array`, `variant_info => array`.
- `OrderStatusLog`: fillable, relationship `order()`, `changedBy()`.

### Backend — Controllers
- `CartController`: `index()` (view cart), `add()`, `update()`, `remove()`. Uses `CartService`.
- `CheckoutController`: `index()` (checkout form), `process()` (validate + create order + redirect based on payment method).
- `PaymentController`: `vnpayReturn()`, `vnpayIpn()`, `paypalReturn()`, `paypalCancel()`. Stateless verification.
- `OrderController` (public): `success()`, `bankTransfer()`, `history()` (auth), `detail()` (auth).
- `Admin\OrderController`: `index()` (list with filters), `show()` (detail), `updateStatus()`.

### Frontend — Views
- `cart/index.blade.php` — table with quantity inputs, totals, checkout button.
- `checkout/index.blade.php` — left: customer form + payment method radio. Right: order summary.
- `orders/success.blade.php` — thank you + order number.
- `orders/bank-transfer.blade.php` — bank info + order number.
- `orders/history.blade.php` — user's order list.
- `orders/detail.blade.php` — order detail for user.
- `admin/orders/index.blade.php` — paginated list + status filter + search.
- `admin/orders/show.blade.php` — full order detail + status update dropdown.
- `partials/cart-badge.blade.php` — mini cart icon in header.
- All extend `layouts/catalog.blade.php` (public) or standalone (admin).

### Infrastructure
- 3 migrations: orders, order_items, order_status_logs.
- `config/payment.php` — bank info, VNPay, PayPal config from `.env`.
- `.env.example` updated with payment env vars.
- No queue, no WebSocket, no external packages.

## Implementation Strategy

**Phase 1 — Data layer + services (task 1-2):**
- Task 1: migrations + models (Order, OrderItem, OrderStatusLog) + config/payment.php + .env vars.
- Task 2: CartService + OrderService + PaymentService.

**Phase 2 — Cart UI (task 3):**
- Task 3: CartController + cart views + cart badge partial + wire add-to-cart button from product detail.

**Phase 3 — Checkout + COD/bank (task 4):**
- Task 4: CheckoutController + checkout view + success/bank-transfer pages. COD + bank transfer flows working end-to-end.

**Phase 4 — Payment gateways (task 5-6, parallelizable):**
- Task 5: VNPay integration (PaymentService methods + controller actions + CSRF exclusion).
- Task 6: PayPal integration (PaymentService methods + controller actions).

**Phase 5 — Order management (task 7-8, parallelizable):**
- Task 7: Admin order management (controller + views + status update).
- Task 8: User order history (controller + views).

**Phase 6 — Routes + lang + tests (task 9-11):**
- Task 9: Wire all routes.
- Task 10: Lang keys.
- Task 11: Feature tests.

## Task Breakdown Preview

1. **Data layer** — 3 migrations, 3 models, config/payment.php, .env vars (S, 1.5h)
2. **Services** — CartService, OrderService, PaymentService (M, 3h)
3. **Cart UI** — CartController + views + badge + wire product detail button (M, 2h)
4. **Checkout + COD/bank** — CheckoutController + views + success pages (M, 2.5h)
5. **VNPay integration** — URL builder, hash verify, IPN/return handlers (M, 2h)
6. **PayPal integration** — REST API create/capture, return/cancel handlers (M, 2h)
7. **Admin orders** — Admin\OrderController + views + status pipeline (M, 2.5h)
8. **User order history** — OrderController history/detail + views (S, 1.5h)
9. **Wire all routes** — web.php updates + CSRF exclusion (XS, 0.5h)
10. **Lang keys** — all __() keys for cart/checkout/orders (XS, 0.5h)
11. **Feature tests** — cart, checkout COD, stock locking, admin orders, VNPay hash, PayPal mock (L, 3h)

**Parallelization:**
- Task 1 unblocks all.
- Task 2 depends on 1.
- Tasks 3, 10 depend on 2.
- Task 4 depends on 3.
- Tasks 5, 6 depend on 2 (parallelizable, different files).
- Tasks 7, 8 depend on 1 (parallelizable, different files).
- Task 9 depends on 3, 4, 5, 6, 7, 8.
- Task 11 depends on all.

## Dependencies

- **Shipped:** `ecommerce-catalog` (Product, ProductVariant models with stock, `HasLocalizedContent`, `PriceFormatter`, product detail page add-to-cart button, `layouts/catalog.blade.php`).
- **Shipped:** `multi-language` (SetLocale middleware, lang files, `{locale}` group).
- **Shipped:** Breeze auth (admin middleware, user model).
- **External:** VNPay sandbox API, PayPal sandbox API. Both need merchant accounts (config via .env).

## Success Criteria (Technical)

- Guest adds 2 items to cart → cart page shows both → checkout COD → order created (ORD-YYYYMMDD-NNN) → stock decremented → success page.
- VNPay flow: redirect → sandbox payment → return → order paid.
- PayPal flow: redirect → sandbox approval → capture → order paid.
- Admin: list orders → view detail → change status pending→confirmed→shipping→delivered → each transition logged.
- User: view own order history → click detail → see items + status.
- Concurrent test: `lockForUpdate()` prevents negative stock.
- Feature tests pass, no regression on existing 38 tests.

## Estimated Effort

- **Serial:** ~21h (~3 days).
- **Parallel:** ~12h wall clock (~1.5 days).
- **Critical path:** Task 1 → 2 → 3 → 4 → 9 → 11.

## Tasks Created
- [ ] 001.md - Data layer: migrations, models, config/payment.php (parallel: true)
- [ ] 002.md - Services: CartService, OrderService, PaymentService (parallel: false)
- [ ] 003.md - Cart UI: CartController, views, badge, wire button (parallel: true)
- [ ] 004.md - Checkout flow: COD and bank transfer (parallel: false)
- [ ] 005.md - VNPay payment integration (parallel: true)
- [ ] 006.md - PayPal payment integration (parallel: true)
- [ ] 007.md - Admin order management (parallel: true)
- [ ] 008.md - User order history (parallel: true)
- [ ] 009.md - Wire all routes (parallel: false)
- [ ] 010.md - Lang keys (parallel: true)
- [ ] 011.md - Feature tests (parallel: false)

Total tasks: 11
Parallel groups:
  - Phase 1: 001 (unblocks all)
  - Phase 2: 002 (serial on 001, unblocks services consumers)
  - Phase 3: 003, 005, 006, 007, 008, 010 (6 parallel after deps met)
  - Phase 4: 004 (serial on 003)
  - Phase 5: 009 (serial on all controllers)
  - Phase 6: 011 (serial on everything)
Estimated total effort: ~21h serial, ~12h wall with parallel execution
