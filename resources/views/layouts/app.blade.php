<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'RuteSales' }} — Sistem Pengelolaan Rute Penjualan</title>
    <!-- Tailwind CSS CDN (Dapat disesuaikan via Vite/Tailwind CLI jika diperlukan) -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-900">
    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- Sidebar Navigasi -->
        <aside class="w-full md:w-64 bg-slate-900 text-white flex-shrink-0">
            <div class="p-4 text-xl font-bold border-b border-slate-800 flex items-center justify-between">
                <span>RuteSales</span>
                <span class="text-xs bg-blue-600 px-2 py-1 rounded uppercase tracking-wider">
                    {{ auth()->user()->role ?? 'Guest' }}
                </span>
            </div>
            
            <nav class="p-4 space-y-1">
                <a href="{{ route('dashboard') }}" 
                   class="block px-4 py-2.5 rounded transition hover:bg-slate-800 {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-gray-300' }}">
                    📊 Dashboard
                </a>

                @if(auth()->check() && auth()->user()->isAdmin())
                    <!-- Navigasi Khusus Admin (Master Data & Rute) -->
                    <div class="pt-4 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider px-4">
                        Manajemen Master
                    </div>
                    <a href="{{ route('sales.index') }}" 
                       class="block px-4 py-2 rounded transition hover:bg-slate-800 {{ request()->routeIs('sales.*') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-gray-300' }}">
                        👤 Data Sales
                    </a>
                    <a href="{{ route('stores.index') }}" 
                       class="block px-4 py-2 rounded transition hover:bg-slate-800 {{ request()->routeIs('stores.*') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-gray-300' }}">
                        🏪 Data Toko
                    </a>
                    <a href="{{ route('routes.index') }}" 
                       class="block px-4 py-2 rounded transition hover:bg-slate-800 {{ request()->routeIs('routes.*') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-gray-300' }}">
                        🗺️ Rute Kunjungan
                    </a>
                    <a href="{{ route('reports.index') }}" 
                       class="block px-4 py-2 rounded transition hover:bg-slate-800 {{ request()->routeIs('reports.*') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-gray-300' }}">
                        📈 Laporan Rekap
                    </a>
                @endif

                @if(auth()->check() && auth()->user()->isSales())
                    <!-- Navigasi Khusus Sales -->
                    <div class="pt-4 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider px-4">
                        Tugas Lapangan
                    </div>
                    <a href="{{ route('route-visits.index') }}" 
                       class="block px-4 py-2 rounded transition hover:bg-slate-800 {{ request()->routeIs('route-visits.*') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-gray-300' }}">
                        📋 Kunjungan Saya
                    </a>
                @endif
            </nav>
        </aside>

        <!-- Area Konten Utama -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Header Top Bar -->
            <header class="bg-white shadow-sm border-b px-6 py-4 flex justify-between items-center">
                <h1 class="text-lg font-semibold text-gray-800">
                    {{ $headerTitle ?? 'Aplikasi RuteSales' }}
                </h1>
                
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">
                        {{ auth()->user()->name ?? 'User' }}
                    </span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="text-sm bg-red-50 hover:bg-red-100 text-red-600 font-medium px-3 py-1.5 rounded transition border border-red-200">
                            Keluar
                        </button>
                    </form>
                </div>
            </header>

            <!-- Main Content Container -->
            <main class="flex-1 p-6">
                <!-- Komponen Alert Flash Message Global -->
                <x-alert />

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>