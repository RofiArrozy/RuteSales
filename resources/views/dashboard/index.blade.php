<x-app-layout title="Dashboard">
    <x-page-header 
        title="Dashboard Overview" 
        subtitle="Selamat datang kembali di panel kendali RuteSales.">
        <x-slot:actions>
            <button onclick="window.location.reload()" 
                    class="bg-white border text-gray-700 text-sm font-medium px-3 py-2 rounded shadow-sm hover:bg-gray-50">
                🔄 Refresh Data
            </button>
        </x-slot:actions>
    </x-page-header>

    <!-- Ringkasan Statistik Contoh -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-sm font-medium text-gray-500">Status Akun</h3>
            <div class="mt-2 flex items-center justify-between">
                <span class="text-xl font-bold text-gray-800">{{ auth()->user()->name }}</span>
                <x-status-badge status="active" />
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-sm font-medium text-gray-500">Peran Sistem</h3>
            <p class="text-2xl font-bold text-blue-600 mt-1 uppercase">{{ auth()->user()->role }}</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-sm font-medium text-gray-500">Integrasi Database</h3>
            <p class="text-2xl font-bold text-green-600 mt-1">Terhubung</p>
        </div>
    </div>
</x-app-layout>