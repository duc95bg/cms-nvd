<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (!SetupController::isSetUp()) {
            return redirect()->route('admin.setup');
        }

        return view('admin.dashboard', [
            'stats' => DashboardService::getStats(),
            'chart' => DashboardService::getRevenueChart(),
            'topProducts' => DashboardService::getTopProducts(),
            'recentOrders' => DashboardService::getRecentOrders(),
            'mainSite' => SetupController::getMainSite(),
        ]);
    }
}
