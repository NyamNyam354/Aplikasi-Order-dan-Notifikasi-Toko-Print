@extends('layouts.admin')
@section('title', 'Dashboard Admin - PrintShop')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-500">Hari Ini</p>
        <p class="text-3xl font-bold text-gray-900">{{ $stats['today'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-500">Minggu Ini</p>
        <p class="text-3xl font-bold text-gray-900">{{ $stats['this_week'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-500">Bulan Ini</p>
        <p class="text-3xl font-bold text-gray-900">{{ $stats['this_month'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <p class="text-sm text-gray-500">Total Customer</p>
        <p class="text-3xl font-bold text-gray-900">{{ $stats['total_customers'] }}</p>
    </div>
</div>

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}"
       class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 hover:shadow-md transition-shadow">
        <p class="text-sm text-yellow-600">Pending</p>
        <p class="text-3xl font-bold text-yellow-700">{{ $stats['pending'] }}</p>
    </a>
    <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}"
       class="bg-blue-50 border border-blue-200 rounded-lg p-6 hover:shadow-md transition-shadow">
        <p class="text-sm text-blue-600">Processing</p>
        <p class="text-3xl font-bold text-blue-700">{{ $stats['processing'] }}</p>
    </a>
    <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}"
       class="bg-green-50 border border-green-200 rounded-lg p-6 hover:shadow-md transition-shadow">
        <p class="text-sm text-green-600">Completed</p>
        <p class="text-3xl font-bold text-green-700">{{ $stats['completed'] }}</p>
    </a>
    <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}"
       class="bg-red-50 border border-red-200 rounded-lg p-6 hover:shadow-md transition-shadow">
        <p class="text-sm text-red-600">Cancelled</p>
        <p class="text-3xl font-bold text-red-700">{{ $stats['cancelled'] }}</p>
    </a>
</div>
@endsection
