<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DatabaseNotification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService,
    ) {}

    public function index(Request $request)
    {
        $notifications = $this->notificationService->getNotifications($request->user());
        $unreadCount = $this->notificationService->getUnreadCount($request->user());

        return view('customer.notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markAsRead(DatabaseNotification $notification): RedirectResponse
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $this->notificationService->markAsRead($notification);

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllAsRead(): RedirectResponse
    {
        $this->notificationService->markAllAsRead(auth()->user());

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
