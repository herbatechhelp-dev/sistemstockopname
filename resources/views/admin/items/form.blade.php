<x-layouts.app title="{{ isset($item) ? 'Edit' : 'Tambah' }} Item" header="{{ isset($item) ? 'Edit' : 'Tambah' }} Item">
    <div class="max-w-2xl">
        <form method="POST" action="{{ isset($item) ? '/admin/items/' . $item->id : '/admin/items' }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            @csrf
            @if(isset($item)) @method('PUT') @endif
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku', $item->sku ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    @error('sku') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Item</label>
                    <input type="text" name="name" value="{{ old('name', $item->name ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <select name="category_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $cat)<option value="{{ $cat->id }}" {{ old('category_id', $item->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Satuan (UoM)</label>
                    <select name="uom_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih UoM</option>
                        @foreach($uoms as $uom)<option value="{{ $uom->id }}" {{ old('uom_id', $item->uom_id ?? '') == $uom->id ? 'selected' : '' }}>{{ $uom->name }} ({{ $uom->abbreviation }})</option>@endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">{{ old('description', $item->description ?? '') }}</textarea>
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }} class="mr-2 rounded">
                <label for="is_active" class="text-sm text-gray-700">Aktif</label>
            </div>
            <div class="flex space-x-3">
                <button type="submit" class="px-6 py-2 bg-blue-700 text-white rounded-lg font-medium hover:bg-blue-800">Simpan</button>
                <a href="/admin/items" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200">Batal</a>
            </div>
        </form>
    </div>
</x-layouts.app>
