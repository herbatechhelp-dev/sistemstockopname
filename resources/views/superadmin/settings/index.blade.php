<x-layouts.app title="System Settings" header="Pengaturan Sistem">
    <div class="max-w-2xl">
        <form method="POST" action="{{ route('superadmin.settings.update') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            @csrf
            @method('PUT')
            @forelse($settings as $setting)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ $setting->description ?? str_replace('_', ' ', ucfirst($setting->key)) }}
                </label>
                <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                <p class="text-xs text-gray-400 mt-1">Key: {{ $setting->key }}</p>
            </div>
            @empty
            <p class="text-gray-400 text-sm">Belum ada pengaturan sistem.</p>
            @endforelse

            @if($settings->count() > 0)
            <button type="submit" class="px-6 py-2 bg-blue-700 text-white rounded-lg font-medium hover:bg-blue-800">Simpan Pengaturan</button>
            @endif
        </form>
    </div>
</x-layouts.app>
