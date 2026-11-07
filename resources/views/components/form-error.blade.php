@props(['field'])

@error($field)
    <p class="text-xs text-red-600 mt-1 font-medium flex items-center gap-1">
        <span>⚠️</span> {{ $message }}
    </p>
@enderror