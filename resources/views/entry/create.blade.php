<x-layouts.mobile title="Input Hitungan" sessionName="{{ $session->name }}">
    <div class="mb-4">
        <a href="/entry" class="inline-flex items-center text-slate-500 hover:text-slate-800 text-xs font-bold transition-all">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Beranda
        </a>
    </div>

    <form method="POST" action="/entry" id="entry-form" class="space-y-5">
        @csrf

        {{-- Location Selection --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5">
            <label class="block text-sm font-bold text-slate-700 mb-2">Lokasi Penyimpanan <span class="text-red-500">*</span></label>
            <div class="relative">
                <select name="location_id" id="location_id" required class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm focus:ring-4 focus:ring-blue-100 focus:border-blue-600 bg-white transition-all appearance-none">
                    <option value="">Pilih Lokasi...</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>
            @error('location_id') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
        </div>

        {{-- Batch Number --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5">
            <label class="block text-sm font-bold text-slate-700 mb-2">Nomor Batch <span class="text-red-500">*</span></label>
            <input type="text" name="batch_code" id="batch_code" value="{{ old('batch_code') }}" required placeholder="Ketik nomor batch (contoh: B-001)"
                class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm focus:ring-4 focus:ring-blue-100 focus:border-blue-600 transition-all">
            @error('batch_code') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
        </div>

        {{-- Item Search --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5">
            <label class="block text-sm font-bold text-slate-700 mb-2">Cari Item</label>
            <div class="relative">
                <input type="text" id="search-input" placeholder="Masukkan nama barang atau SKU..."
                    class="w-full pl-11 pr-4 py-3 border border-slate-300 rounded-xl text-sm focus:ring-4 focus:ring-blue-100 focus:border-blue-600 transition-all">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
            
            {{-- Search Results --}}
            <div id="search-results" class="hidden mt-3 bg-white border border-slate-200 rounded-xl shadow-xl max-h-60 overflow-y-auto z-50"></div>
        </div>

        {{-- Item Info (auto-filled) --}}
        <input type="hidden" name="item_id" id="item_id" value="{{ old('item_id') }}">
        <div id="item-info" class="hidden bg-gradient-to-br from-blue-50 via-indigo-50/30 to-violet-50/50 border border-blue-200/80 rounded-2xl p-5 shadow-sm">
            <div class="flex justify-between items-start">
                <div class="flex-1 min-w-0 pr-2">
                    <p class="text-[10px] text-blue-600 font-bold uppercase tracking-wider mb-1">Item Terpilih</p>
                    <p class="font-extrabold text-blue-900 text-lg leading-snug" id="item-name"></p>
                    <div class="text-xs text-blue-700 mt-2 flex items-center space-x-2 font-semibold">
                        <span id="item-category" class="bg-blue-100/60 px-2.5 py-0.5 rounded-lg text-blue-800"></span>
                        <span class="text-blue-300">|</span>
                        <span class="font-bold uppercase" id="item-uom"></span>
                    </div>
                </div>
                <button type="button" id="change-item" class="text-xs text-blue-700 font-bold px-3 py-2 bg-white rounded-xl border border-blue-200 hover:bg-blue-50 transition-all select-none shadow-sm flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Ganti
                </button>
            </div>
        </div>
        @error('item_id') <p class="text-red-500 text-xs px-2 mt-1 font-medium">{{ $message }}</p> @enderror

        {{-- Qty Input --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5">
            <label class="block text-sm font-bold text-slate-700 mb-3">Kuantitas Fisik Aktual</label>
            <div class="flex items-center space-x-4 mb-4">
                <button type="button" id="qty-minus" class="w-16 h-16 bg-slate-100 text-slate-700 rounded-2xl text-2xl font-black hover:bg-slate-200 active:scale-95 transition-all flex items-center justify-center shadow-sm select-none">-</button>
                <input type="number" name="fisik_qty" id="fisik_qty" value="{{ old('fisik_qty', '0') }}" min="0" step="1" required
                    class="flex-1 text-center text-4xl font-black py-3 border-2 border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-blue-600 transition-all bg-slate-50/50">
                <button type="button" id="qty-plus" class="w-16 h-16 bg-slate-100 text-slate-700 rounded-2xl text-2xl font-black hover:bg-slate-200 active:scale-95 transition-all flex items-center justify-center shadow-sm select-none">+</button>
            </div>
            
            {{-- Quick Presets --}}
            <div class="grid grid-cols-5 gap-2">
                <button type="button" data-preset="1" class="qty-preset py-2.5 bg-blue-50/50 hover:bg-blue-100/60 border border-blue-100/50 text-blue-700 text-xs font-bold rounded-xl active:scale-95 transition-all shadow-sm">+1</button>
                <button type="button" data-preset="5" class="qty-preset py-2.5 bg-blue-50/50 hover:bg-blue-100/60 border border-blue-100/50 text-blue-700 text-xs font-bold rounded-xl active:scale-95 transition-all shadow-sm">+5</button>
                <button type="button" data-preset="10" class="qty-preset py-2.5 bg-blue-50/50 hover:bg-blue-100/60 border border-blue-100/50 text-blue-700 text-xs font-bold rounded-xl active:scale-95 transition-all shadow-sm">+10</button>
                <button type="button" data-preset="50" class="qty-preset py-2.5 bg-blue-50/50 hover:bg-blue-100/60 border border-blue-100/50 text-blue-700 text-xs font-bold rounded-xl active:scale-95 transition-all shadow-sm">+50</button>
                <button type="button" id="qty-reset" class="py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl active:scale-95 transition-all shadow-sm">Reset</button>
            </div>
            @error('fisik_qty') <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p> @enderror
        </div>

        {{-- Keterangan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-5">
            <label class="block text-sm font-bold text-slate-700 mb-2" id="keterangan-label">Keterangan</label>
            <textarea name="keterangan" id="keterangan" rows="3" placeholder="Catatan tambahan (wajib diisi jika jumlah fisik = 0)"
                class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm focus:ring-4 focus:ring-blue-100 focus:border-blue-600 transition-all resize-none">{{ old('keterangan') }}</textarea>
            @error('keterangan') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
        </div>

        {{-- Submit --}}
        <button type="submit" id="submit-btn" class="w-full py-4 bg-gradient-to-r from-blue-600 via-indigo-600 to-violet-600 text-white text-base font-extrabold rounded-2xl shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/40 active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed">
            SIMPAN DATA HITUNGAN
        </button>
    </form>

    @push('scripts')
    <script>
        // Qty stepper
        const qtyInput = document.getElementById('fisik_qty');
        
        document.getElementById('qty-minus').addEventListener('click', () => {
            qtyInput.value = Math.max(0, parseFloat(qtyInput.value || 0) - 1);
            checkQtyZero();
        });
        document.getElementById('qty-plus').addEventListener('click', () => {
            qtyInput.value = parseFloat(qtyInput.value || 0) + 1;
            checkQtyZero();
        });
        qtyInput.addEventListener('input', checkQtyZero);

        // Quick presets
        document.querySelectorAll('.qty-preset').forEach(btn => {
            btn.addEventListener('click', function() {
                const presetVal = parseFloat(this.getAttribute('data-preset'));
                qtyInput.value = parseFloat(qtyInput.value || 0) + presetVal;
                checkQtyZero();
            });
        });

        document.getElementById('qty-reset').addEventListener('click', () => {
            qtyInput.value = '0';
            checkQtyZero();
        });

        function checkQtyZero() {
            const qty = parseFloat(qtyInput.value);
            const label = document.getElementById('keterangan-label');
            if (qty === 0) {
                label.innerHTML = 'Keterangan <span class="text-red-500 font-black">* (Wajib diisi jika Qty 0)</span>';
            } else {
                label.innerHTML = 'Keterangan';
            }
        }

        // Run check on page load to set correct label
        checkQtyZero();

        // Item search
        const searchInput = document.getElementById('search-input');
        const searchResults = document.getElementById('search-results');
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const q = this.value.trim();
            if (q.length < 2) { searchResults.classList.add('hidden'); return; }
            searchTimeout = setTimeout(() => searchItems(q), 300);
        });

        function searchItems(q) {
            if (!q) return;
            fetch(`/api/items/search?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(items => {
                    if (!items.length) {
                        searchResults.innerHTML = '<p class="p-4 text-sm text-slate-400 text-center font-medium">Item tidak ditemukan.</p>';
                    } else {
                        searchResults.innerHTML = items.map(item => `
                            <button type="button" class="w-full text-left px-4 py-3 hover:bg-slate-50 border-b border-slate-100 last:border-0 transition-colors" onclick="selectItem(${item.id}, '${item.name.replace(/'/g, "\\'")}', '${item.category.name.replace(/'/g, "\\'")}', '${item.uom.abbreviation}')">
                                <p class="text-sm font-bold text-slate-800">${item.name}</p>
                                <p class="text-xs text-slate-400 mt-1 flex items-center gap-2 font-medium">
                                    <span class="font-mono bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-bold">${item.sku}</span>
                                    <span class="text-slate-300">|</span>
                                    <span>${item.category.name}</span>
                                    <span class="text-slate-300">|</span>
                                    <span class="uppercase">${item.uom.abbreviation}</span>
                                </p>
                            </button>
                        `).join('');
                    }
                    searchResults.classList.remove('hidden');
                })
                .catch(() => {
                    searchResults.innerHTML = '<p class="p-4 text-sm text-red-500 text-center font-medium">Gagal mencari item.</p>';
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
                alert('Pilih item terlebih dahulu dengan menggunakan kolom Cari Item.');
                return false;
            }
            const btn = document.getElementById('submit-btn');
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Menyimpan...';
        });
    </script>
    @endpush
</x-layouts.mobile>