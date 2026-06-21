<x-layouts.mobile title="Input Hitungan" sessionName="{{ $session->name }}">
    <form method="POST" action="/entry" id="entry-form" class="space-y-5">
        @csrf

        {{-- Location Selection --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi Penyimpanan</label>
            <select name="location_id" id="location_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                <option value="">Pilih Lokasi...</option>
                @foreach($locations as $loc)<option value="{{ $loc->id }}">{{ $loc->name }}</option>@endforeach
            </select>
            @error('location_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Batch Number --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Batch <span class="text-red-500">*</span></label>
            <input type="text" name="batch_code" id="batch_code" value="{{ old('batch_code') }}" required placeholder="Masukkan nomor batch..."
                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            @error('batch_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Item Search --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Cari Item</label>
            <div class="flex space-x-2">
                <input type="text" id="search-input" placeholder="Ketik nama atau kode item..."
                    class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <button type="button" id="search-btn" class="px-4 py-3 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 flex items-center justify-center min-w-[48px]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
            </div>
            {{-- Search Results --}}
            <div id="search-results" class="hidden mt-2 bg-white border border-gray-200 rounded-lg shadow-lg max-h-56 overflow-y-auto"></div>
        </div>

        {{-- Item Info (auto-filled) --}}
        <input type="hidden" name="item_id" id="item_id" value="{{ old('item_id') }}">
        <div id="item-info" class="hidden bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <p class="font-semibold text-blue-800 text-lg" id="item-name"></p>
                    <p class="text-sm text-blue-600 mt-1 flex items-center gap-2">
                        <span id="item-category"></span>
                        <span class="text-blue-300">|</span>
                        <span class="font-medium" id="item-uom"></span>
                    </p>
                </div>
                <button type="button" id="change-item" class="text-sm text-blue-600 hover:text-blue-800 underline px-3 py-1 bg-white rounded-lg border border-blue-200">
                    Ganti
                </button>
            </div>
        </div>
        @error('item_id') <p class="text-red-500 text-sm px-1">{{ $message }}</p> @enderror

        {{-- Qty Input --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Kuantitas Fisik Aktual</label>
            <div class="flex items-center space-x-3">
                <button type="button" id="qty-minus" class="w-14 h-14 bg-gray-100 text-gray-700 rounded-xl text-2xl font-bold hover:bg-gray-200 active:scale-95 transition-transform flex items-center justify-center">-</button>
                <input type="number" name="fisik_qty" id="fisik_qty" value="{{ old('fisik_qty', '0') }}" min="0" step="1" required
                    class="flex-1 text-center text-3xl font-bold py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <button type="button" id="qty-plus" class="w-14 h-14 bg-gray-100 text-gray-700 rounded-xl text-2xl font-bold hover:bg-gray-200 active:scale-95 transition-transform flex items-center justify-center">+</button>
            </div>
            @error('fisik_qty') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Keterangan --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <label class="block text-sm font-medium text-gray-700 mb-2" id="keterangan-label">Keterangan</label>
            <textarea name="keterangan" id="keterangan" rows="3" placeholder="Catatan tambahan (wajib jika qty = 0)"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none">{{ old('keterangan') }}</textarea>
            @error('keterangan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Submit --}}
        <button type="submit" id="submit-btn" class="w-full py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-lg font-bold rounded-xl shadow-lg shadow-blue-600/20 hover:from-blue-700 hover:to-blue-800 active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed">
            SIMPAN DATA HITUNGAN
        </button>
    </form>

    @push('scripts')
    <script>
        // Qty stepper
        document.getElementById('qty-minus').addEventListener('click', () => {
            const input = document.getElementById('fisik_qty');
            input.value = Math.max(0, parseFloat(input.value || 0) - 1);
            checkQtyZero();
        });
        document.getElementById('qty-plus').addEventListener('click', () => {
            const input = document.getElementById('fisik_qty');
            input.value = parseFloat(input.value || 0) + 1;
            checkQtyZero();
        });
        document.getElementById('fisik_qty').addEventListener('input', checkQtyZero);

        function checkQtyZero() {
            const qty = parseFloat(document.getElementById('fisik_qty').value);
            const label = document.getElementById('keterangan-label');
            if (qty === 0) {
                label.innerHTML = 'Keterangan <span class="text-red-500">*</span>';
            } else {
                label.innerHTML = 'Keterangan';
            }
        }

        // Item search
        const searchInput = document.getElementById('search-input');
        const searchBtn = document.getElementById('search-btn');
        const searchResults = document.getElementById('search-results');
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const q = this.value.trim();
            if (q.length < 2) { searchResults.classList.add('hidden'); return; }
            searchTimeout = setTimeout(() => searchItems(q), 300);
        });

        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); searchItems(this.value.trim()); }
        });
        searchBtn.addEventListener('click', () => searchItems(searchInput.value.trim()));

        function searchItems(q) {
            if (!q) return;
            fetch(`/api/items/search?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(items => {
                    if (!items.length) {
                        searchResults.innerHTML = '<p class="p-4 text-sm text-gray-400 text-center">Item tidak ditemukan.</p>';
                    } else {
                        searchResults.innerHTML = items.map(item => `
                            <button type="button" class="w-full text-left px-4 py-3 hover:bg-blue-50 border-b border-gray-100 last:border-0 transition-colors" onclick="selectItem(${item.id}, '${item.name.replace(/'/g, "\\'")}', '${item.category.name.replace(/'/g, "\\'")}', '${item.uom.abbreviation}')">
                                <p class="text-sm font-medium text-gray-800">${item.name}</p>
                                <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-2">
                                    <span class="font-mono bg-gray-100 px-1.5 py-0.5 rounded">${item.sku}</span>
                                    <span>${item.category.name}</span>
                                    <span class="text-gray-300">|</span>
                                    <span>${item.uom.abbreviation}</span>
                                </p>
                            </button>
                        `).join('');
                    }
                    searchResults.classList.remove('hidden');
                })
                .catch(() => {
                    searchResults.innerHTML = '<p class="p-4 text-sm text-red-400 text-center">Gagal mencari item.</p>';
                    searchResults.classList.remove('hidden');
                });
        }

        function selectItem(id, name, category, uom) {
            document.getElementById('item_id').value = id;
            document.getElementById('item-name').textContent = name;
            document.getElementById('item-category').textContent = category;
            document.getElementById('item-uom').textContent = uom;
            document.getElementById('item-info').classList.remove('hidden');
            searchResults.classList.add('hidden');
            searchInput.value = '';
        }

        document.getElementById('change-item').addEventListener('click', function() {
            document.getElementById('item_id').value = '';
            document.getElementById('item-info').classList.add('hidden');
            searchInput.focus();
        });

        // Double-submit prevention
        document.getElementById('entry-form').addEventListener('submit', function(e) {
            if (!document.getElementById('item_id').value) {
                e.preventDefault();
                alert('Pilih item terlebih dahulu.');
                return false;
            }
            const btn = document.getElementById('submit-btn');
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Menyimpan...';
        });
    </script>
    @endpush
</x-layouts.mobile>