<x-layouts.app title="{{ isset($category) ? 'Edit' : 'Tambah' }} Kategori" header="{{ isset($category) ? 'Edit' : 'Tambah' }} Kategori">
    <div class="max-w-2xl">
        <form method="POST" action="{{ isset($category) ? '/admin/categories/' . $category->id : '/admin/categories' }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            @csrf
            @if(isset($category)) @method('PUT') @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode</label>
                <input type="text" name="code" value="{{ old('code', $category->code ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori</label>
                <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">{{ old('description', $category->description ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Toleransi Variance (%) <span class="text-gray-400 font-normal">— kosong = pakai global</span></label>
                <input type="number" step="0.01" min="0" max="100" name="tolerance_percentage" value="{{ old('tolerance_percentage', $category->tolerance_percentage ?? '') }}" placeholder="mis. 2.5" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                @error('tolerance_percentage') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex space-x-3">
                <button type="submit" class="px-6 py-2 bg-blue-700 text-white rounded-lg font-medium hover:bg-blue-800">Simpan</button>
                <a href="/admin/categories" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200">Batal</a>
            </div>
        </form>
    </div>
</x-layouts.app>
