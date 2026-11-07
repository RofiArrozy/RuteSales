@if (session()->has('success'))
    <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 flex items-center justify-between shadow-sm">
        <div class="flex items-center space-x-2">
            <span>✅</span>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800 font-bold">&times;</button>
    </div>
@endif

@if (session()->has('error'))
    <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 flex items-center justify-between shadow-sm">
        <div class="flex items-center space-x-2">
            <span>⚠️</span>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800 font-bold">&times;</button>
    </div>
@endif