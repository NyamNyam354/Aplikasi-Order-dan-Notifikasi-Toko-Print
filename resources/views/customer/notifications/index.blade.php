@extends('layouts.app')
@section('title', 'Notifikasi - PrintShop')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Notifikasi</h1>
    @if($unreadCount > 0)
        <form method="POST" action="{{ route('customer.notifications.markAllAsRead') }}">
            @csrf
            <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-500">
                Tandai semua sudah dibaca
            </button>
        </form>
    @endif
</div>

@if($notifications->count() > 0)
    <div class="space-y-3">
        @foreach($notifications as $notification)
            <div class="bg-white rounded-lg shadow-sm p-4 {{ !$notification->is_read ? 'border-l-4 border-indigo-500' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ $notification->title }}</p>
                        <p class="mt-1 text-sm text-gray-600">{{ $notification->message }}</p>
                        <p class="mt-2 text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @if(!$notification->is_read)
                        <form method="POST" action="{{ route('customer.notifications.markAsRead', $notification) }}" class="ml-4">
                            @csrf
                            <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-500">
                                Tandai dibaca
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
@else
    <div class="bg-white rounded-lg shadow-sm p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">Belum ada notifikasi</h3>
        <p class="mt-2 text-sm text-gray-500">Notifikasi akan muncul di sini.</p>
    </div>
@endif
@endsection
