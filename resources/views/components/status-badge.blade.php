@props(['status'])

@php
    $statusLower = strtolower($status);
    $classes = match($statusLower) {
        'active', 'completed', 'successful' => 'bg-green-100 text-green-800 border-green-300',
        'in_progress', 'planned' => 'bg-blue-100 text-blue-800 border-blue-300',
        'inactive', 'cancelled' => 'bg-gray-100 text-gray-800 border-gray-300',
        'failed', 'store_closed' => 'bg-red-100 text-red-800 border-red-300',
        default => 'bg-gray-100 text-gray-700 border-gray-200',
    };
    
    $labels = [
        'active' => 'Aktif',
        'inactive' => 'Nonaktif',
        'planned' => 'Rencana',
        'in_progress' => 'Berlangsung',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        'successful' => 'Berhasil',
        'store_closed' => 'Toko Tutup',
        'failed' => 'Gagal',
    ];

    $label = $labels[$statusLower] ?? ucfirst($status);
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $classes }}">
    {{ $label }}
</span>