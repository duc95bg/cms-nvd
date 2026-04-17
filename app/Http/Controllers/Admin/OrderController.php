<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::with('user')->latest();

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'currentStatus' => $request->status,
            'search' => $request->search,
            'statuses' => ['pending', 'confirmed', 'shipping', 'delivered', 'cancelled'],
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['items', 'statusLogs.changedBy', 'user']);

        $allowedTransitions = collect(['pending', 'confirmed', 'shipping', 'delivered', 'cancelled'])
            ->filter(fn ($status) => $order->canTransitionTo($status))
            ->values();

        return view('admin.orders.show', [
            'order' => $order,
            'allowedTransitions' => $allowedTransitions,
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,confirmed,shipping,delivered,cancelled',
            'note' => 'nullable|string|max:500',
        ]);

        if (!$order->canTransitionTo($validated['status'])) {
            return back()->with('error', __('Cannot transition to this status'));
        }

        $oldStatus = $order->status;
        $order->update(['status' => $validated['status']]);

        // Restore stock on cancellation
        if ($validated['status'] === 'cancelled') {
            foreach ($order->items as $item) {
                if ($item->variant_id) {
                    \App\Models\ProductVariant::where('id', $item->variant_id)
                        ->increment('stock', $item->quantity);
                }
            }
        }

        OrderStatusLog::create([
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $validated['status'],
            'note' => $validated['note'] ?? null,
            'changed_by' => auth()->id(),
        ]);

        return back()->with('success', __('Status updated'));
    }
}
