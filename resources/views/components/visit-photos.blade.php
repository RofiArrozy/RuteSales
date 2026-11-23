@props(['visit'])

{{-- Galeri foto bukti kunjungan (file diambil lewat route terotorisasi, bukan link publik). --}}
@if($visit->photos->isNotEmpty())
    <div class="flex flex-wrap gap-2 mt-2">
        @foreach($visit->photos as $photo)
            <a href="{{ route('visit-photos.show', $photo->id) }}" target="_blank" rel="noopener"
               class="block w-20 h-20 rounded-lg overflow-hidden border border-slate-200 bg-slate-100 hover:ring-2 hover:ring-blue-500/40 transition-all">
                <img src="{{ route('visit-photos.show', $photo->id) }}" loading="lazy" alt="Foto bukti kunjungan"
                     class="w-full h-full object-cover">
            </a>
        @endforeach
    </div>
@elseif($visit->isCheckedIn())
    <p class="text-xs text-slate-400 italic mt-2">Tanpa foto bukti (data dicatat sebelum fitur foto ada).</p>
@endif
