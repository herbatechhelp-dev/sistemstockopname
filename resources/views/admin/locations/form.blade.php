<x-layouts.app title="{{ isset($location) ? 'Edit' : 'Tambah' }} Lokasi" header="{{ isset($location) ? 'Edit' : 'Tambah' }} Lokasi">
    <div class="max-w-2xl">
        <form method="POST" action="{{ isset($location) ? '/admin/locations/' . $location->id : '/admin/locations' }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            @csrf
            @if(isset($location)) @method('PUT') @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lokasi</label>
                <input type="text" name="name" value="{{ old('name', $location->name ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Gudang</label><input type="text" name="warehouse" value="{{ old('warehouse', $location->warehouse ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Blok</label><input type="text" name="block" value="{{ old('block', $location->block ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Rak</label><input type="text" name="rack" value="{{ old('rack', $location->rack ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Baris (opsional)</label><input type="text" name="row" value="{{ old('row', $location->row ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></div>
            </div>
            <div class="flex items-center"><input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $location->is_active ?? true) ? 'checked' : '' }} class="mr-2 rounded"><label for="is_active" class="text-sm text-gray-700">Aktif</label></div>
            <div class="flex space-x-3">
                <button type="submit" class="px-6 py-2 bg-blue-700 text-white rounded-lg font-medium hover:bg-blue-800">Simpan</button>
                <a href="/admin/locations" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200">Batal</a>
            </div>
        </form>
    </div>
</x-layouts.app>
