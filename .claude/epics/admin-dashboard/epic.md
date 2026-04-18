---
name: admin-dashboard
status: backlog
created: 2026-04-18T00:00:00Z
updated: 2026-04-18T15:06:18Z
progress: 0%
prd: .claude/prds/admin-dashboard.md
github: https://github.com/duc95bg/cms-nvd/issues/50
---

# Epic: admin-dashboard

## Overview
Admin dashboard tại `/admin/dashboard` — 4 stat cards, biểu đồ doanh thu 7 ngày (CSS bars), top 5 sản phẩm, 10 đơn gần đây. `DashboardService` aggregate từ Order/OrderItem. Không external lib. Simple, fast, production-ready.

## Architecture Decisions
1. **DashboardService = static query methods.** Trả raw data, controller format rồi pass to view.
2. **CSS bar chart.** Div width `style="width: {percent}%"` relative to max daily revenue. Không SVG, không JS chart lib.
3. **Admin nav partial.** Shared nav bar cho tất cả admin pages — reuse across epics.
4. **No caching.** Queries simple aggregate — fast enough cho single-tenant.

## Task Breakdown
1. **DashboardService** — service class with 4 static methods (S, 1h)
2. **DashboardController + view** — controller + Blade with stat cards, chart, tables (M, 2h)
3. **Admin nav partial** — shared navigation for all admin pages (XS, 0.5h)
4. **Routes + lang keys** — wire route, redirect /admin, add lang keys (XS, 0.5h)
5. **Feature tests** — test stats, chart data, controller response (S, 1h)

Total: ~5h serial, ~3h parallel.

## Tasks Created
- [ ] 001.md - DashboardService (parallel: true)
- [ ] 002.md - DashboardController + view (parallel: false)
- [ ] 003.md - Admin nav partial (parallel: true)
- [ ] 004.md - Routes + lang keys (parallel: false)
- [ ] 005.md - Feature tests (parallel: false)

Total tasks: 5
Execution: 001+003 parallel → 002 → 004 → 005
Estimated: ~5h serial, ~3h parallel
