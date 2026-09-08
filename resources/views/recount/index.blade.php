<x-layouts.mobile title="Tugas Recount" header="Tugas Recount">
    @if(!$session)
    <div class="bg-white rounded-xl p-6 text-center border">
        <p class="text-gray-500">Tidak ada sesi aktif untuk Anda.</p>
    </div>
    @else
    <div class="bg-white rounded-xl p-4 mb-4 border">
        <p class="text-xs text-gray-500">Sesi aktif</p>
        <p class="font-semibold text-gray-800">{{ $session->name }}</p>
        @if($team)<p class="text-xs text-gray-500">Tim: {{ $team->name }}</p>@endif
    </div>

    @if($recounts->isEmpty())
        <div class="bg-white rounded-xl p-8 text-center border">
            <p class="text-gray-500">Tidak ada tugas recount untuk Anda.</p>
            <p class="text-xs text-gray-400 mt-2">Recount akan muncul jika Admin menugaskan Anda.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($recounts as $rc)
            <a href="{{ route('recount.show', $rc->id) }}" class="block bg-white rounded-xl p-4 border hover:shadow-md transition">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-medium text-gray-900">{{ $rc->entry->item->name }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $rc->entry->item->sku }} • {{ $rc->entry->batch_code }}</p>
                        <p class="text-xs text-gray-500 mt-1">Lokasi: {{ $rc->entry->location->name }}</p>
                        <p class="text-xs text-gray-400">Petugas asal: {{ $rc->entry->petugas->full_name ?? $rc->entry->petugas->name }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">Pending</span>
                </div>
                <p class="text-xs text-gray-500 mt-2">Catatan: {{ $rc->notes ?? '-' }}</p>
                <p class="text-xs text-blue-600 mt-2 font-medium">Kerjakan Recount →</p>
            </a>
            @endforeach
        </div>
    @endif
    @endif
</x-layouts.mobile>
