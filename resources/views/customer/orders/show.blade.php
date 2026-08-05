@extends('layouts.app')
@section('title', 'Detail Pesanan - PrintShop')

@section('content')
<div class="flex items-center mb-6">
    <a href="{{ route('customer.orders.index') }}" class="mr-3 text-gray-500 hover:text-gray-700">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Detail Pesanan</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-gray-900">{{ $order->order_number }}</h2>
                <span class="px-3 py-1 text-sm font-semibold rounded-full
                    {{ $order->status->value === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $order->status->value === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $order->status->value === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $order->status->value === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                    {{ $order->status->label() }}
                </span>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm text-gray-500">File</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $order->original_filename }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Ukuran</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $order->file_size_formatted }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Tanggal Upload</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $order->created_at->format('d M Y H:i') }}</dd>
                </div>
                @if($order->completed_at)
                    <div>
                        <dt class="text-sm text-gray-500">Tanggal Selesai</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $order->completed_at->format('d M Y H:i') }}</dd>
                    </div>
                @endif
            </dl>

            @if($order->notes)
                <div class="mt-6">
                    <dt class="text-sm text-gray-500">Catatan</dt>
                    <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $order->notes }}</dd>
                </div>
            @endif

            @if($order->status->value === 'cancelled' && $order->cancel_reason)
                <div class="mt-6 p-4 bg-red-50 rounded-lg">
                    <dt class="text-sm text-red-700 font-medium">Alasan Pembatalan</dt>
                    <dd class="mt-1 text-sm text-red-600">{{ $order->cancel_reason }}</dd>
                </div>
            @endif
        </div>
    </div>

    <div>
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h3>
            <div class="space-y-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Pesanan Diterima</p>
                        <p class="text-xs text-gray-500">{{ $order->created_at->format('d M Y H:i') }}</p>
                    </div>
                </div>

                @if($order->status->value === 'processing' || $order->status->value === 'completed')
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Sedang Diproses</p>
                            <p class="text-xs text-gray-500">Pesanan sedang dikerjakan</p>
                        </div>
                    </div>
                @endif

                @if($order->status->value === 'completed')
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Selesai</p>
                            <p class="text-xs text-gray-500">{{ $order->completed_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                @endif

                @if($order->status->value === 'cancelled')
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Dibatalkan</p>
                            <p class="text-xs text-gray-500">{{ $order->cancel_reason }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
