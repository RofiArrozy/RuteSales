<x-layout title="Kunjungan Saya">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Kunjungan Saya</h1>
            <p class="text-sm text-slate-500 mt-1">
                Daftar rute dan titik toko yang ditugaskan khusus kepada Anda: <strong class="text-slate-700">{{ $sales->name }}</strong>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                ● Sales Lapangan Aktif
            </span>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs mb-6">
        <form method="GET" action="{{ route('my-visits.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-6">
                <input type="date" name="date" value="{{ request('date') }}" 
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            </div>

            <div class="sm:col-span-4">
                <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    <option value="">Semua Status Rute</option>
                    <option value="planned" {{ request('status') == 'planned' ? 'selected' : '' }}>Direncanakan</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Sedang Berjalan</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <button type="submit" class="w-full px-3 py-2 bg-blue-600 text-white rounded-xl text-xs font-semibold hover:bg-blue-700 transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Routes & Stops List -->
    <div class="space-y-6">
        @forelse($routes as $route)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <!-- Route Card Header -->
                <div class="p-5 bg-slate-50/70 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h2 class="text-base font-bold text-slate-900">{{ $route->name }}</h2>
                            <x-badge :status="$route->status" />
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            📅 Tanggal: <strong class="text-slate-700">{{ $route->route_date->translatedFormat('l, d F Y') }}</strong>
                            &bull; 🏬 {{ $route->visits->count() }} Toko
                        </p>
                    </div>

                    <div class="text-xs text-slate-500">
                        Progres: <strong class="text-blue-600">{{ $route->visits->whereNotNull('result')->count() }} / {{ $route->visits->count() }}</strong> Selesai
                    </div>
                </div>

                <!-- Stops in Route -->
                <div class="divide-y divide-slate-100">
                    @foreach($route->visits as $visit)
                        <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/50 transition-colors">
                            <div class="flex items-start gap-3.5">
                                <div class="w-8 h-8 rounded-lg {{ $visit->isCheckedIn() ? 'bg-emerald-600' : 'bg-slate-700' }} text-white font-bold flex items-center justify-center text-xs shrink-0 mt-0.5">
                                    #{{ $visit->sequence }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-bold text-slate-900 text-sm">
                                            {{ $visit->store->name ?? 'Toko' }}
                                        </h3>
                                        @if($visit->result)
                                            <x-badge :status="$visit->result" />
                                        @else
                                            <span class="text-[11px] font-medium text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">
                                                Belum Check-in
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        📍 {{ $visit->store->address ?? '-' }}
                                    </p>
                                    @if($visit->isCheckedIn())
                                        <div class="text-xs text-slate-500 mt-1 flex flex-wrap gap-2">
                                            <span>⏱ Waktu: {{ $visit->checked_in_at->translatedFormat('H:i') }} WIB</span>
                                            @if($visit->order)
                                                <span class="text-emerald-700 font-semibold">🛒 Rp {{ number_format($visit->order->total_amount, 0, ',', '.') }}</span>
                                            @endif
                                            @if($visit->failure_reason)
                                                <span class="text-rose-600">⚠ {{ $visit->failure_reason }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="shrink-0 self-end sm:self-center">
                                <a href="{{ route('my-visits.show', $visit->id) }}" 
                                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold shadow-xs transition-all {{ $visit->isCheckedIn() ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                    {{ $visit->isCheckedIn() ? 'Lihat / Edit Check-in' : 'Check-in Toko Ini' }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <h3 class="text-base font-bold text-slate-700 mb-1">Belum Ada Tugas Kunjungan</h3>
                <p class="text-xs text-slate-400">Tidak ada rute yang ditugaskan kepada Anda pada periode ini.</p>
            </div>
        @endforelse

        @if($routes->hasPages())
            <div class="p-4 bg-white rounded-2xl border border-slate-100">
                {{ $routes->links() }}
            </div>
        @endif
    </div>
</x-layout>
