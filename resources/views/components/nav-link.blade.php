@props(['href' => '#', 'active' => false])

<a href="{{ $href }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ $active ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
    {{ $slot }}
</a>