<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PrintShop')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('customer.dashboard') }}" class="text-xl font-bold text-indigo-600">
                        PrintShop
                    </a>
                    <div class="hidden sm:ml-8 sm:flex sm:space-x-4">
                        <a href="{{ route('customer.dashboard') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('customer.dashboard') ? 'text-indigo-600' : 'text-gray-600 hover:text-gray-900' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('customer.orders.index') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('customer.orders.*') ? 'text-indigo-600' : 'text-gray-600 hover:text-gray-900' }}">
                            Riwayat
                        </a>
                        <a href="{{ route('customer.notifications.index') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('customer.notifications.*') ? 'text-indigo-600' : 'text-gray-600 hover:text-gray-900' }}">
                            Notifikasi
                            @if(isset($unreadCount) && $unreadCount > 0)
                                <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full">
                                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                </span>
                            @endif
                        </a>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-sm text-gray-600 hover:text-gray-900">
                            {{ auth()->user()->name }}
                            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            <form method="POST" action="{{ route('customer.logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
