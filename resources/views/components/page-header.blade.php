@props(['title', 'subtitle' => null])

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between border-b pb-4 border-gray-200">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-sm text-gray-500 mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions))
        <div class="mt-4 md:mt-0 flex items-center space-x-3">
            {{ $actions }}
        </div>
    @endif
</div>