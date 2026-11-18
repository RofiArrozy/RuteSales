@props(['status', 'type' => null])

@php
    $statusNormalized = strtolower($status ?? '');
    
    $classes = match($statusNormalized) {
        'planned', 'direncanakan' => 'bg-blue-100 text-blue-800 border-blue-200',
        'in_progress', 'sedang berjalan' => 'bg-amber-100 text-amber-800 border-amber-200',
        'completed', 'selesai' => 'bg-purple-100 text-purple-800 border-purple-200',
        'cancelled', 'dibatalkan' => 'bg-slate-100 text-slate-800 border-slate-200',
        'successful', 'berhasil' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'store_closed', 'tutup' => 'bg-amber-100 text-amber-800 border-amber-200',
        'failed', 'ditolak' => 'bg-rose-100 text-rose-800 border-rose-200',
        'active', 'aktif' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'inactive', 'nonaktif' => 'bg-slate-100 text-slate-700 border-slate-300',
        'admin' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
        'sales' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
        default => 'bg-gray-100 text-gray-800 border-gray-200'
    };

    $dotClasses = match($statusNormalized) {
        'planned', 'direncanakan' => 'bg-blue-500',
        'in_progress', 'sedang berjalan' => 'bg-amber-500',
        'completed', 'selesai' => 'bg-purple-500',
        'cancelled', 'dibatalkan' => 'bg-slate-500',
        'successful', 'berhasil' => 'bg-emerald-500',
        'store_closed', 'tutup' => 'bg-amber-500',
        'failed', 'ditolak' => 'bg-rose-500',
        'active', 'aktif' => 'bg-emerald-500',
        'inactive', 'nonaktif' => 'bg-slate-400',
        'admin' => 'bg-indigo-500',
        'sales' => 'bg-cyan-500',
        default => 'bg-gray-400'
    };

    $displayLabel = match($statusNormalized) {
        'planned' => 'Direncanakan',
        'in_progress' => 'Sedang Berjalan',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        'successful' => 'Berhasil',
        'store_closed' => 'Toko Tutup',
        'failed' => 'Ditolak / Gagal',
        'active' => 'Aktif',
        'inactive' => 'Nonaktif',
        default => ($status ?? '-')
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border $classes"]) }}>
    <span class="w-1.5 h-1.5 mr-1.5 rounded-full {{ $dotClasses }}"></span>
    {{ $slot->isEmpty() ? $displayLabel : $slot }}
</span>
