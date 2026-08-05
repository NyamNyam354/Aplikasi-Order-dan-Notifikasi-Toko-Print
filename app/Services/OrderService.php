<?php

namespace App\Services;

use App\Constants\OrderConstant;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderService
{
    public function __construct(
        private FileService $fileService,
        private NotificationService $notificationService,
    ) {}

    public function generateOrderNumber(): string
    {
        $today = now()->format('Ymd');
        $prefix = 'ORD-' . $today . '-';

        $lastOrder = Order::where('order_number', 'like', $prefix . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function createOrder(User $user, UploadedFile $file, ?string $notes): Order
    {
        return DB::transaction(function () use ($user, $file, $notes) {
            $storedFilename = $this->fileService->upload($file);

            try {
                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => $this->generateOrderNumber(),
                    'original_filename' => $file->getClientOriginalName(),
                    'stored_filename' => $storedFilename,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'notes' => $notes,
                    'status' => OrderStatus::PENDING,
                ]);

                $this->notificationService->queue(
                    $user,
                    'Pesanan Diterima',
                    "Pesanan {$order->order_number} telah diterima dan akan segera kami proses.",
                    $order
                );

                return $order;
            } catch (\Exception $e) {
                $this->fileService->delete($storedFilename);
                throw $e;
            }
        });
    }

    public function changeStatus(Order $order, OrderStatus $newStatus, ?string $reason = null): Order
    {
        $this->validateTransition($order->status, $newStatus);

        if ($newStatus === OrderStatus::CANCELLED && empty($reason)) {
            throw new InvalidArgumentException('Alasan pembatalan wajib diisi');
        }

        $order->update([
            'status' => $newStatus,
            'cancel_reason' => $reason,
            'completed_at' => $newStatus === OrderStatus::COMPLETED ? now() : $order->completed_at,
        ]);

        $this->sendStatusNotification($order, $newStatus, $reason);

        return $order->fresh();
    }

    public function processing(Order $order): Order
    {
        return $this->changeStatus($order, OrderStatus::PROCESSING);
    }

    public function complete(Order $order): Order
    {
        return $this->changeStatus($order, OrderStatus::COMPLETED);
    }

    public function cancel(Order $order, string $reason): Order
    {
        return $this->changeStatus($order, OrderStatus::CANCELLED, $reason);
    }

    public function getOrders(
        ?string $search = null,
        ?string $status = null,
        ?string $sort = null,
        ?string $startDate = null,
        ?string $endDate = null,
    ): LengthAwarePaginator {
        $query = Order::with('user');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('original_filename', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $query = match ($sort) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'status' => $query->orderBy('status', 'asc'),
            'customer' => $query->join('users', 'orders.user_id', '=', 'users.id')->orderBy('users.name', 'asc')->select('orders.*'),
            default => $query->orderBy('created_at', 'desc'),
        };

        return $query->paginate(OrderConstant::ORDER_PER_PAGE);
    }

    public function getUserOrders(User $user, ?string $status = null): LengthAwarePaginator
    {
        $query = Order::where('user_id', $user->id);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate(OrderConstant::ORDER_PER_PAGE);
    }

    public function getDashboardStats(): array
    {
        return [
            'today' => Order::whereDate('created_at', today())->count(),
            'this_week' => Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => Order::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'pending' => Order::where('status', OrderStatus::PENDING)->count(),
            'processing' => Order::where('status', OrderStatus::PROCESSING)->count(),
            'completed' => Order::where('status', OrderStatus::COMPLETED)->count(),
            'cancelled' => Order::where('status', OrderStatus::CANCELLED)->count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_uploads' => Order::count(),
        ];
    }

    public function getCustomerDashboardStats(User $user): array
    {
        return [
            'total' => Order::where('user_id', $user->id)->count(),
            'pending' => Order::where('user_id', $user->id)->where('status', OrderStatus::PENDING)->count(),
            'processing' => Order::where('user_id', $user->id)->where('status', OrderStatus::PROCESSING)->count(),
            'completed' => Order::where('user_id', $user->id)->where('status', OrderStatus::COMPLETED)->count(),
        ];
    }

    private function validateTransition(OrderStatus $current, OrderStatus $new): void
    {
        $allowed = match ($current) {
            OrderStatus::PENDING => [OrderStatus::PROCESSING, OrderStatus::CANCELLED],
            OrderStatus::PROCESSING => [OrderStatus::COMPLETED],
            default => [],
        };

        if (!in_array($new, $allowed)) {
            throw new InvalidArgumentException(
                "Transisi dari {$current->label()} ke {$new->label()} tidak diizinkan"
            );
        }
    }

    private function sendStatusNotification(Order $order, OrderStatus $status, ?string $reason): void
    {
        $user = $order->user;

        match ($status) {
            OrderStatus::PROCESSING => $this->notificationService->queue(
                $user,
                'Pesanan Diproses',
                "Pesanan {$order->order_number} sedang kami proses. Mohon menunggu.",
                $order
            ),
            OrderStatus::COMPLETED => $this->notificationService->queue(
                $user,
                'Pesanan Selesai',
                "Pesanan {$order->order_number} telah selesai. Silakan cek detail pesanan.",
                $order
            ),
            OrderStatus::CANCELLED => $this->notificationService->queue(
                $user,
                'Pesanan Dibatalkan',
                "Pesanan {$order->order_number} dibatalkan. Alasan: {$reason}",
                $order
            ),
            default => null,
        };
    }
}
