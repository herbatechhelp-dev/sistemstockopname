# Dokumen Aturan Bisnis & Logika Sistem (Rules)
## Sistem Informasi Stock Opname (SISO)

Dokumen ini mendefinisikan seluruh batasan, logika validasi, hak akses, dan kebijakan operasional gudang yang wajib ditransformasikan ke dalam kode program sistem.

### 1. Matriks Otorisasi Hak Akses (Role & Permission Matrix)

| Fitur / Modul | Petugas SO (Checker) | Team Leader (TL) | Admin Gudang / Supervisor |
|---|:---:|:---:|:---:|
| Membuat / Menutup Sesi SO | ❌ No | ❌ No |  Yes |
| Alokasi Wilayah/Lokasi Tim | ❌ No | ❌ No |  Yes |
| Input Data Fisik Lapangan |  Yes |  Yes | ❌ No |
| Edit Data Input Tim Sendiri |  Yes (Belum Verif) |  Yes | ❌ No |
| Verifikasi Data Tim (Approve) | ❌ No |  Yes | ❌ No |
| Lihat Stok Komputer/Sistem | ❌ No | ❌ No |  Yes |
| Lihat Laporan Selisih (Variance) | ❌ No | ❌ No |  Yes |
| Perintah Hitung Ulang (Recount) | ❌ No | ❌ No |  Yes |
| Inventory Adjustment (Export/Sync) | ❌ No | ❌ No |  Yes |

### 2. Aturan Ketat Validasi Formulir Input
Untuk menjaga kebersihan database dan konsistensi data, aturan validasi backend dan frontend berikut bersifat mutlak:
* **Anti-Minus:** Kolom `STOCK FISIK` hanya menerima bilangan bulat positif atau pecahan desimal positif (maksimal 2 angka di belakang koma untuk akomodasi satuan Kg/Liter). Input angka negatif wajib ditolak oleh validasi sistem dengan memunculkan pesan error: *"Kuantitas fisik tidak boleh kurang dari nol."*
* **Kewajiban Keterangan Kuantitas Nol:** Jika petugas memasukkan nilai `STOCK FISIK = 0`, maka kolom `KETERANGAN` secara otomatis berubah status menjadi **Wajib Diisi (Mandatory)**. Petugas harus memberikan alasan yang jelas, contoh: *"Barang kosong di rak, kemasan rusak dibuang, dll."*
* **Dropdown Terkunci:** Kolom `Kategori` dan `UoM` tidak diisi secara manual (*free text*), melainkan mengambil relasi dari `ITEM` yang dipilih untuk mencegah ketidaksesuaian data (misal: barang A diinput berkategori B).

### 3. Logika Mekanisme Blind Count
* Aplikasi dilarang keras mengirimkan parameter stok buku/komputer ke sisi client perangkat Petugas SO dan Team Leader melalui API apa pun.
* Field komparasi hanya diolah di sisi server (*server-side processing*) khusus untuk konsumsi akun berhak akses Admin pada dashboard rekonsiliasi.

### 4. Aturan Pencegahan Tumpang Tindih Area (Collision Prevention Rules)
* Sebuah `LOKASI PENYIMPANAN` (misalnya: Gudang Utama - Blok A - Rak 01) yang telah dialokasikan oleh Admin kepada **Tim 1**, secara otomatis akan dikunci (*lock*) oleh sistem sehingga tidak dapat dipilih atau diinput oleh anggota **Tim 2**.
* Sistem baru akan membuka kunci lokasi tersebut apabila Admin secara manual memindahkan alokasi area atau status lokasi tersebut dinyatakan *Selesai diverifikasi* oleh Team Leader terkait.

### 5. Aturan Jejak Audit & Kebijakan Log (Audit Trail)
Setiap transaksi atau perubahan data yang terjadi dalam sistem wajib dicatat dalam tabel log tersendiri dengan ketentuan:
* **Larangan Hard Delete:** Data entri hasil Stock Opname tidak boleh dihapus secara fisik dari database (`Hard Delete` ditutup). Jika terjadi kesalahan parah, pembetulan dilakukan dengan mengubah kuantitas fisik oleh pihak berwenang (TL), di mana sistem akan menyimpan riwayat data sebelum diubah (*soft log revision*).
* **Metadata Otomatis:** Sistem wajib merekam IP Address, User-Agent device, ID Petugas, dan Timestamp presisi hingga satuan milidetik (`YYYY-MM-DD HH:mm:ss.SSS`) pada saat penulisan data ke dalam database.

### 6. Batas Toleransi Selisih & Alur Recount (Variance & Recount Rules)
* **Status Variance:** Sistem otomatis menandai hasil SO dengan 3 indikator warna berdasarkan perhitungan:
  * 🟢 **Match (Hijau):** `Variance == 0`
  * 🟡 **Tolerable (Kuning):** `Variance` berada dalam rentang toleransi perusahaan (misal: selisih < 1% dari stok sistem untuk item tertentu).
  * 🔴 **Unacceptable (Merah):** `Variance` melampaui batas toleransi atau kuantitas selisih bernilai besar.
* **Aturan Double-Blind Recount:** Ketika Admin menekan tombol "Minta Recount" pada baris berstatus merah, data tersebut akan dilempar kembali ke antrean lapangan. Aturannya adalah: Tugas recount tersebut **tidak boleh** dikerjakan oleh Petugas SO asli yang melakukan hitungan pertama pada baris data tersebut.