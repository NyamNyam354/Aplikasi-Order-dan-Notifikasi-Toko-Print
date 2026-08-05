@extends('layouts.guest')
@section('title', '403 - Akses Ditolak')

@section('content')
<div class="bg-white rounded-lg shadow-md p-8 text-center">
    <h1 class="text-6xl font-bold text-indigo-600">403</h1>
    <h2 class="mt-4 text-2xl font-bold text-gray-900">Akses Ditolak</h2>
    <p class="mt-2 text-gray-600">Anda tidak memiliki akses ke halaman ini.</p>
    <a href="{{ route('home') }}" class="mt-6 inline-block bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
        Kembali ke Beranda
    </a>
</div>
@endsection
