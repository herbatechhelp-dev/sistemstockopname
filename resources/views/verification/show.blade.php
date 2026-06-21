<x-layouts.mobile title="Detail Entri">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4">
        <div class="space-y-3">
            <div>
                <p class="text-xs text-gray-500">Item</p>
                <p class="font-semibold text-gray-900">{{ $entry->item->name }}</p>
                <p class="text-xs text-gray-500">{{ $entry->item->sku }}</p>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <p class="text-xs text-gray-500">Lokasi</p>
                    <p class="text-sm font-medium text-gray-900">{{ $entry->location->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Batch</p>
                    <p class="text-sm font-medium text-gray-900">{{ $entry->batch_code ?? '-' }}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <p class="text-xs text-gray-500">Qty Fisik</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($entry->fisik_qty, 0) }} <span class="text-sm font-normal text-gray-500">{{ $entry->uom }}</span></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Petugas</p>
                    <p class="text-sm font-medium text-gray-900">{{ $entry->petugas->full_name ?? $entry->petugas->name }}</p>
                </div>
            </div>
            <div>
                <p class="text-xs text-gray-500">Keterangan</p>
                <p class="text-sm text-gray-700">{{ $entry->keterangan ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Status</p>
                <span class="px-2 py-0.5 text-xs rounded-full {{ $entry->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : ($entry->status === 'verified' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700') }}">
                    {{ ucfirst(str_replace('_',' ',$entry->status)) }}
                </span>
            </div>
            <div class="text-xs text-gray-400">
                <p>Waktu: {{ $entry->created_at->format('d M Y H:i') }}</p>
                <p>IP: {{ $entry->ip_address ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Revision History --}}
    @if($entry->revisions->count() > 0)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4">
        <h3 class="text-sm font-semibold text-gray-800 mb-2">Riwayat Perubahan</h3>
        @foreach($entry->revisions as $rev)
        <div class="text-xs border-b border-gray-100 py-2 last:border-0">
            <p class="text-gray-700">{{ $rev->old_fisik_qty }} &rarr; {{ $rev->new_fisik_qty }}</p>
            <p class="text-gray-400">{{ $rev->reason }} - {{ $rev->created_at->format('H:i d M') }}</p>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Edit Form (only pending) --}}
    @if($entry->status === 'pending')
    <div class="bg-white rounded-lg shadow-sm border border-yellow-200 p-4 mb-4">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">Edit Data (TL)</h3>
        <form method="POST" action="{{ route('verification.update', $entry) }}" class="space-y-3">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Qty Fisik</label>
                <input type="number" name="fisik_qty" value="{{ $entry->fisik_qty }}" min="0" step="1" required class="w-full px-4 py-3 border border-gray-300 rounded-lg text-lg font-bold">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Batch Code</label>
                <input type="text" name="batch_code" value="{{ $entry->batch_code }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea name="keterangan" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">{{ $entry->keterangan }}</textarea>
            </div>
            <button type="submit" class="w-full py-3 bg-yellow-600 text-white font-semibold rounded-lg hover:bg-yellow-700">Simpan Perubahan</button>
        </form>
    </div>

    {{-- Verify Button --}}
    <form method="POST" action="{{ route('verification.verify', $entry) }}" onsubmit="return confirm('Verifikasi data ini?')">
        @csrf
        <button type="submit" class="w-full py-4 bg-green-600 text-white text-lg font-bold rounded-xl shadow-lg hover:bg-green-700">
            VERIFIKASI DATA
        </button>
    </form>
    @endif

    <a href="{{ route('verification.index') }}" class="block mt-4 text-center text-blue-600 hover:text-blue-800 text-sm">&larr; Kembali ke daftar</a>
</x-layouts.mobile>
