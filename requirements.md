# Requirements Document

## Introduction

Sistem Manajemen Laundry adalah aplikasi berbasis web yang dirancang untuk mengelola seluruh operasional bisnis laundry secara terintegrasi. Sistem ini mencakup manajemen pelanggan, pesanan, produk, keuangan, inventaris, mesin, promosi, serta dukungan pelanggan. Sistem mendukung empat peran pengguna: **Pemilik (Owner)**, **Administrator (Admin)**, **Karyawan**, dan **Pelanggan (Customer)**.

---

## Glossary

- **Sistem**: Aplikasi Sistem Manajemen Laundry secara keseluruhan.
- **Owner**: Pemilik usaha laundry yang memiliki akses penuh ke semua modul dan laporan.
- **Admin**: Administrator yang mengelola operasional harian dan konfigurasi sistem.
- **Karyawan**: Staf operasional laundry yang memproses pesanan pelanggan.
- **Customer**: Pelanggan yang menggunakan layanan laundry.
- **Order**: Pesanan layanan laundry yang dibuat oleh pelanggan atau karyawan.
- **Produk**: Layanan atau item laundry yang ditawarkan (misalnya: cuci kiloan, cuci sepatu).
- **Kategori**: Pengelompokan produk/layanan laundry (misalnya: Pakaian, Sepatu, Tas).
- **Status Order**: Kondisi terkini dari suatu pesanan dalam alur proses laundry.
- **Inventaris**: Stok barang habis pakai yang digunakan dalam operasional laundry.
- **Mesin**: Peralatan laundry (mesin cuci, pengering, setrika) yang dikelola dalam sistem.
- **Promo**: Diskon atau penawaran khusus yang dapat diterapkan pada pesanan.
- **CRM**: Customer Relationship Management — modul pengelolaan hubungan dan komunikasi dengan pelanggan.
- **Transaksi_Keuangan**: Setiap pencatatan pemasukan atau pengeluaran dalam sistem keuangan.
- **Laporan_Laba_Rugi**: Ringkasan finansial yang menampilkan total pemasukan, pengeluaran, dan laba bersih.
- **FAQ**: Frequently Asked Questions — daftar pertanyaan dan jawaban umum untuk pelanggan.
- **Token_Autentikasi**: Kredensial digital yang digunakan untuk memverifikasi sesi pengguna yang aktif.
- **Notifikasi**: Pesan otomatis yang dikirimkan sistem kepada pengguna terkait perubahan status atau informasi penting.

---

## Requirements

---

### Persyaratan 1: Manajemen Pengguna dan Autentikasi

**Cerita Pengguna:** Sebagai pengguna sistem (Owner, Admin, Karyawan, atau Customer), saya ingin dapat masuk dan keluar dari sistem dengan aman, sehingga hanya pengguna yang berwenang yang dapat mengakses fitur sesuai perannya.

#### Kriteria Penerimaan

1. WHEN pengguna mengirimkan kombinasi email dan kata sandi yang valid, THE Sistem SHALL mengautentikasi pengguna dan mengembalikan Token_Autentikasi yang berlaku selama 8 jam.
2. WHEN pengguna mengirimkan email atau kata sandi yang tidak valid, THE Sistem SHALL menolak akses dan menampilkan pesan kesalahan yang mengindikasikan kombinasi email atau kata sandi tidak dikenali tanpa mengungkapkan field mana yang salah.
3. WHEN pengguna gagal login sebanyak 5 kali berturut-turut, THE Sistem SHALL mengunci akun selama 15 menit dan menampilkan pesan yang mengindikasikan akun terkunci beserta durasi penguncian tersisa.
4. WHEN pengguna yang terautentikasi meminta logout, THE Sistem SHALL mencabut Token_Autentikasi, mengakhiri sesi pengguna, dan mengarahkan pengguna ke halaman login.
5. THE Sistem SHALL membatasi akses setiap fitur berdasarkan peran pengguna (Owner, Admin, Karyawan, Customer) sesuai matriks izin yang telah ditetapkan.
6. WHEN pengguna yang tidak memiliki izin mencoba mengakses fitur di luar perannya, THE Sistem SHALL menolak akses dan menampilkan pesan yang mengindikasikan pengguna tidak memiliki izin untuk mengakses fitur tersebut.
7. WHEN Token_Autentikasi pengguna kedaluwarsa, THE Sistem SHALL mencabut sesi aktif dan mengarahkan pengguna ke halaman login.
8. THE Admin SHALL dapat membuat, mengubah, menonaktifkan, dan menghapus akun pengguna dengan peran Karyawan dan Customer.
9. THE Owner SHALL dapat membuat, mengubah, menonaktifkan, dan menghapus akun pengguna dengan peran Admin, Karyawan, dan Customer.
10. WHEN Admin atau Owner membuat akun pengguna baru, THE Sistem SHALL mengirimkan email verifikasi berisi tautan aktivasi akun yang berlaku selama 24 jam kepada pengguna baru.
11. WHEN pengguna meminta reset kata sandi melalui email terdaftar, THE Sistem SHALL mengirimkan tautan reset yang berlaku selama 1 jam ke email tersebut.
12. IF pengguna mengakses tautan reset kata sandi yang telah kedaluwarsa, THEN THE Sistem SHALL menampilkan pesan yang mengindikasikan tautan telah kedaluwarsa dan menawarkan opsi untuk meminta tautan reset baru.
13. THE Sistem SHALL menyimpan kata sandi pengguna menggunakan algoritma hashing bcrypt dengan salt rounds minimal 10.
14. WHEN akun pengguna yang dinonaktifkan mencoba login, THE Sistem SHALL menolak akses dan menampilkan pesan yang mengindikasikan akun tidak aktif tanpa mengungkapkan alasan penonaktifan.

---

### Persyaratan 2: Manajemen Data Pelanggan (Customer Database)

**Cerita Pengguna:** Sebagai Admin atau Karyawan, saya ingin mengelola data pelanggan secara terpusat, sehingga saya dapat dengan mudah mencari, memperbarui, dan melacak riwayat layanan setiap pelanggan.

#### Kriteria Penerimaan

1. THE Sistem SHALL menyimpan data pelanggan yang mencakup: nama lengkap (maksimal 100 karakter), nomor telepon (8–15 digit angka), alamat email (maksimal 254 karakter dengan format valid), alamat pengiriman/pengambilan (maksimal 500 karakter), dan tanggal pendaftaran (dicatat otomatis oleh sistem).
2. WHEN Admin atau Karyawan menambahkan pelanggan baru dengan nomor telepon yang sudah terdaftar, THE Sistem SHALL menolak pendaftaran dan menampilkan pesan yang mengindikasikan nomor telepon sudah terdaftar dalam sistem.
3. WHEN Admin atau Karyawan mencari pelanggan menggunakan nama, nomor telepon, atau email, THE Sistem SHALL menampilkan hasil pencarian dalam waktu kurang dari 2 detik dengan daftar pelanggan yang cocok mencakup nama lengkap, nomor telepon, dan email.
4. IF Admin atau Karyawan tidak memasukkan kata kunci pencarian atau kata kunci tidak cocok dengan data pelanggan mana pun, THEN THE Sistem SHALL menampilkan pesan yang menginformasikan bahwa tidak ada hasil ditemukan.
5. WHEN Admin atau Karyawan membuka profil pelanggan, THE Sistem SHALL menampilkan riwayat Order pelanggan yang mencakup tanggal, produk, status, dan total pembayaran, diurutkan dari yang terbaru ke yang terlama, dengan maksimal 50 entri per halaman.
6. THE Sistem SHALL menampilkan total jumlah transaksi dan total nilai transaksi kumulatif pada profil setiap pelanggan.
7. WHEN Admin memperbarui data pelanggan, THE Sistem SHALL menyimpan perubahan dan mencatat log yang memuat: identitas pengguna yang melakukan perubahan, waktu perubahan (tanggal dan jam), serta nilai data sebelum dan sesudah perubahan.
8. IF Admin memperbarui data pelanggan dengan nilai yang tidak valid (nomor telepon kurang dari 8 digit atau lebih dari 15 digit, email tanpa format yang valid, atau nama kosong), THEN THE Sistem SHALL menolak penyimpanan dan menampilkan pesan kesalahan yang menginformasikan field mana yang tidak valid.
9. WHERE fitur segmentasi pelanggan diaktifkan, THE Sistem SHALL mengelompokkan pelanggan berdasarkan frekuensi transaksi (Baru: 1 transaksi, Reguler: 2–9 transaksi, Loyal: 10+ transaksi).
10. WHEN Admin mengekspor data pelanggan, THE Sistem SHALL menghasilkan file CSV yang memuat kolom: nama, telepon, email, alamat, jumlah transaksi, total nilai transaksi, dan segmen pelanggan (jika fitur segmentasi aktif), dengan jumlah baris sesuai data pelanggan yang ada pada saat ekspor dilakukan.

---

### Persyaratan 3: Manajemen Kategori

**Cerita Pengguna:** Sebagai Admin, saya ingin mengelola kategori layanan laundry, sehingga produk-produk dapat dikelompokkan dengan rapi dan mudah ditemukan.

#### Kriteria Penerimaan

1. THE Admin SHALL dapat membuat kategori baru dengan nama kategori (maksimal 100 karakter, wajib diisi), deskripsi (maksimal 500 karakter, opsional), dan status aktif/tidak aktif (default: aktif).
2. WHEN Admin membuat kategori dengan nama yang sama persis (tidak peka huruf besar/kecil) dengan kategori yang sudah ada, THE Sistem SHALL menolak pembuatan dan menampilkan pesan error yang mengindikasikan nama kategori sudah digunakan.
3. THE Admin SHALL dapat mengubah nama, deskripsi, dan status aktif setiap kategori, dengan aturan validasi nama yang sama seperti pada kriteria 1.
4. WHEN Admin menonaktifkan kategori yang memiliki Produk aktif, THE Sistem SHALL menampilkan peringatan yang mengindikasikan jumlah produk aktif yang terdampak sebelum konfirmasi dilakukan.
5. IF Admin mengkonfirmasi penonaktifan kategori yang memiliki Produk aktif, THEN THE Sistem SHALL menonaktifkan kategori tersebut dan menyembunyikan seluruh produk aktif yang terkait dari daftar pemesanan.
6. THE Sistem SHALL menampilkan daftar kategori beserta jumlah produk aktif yang terkait pada setiap kategori, diperbarui dalam waktu tidak lebih dari 5 detik setelah perubahan data produk atau kategori.
7. WHEN Admin menghapus kategori yang memiliki riwayat Order, THE Sistem SHALL menolak penghapusan dan menampilkan pesan error yang mengindikasikan kategori tidak dapat dihapus karena memiliki riwayat transaksi.
8. IF Admin menghapus kategori yang tidak memiliki riwayat Order, THEN THE Sistem SHALL menghapus kategori tersebut secara permanen dan memperbarui daftar kategori.

---

### Persyaratan 4: Manajemen Produk (Layanan)

**Cerita Pengguna:** Sebagai Admin, saya ingin mengelola daftar produk dan layanan laundry beserta harganya, sehingga Karyawan dapat memilih layanan yang tepat saat membuat Order.

#### Kriteria Penerimaan

1. THE Admin SHALL dapat membuat Produk baru dengan atribut: nama produk (maksimal 100 karakter), Kategori, satuan (per kg, per item, atau per pasang), harga dasar (antara 0,01 hingga 999.999.999,99), estimasi durasi pengerjaan (antara 1 hingga 720 jam), deskripsi (maksimal 500 karakter), dan status aktif/tidak aktif.
2. WHEN Admin membuat Produk dengan nama yang sudah ada dalam Kategori yang sama, THE Sistem SHALL menolak pembuatan Produk dan menampilkan pesan "Nama produk sudah ada dalam kategori ini".
3. IF Admin mengosongkan kolom wajib (nama produk, Kategori, satuan, atau harga dasar) saat membuat atau mengubah Produk, THEN THE Sistem SHALL menolak penyimpanan dan menampilkan pesan kesalahan yang menunjukkan kolom mana yang belum diisi.
4. THE Admin SHALL dapat mengubah semua atribut Produk termasuk harga dasar, satuan, estimasi durasi pengerjaan, deskripsi, dan status aktif/tidak aktif.
5. WHEN Admin menyimpan perubahan harga dasar Produk, THE Sistem SHALL menyimpan riwayat perubahan harga beserta tanggal dan waktu berlaku sehingga Order yang dibuat sebelum perubahan tetap menggunakan harga pada saat Order tersebut dibuat.
6. WHEN Karyawan membuka formulir pembuatan Order, THE Sistem SHALL menampilkan daftar Produk aktif yang dikelompokkan berdasarkan Kategori aktif, terurut berdasarkan nama Kategori secara alfabet.
7. WHEN Admin menonaktifkan Produk, THE Sistem SHALL menyembunyikan Produk tersebut dari daftar pemilihan pada formulir Order baru tanpa menghapus data Produk dan riwayat Order yang sudah ada.
8. WHEN Karyawan memilih satu atau lebih Produk pada formulir Order, THE Sistem SHALL menghitung dan menampilkan estimasi waktu selesai berdasarkan jumlah durasi pengerjaan seluruh Produk yang dipilih ditambahkan ke waktu Order diterima.

---

### Persyaratan 5: Manajemen Status Order

**Cerita Pengguna:** Sebagai Karyawan, saya ingin memperbarui status pesanan secara bertahap, sehingga pelanggan dan manajemen dapat melacak progress pengerjaan secara real-time.

#### Kriteria Penerimaan

1. THE Sistem SHALL mendefinisikan alur Status Order berikut secara berurutan: **Menunggu Konfirmasi → Diterima → Dalam Proses → Selesai Dicuci → Siap Diambil / Dalam Pengiriman → Selesai**. Status "Dibatalkan" adalah status terminal yang dapat dicapai dari status manapun sebelum "Selesai".
2. WHEN Karyawan memperbarui Status Order ke tahap yang tidak diizinkan (mundur atau melompati tahap), THE Sistem SHALL menolak perubahan dan menampilkan pesan yang mengindikasikan perpindahan status tidak valid beserta daftar status yang diizinkan.
3. WHEN Status Order berubah, THE Sistem SHALL mencatat timestamp perubahan (hingga detik), status sebelum perubahan, status sesudah perubahan, dan identitas pengguna yang melakukan perubahan.
4. WHEN Status Order berubah menjadi "Siap Diambil" atau "Dalam Pengiriman", THE Sistem SHALL mengirimkan Notifikasi kepada Customer melalui saluran yang terdaftar (SMS atau email) dalam waktu paling lama 60 detik setelah perubahan status berhasil disimpan.
5. IF pengiriman Notifikasi gagal setelah status berubah menjadi "Siap Diambil" atau "Dalam Pengiriman", THEN THE Sistem SHALL mencatat kegagalan tersebut beserta kode kesalahan dan menampilkan indikator kegagalan notifikasi pada halaman detail Order tanpa membatalkan perubahan status.
6. WHEN Status Order berubah menjadi "Selesai", THE Sistem SHALL memperbarui total pemasukan pada modul Keuangan dalam waktu paling lama 10 detik, mencatat nilai transaksi sesuai total harga Order.
7. IF Owner atau Admin membatalkan Order pada status apapun sebelum "Selesai", THEN THE Sistem SHALL mewajibkan pengisian alasan pembatalan (1–255 karakter) sebelum menyimpan perubahan status ke "Dibatalkan".
8. WHEN Order dibatalkan setelah pembayaran diterima, THE Sistem SHALL mencatat transaksi refund sebagai pengeluaran pada modul Keuangan senilai jumlah pembayaran yang telah diterima.
9. THE Sistem SHALL menampilkan timeline perubahan Status Order secara kronologis pada halaman detail Order, mencakup timestamp, status sebelum, status sesudah, dan identitas pengguna yang melakukan perubahan untuk setiap entri.

---

### Persyaratan 6: Manajemen Order (Pesanan)

**Cerita Pengguna:** Sebagai Karyawan, saya ingin membuat dan mengelola pesanan laundry untuk pelanggan, sehingga setiap pesanan tercatat lengkap dan dapat diproses dengan tepat.

#### Kriteria Penerimaan

1. WHEN Karyawan membuat Order baru, THE Sistem SHALL mewajibkan pengisian: data Customer (baru atau terdaftar), minimal 1 Produk beserta jumlah (antara 1–999 per Produk), metode pengambilan (antar ke toko / jemput), dan metode pembayaran (tunai, transfer, dompet digital).
2. IF Karyawan mengkonfirmasi Order baru dengan field wajib yang tidak lengkap atau tidak valid, THEN THE Sistem SHALL menolak penyimpanan dan menampilkan pesan kesalahan yang mengidentifikasi field mana yang belum diisi atau tidak valid.
3. THE Sistem SHALL menghasilkan nomor Order unik dengan format **LDY-YYYYMMDD-XXXX** (XXXX adalah nomor urut harian 4 digit yang direset ke 0001 setiap hari) untuk setiap Order yang dikonfirmasi.
4. WHEN Karyawan memilih Promo yang valid pada saat membuat Order, THE Sistem SHALL menghitung dan menampilkan total harga setelah diskon sebelum Order dikonfirmasi.
5. IF kode Promo yang diinput pada formulir Order tidak valid, sudah kedaluwarsa, atau belum memenuhi syarat, THEN THE Sistem SHALL menampilkan pesan kesalahan yang spesifik sesuai kondisi dan tidak menerapkan diskon.
6. THE Sistem SHALL menghitung total harga Order sebagai jumlah dari (harga satuan × jumlah) setiap Produk dikurangi nilai diskon Promo yang berlaku, dengan nilai total minimal 0,01.
7. WHEN Order dikonfirmasi, THE Sistem SHALL menghasilkan struk digital yang memuat: nomor Order, tanggal dan waktu konfirmasi, data Customer, daftar Produk, jumlah, harga satuan, diskon, total harga, dan estimasi selesai dalam satuan hari.
8. THE Karyawan SHALL dapat menambahkan catatan khusus pada Order maksimal 500 karakter (contoh: instruksi pencucian, permintaan pelipatan khusus).
9. THE Sistem SHALL mendukung pembuatan Order oleh Customer secara mandiri melalui antarmuka Customer dengan memilih Produk, jumlah, dan slot waktu antar-jemput yang tersedia.
10. IF Customer atau Karyawan memilih layanan antar-jemput namun tidak mengisi alamat lengkap (maksimal 255 karakter) dan slot waktu penjemputan atau pengantaran, THEN THE Sistem SHALL menolak konfirmasi Order dan menampilkan pesan yang mengindikasikan field alamat dan jadwal wajib diisi.
11. THE Admin dan Owner SHALL dapat melihat daftar semua Order diurutkan dari terbaru, dengan filter berdasarkan: rentang tanggal (tanggal mulai dan tanggal selesai), Status Order, Karyawan pembuat, dan Customer.
12. THE Sistem SHALL menampilkan ringkasan Order harian pada dashboard Admin dan Owner yang mencakup: jumlah Order masuk, jumlah dalam proses, jumlah selesai, dan jumlah dibatalkan pada tanggal kalender berjalan.

---

### Persyaratan 7: Manajemen CRM (Customer Relationship Management)

**Cerita Pengguna:** Sebagai Admin atau Owner, saya ingin mengelola hubungan dengan pelanggan melalui komunikasi terpusat dan pelacakan kepuasan, sehingga dapat meningkatkan loyalitas dan retensi pelanggan.

#### Kriteria Penerimaan

1. WHEN Admin memilih segmen Customer (Baru: terdaftar ≤ 30 hari dan belum pernah transaksi; Reguler: 1–9 transaksi selesai; Loyal: ≥ 10 transaksi selesai; atau Semua) dan mengirimkan pesan broadcast dengan isi maksimal 2.000 karakter, THE Sistem SHALL mengirimkan pesan tersebut melalui email ke seluruh Customer dalam segmen yang dipilih.
2. WHEN Admin mengirimkan pesan broadcast, THE Sistem SHALL mencatat jumlah penerima, waktu pengiriman, dan isi pesan dalam riwayat komunikasi.
3. WHEN tanggal sistem mencapai tanggal ulang tahun Customer pada pukul 07.00 waktu lokal server, IF tanggal lahir tersedia dalam data Customer, THEN THE Sistem SHALL mengirimkan pesan ucapan ulang tahun secara otomatis ke email Customer tersebut.
4. WHEN Order berstatus "Selesai", THE Sistem SHALL mengirimkan permintaan ulasan kepada Customer berisi tautan formulir penilaian layanan (skala 1–5 bintang beserta komentar opsional).
5. WHEN Customer mengirimkan ulasan, THE Sistem SHALL menyimpan ulasan dan menampilkannya pada laporan kepuasan pelanggan yang dapat diakses oleh Admin dan Owner.
6. WHEN Admin atau Owner mengakses dashboard CRM, THE Sistem SHALL menampilkan rata-rata nilai ulasan per Produk dan rata-rata keseluruhan; IF suatu Produk belum memiliki ulasan, THEN THE Sistem SHALL menampilkan keterangan "Belum ada ulasan" pada kolom rata-rata Produk tersebut.
7. WHEN Admin mengirimkan balasan terhadap ulasan Customer melalui dashboard CRM dengan isi maksimal 1.000 karakter, THE Sistem SHALL menyimpan balasan tersebut dan mengirimkannya ke email Customer.
8. THE Sistem SHALL menandai Customer yang tidak melakukan transaksi dalam 60 hari terakhir sebagai "Tidak Aktif" dan menampilkannya dalam daftar Customer at-risk pada dashboard CRM.
9. WHERE program loyalitas diaktifkan, THE Sistem SHALL mengakumulasi poin loyalitas Customer berdasarkan total nilai transaksi, dihitung sebagai 1 poin untuk setiap kelipatan penuh Rp 1.000 dari nilai transaksi (sisa di bawah Rp 1.000 tidak dihitung).
10. IF pengiriman email broadcast gagal terkirim ke satu atau lebih penerima, THEN THE Sistem SHALL mencatat alamat email yang gagal beserta keterangan kegagalan dalam riwayat komunikasi broadcast tersebut dan menampilkan jumlah email gagal kepada Admin.
11. IF pengiriman email otomatis (ulang tahun atau permintaan ulasan) gagal, THEN THE Sistem SHALL mencatat kegagalan tersebut beserta identitas Customer dan jenis pesan yang gagal, serta menampilkan entri tersebut dalam log komunikasi yang dapat diakses Admin.

---

### Persyaratan 8: Manajemen Keuangan

**Cerita Pengguna:** Sebagai Owner atau Admin, saya ingin mencatat dan memantau seluruh transaksi keuangan bisnis, sehingga dapat mengetahui kondisi finansial secara akurat dan membuat keputusan bisnis berdasarkan data.

#### Kriteria Penerimaan

1. WHEN sebuah Order berubah menjadi berstatus "Selesai", THE Sistem SHALL mencatat pemasukan secara otomatis dengan atribut: nomor Order, tanggal penyelesaian, nominal antara 0,01 hingga 999.999.999,99, dan metode pembayaran.
2. WHEN Admin menyimpan data pengeluaran manual, THE Sistem SHALL mencatat pengeluaran dengan atribut wajib: tanggal, kategori pengeluaran (Operasional, Pembelian Inventaris, Utilitas, Gaji, Lainnya), deskripsi maksimal 255 karakter, dan nominal antara 0,01 hingga 999.999.999,99, serta atribut opsional berupa bukti transaksi berformat gambar (JPG, PNG) atau dokumen (PDF) dengan ukuran maksimal 5 MB.
3. IF Admin menyimpan data pengeluaran dengan nominal kurang dari 0,01 atau lebih dari 999.999.999,99 atau tanpa kategori, THEN THE Sistem SHALL menolak penyimpanan dan menampilkan pesan kesalahan yang mengindikasikan atribut mana yang tidak valid tanpa menyimpan data.
4. WHEN Owner atau Admin meminta Laporan_Laba_Rugi untuk suatu periode, THE Sistem SHALL menghitung dan menampilkan laporan dalam waktu tidak lebih dari 5 detik, dengan nilai: Total Pemasukan dikurangi Total Pengeluaran pada periode tersebut (harian, mingguan, bulanan, atau tahunan).
5. WHEN Owner atau Admin menerapkan filter pada daftar Transaksi_Keuangan, THE Sistem SHALL menampilkan hasil filter dalam waktu tidak lebih dari 5 detik berdasarkan kombinasi satu atau lebih kriteria: jenis (pemasukan/pengeluaran), kategori, rentang tanggal, dan metode pembayaran.
6. THE Sistem SHALL menampilkan grafik tren pemasukan dan pengeluaran per bulan untuk 12 bulan kalender terakhir pada dashboard keuangan, di mana bulan tanpa transaksi ditampilkan dengan nilai nol.
7. WHEN total pengeluaran suatu kategori dalam satu bulan kalender berjalan melebihi 80% dari anggaran bulanan yang telah ditetapkan untuk kategori tersebut, THE Sistem SHALL menampilkan notifikasi peringatan kepada Owner dan Admin yang mengindikasikan nama kategori, jumlah yang telah digunakan, dan persentase penggunaan anggaran.
8. WHEN Owner meminta ekspor Laporan_Laba_Rugi, THE Sistem SHALL menghasilkan file dalam format PDF atau Excel dalam waktu tidak lebih dari 30 detik, mencakup periode yang ditentukan dengan rentang tanggal mulai dan tanggal selesai yang dipilih oleh Owner, dengan nilai minimum satu hari dan maksimum 366 hari.
9. THE Sistem SHALL menampilkan ringkasan finansial harian pada dashboard yang mencakup: total pemasukan hari ini, total pengeluaran hari ini, dan saldo bersih hari ini (total pemasukan dikurangi total pengeluaran pada tanggal kalender yang sedang berjalan).
10. WHEN Admin menyimpan anggaran bulanan untuk suatu kategori pengeluaran, THE Sistem SHALL menyimpan nilai anggaran antara 0,01 hingga 999.999.999,99 per kategori per bulan kalender dan menampilkan persentase penggunaan anggaran yang diperbarui dalam waktu tidak lebih dari 3 detik.
11. IF anggaran bulanan untuk suatu kategori belum ditetapkan, THEN THE Sistem SHALL menonaktifkan notifikasi peringatan penggunaan anggaran untuk kategori tersebut dan menampilkan indikator bahwa anggaran belum dikonfigurasi.

---

### Persyaratan 9: Manajemen Inventaris

**Cerita Pengguna:** Sebagai Admin atau Karyawan, saya ingin memantau dan mengelola stok barang habis pakai operasional laundry, sehingga operasional tidak terganggu akibat kehabisan bahan.

#### Kriteria Penerimaan

1. THE Sistem SHALL menyimpan data setiap item inventaris yang mencakup: nama item (maksimal 100 karakter), satuan (kg, liter, pcs, dll.), stok saat ini (nilai numerik tidak negatif dengan presisi hingga 2 desimal), stok minimum/reorder point (nilai numerik tidak negatif dengan presisi hingga 2 desimal), harga per satuan (nilai numerik antara 0,01 hingga 999.999.999,99), dan kategori (Detergen, Pelembut, Plastik Kemasan, Lainnya).
2. WHEN stok item inventaris mencapai atau di bawah nilai stok minimum, THE Sistem SHALL menampilkan peringatan stok rendah pada dashboard Admin dan mengirimkan Notifikasi kepada Admin dalam waktu paling lama 60 detik sejak kondisi stok rendah terdeteksi.
3. IF Admin menyimpan catatan penambahan stok dengan atribut wajib (item, jumlah ditambahkan lebih dari 0, harga beli per satuan lebih dari 0, nama pemasok, tanggal pembelian) tidak lengkap atau tidak valid, THEN THE Sistem SHALL menolak penyimpanan dan menampilkan pesan kesalahan yang mengidentifikasi atribut yang tidak valid tanpa mengubah stok atau membuat transaksi keuangan.
4. WHEN Admin menyimpan catatan penambahan stok yang valid, THE Sistem SHALL memperbarui stok saat ini dengan menambahkan jumlah yang dicatat dan secara otomatis membuat Transaksi_Keuangan dengan kategori "Pembelian Inventaris" senilai jumlah × harga beli per satuan.
5. IF Karyawan mencatat penggunaan barang inventaris dengan jumlah melebihi stok saat ini, THEN THE Sistem SHALL menolak pencatatan dan menampilkan pesan kesalahan yang menyatakan stok tidak mencukupi tanpa mengubah nilai stok.
6. WHEN Karyawan menyimpan pencatatan penggunaan barang inventaris yang valid (item dipilih dan jumlah digunakan lebih dari 0 serta tidak melebihi stok saat ini), THE Sistem SHALL mengurangi stok saat ini sebesar jumlah yang digunakan.
7. THE Sistem SHALL menampilkan riwayat lengkap penambahan dan penggunaan setiap item inventaris beserta timestamp (tanggal dan waktu hingga menit) dan identitas pengguna yang mencatat, diurutkan dari entri terbaru ke terlama.
8. WHEN Admin meminta ekspor laporan inventaris, THE Sistem SHALL menghasilkan file berformat Excel (.xlsx) yang memuat: daftar semua item, stok saat ini, nilai stok (stok × harga per satuan terkini), dan status ketersediaan (Normal jika stok di atas stok minimum, Rendah jika stok di bawah atau sama dengan stok minimum).
9. WHILE jumlah item inventaris dengan status stok rendah lebih dari 0, THE Sistem SHALL menampilkan indikator peringatan pada menu navigasi utama Admin.
10. IF pencatatan penambahan stok atau penggunaan barang gagal disimpan akibat kesalahan sistem, THEN THE Sistem SHALL menampilkan pesan kesalahan yang menyatakan operasi gagal dan memastikan tidak ada perubahan pada data stok maupun Transaksi_Keuangan.

---

### Persyaratan 10: Manajemen Mesin Laundry

**Cerita Pengguna:** Sebagai Admin atau Karyawan, saya ingin memantau kondisi dan jadwal pemeliharaan mesin laundry, sehingga mesin selalu dalam kondisi optimal dan downtime dapat diminimalkan.

#### Kriteria Penerimaan

1. THE Sistem SHALL menyimpan data setiap Mesin yang mencakup: nama/kode mesin (maksimal 50 karakter), jenis (Cuci, Pengering, Setrika, Lainnya), merek (maksimal 100 karakter), model (maksimal 100 karakter), tanggal pembelian, status (Aktif, Dalam Perbaikan, Tidak Aktif), dan kapasitas dalam kg (antara 0,1 hingga 999,9; hanya wajib untuk jenis Cuci dan Pengering).
2. WHEN Admin mengubah status Mesin, THE Sistem SHALL mewajibkan pengisian keterangan alasan perubahan status (1–500 karakter) sebelum menyimpan perubahan.
3. IF Admin mengkonfirmasi perubahan status Mesin tanpa mengisi keterangan alasan, THEN THE Sistem SHALL menolak penyimpanan dan menampilkan pesan yang mengindikasikan keterangan alasan wajib diisi.
4. WHEN status Mesin diubah menjadi "Dalam Perbaikan", THE Sistem SHALL mencatat tanggal dan waktu mulai perbaikan secara otomatis berdasarkan waktu sistem dan menampilkan Mesin tersebut dalam daftar mesin bermasalah.
5. WHEN Admin menyimpan catatan pemeliharaan preventif yang valid, THE Sistem SHALL menyimpan data pemeliharaan dengan atribut: tanggal pemeliharaan, jenis pemeliharaan (maksimal 100 karakter), teknisi yang menangani (maksimal 100 karakter), biaya pemeliharaan (antara 0 hingga 999.999.999 Rupiah), dan catatan (maksimal 500 karakter).
6. WHEN Admin mencatat biaya pemeliharaan Mesin dengan nilai lebih dari 0, THE Sistem SHALL secara otomatis membuat Transaksi_Keuangan dengan kategori "Operasional" senilai biaya pemeliharaan tersebut dengan tanggal transaksi mengikuti tanggal pemeliharaan.
7. IF pembuatan Transaksi_Keuangan otomatis dari biaya pemeliharaan gagal, THEN THE Sistem SHALL membatalkan penyimpanan catatan pemeliharaan dan menampilkan pesan kesalahan yang mengindikasikan operasi gagal disimpan tanpa mengubah data.
8. WHILE tanggal pemeliharaan berikutnya telah dijadwalkan dan kurang dari atau sama dengan 7 hari kalender dari tanggal saat ini, THE Sistem SHALL menampilkan pengingat pemeliharaan preventif kepada Admin yang memuat: nama/kode mesin, tanggal pemeliharaan yang dijadwalkan, dan jenis pemeliharaan.
9. WHEN status Mesin berubah, THE Sistem SHALL memperbarui ringkasan kondisi Mesin pada dashboard yang mencakup: jumlah Mesin aktif, jumlah dalam perbaikan, dan jumlah tidak aktif.
10. WHEN Owner atau Admin memilih rentang periode (tanggal mulai dan tanggal selesai, inklusif), THE Sistem SHALL menampilkan total biaya pemeliharaan per Mesin untuk periode tersebut dalam waktu tidak lebih dari 5 detik.

---

### Persyaratan 11: Manajemen Promo dan Diskon

**Cerita Pengguna:** Sebagai Admin atau Owner, saya ingin membuat dan mengelola promo diskon yang dapat digunakan oleh Karyawan saat memproses Order, sehingga dapat mendorong penjualan dan memberikan insentif kepada pelanggan setia.

#### Kriteria Penerimaan

1. THE Admin SHALL dapat membuat Promo baru dengan atribut: nama promo (maksimal 100 karakter), kode promo unik (3–20 karakter alfanumerik), jenis diskon (persentase atau nominal tetap), nilai diskon (persentase: 0,01%–100%; nominal: 0,01 hingga 999.999.999,99), tanggal mulai berlaku, tanggal berakhir (harus sama dengan atau setelah tanggal mulai), batas maksimum penggunaan (opsional, bilangan bulat 1–999.999), dan minimum nilai Order untuk berlaku (opsional, 0,01 hingga 999.999.999,99).
2. WHEN Admin membuat Promo dengan kode yang sudah ada, THE Sistem SHALL menolak pembuatan dan menampilkan pesan kesalahan yang mengindikasikan kode promo sudah digunakan.
3. IF Admin menetapkan tanggal berakhir lebih awal dari tanggal mulai berlaku, THEN THE Sistem SHALL menolak pembuatan Promo dan menampilkan pesan kesalahan yang mengindikasikan rentang tanggal tidak valid.
4. WHEN Karyawan menginput kode Promo saat membuat Order, THE Sistem SHALL memvalidasi kode tersebut dan menampilkan nama promo, jenis diskon, nilai diskon yang akan diterapkan, dan total harga Order setelah diskon sebelum konfirmasi.
5. IF kode Promo yang diinput tidak ditemukan dalam sistem, THEN THE Sistem SHALL menampilkan pesan kesalahan yang mengindikasikan kode promo tidak dikenali.
6. IF kode Promo yang diinput ditemukan namun tanggal hari ini berada di luar rentang tanggal berlaku Promo, THEN THE Sistem SHALL menampilkan pesan kesalahan yang mengindikasikan promo sudah kedaluwarsa atau belum aktif.
7. IF kode Promo yang diinput ditemukan dan masih berlaku namun telah mencapai batas maksimum penggunaan, THEN THE Sistem SHALL menampilkan pesan kesalahan yang mengindikasikan batas penggunaan promo telah tercapai.
8. IF kode Promo yang diinput ditemukan dan masih berlaku namun nilai total Order kurang dari minimum nilai Order yang ditetapkan, THEN THE Sistem SHALL menampilkan pesan kesalahan yang mengindikasikan nilai Order tidak memenuhi minimum dan menampilkan nilai minimum tersebut.
9. WHEN diskon persentase diterapkan dan nilai diskon yang dihitung melebihi total harga Order sebelum diskon, THE Sistem SHALL membatasi nilai diskon yang diterapkan maksimal sebesar total harga Order sehingga total akhir Order tidak kurang dari 0,01.
10. THE Sistem SHALL mencatat setiap penggunaan Promo dengan detail: nomor Order, tanggal dan waktu penggunaan, identitas Karyawan yang memproses, Customer, dan nilai diskon yang diberikan.
11. THE Admin dan Owner SHALL dapat melihat laporan penggunaan Promo yang menampilkan: total penggunaan, total nilai diskon yang diberikan, dan daftar Order yang menggunakan Promo tersebut.
12. WHEN tanggal hari ini melewati tanggal berakhir Promo, THE Sistem SHALL secara otomatis menonaktifkan Promo sehingga kode Promo tersebut tidak dapat lagi divalidasi pada Order baru.

---

### Persyaratan 12: Manajemen FAQ

**Cerita Pengguna:** Sebagai Admin, saya ingin mengelola daftar pertanyaan yang sering diajukan, sehingga Customer dapat menemukan jawaban secara mandiri tanpa harus menghubungi layanan pelanggan.

#### Kriteria Penerimaan

1. WHEN Admin menyimpan entri FAQ baru, THE Sistem SHALL menyimpan entri dengan atribut wajib: pertanyaan (1–500 karakter), jawaban (1–5.000 karakter dengan format teks kaya/rich text), kategori FAQ, dan status tampil (aktif/tidak aktif, default: aktif).
2. IF Admin menyimpan entri FAQ baru dengan pertanyaan atau jawaban kosong, THEN THE Sistem SHALL menolak penyimpanan dan menampilkan pesan kesalahan yang mengidentifikasi field mana yang belum diisi.
3. WHEN Admin mengkonfirmasi penghapusan entri FAQ, THE Sistem SHALL menghapus entri tersebut secara permanen dan memperbarui daftar FAQ.
4. IF Admin memilih opsi hapus entri FAQ, THEN THE Sistem SHALL menampilkan dialog konfirmasi sebelum menghapus secara permanen; jika Admin membatalkan konfirmasi, entri FAQ SHALL tetap tersimpan tanpa perubahan.
5. WHEN Customer atau Admin mengakses halaman FAQ, THE Sistem SHALL menampilkan hanya entri FAQ aktif yang dikelompokkan berdasarkan kategori FAQ; IF suatu kategori FAQ tidak memiliki entri aktif, THEN kategori tersebut tidak ditampilkan.
6. WHEN Customer mencari kata kunci di halaman FAQ, THE Sistem SHALL menampilkan entri FAQ aktif yang relevan berdasarkan kecocokan parsial dan tidak peka huruf besar/kecil pada kolom pertanyaan dan jawaban dalam waktu kurang dari 1 detik; IF tidak ada entri yang cocok, THEN THE Sistem SHALL menampilkan pesan yang mengindikasikan tidak ada hasil ditemukan.
7. WHEN Admin menyimpan perubahan nomor urut entri FAQ, THE Sistem SHALL memperbarui urutan tampil entri; IF dua entri memiliki nomor urut yang sama dalam kategori yang sama, THEN THE Sistem SHALL menampilkan pesan kesalahan yang mengindikasikan konflik nomor urut tanpa menyimpan perubahan.
8. WHEN Customer membuka atau memperluas entri FAQ, THE Sistem SHALL menambah hitungan view count entri tersebut sebesar 1 dan menampilkan nilai view count terbaru kepada Admin.
9. WHERE fitur umpan balik FAQ diaktifkan, THE Sistem SHALL menampilkan tombol "Jawaban ini membantu" pada setiap entri FAQ aktif dan mencatat hitungan respons positif yang dapat dilihat oleh Admin.

---

### Persyaratan 13: Dashboard dan Pelaporan

**Cerita Pengguna:** Sebagai Owner atau Admin, saya ingin melihat ringkasan performa bisnis secara menyeluruh dalam satu tampilan, sehingga dapat memantau kondisi operasional dan membuat keputusan bisnis dengan cepat.

#### Kriteria Penerimaan

1. THE Sistem SHALL menampilkan dashboard Owner yang memuat: total pemasukan hari ini (dari Order berstatus Selesai), total Order aktif saat ini, total Customer terdaftar, grafik pemasukan 30 hari kalender terakhir berdasarkan zona waktu server, dan 5 Produk terlaris bulan ini berdasarkan volume satuan terjual.
2. THE Sistem SHALL menampilkan dashboard Karyawan yang memuat: daftar Order yang ditugaskan kepada Karyawan tersebut pada tanggal hari ini beserta statusnya; IF tidak ada Order yang ditugaskan hari ini, THEN THE Sistem SHALL menampilkan pesan yang mengindikasikan tidak ada Order untuk hari ini.
3. WHEN data dashboard tidak berhasil diperbarui, THE Sistem SHALL menampilkan indikator kegagalan pengambilan data beserta timestamp terakhir data berhasil dimuat, tanpa menyembunyikan data lama yang sudah ada.
4. THE Sistem SHALL memperbarui data dashboard secara otomatis setiap 5 menit tanpa memerlukan reload halaman oleh pengguna.
5. WHEN Owner mengakses laporan performa bulanan, THE Sistem SHALL menampilkan perbandingan total Order, total pemasukan dari Order berstatus Selesai, dan jumlah Customer baru antara bulan berjalan dan bulan sebelumnya.
6. WHEN Owner memilih periode analisis (7 hari terakhir, 30 hari terakhir, bulan berjalan, atau bulan sebelumnya), THE Sistem SHALL menampilkan 5 Customer dengan total nilai transaksi dari Order berstatus Selesai tertinggi dalam periode yang dipilih tersebut.
