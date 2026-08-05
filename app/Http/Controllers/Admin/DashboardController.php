<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Services\OrderService;

class DashboardController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private NotificationService $notificationService,
    ) {}

    public function index()
    {
        $stats = $this->orderService->getDashboardStats();

        return view('admin.dashboard', compact('stats'));
    }
}
