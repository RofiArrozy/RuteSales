<x-layout title="Kelola Rute Kunjungan">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Kelola Rute Kunjungan Multi-Toko</h1>
            <p class="text-sm text-slate-500 mt-1">Penyusunan agenda penugasan sales ke beberapa toko mitra dalam satu rute terurut.</p>
        </div>
        <a href="{{ route('rute.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Rute Baru
        </a>
    </div>

    <!-- Filter & Search Section -->
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs mb-6">
        <form method="GET" action="{{ route('rute.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama rute, sales, atau toko..." 
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            </div>

            <div class="sm:col-span-3">
                <select name="sales_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    <option value="">Semua Sales</option>
                    @foreach($activeSales as $sales)
                        <option value="{{ $sales->id }}" {{ request('sales_id') == $sales->id ? 'selected' : '' }}>{{ $sales->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="planned" {{ request('status') == 'planned' ? 'selected' : '' }}>Direncanakan</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Sedang Berjalan</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <input type="date" name="date" value="{{ request('date') }}" 
                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            </div>

            <div class="sm:col-span-1">
                <button type="submit" class="w-full px-3 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Routes Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3.5">Tanggal &amp; ID</th>
                        <th class="px-5 py-3.5">Nama Rute</th>
                        <th class="px-5 py-3.5">Sales Ditugaskan</th>
                        <th class="px-5 py-3.5">Titik Toko</th>
                        <th class="px-5 py-3.5">Status Rute</th>
                        <th class="px-5 py-3.5">Progres Check-in</th>
                        <th class="px-5 py-3.5">Total Omzet</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($routes as $route)
                        @php
                            $totalVisits = $route->visits->count();
                            $completedVisits = $route->visits->whereNotNull('result')->count();
                            $routeRevenue = $route->visits->sum(fn($v) => $v->order ? $v->order->total_amount : 0);
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-mono text-xs font-bold text-slate-400">#{{ $route->id }}</span>
                                <div class="font-semibold text-slate-800 text-xs mt-0.5">
                                    {{ $route->route_date->translatedFormat('d M Y') }}
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <a href="{{ route('rute.detail', $route->id) }}" class="font-bold text-slate-900 hover:text-blue-600 block">
                                    {{ $route->name }}
                                </a>
                                @if($route->notes)
                                    <p class="text-xs text-slate-400 mt-0.5 line-clamp-1">
                                        {{ $route->notes }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-semibold text-slate-700">{{ $route->sales->name ?? '-' }}</span>
                                <div class="text-[11px] text-slate-400">{{ $route->sales->phone ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ $totalVisits }} Toko
                                </span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <x-badge :status="$route->status" />
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-16 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $totalVisits > 0 ? ($completedVisits / $totalVisits) * 100 : 0 }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-slate-600">{{ $completedVisits }}/{{ $totalVisits }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap font-semibold">
                                @if($routeRevenue > 0)
                                    <span class="text-emerald-600">Rp {{ number_format($routeRevenue, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-right space-x-1">
                                <a href="{{ route('rute.detail', $route->id) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors">
                                    Detail Rute
                                </a>
                                <a href="{{ route('rute.edit', $route->id) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">
                                    Edit
                                </a>
                                <form action="{{ route('rute.destroy', $route->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus rute ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Tidak ada data rute yang cocok dengan kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($routes->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">
                {{ $routes->links() }}
            </div>
        @endif
    </div>
</x-layout>
