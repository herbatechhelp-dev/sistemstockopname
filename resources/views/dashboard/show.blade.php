<x-layouts.app title="Detail Entri" header="Detail Entri Stock Opname">
    <div class="max-w-3xl">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Entri</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Item</label>
                    <p class="text-sm font-semibold text-gray-900">{{ $entry->item->name }}</p>
                    <p class="text-xs text-gray-500">SKU: {{ $entry->item->sku }}</p>
                    @if($entry->item->category)<p class="text-xs text-gray-500">Kategori: {{ $entry->item->category->name }}</p>@endif
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Lokasi</label>
                    <p class="text-sm font-semibold text-gray-900">{{ $entry->location->name }}</p>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Batch Code</label>
                    <p class="text-sm font-mono text-gray-900">{{ $entry->batch_code ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Qty Fisik</label>
                    <p class="text-xl font-bold text-gray-900">{{ number_format($entry->fisik_qty, 0) }} <span class="text-sm font-normal text-gray-500">{{ $entry->uom }}</span></p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                    <span class="px-2 py-1 text-xs rounded-full {{ $entry->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : ($entry->status === 'verified' ? 'bg-green-100 text-green-700' : ($entry->status === 'recount_requested' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700')) }}">
                        {{ ucfirst(str_replace('_',' ',$entry->status)) }}
                    </span>
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-xs font-medium text-gray-500 mb-1">Keterangan</label>
                <p class="text-sm text-gray-700">{{ $entry->keterangan ?? '-' }}</p>
            </div>
            <div class="mt-4 grid grid-cols-3 gap-4 text-xs text-gray-400">
                <p>Petugas: {{ $entry->petugas->full_name ?? $entry->petugas->name }}</p>
                <p>IP: {{ $entry->ip_address ?? '-' }}</p>
                <p>Waktu: {{ $entry->created_at->format('d M Y H:i:s') }}</p>
            </div>
        </div>

        {{-- Variance Analysis --}}
        @if($snapshot)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Analisis Selisih</h3>
            @php $variance = $entry->variance; $vStatus = $entry->variance_status; @endphp
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Stok Sistem (Snapshot)</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($snapshot->system_qty, 0) }}</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Qty Fisik</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($entry->fisik_qty, 0) }}</p>
                </div>
                <div class="text-center p-4 rounded-lg {{ $vStatus === 'match' ? 'bg-green-50' : ($vStatus === 'tolerable' ? 'bg-yellow-50' : 'bg-red-50') }}">
                    <p class="text-xs {{ $vStatus === 'match' ? 'text-green-600' : ($vStatus === 'tolerable' ? 'text-yellow-600' : 'text-red-600') }}">Selisih</p>
                    <p class="text-2xl font-bold {{ $variance > 0 ? 'text-blue-600' : ($variance < 0 ? 'text-red-600' : 'text-green-600') }}">{{ $variance > 0 ? '+' : '' }}{{ number_format($variance, 0) }}</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Revision History --}}
        @if($entry->revisions->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Riwayat Perubahan</h3>
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-2 text-xs font-medium text-gray-600">Waktu</th>
                        <th class="text-left px-4 py-2 text-xs font-medium text-gray-600">Diubah Oleh</th>
                        <th class="text-right px-4 py-2 text-xs font-medium text-gray-600">Lama</th>
                        <th class="text-right px-4 py-2 text-xs font-medium text-gray-600">Baru</th>
                        <th class="text-left px-4 py-2 text-xs font-medium text-gray-600">Alasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($entry->revisions as $rev)
                    <tr>
                        <td class="px-4 py-2 text-xs text-gray-500">{{ $rev->created_at->format('d M H:i') }}</td>
                        <td class="px-4 py-2 text-xs text-gray-700">{{ $rev->changer->full_name ?? $rev->changer->name ?? 'System' }}</td>
                        <td class="px-4 py-2 text-right text-sm">{{ number_format($rev->old_fisik_qty, 0) }}</td>
                        <td class="px-4 py-2 text-right text-sm font-semibold">{{ number_format($rev->new_fisik_qty, 0) }}</td>
                        <td class="px-4 py-2 text-xs text-gray-500">{{ $rev->reason }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Recount Requests --}}
        @if($entry->recountRequests->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Permintaan Recount</h3>
            @foreach($entry->recountRequests as $rr)
            <div class="border border-gray-200 rounded-lg p-3 mb-2">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-700">Petugas Recount: <strong>{{ $rr->assignedPetugas->full_name ?? $rr->assignedPetugas->name }}</strong></p>
                        <p class="text-xs text-gray-400">Diminta oleh: {{ $rr->requester->full_name ?? $rr->requester->name }} pada {{ $rr->created_at->format('d M Y H:i') }}</p>
                        @if($rr->notes)<p class="text-xs text-gray-500 mt-1">Catatan: {{ $rr->notes }}</p>@endif
                    </div>
                    <span class="px-2 py-1 text-xs rounded-full {{ $rr->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">{{ ucfirst($rr->status) }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Request Recount (if not yet requested and session not closed) --}}
        @if($entry->status !== 'recount_requested' && $entry->status !== 'recount_done' && $entry->recountRequests->where('status','pending')->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-orange-800 mb-4">Minta Recount</h3>
            <form method="POST" action="{{ route('recounts.store', $entry) }}" class="space-y-4" id="recount-form">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tim Recount</label>
                    <select name="assigned_team_id" id="recount-team" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm" onchange="loadPetugas(this.value)">
                        <option value="">Pilih Tim...</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Petugas Recount</label>
                    <select name="assigned_petugas_id" id="recount-petugas" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Pilih tim terlebih dahulu...</option>
                    </select>
                    <p class="text-xs text-orange-500 mt-1">Petugas recount harus berbeda dari petugas asli (double-blind rule).</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                </div>
                <button type="submit" class="px-6 py-2 bg-orange-600 text-white rounded-lg font-medium hover:bg-orange-700">Kirim Permintaan Recount</button>
            </form>
        </div>
        @endif

        <a href="/dashboard" class="text-blue-600 hover:text-blue-800 text-sm">&larr; Kembali ke dashboard</a>
    </div>

    @push('scripts')
    <script>
        // Load available teams for recount
        const entryId = {{ $entry->id }};
        fetch(`/admin/recount-options?entry_id=${entryId}`)
            .then(r => r.json())
            .then(data => {
                const sel = document.getElementById('recount-team');
                data.teams.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.id;
                    opt.textContent = t.name;
                    opt.dataset.members = JSON.stringify(t.members);
                    sel.appendChild(opt);
                });
            });

        function loadPetugas(teamId) {
            const sel = document.getElementById('recount-team');
            const opt = sel.querySelector(`option[value="${teamId}"]`);
            const petugasSel = document.getElementById('recount-petugas');
            petugasSel.innerHTML = '<option value="">Pilih Petugas...</option>';
            if (opt && opt.dataset.members) {
                const members = JSON.parse(opt.dataset.members);
                members.forEach(m => {
                    if (m.user && m.user.id !== {{ $entry->petugas_id }}) {
                        const o = document.createElement('option');
                        o.value = m.user.id;
                        o.textContent = m.user.full_name || m.user.name;
                        petugasSel.appendChild(o);
                    }
                });
            }
        }
    </script>
    @endpush
</x-layouts.app>
