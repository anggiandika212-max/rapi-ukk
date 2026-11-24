
@php
use Illuminate\Support\Facades\Route;

$currentRouteName = Route::currentRouteName() ?? '';
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    {{-- Brand --}}
    <div class="app-brand demo">
        <a href="{{ url('#') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                @include('_partials.macros')
            </span>
            <span class="app-brand-text demo menu-text fw-bold ms-2">
                RAPI-Sekolah
            </span>
        </a>

        <a href="javascript:void(0);"
           class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="icon-base bx bx-chevron-left icon-sm d-flex align-items-center justify-content-center"></i>
        </a>
    </div>

    <div class="menu-divider mt-0"></div>
    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">

        {{-- MENU UTAMA --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Menu Utama</span>
        </li>

        <li class="menu-item {{ request()->is('rapi/dashboard') ? 'active' : '' }}">
            <a href="{{ url('/rapi/dashboard') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-home-circle"></i>
                <div>Dashboard</div>
            </a>
        </li>

        <li class="menu-item {{ request()->is('rapi/calendar') ? 'active' : '' }}">
            <a href="{{ url('/rapi/calendar') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-calendar-event"></i>
                <div>Kalender Agenda</div>
            </a>
        </li>

        {{-- DATA DAN PEMINJAMAN --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Fasilitas Sekolah</span>
        </li>

        <li class="menu-item {{ request()->is('rapi/rooms') ? 'active' : '' }}">
            <a href="{{ url('/rapi/rooms') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-buildings"></i>
                <div>Data Ruangan</div>
            </a>
        </li>

        <li class="menu-item {{ request()->is('rapi/events') ? 'active' : '' }}">
            <a href="{{ url('/rapi/events') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-calendar-check"></i>
                <div>Agenda</div>
            </a>
        </li>

        {{-- PENGADUAN --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Pemeliharaan</span>
        </li>

        <li class="menu-item {{ request()->is('rapi/complaints') ? 'active' : '' }}">
            <a href="{{ url('/rapi/complaints') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-message-square-error"></i>
                <div>Laporan Kerusakan</div>
            </a>
        </li>

        {{-- ADMIN --}}
        <li class="menu-header small text-uppercase menu-admin d-none">
            <span class="menu-header-text">Administrasi</span>
        </li>

        <li class="menu-item menu-admin d-none {{ request()->is('rapi/users') ? 'active' : '' }}">
            <a href="{{ url('/rapi/users') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-user"></i>
                <div>Manajemen User</div>
            </a>
        </li>

        <li class="menu-item menu-admin d-none {{ request()->is('rapi/reports/monthly') ? 'active' : '' }}">
            <a href="{{ url('/rapi/reports/monthly') }}" class="menu-link">
                <i class="menu-icon icon-base bx bx-file"></i>
                <div>Laporan Bulanan</div>
            </a>
        </li>
    </ul>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof rapiRole !== 'function') {
        console.warn('Fungsi rapiRole() belum tersedia.');
        return;
    }

    const role = rapiRole();

    // Menu administrasi hanya untuk Admin.
    if (role === 'Admin') {
        document.querySelectorAll('.menu-admin').forEach(function (element) {
            element.classList.remove('d-none');
        });
    }
});
</script>
