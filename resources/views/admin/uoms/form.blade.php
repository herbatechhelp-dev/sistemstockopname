<x-layouts.app title="{{ isset($uom) ? 'Edit' : 'Tambah' }} UoM" header="{{ isset($uom) ? 'Edit' : 'Tambah' }} Satuan">
    <div class="max-w-2xl">
        <form method="POST" action="{{ isset($uom) ? '/admin/uoms/' . $uom->id : '/admin/uoms' }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            @csrf
            @if(isset($uom)) @method('PUT') @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Singkatan</label>
                <input type="text" name="abbreviation" value="{{ old('abbreviation', $uom->abbreviation ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                @error('abbreviation') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Satuan</label>
                <input type="text" name="name" value="{{ old('name', $uom->name ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex space-x-3">
                <button type="submit" class="px-6 py-2 bg-blue-700 text-white rounded-lg font-medium hover:bg-blue-800">Simpan</button>
                <a href="/admin/uoms" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200">Batal</a>
            </div>
        </form>
    </div>
</x-layouts.app>
