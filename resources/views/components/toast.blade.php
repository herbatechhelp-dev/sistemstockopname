@props(['type' => 'success', 'message' => ''])

@php
$colors = [
    'success' => 'bg-green-50 border-green-400 text-green-800',
    'error' => 'bg-red-50 border-red-400 text-red-800',
    'warning' => 'bg-yellow-50 border-yellow-400 text-yellow-800',
    'info' => 'bg-blue-50 border-blue-400 text-blue-800',
];
$icons = [
    'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'error' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>',
    'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
];
@endphp

<div id="toast-notification" class="mb-4 flex items-center p-4 border-l-4 rounded-r-lg {{ $colors[$type] }} shadow-sm animate-fade-in" role="alert">
    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $icons[$type] !!}</svg>
    <p class="text-sm font-medium">{{ $message }}</p>
    <button onclick="this.parentElement.remove()" class="ml-auto text-current opacity-50 hover:opacity-75">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>

<script>
    setTimeout(() => {
        const toast = document.getElementById('toast-notification');
        if (toast) toast.remove();
    }, 4000);
</script>
