<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelOrderRequest;
use App\Models\Order;
use App\Services\FileService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private FileService $fileService,
    ) {}

    public function index(Request $request)
    {
        $orders = $this->orderService->getOrders(
            $request->get('search'),
            $request->get('status'),
            $request->get('sort'),
            $request->get('start_date'),
            $request->get('end_date'),
        );

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        return view('admin.orders.show', compact('order'));
    }

    public function processing(Order $order): RedirectResponse
    {
        try {
            $this->orderService->processing($order);
            return back()->with('success', 'Pesanan sedang diproses.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function complete(Order $order): RedirectResponse
    {
        try {
            $this->orderService->complete($order);
            return back()->with('success', 'Pesanan selesai.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(CancelOrderRequest $request, Order $order): RedirectResponse
    {
        try {
            $this->orderService->cancel($order, $request->get('cancel_reason'));
            return back()->with('success', 'Pesanan dibatalkan.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function download(Order $order)
    {
        if (!$this->fileService->exists($order->stored_filename)) {
            return back()->with('error', 'File tidak tersedia.');
        }

        return $this->fileService->download($order->stored_filename);
    }
}
