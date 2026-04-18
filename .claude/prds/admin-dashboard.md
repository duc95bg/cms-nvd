---
name: admin-dashboard
description: Admin analytics dashboard — revenue stats, order counts, top products, recent orders, simple CSS charts
status: backlog
created: 2026-04-18T00:00:00Z
---

# PRD: admin-dashboard

## Executive Summary
Trang dashboard tổng hợp cho admin tại `/admin/dashboard` hiển thị: tổng doanh thu, tổng đơn hàng, đơn hôm nay, sản phẩm bán chạy, đơn hàng gần đây, biểu đồ doanh thu 7 ngày. Không dùng charting library — chỉ Tailwind CSS bars/progress. Data từ Order/OrderItem models đã có.

## User Stories

### US-1 — Xem tổng quan kinh doanh
**As** admin **I want** thấy ngay các chỉ số chính khi mở admin **So that** tôi nắm tình hình nhanh.

**Acceptance criteria:**
- 4 stat cards: Tổng doanh thu (tổng `orders.total` where paid), Tổng đơn hàng, Đơn hôm nay, Đơn chờ xử lý (pending).
- Mỗi card: icon, label, số liệu formatted.

### US-2 — Biểu đồ doanh thu 7 ngày
**As** admin **I want** thấy trend doanh thu 7 ngày gần nhất **So that** tôi biết xu hướng.

**Acceptance criteria:**
- Bar chart đơn giản bằng CSS (div width proportional to max value).
- 7 bars, mỗi bar = 1 ngày, label ngày + số tiền.
- Chỉ tính đơn `payment_status=paid`.

### US-3 — Top 5 sản phẩm bán chạy
**As** admin **I want** biết sản phẩm nào bán nhiều nhất **So that** tôi biết focus inventory.

**Acceptance criteria:**
- Table: #, Tên sản phẩm, Số lượng bán, Doanh thu.
- Aggregate từ `order_items` group by `product_id`, sum quantity + line_total.
- Top 5, chỉ tính đơn không cancelled.

### US-4 — Đơn hàng gần đây
**As** admin **I want** thấy 10 đơn hàng mới nhất **So that** tôi xử lý nhanh.

**Acceptance criteria:**
- Table: Mã đơn (link), Khách hàng, Tổng tiền, Trạng thái (badge), Ngày.
- 10 đơn mới nhất, link sang `/admin/orders/{id}`.

## Functional Requirements

### FR-1 — DashboardService
```php
class DashboardService {
    public static function getStats(): array  // total_revenue, total_orders, today_orders, pending_orders
    public static function getRevenueChart(int $days = 7): array  // [{date, total}]
    public static function getTopProducts(int $limit = 5): Collection  // product_name, total_qty, total_revenue
    public static function getRecentOrders(int $limit = 10): Collection
}
```

### FR-2 — Routing
- `GET /admin/dashboard` → `Admin\DashboardController@index`
- Redirect `/admin` → `/admin/dashboard` (convenience).

### FR-3 — View
- Standalone admin Blade + Tailwind CDN.
- Navigation links to other admin sections (products, orders, categories, settings).
- 4 stat cards in grid.
- Revenue chart: CSS bars.
- 2-column below: top products table + recent orders table.

## Out of Scope
- Real-time updates, WebSocket.
- Date range picker, custom periods.
- Export to PDF/Excel.
- Charts with JS libraries.

## Dependencies
- **Shipped:** Order, OrderItem models (from cart-checkout epic).
- **Shipped:** Product model (from ecommerce-catalog).
- **Shipped:** PriceFormatter helper.
