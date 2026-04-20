<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => DashboardService::getStats(),
            'chart' => DashboardService::getRevenueChart(),
            'topProducts' => DashboardService::getTopProducts(),
            'recentOrders' => DashboardService::getRecentOrders(),
        ]);
    }
}
