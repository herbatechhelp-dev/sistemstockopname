# Spesifikasi UI/UX & Desain Antarmuka
## Sistem Informasi Stock Opname (SISO)

### 1. Filosofi & Prinsip Desain
* **Mobile-First untuk Lapangan:** Mengingat Petugas SO bekerja sambil bergerak di koridor gudang, antarmuka lapangan dioptimalkan penuh untuk pengoperasian satu tangan menggunakan layar sentuh *smartphone* atau tablet ukuran 7-10 inci. Komponen tombol dibuat besar dengan jarak aman minimal 48px untuk mencegah salah ketik.
* **Desktop-Optimized untuk Admin:** Modul admin dirancang padat informasi (*data-dense*) menggunakan layout lebar penuh untuk memudahkan pemantauan tabel berskala besar dan komparasi data.
* **Aksesibilitas Kontras Tinggi:** Layout gudang sering kali memiliki pencahayaan yang fluktuatif. Desain UI wajib mengadopsi tema dengan tingkat kontras tinggi, teks gelap di atas latar belakang terang jernih (*Clean Light Theme*).

### 2. Alur Pengguna (User Flow)
```
[Petugas Login] 
       │
       ▼
[Pilih Sesi & Lokasi Gudang]
       │
       ▼
[Scan Barcode Item / Cari Manual] ──► (Validasi Lokasi Terkunci)
       │
       ▼
[Muncul Form Entri Fisik] ──► (Blind Count: Nilai Komputer Tersembunyi)
       │
       ▼
[Input Qty Aktual & Kode Batch]
       │
       ▼
[Klik Simpan] ──► (Sistem rekam Petugas + Waktu Real-time)
       │
       ▼
[Verifikasi oleh Team Leader] ──► [Dashboard Analisis Selisih Admin]
```

### 3. Panduan Wireframe & Tata Letak Halaman Utama

#### A. Halaman Entri Data Lapangan (Mobile Web / PWA)
* **Header:** Menampilkan Nama Petugas, Nama Tim, dan Nama Sesi SO berjalan beserta indikator sinyal sinkronisasi.
* **Komponen Scan:** Tombol berikon kamera berukuran besar bertuliskan **"Pindai Barcode Barang/Lokasi"**. Ketika diklik, mengaktifkan modul kamera belakang perangkat atau menangkap input dari hardware scanner.
* **Form Isian Blok:**
  * Dropdown Pemilihan Lokasi (hanya menampilkan lokasi yang dialokasikan untuk timnya).
  * Kolom Item (menampilkan Nama Barang & Kategori secara otomatis setelah barcode terpindai).
  * Pilihan Satuan (UoM) terisi otomatis dan bersifat *Read-Only*.
  * Input Text: **Kode Release / Batch**.
  * Input Number: **Kuantitas Fisik Aktual** (ukuran font besar, min: 24pt bold, dengan tombol stepper `+` dan `-` di samping kanan-kiri kolom).
  * Input Long Text: **Keterangan Tambahan** (muncul tanda bintang merah wajib jika qty = 0).
* **Footer Action:** Satu tombol penuh di area bawah berwarna biru terang kontras bertuliskan **"SIMPAN DATA HITUNGAN"**.

#### B. Dashboard Rekonsiliasi & Analisis Selisih (Desktop Admin)
* **Panel Ringkasan Atas (Cards Matrix):**
  * Total Item Terhitung (Persentase progress).
  * Total Selisih Cocok (Match - Hijau).
  * Total Selisih Kurang (Minus Variance - Merah).
  * Total Selisih Lebih (Plus Variance - Biru).
* **Tabel Utama Rekonsiliasi Dinamis:**
  * Tabel mendukung fitur pencarian global, filter per kategori, filter per lokasi penyimpanan, dan pengurutan (*sorting*) berdasarkan kolom selisih terbesar.
  * *Pewarnaan Baris:* Jika kolom `Variance` bernilai bukan nol, baris tabel akan diberi latar warna kuning lembut (selisih kecil) atau merah muda lembut (selisih besar).
* **Kolom Aksi Integrasi:** Setiap baris data memiliki tombol aksi cepat berlambang lingkaran panah putar bertuliskan **"Minta Recount"** yang hanya aktif jika status data belum di-finalisasi.

### 4. Elemen Respons Interaktif (Micro-Interactions)
* **Feedback Sukses Simpan:** Saat petugas lapangan menekan tombol simpan, layar akan memunculkan animasi centang hijau kilat (*toast notification*) selama 1.5 detik sebelum mengosongkan form kembali ke state pencarian barang berikutnya, memberikan konfirmasi psikologis bahwa data aman tersimpan di server.
* **Pencegahan Double-Submit:** Saat tombol "Simpan Data Hitungan" ditekan, sistem wajib menonaktifkan (*disable*) tombol tersebut secara instan untuk mencegah pengiriman data ganda akibat ketukan berulang oleh pengguna sewaktu koneksi internet melambat.