<div style="position: fixed; bottom: 0; left: 0; z-index: 50; width: 100%; height: 64px; background-color: white; border-top: 1px solid #e5e7eb; display: flex;" class="md:hidden">
    <style>
        @media (max-width: 768px) {
            .fi-topbar-open-sidebar-btn { display: none !important; }
            .fi-topbar { padding-left: 1rem; }
        }
    </style>
    <div style="display: grid; grid-template-columns: repeat(5, 1fr); width: 100%; max-width: 32rem; margin: 0 auto; height: 100%;">
        <a href="/admin" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 0 0.5rem;" class="group">
            <x-heroicon-o-home style="width: 24px; height: 24px; margin-bottom: 4px;" class="{{ request()->routeIs('filament.admin.pages.dashboard') ? 'text-primary-600' : 'text-gray-500' }}" />
            <span style="font-size: 0.65rem;" class="{{ request()->routeIs('filament.admin.pages.dashboard') ? 'text-primary-600' : 'text-gray-500' }}">Home</span>
        </a>

        <a href="/admin/pendaftar-lingkungans" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 0 0.5rem;" class="group">
            <x-heroicon-o-document-plus style="width: 24px; height: 24px; margin-bottom: 4px;" class="{{ request()->is('admin/pendaftar-lingkungans*') ? 'text-primary-600' : 'text-gray-500' }}" />
            <span style="font-size: 0.65rem;" class="{{ request()->is('admin/pendaftar-lingkungans*') ? 'text-primary-600' : 'text-gray-500' }}">Daftar</span>
        </a>

        <a href="/admin/ekspedisi" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 0 0.5rem;" class="group">
            <x-heroicon-o-truck style="width: 24px; height: 24px; margin-bottom: 4px;" class="{{ request()->is('admin/ekspedisi*') ? 'text-primary-600' : 'text-gray-500' }}" />
            <span style="font-size: 0.65rem;" class="{{ request()->is('admin/ekspedisi*') ? 'text-primary-600' : 'text-gray-500' }}">Ekspedisi</span>
        </a>

        <a href="/admin/transaksis" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 0 0.5rem;" class="group">
            <x-heroicon-o-banknotes style="width: 24px; height: 24px; margin-bottom: 4px;" class="{{ request()->is('admin/transaksis*') ? 'text-primary-600' : 'text-gray-500' }}" />
            <span style="font-size: 0.65rem;" class="{{ request()->is('admin/transaksis*') ? 'text-primary-600' : 'text-gray-500' }}">Keuangan</span>
        </a>

        <a href="/admin/laporan" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 0 0.5rem;" class="group">
            <x-heroicon-o-chart-bar style="width: 24px; height: 24px; margin-bottom: 4px;" class="{{ request()->is('admin/laporan*') ? 'text-primary-600' : 'text-gray-500' }}" />
            <span style="font-size: 0.65rem;" class="{{ request()->is('admin/laporan*') ? 'text-primary-600' : 'text-gray-500' }}">Laporan</span>
        </a>
    </div>
</div>
