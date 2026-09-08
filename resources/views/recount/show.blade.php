<x-layouts.mobile title="Kerjakan Recount" header="Kerjakan Recount">
    <div class="bg-white rounded-xl p-4 border mb-4">
        <h3 class="font-semibold text-gray-900">{{ $recount->entry->item->name }}</h3>
        <p class="text-xs text-gray-400 font-mono">{{ $recount->entry->item->sku }} • Kategori: {{ $recount->entry->item->category->name }}</p>
        <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
            <div><p class="text-xs text-gray-500">Lokasi</p><p class="font-medium">{{ $recount->entry->location->name }}</p></div>
            <div><p class="text-xs text-gray-500">Batch Saat Ini</p><p class="font-mono">{{ $recount->entry->batch_code ?? '-' }}</p></div>
            <div><p class="text-xs text-gray-500">Qty Saat Ini</p><p class="font-bold text-lg">{{ number_format($recount->entry->fisik_qty,2) }}</p></div>
            <div><p class="text-xs text-gray-500">Petugas Asal</p><p>{{ $recount->entry->petugas->full_name ?? $recount->entry->petugas->name }}</p></div>
        </div>
        @if($recount->notes)<div class="mt-3 p-3 bg-yellow-50 rounded-lg text-sm text-yellow-800">Catatan Admin: {{ $recount->notes }}</div>@endif
    </div>

    <form method="POST" action="{{ route('recount.submit', $recount->id) }}" class="bg-white rounded-xl p-4 border space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Batch Code</label>
            <input type="text" name="batch_code" value="{{ old('batch_code', $recount->entry->batch_code) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500">
            @error('batch_code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kuantitas Fisik Hasil Recount <span class="text-red-500">*</span></label>
            <input type="number" step="0.01" min="0" name="fisik_qty" value="{{ old('fisik_qty', $recount->entry->fisik_qty) }}" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 text-lg font-bold">
            @error('fisik_qty')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan <span class="text-xs text-gray-400">(wajib jika qty 0)</span></label>
            <textarea name="keterangan" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500">{{ old('keterangan', $recount->entry->keterangan) }}</textarea>
            @error('keterangan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        @if($errors->any())
            <div class="p-3 bg-red-50 rounded-lg text-sm text-red-700">
                <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif
        <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition">Simpan Hasil Recount</button>
        <a href="{{ route('recount.index') }}" class="block text-center py-3 text-gray-500 text-sm">Kembali</a>
    </form>
</x-layouts.mobile>
