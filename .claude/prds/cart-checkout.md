---
name: cart-checkout
description: Shopping cart, checkout flow, order management, and payment integration (COD, bank transfer, VNPay, PayPal)
status: backlog
created: 2026-04-16T00:00:00Z
---

# PRD: cart-checkout

## Executive Summary
Xây dựng luồng mua hàng hoàn chỉnh: giỏ hàng session-based → checkout form → tạo đơn hàng + trừ tồn kho → thanh toán (COD / chuyển khoản / VNPay / PayPal). Admin quản lý đơn hàng với status pipeline. Khách đăng nhập xem lịch sử đơn. Tất cả multi-language, không thêm composer package — payment gateway gọi qua `Http::`.

## Problem Statement
Epic `ecommerce-catalog` đã ship catalog sản phẩm + variant + tồn kho nhưng nút "Thêm vào giỏ" chỉ là HTML dumb — chưa có cart, checkout hay đơn hàng. Không có luồng mua hàng = không bán được. Epic này biến catalog thành e-commerce thật.

## User Stories

### US-1 — Thêm sản phẩm vào giỏ hàng
**As** khách truy cập (guest hoặc logged-in)
**I want** nhấn "Thêm vào giỏ" trên trang chi tiết sản phẩm
**So that** sản phẩm được lưu vào giỏ hàng và tôi tiếp tục mua sắm.

**Acceptance criteria:**
- Click "Thêm vào giỏ" gửi POST `/cart/add` với `product_id`, `variant_id`, `quantity`.
- Nếu chưa chọn variant (khi sản phẩm có variant) → validation error, không thêm.
- Nếu quantity > stock → validation error với message "Không đủ hàng".
- Giỏ hàng lưu trong session (guest) — không cần đăng nhập để mua.
- Nếu sản phẩm+variant đã có trong giỏ → cộng dồn quantity (không vượt stock).
- Sau khi thêm → redirect back với flash "Đã thêm vào giỏ".
- Header hiển thị icon giỏ hàng + badge số lượng items.

### US-2 — Xem và quản lý giỏ hàng
**As** khách
**I want** xem giỏ hàng, thay đổi số lượng, xóa sản phẩm
**So that** tôi kiểm tra lại trước khi thanh toán.

**Acceptance criteria:**
- URL: `/{locale}/cart`.
- Hiển thị: ảnh sản phẩm, tên, variant attributes (vd "Size: M, Màu: Đỏ"), đơn giá, quantity input, thành tiền, nút xóa.
- Cập nhật quantity: POST `/cart/update` với `item_key` + `quantity`. Validate stock realtime.
- Xóa item: POST `/cart/remove` với `item_key`.
- Hiển thị tổng tiền ở cuối.
- Nút "Tiếp tục mua sắm" (back to products) + "Thanh toán" (to checkout).
- Giỏ trống → hiển thị empty state với link đến danh sách sản phẩm.

### US-3 — Checkout và đặt hàng
**As** khách
**I want** điền thông tin giao hàng, chọn phương thức thanh toán, và đặt hàng
**So that** đơn hàng được tạo và xử lý.

**Acceptance criteria:**
- URL: `/{locale}/checkout`. Redirect về cart nếu giỏ trống.
- Form fields: họ tên (required), email (required, email), điện thoại (required), địa chỉ giao hàng (required), ghi chú (optional).
- Chọn phương thức thanh toán: COD / Chuyển khoản / VNPay / PayPal.
- Hiển thị tóm tắt đơn hàng bên phải (items, quantity, giá, tổng).
- Submit → validate → tạo Order + OrderItems → trừ stock variant → clear cart.
- **COD:** redirect sang trang "Đặt hàng thành công" với mã đơn.
- **Chuyển khoản:** redirect sang trang hiển thị thông tin tài khoản ngân hàng + mã đơn.
- **VNPay:** redirect sang VNPay payment URL → callback xử lý kết quả → redirect success/fail.
- **PayPal:** redirect sang PayPal checkout → return URL xử lý capture → redirect success/fail.
- Nếu stock không đủ tại thời điểm submit (race condition) → rollback, show error.
- Đặt hàng không yêu cầu đăng nhập (guest checkout). Nếu đã login thì gắn `user_id` vào order.

### US-4 — Thanh toán VNPay
**As** khách
**I want** thanh toán qua VNPay
**So that** đơn hàng được xác nhận thanh toán online.

**Acceptance criteria:**
- Khi chọn VNPay → hệ thống tạo payment URL với hash HMAC-SHA512 theo spec VNPay.
- Redirect khách sang VNPay gateway.
- VNPay callback (IPN) + return URL → verify hash → update order `payment_status`.
- Nếu thanh toán thành công: order status = `confirmed`, payment_status = `paid`.
- Nếu thất bại/hủy: order giữ `pending`, payment_status = `failed`. Khách có thể thử lại.
- Config: `VNPAY_TMN_CODE`, `VNPAY_HASH_SECRET`, `VNPAY_URL`, `VNPAY_RETURN_URL` trong `.env`.

### US-5 — Thanh toán PayPal
**As** khách
**I want** thanh toán qua PayPal
**So that** khách quốc tế có thể mua hàng.

**Acceptance criteria:**
- Khi chọn PayPal → hệ thống gọi PayPal REST API `v2/checkout/orders` tạo order.
- Redirect khách sang PayPal approval URL.
- PayPal return → capture payment → update order `payment_status`.
- Nếu thành công: order `confirmed`, payment `paid`.
- Nếu thất bại: order `pending`, payment `failed`.
- Config: `PAYPAL_CLIENT_ID`, `PAYPAL_SECRET`, `PAYPAL_MODE` (sandbox/live) trong `.env`.
- Giá chuyển sang USD dùng tỷ giá config `PAYPAL_USD_RATE` (vd 25000).

### US-6 — Admin quản lý đơn hàng
**As** admin
**I want** xem danh sách đơn hàng, chi tiết, cập nhật trạng thái
**So that** tôi xử lý đơn hàng hiệu quả.

**Acceptance criteria:**
- URL: `/admin/orders` — danh sách đơn paginated, filter theo status, search theo mã đơn/tên/email.
- URL: `/admin/orders/{order}` — chi tiết đơn: thông tin khách, items, giá, payment method, status.
- Cập nhật status: dropdown chuyển trạng thái. Pipeline: `pending → confirmed → shipping → delivered`. Hoặc `pending → cancelled` / `confirmed → cancelled`.
- Ghi log khi đổi status (order_status_logs table).
- Hiển thị payment status (pending/paid/failed/refunded).
- Không cho xóa đơn — chỉ cancel.

### US-7 — Lịch sử đơn hàng (khách đăng nhập)
**As** khách đã đăng nhập
**I want** xem lịch sử đơn hàng
**So that** tôi theo dõi trạng thái giao hàng.

**Acceptance criteria:**
- URL: `/{locale}/orders` (auth required).
- Hiển thị danh sách đơn của user: mã đơn, ngày, tổng tiền, status badge.
- Click vào → xem chi tiết: items, thông tin giao hàng, trạng thái thanh toán.
- Chỉ thấy đơn của mình (user_id match).

## Functional Requirements

### FR-1 — Database schema

```
orders
  id, order_number (unique, auto-gen VD: ORD-20260416-001), user_id (nullable FK),
  customer_name, customer_email, customer_phone, shipping_address, notes (nullable),
  subtotal (decimal 12,2), total (decimal 12,2),
  status (enum: pending/confirmed/shipping/delivered/cancelled),
  payment_method (enum: cod/bank_transfer/vnpay/paypal),
  payment_status (enum: pending/paid/failed/refunded),
  payment_transaction_id (nullable), paid_at (nullable timestamp),
  timestamps

order_items
  id, order_id FK, product_id FK, variant_id (nullable FK),
  product_name (json, snapshot), variant_info (json, snapshot attributes),
  price (decimal 12,2), quantity (int), total (decimal 12,2),
  timestamps

order_status_logs
  id, order_id FK, from_status (nullable), to_status,
  note (nullable), changed_by (nullable FK users), timestamps
```

### FR-2 — Cart service (session-based)
```php
class CartService {
    public function add(int $productId, ?int $variantId, int $quantity): void
    public function update(string $key, int $quantity): void
    public function remove(string $key): void
    public function clear(): void
    public function items(): Collection  // returns CartItem DTOs
    public function total(): float
    public function count(): int
    public function isEmpty(): bool
}
```
- Cart key = `"cart"` in session. Each item keyed by `"{productId}-{variantId}"`.
- Item stores: `product_id`, `variant_id`, `quantity`, loaded relations cached (lazy load on `items()`).
- Validate stock on `add()` and `update()`. Throw `InsufficientStockException` if exceeds.

### FR-3 — Stock management
- On order create: decrement `product_variants.stock` (or check `base_price` if no variant) within a DB transaction.
- If any item stock < requested quantity: rollback entire order, return error.
- On order cancel: restore stock (increment back).
- Use `DB::transaction()` + `lockForUpdate()` to prevent race conditions.

### FR-4 — Order number generation
`ORD-{YYYYMMDD}-{sequential_3_digits}` — vd `ORD-20260416-001`. Sequential per day, zero-padded.
```php
$today = now()->format('Ymd');
$count = Order::whereDate('created_at', today())->count() + 1;
$orderNumber = sprintf('ORD-%s-%03d', $today, $count);
```

### FR-5 — VNPay integration (via Http::)
- Tạo payment URL theo VNPay spec: sort params → append `vnp_SecureHash` (HMAC-SHA512).
- IPN callback: `GET /payment/vnpay/ipn` → verify hash → update order → return `{"RspCode":"00"}`.
- Return URL: `GET /payment/vnpay/return` → verify hash → redirect to success/fail page.
- Không dùng SDK — build URL bằng `http_build_query` + `hash_hmac('sha512', ...)`.

### FR-6 — PayPal integration (via Http::)
- Create order: `Http::withBasicAuth($clientId, $secret)->post('https://api.paypal.com/v2/checkout/orders', {...})`.
- Capture: `Http::post('.../v2/checkout/orders/{id}/capture')`.
- Sandbox URL: `https://api-m.sandbox.paypal.com`, Live: `https://api-m.paypal.com`.
- Currency conversion: `total_usd = total_vnd / config('services.paypal.usd_rate')`.

### FR-7 — Routing
Cart (public, no auth):
- `POST /cart/add` → CartController@add
- `GET /{locale}/cart` → CartController@index
- `POST /cart/update` → CartController@update
- `POST /cart/remove` → CartController@remove

Checkout (public, no auth):
- `GET /{locale}/checkout` → CheckoutController@index
- `POST /checkout/process` → CheckoutController@process

Payment callbacks (no auth, no CSRF):
- `GET /payment/vnpay/return` → PaymentController@vnpayReturn
- `GET /payment/vnpay/ipn` → PaymentController@vnpayIpn
- `GET /payment/paypal/return` → PaymentController@paypalReturn
- `GET /payment/paypal/cancel` → PaymentController@paypalCancel

Order success:
- `GET /{locale}/order/success/{order}` → OrderController@success
- `GET /{locale}/order/bank-transfer/{order}` → OrderController@bankTransfer

Admin:
- `GET /admin/orders` → Admin\OrderController@index
- `GET /admin/orders/{order}` → Admin\OrderController@show
- `POST /admin/orders/{order}/status` → Admin\OrderController@updateStatus

User order history (auth):
- `GET /{locale}/orders` → OrderController@history
- `GET /{locale}/orders/{order}` → OrderController@detail

### FR-8 — Cart badge in header
- Shared Blade partial `partials/cart-badge.blade.php` shows item count from `CartService::count()`.
- Included in `layouts/catalog.blade.php` header.
- Updates on every page load (session read).

### FR-9 — Bank transfer info page
- Static config: bank name, account number, account holder — from `config/payment.php`.
- Display after order + instruct customer to transfer with order number as reference.

## Non-Functional Requirements

- **No new Composer packages.** Payment via `Http::` + `hash_hmac`.
- **Shared hosting compatible:** no queue, no WebSocket. Payment callbacks are synchronous GET/POST.
- **Session-based cart:** works for guests. Cart survives page navigations but clears on session expiry (default 2h).
- **Stock atomicity:** `DB::transaction()` + `lockForUpdate()` on variant rows during checkout.
- **Multi-language:** all UI text via `__()`. Order snapshots (product_name, variant_info) stored in both locales.
- **Test coverage:** feature tests for cart add/update/remove, checkout COD flow, order create + stock decrement, admin order status update, VNPay hash generation/verification, PayPal mock flow.

## Success Criteria

1. Guest adds product to cart → views cart → proceeds to checkout → selects COD → order created with stock decremented → sees success page with order number.
2. Same flow with VNPay: redirect to VNPay → return with success hash → order confirmed + paid.
3. Same flow with PayPal: redirect to PayPal → return → capture → order confirmed + paid.
4. Admin views order list → clicks order → changes status to "shipping" → log created.
5. Logged-in user sees their order history at `/{locale}/orders`.
6. Concurrent checkout: 2 users try to buy the last item → one succeeds, other gets "Không đủ hàng" error — no negative stock.
7. All feature tests pass.

## Constraints & Assumptions

- VNPay/PayPal credentials in `.env` — sandbox mode by default. Switching to production = change env vars only.
- PayPal USD conversion uses a static config rate, not a live API (for simplicity + no external dependency).
- No shipping fee calculation — total = subtotal for now. Shipping fee is out of scope.
- No email notification on order — can be added in a follow-up.
- No coupon/discount system — out of scope.
- Guest checkout creates order with `user_id = null`. If user is logged in, `user_id` is set for history.
- Bank transfer orders stay `pending` until admin manually confirms payment.

## Out of Scope

- Shipping fee calculation / delivery zones.
- Email/SMS notifications on order status change.
- Coupons, discounts, promotions.
- Refund processing (admin can mark payment_status=refunded manually).
- Multiple shipping addresses / address book.
- Order invoice PDF generation.
- Webhook-based payment verification (VNPay IPN is GET-based per their spec).
- Cart merge on login (guest cart → user cart).
- Saved payment methods.

## Dependencies

- **Shipped:** `ecommerce-catalog` (Product, ProductVariant, Category models, public detail page with add-to-cart button data attributes, `HasLocalizedContent` trait, `PriceFormatter`).
- **Shipped:** `multi-language` (SetLocale middleware, lang files, `{locale}` route group).
- **Shipped:** Breeze auth (for admin + user order history).
- **External APIs:** VNPay sandbox (https://sandbox.vnpayment.vn), PayPal sandbox (https://api-m.sandbox.paypal.com).
- **Config files needed:** `config/payment.php` (bank info, VNPay, PayPal settings — reads from `.env`).
