<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\NotificationService;
use App\Services\OrderService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private NotificationService $notificationService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $stats = $this->orderService->getCustomerDashboardStats($user);
        $recentOrders = Order::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        $unreadCount = $this->notificationService->getUnreadCount($user);

        return view('customer.dashboard', compact('stats', 'recentOrders', 'unreadCount'));
    }
}
