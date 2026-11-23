@props(['visit'])

@php
    $existing = $visit->photos;
    $needsFirst = $existing->isEmpty();
@endphp

<div class="p-4 rounded-xl bg-slate-50 border {{ $errors->has('photos') || $errors->has('photos.*') ? 'border-rose-300' : 'border-slate-200' }} space-y-3">
    <div>
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">
            Foto Bukti Kunjungan @if($needsFirst)<span class="text-rose-500">*</span>@endif
        </h3>
        <p class="text-[11px] text-slate-500 mt-1">
            Wajib minimal 1 foto, maksimal 3. Contoh: foto depan toko, nota/pesanan (berhasil), pintu tertutup (toko tutup),
            atau bukti penolakan. Foto bisa diambil dari kamera atau dipilih dari galeri.
        </p>
    </div>

    @if($existing->isNotEmpty())
        <div>
            <p class="text-[11px] font-semibold text-slate-600 mb-1.5">Foto yang sudah tersimpan (centang untuk menghapus)</p>
            <div class="flex flex-wrap gap-3">
                @foreach($existing as $photo)
                    <label class="relative block w-20 cursor-pointer">
                        <img src="{{ route('visit-photos.show', $photo->id) }}" alt="Foto bukti tersimpan"
                             class="w-20 h-20 object-cover rounded-lg border border-slate-200">
                        <span class="flex items-center gap-1 mt-1 text-[11px] text-rose-600">
                            <input type="checkbox" name="remove_photos[]" value="{{ $photo->id }}" class="rounded border-slate-300">
                            Hapus
                        </span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-3 gap-3">
        @for($i = 1; $i <= 3; $i++)
            <label class="relative block aspect-square rounded-xl border-2 border-dashed border-slate-300 bg-white overflow-hidden cursor-pointer hover:border-blue-400 transition-colors">
                <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" data-photo-input
                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                <img data-photo-preview alt="Pratinjau foto" class="hidden absolute inset-0 w-full h-full object-cover">
                <span data-photo-placeholder class="absolute inset-0 flex flex-col items-center justify-center text-center px-1 text-[11px] text-slate-500">
                    <span class="text-2xl leading-none mb-1">📷</span>
                    Foto {{ $i }}<br>{{ $i === 1 && $needsFirst ? '(wajib)' : '(opsional)' }}
                </span>
            </label>
        @endfor
    </div>

    @error('photos')
        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
    @enderror
    @foreach($errors->get('photos.*') as $messages)
        @foreach($messages as $message)
            <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
        @endforeach
    @endforeach
</div>

@once
    @push('scripts')
        <script>
            // Pratinjau + kompres foto di browser (maks. 1600px, JPEG) agar tidak terkena batas ukuran upload server.
            (function () {
                async function compress(file) {
                    const bmp = await createImageBitmap(file);
                    const scale = Math.min(1, 1600 / Math.max(bmp.width, bmp.height));
                    const canvas = document.createElement('canvas');
                    canvas.width = Math.round(bmp.width * scale);
                    canvas.height = Math.round(bmp.height * scale);
                    canvas.getContext('2d').drawImage(bmp, 0, 0, canvas.width, canvas.height);
                    const blob = await new Promise((r) => canvas.toBlob(r, 'image/jpeg', 0.8));
                    if (!blob || blob.size >= file.size) return file;
                    return new File([blob], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' });
                }

                document.addEventListener('change', async (e) => {
                    const input = e.target;
                    if (!input.matches || !input.matches('[data-photo-input]')) return;

                    const box = input.closest('label');
                    const img = box.querySelector('[data-photo-preview]');
                    const ph = box.querySelector('[data-photo-placeholder]');
                    const file = input.files[0];

                    if (!file) {
                        img.classList.add('hidden'); ph.classList.remove('hidden');
                        return;
                    }
                    if (!file.type.startsWith('image/')) {
                        input.value = '';
                        alert('File harus berupa gambar (JPG, PNG, atau WebP).');
                        return;
                    }

                    let out = file;
                    try {
                        out = await compress(file);
                        if (out !== file) {
                            const dt = new DataTransfer();
                            dt.items.add(out);
                            input.files = dt.files;
                        }
                    } catch (_) { /* gagal kompres: kirim file asli */ }

                    img.src = URL.createObjectURL(out);
                    img.classList.remove('hidden');
                    ph.classList.add('hidden');
                });
            })();
        </script>
    @endpush
@endonce
