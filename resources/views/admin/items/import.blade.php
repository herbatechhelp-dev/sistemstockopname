<x-layouts.app title="Import Item" header="Import Data Material dari Excel">
    <div class="max-w-3xl">
        {{-- Instructions --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
            <h3 class="text-lg font-semibold text-blue-800 mb-3">Panduan Import</h3>
            <ol class="text-sm text-blue-700 space-y-2 list-decimal list-inside">
                <li>Download template Excel (<code>.xlsx</code>) di bawah ini</li>
                <li>Isi data material di sheet <strong>"Import Material"</strong> dengan 3 kolom:
                    <strong>Nama Item, Kategori, UoM</strong>
                </li>
                <li>
                    Lihat sheet <strong>"Referensi"</strong> untuk daftar Kategori dan UoM yang tersedia
                </li>
                <li>
                    <strong>Kategori</strong> bisa diisi nama (<em>{{ $categories->first()->name ?? 'Raw Material' }}</em>) atau kode (<em>{{ $categories->first()->code ?? 'RM' }}</em>)
                </li>
                <li>
                    <strong>UoM</strong> bisa diisi singkatan (<em>{{ $uoms->first()->abbreviation ?? 'pcs' }}</em>) atau nama (<em>{{ $uoms->first()->name ?? 'Pieces' }}</em>)
                </li>
                <li><strong>SKU</strong> akan di-generate otomatis oleh sistem</li>
                <li><span class="text-red-600 font-medium">Penting:</span> Hapus baris contoh (baris 2-4) sebelum upload</li>
            </ol>

            <div class="mt-4">
                <a href="{{ route('items.template') }}" class="inline-flex items-center px-4 py-2 bg-blue-700 text-white rounded-lg text-sm font-medium hover:bg-blue-800">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Download Template Excel (.xlsx)
                </a>
            </div>
        </div>

        {{-- Available Categories & UoMs Reference --}}
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Kategori Tersedia</h4>
                <div class="space-y-1">
                    @foreach($categories as $cat)
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-700">{{ $cat->name }}</span>
                        <span class="text-gray-400 font-mono">{{ $cat->code }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">UoM Tersedia</h4>
                <div class="space-y-1">
                    @foreach($uoms as $uom)
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-700">{{ $uom->name }}</span>
                        <span class="text-gray-400 font-mono">{{ $uom->abbreviation }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Upload Form --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Upload File Excel</h3>
            <form method="POST" action="{{ route('items.import.process') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File Excel</label>
                    <div class="relative">
                        <input type="file" name="file" id="excel-file" accept=".xlsx,.xls" required
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 file:cursor-pointer cursor-pointer border border-gray-300 rounded-lg">
                    </div>
                    @error('file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-400 mt-1">Format: .xlsx atau .xls, maksimal 5MB</p>
                </div>

                {{-- File info --}}
                <div id="file-info" class="hidden bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center text-sm text-gray-700">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span id="file-name"></span>
                        <span class="ml-2 text-gray-400">(<span id="file-size"></span>)</span>
                    </div>
                </div>

                <div class="flex space-x-3">
                    <button type="submit" id="submit-btn" class="px-6 py-2 bg-green-700 text-white rounded-lg font-medium hover:bg-green-800 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="btn-text">Import Data</span>
                        <span id="btn-loading" class="hidden">Memproses...</span>
                    </button>
                    <a href="/admin/items" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200">Batal</a>
                </div>
            </form>
        </div>

        {{-- Tips --}}
        <div class="mt-6 bg-yellow-50 rounded-xl border border-yellow-200 p-6">
            <h4 class="text-sm font-semibold text-yellow-800 mb-2">Tips</h4>
            <ul class="text-xs text-yellow-700 space-y-1 list-disc list-inside">
                <li>Gunakan template yang sudah disediakan agar format sesuai</li>
                <li>Jangan ubah nama header kolom di baris pertama</li>
                <li>Pastikan data Kategori dan UoM sudah ada di sistem (lihat daftar di atas)</li>
                <li>Data akan di-import ke sheet aktif pertama di file Excel</li>
            </ul>
        </div>
    </div>

    @push('scripts')
    <script>
        const fileInput = document.getElementById('excel-file');
        const fileInfo = document.getElementById('file-info');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');
        const submitBtn = document.getElementById('submit-btn');
        const btnText = document.getElementById('btn-text');
        const btnLoading = document.getElementById('btn-loading');

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) {
                fileInfo.classList.add('hidden');
                return;
            }
            fileName.textContent = file.name;
            fileSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
            fileInfo.classList.remove('hidden');
        });

        // Prevent double submit
        document.querySelector('form').addEventListener('submit', function() {
            submitBtn.disabled = true;
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
        });
    </script>
    @endpush
</x-layouts.app>
