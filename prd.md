# Product Requirements Document (PRD)
## Sistem Informasi Stock Opname (SISO)

### 1. Pendahuluan & Latar Belakang
Proses Stock Opname (SO) konvensional yang mengandalkan pencatatan kertas rawan terhadap kesalahan manusia (human error), hilangnya berkas fisik, lambatnya rekonsiliasi data, serta potensi manipulasi data akibat petugas yang mengetahui saldo stok sistem komputer (stok buku). 

Sistem Informasi Stock Opname (SISO) dirancang sebagai solusi *custom web application* yang *responsive* (mobile-first untuk petugas lapangan dan desktop-optimized untuk administrator). Sistem ini memfasilitasi kolaborasi multi-tim secara *real-time*, menerapkan metode *blind-count*, dan menyediakan otomatisasi perhitungan selisih (*variance analysis*).

### 2. Tujuan Utama (Objectives)
* **Akurasi Tinggi:** Menghilangkan salah ketik atau salah baca data barang di lapangan melalui digitalisasi formulir dan integrasi barcode.
* **Kecepatan Rekonsiliasi:** Mempertemukan hasil hitung fisik dengan stok sistem secara instan saat sesi SO ditutup.
* **Transparansi & Akuntabilitas:** Mencatat jejak audit komprehensif (siapa, kapan, dan apa yang diinput/diubah) serta menerapkan otorisasi ketat.
* **Efisiensi Operasional:** Membagi area kerja gudang menjadi kluster-kluster kecil yang dikerjakan oleh tim mandiri secara paralel tanpa tumpang tindih.

### 3. User Persona & Matriks Kebutuhan
* **Admin Gudang / Supervisor SO**
  * Membuka, mengatur, dan menutup sesi Stock Opname.
  * Menarik data *snapshot* stok dari sistem berjalan.
  * Menganalisis selisih (*variance*) dan menugaskan *recount* (hitung ulang).
* **Team Leader (TL)**
  * Memantau *progress* input data dari anggota timnya di area tertentu.
  * Melakukan verifikasi awal sebelum data diajukan ke Admin.
* **Petugas SO (Checker / Counter)**
  * Melakukan *login* melalui perangkat seluler/tablet di lapangan.
  * Memindai barcode barang/lokasi dan memasukkan jumlah fisik yang dihitung secara langsung.

### 4. Spesifikasi Struktur Data & Representasi Tabel
Setiap baris data transaksi Stock Opname wajib merekam entitas berikut sesuai dengan kebutuhan operasional:

| No | Nama Kolom Sistem | Tipe Data | Deskripsi / Validasi |
|---|---|---|---|
| 1 | `id` (Auto-Number) | BigInteger (PK) | Dihasilkan otomatis oleh sistem sebagai pengenal unik. |
| 2 | `petugas_so` | String / Foreign Key | Mengunci ID dan Nama pengguna yang sedang login saat menyimpan data. |
| 3 | `tanggal_so` | Timestamp | Mengisi otomatis tanggal dan jam (hingga detik) saat tombol simpan ditekan. |
| 4 | `kategori` | Enum / Dropdown | Kategori barang baku (misal: *Raw Material, Finish Good, Packaging*). |
| 5 | `item_id` / `item_name` | String / Searchable | Kode SKU dan nama barang resmi dari master data (diisi via scan/pencarian). |
| 6 | `uom` | String / Dropdown | Unit of Measurement baku sesuai item (pcs, kg, box, dll). |
| 7 | `lokasi_penyimpanan`| String / Dropdown | Koordinat/posisi barang (Gudang-Blok-Rak-Baris). |
| 8 | `kode_release_batch`| String | Nomor lot produksi atau batch release barang (opsional/wajib tergantung kategori). |
| 9 | `stock_fisik` | Decimal (12,2) | Kuantitas aktual yang dihitung di lapangan (tidak boleh minus). |
| 10| `keterangan` | Text | Catatan tambahan mengenai kondisi fisik barang atau anomali di lokasi. |

### 5. Kebutuhan Fungsional (Functional Requirements)
* **FR-01: Manajemen Sesi & Snapshot Data:** Admin dapat membuat sesi SO baru. Saat sesi dibuat, sistem mengunci nilai stok dari database utama pada saat itu juga sebagai pembanding statis (*snapshot/cut-off*).
* **FR-02: Manajemen Tim dan Alokasi Lokasi:** Sistem dapat memetakan Petugas SO ke dalam Tim tertentu, dan mengalokasikan Tim tersebut pada area/lokasi penyimpanan spesifik agar tidak terjadi bentrokan input.
* **FR-03: Pencarian Pintar & Scan Barcode:** Aplikasi pada modul lapangan harus dapat mengaktifkan kamera perangkat untuk memindai barcode SKU barang maupun barcode Lokasi Penyimpanan untuk mempercepat pengisian data.
* **FR-04: Blind Count Engine:** Sistem menyembunyikan kolom stok sistem/komputer dari modul Petugas SO dan Team Leader. Mereka hanya diizinkan menginput angka murni berdasarkan temuan fisik.
* **FR-05: Dashboard Analisis Selisih & Rekonsiliasi:** Menyediakan antarmuka desktop bagi Admin untuk melihat komparasi instan antara stok hasil input lapangan dengan stok *snapshot*. Sistem otomatis menghitung variabel selisih (`Variance = Kuantitas Fisik - Kuantitas Sistem`).
* **FR-06: Manajemen Hitung Ulang (Recount Workflow):** Admin dapat memberikan bendera (*flag*) *Recount* pada baris data yang memiliki selisih di luar batas toleransi. Sistem akan membuat tugas hitung ulang secara otomatis untuk tim yang berbeda.

### 6. Kebutuhan Non-Fungsional (Non-Functional Requirements)
* **NFR-01 (Keamanan):** Koneksi wajib menggunakan HTTPS. Kata sandi dienkripsi menggunakan algoritma standar industri (misal: bcrypt).
* **NFR-02 (Ketersediaan & Kinerja):** Sistem harus mampu menangani penulisan data bersamaan (*concurrent writes*) dari puluhan petugas lapangan secara *real-time* tanpa terjadinya penurunan performa aplikasi (latensi di bawah 1 detik untuk simpan data).
* **NFR-03 (Mobilitas & Responsif):** Halaman entri data lapangan harus dioptimalkan untuk performa web seluler (PWA atau responsive web design) dengan konsumsi kuota data seminimal mungkin.