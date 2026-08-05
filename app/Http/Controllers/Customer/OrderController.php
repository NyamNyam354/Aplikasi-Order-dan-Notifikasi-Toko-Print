<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    public function index(Request $request)
    {
        $orders = $this->orderService->getUserOrders($request->user(), $request->get('status'));

        return view('customer.orders.index', compact('orders'));
    }

    public function create()
    {
        return view('customer.orders.create');
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $order = $this->orderService->createOrder(
            $request->user(),
            $request->file('file'),
            $request->get('notes')
        );

        return redirect()->route('customer.orders.show', $order)
            ->with('success', 'Pesanan berhasil dikirim!');
    }

    public function show(Order $order)
    {
        Gate::authorize('view', $order);

        return view('customer.orders.show', compact('order'));
    }
}
