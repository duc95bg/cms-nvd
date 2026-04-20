<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public static function getStats(): array
    {
        return [
            'total_revenue' => (float) Order::where('payment_status', 'paid')->sum('total'),
            'total_orders' => Order::count(),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
        ];
    }

    public static function getRevenueChart(int $days = 7): array
    {
        $startDate = Carbon::today()->subDays($days - 1);

        $revenues = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $chart = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $key = $date->format('Y-m-d');
            $chart[] = [
                'date' => $key,
                'label' => $date->format('d/m'),
                'total' => (float) ($revenues[$key] ?? 0),
            ];
        }

        return $chart;
    }

    public static function getTopProducts(int $limit = 5): Collection
    {
        return OrderItem::select(
                'product_name',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(line_total) as total_revenue')
            )
            ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();
    }

    public static function getRecentOrders(int $limit = 10): Collection
    {
        return Order::latest()->limit($limit)->get();
    }
}
