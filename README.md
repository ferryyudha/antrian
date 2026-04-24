# Q-Master: Sistem Antrian Berbasis QR Code 🚀

Sistem manajemen antrian modern yang dirancang untuk efisiensi tinggi, menggunakan teknologi **QR Code** untuk pendaftaran dan verifikasi. Dibangun dengan Laravel dan sistem real-time menggunakan Socket.io.

## ✨ Fitur Unggulan

- **Pendaftaran Instan**: Cukup masukkan NIK & KK, sistem akan menvalidasi secara otomatis.
- **Validasi Ketat**: Mencegah pendaftaran ganda berdasarkan NIK atau KK di hari yang sama untuk keadilan antrian.
- **Kartu Antrian Premium**: Desain tiket yang estetik, hemat kertas (fit 1 halaman), dan dilengkapi sensor privasi (masking NIK/KK).
- **Monitoring Real-time**: Admin dapat memantau pergerakan antrian secara langsung tanpa refresh halaman.
- **Verifikasi QR Cepat**: Staff cukup memindai QR untuk memproses antrian pelanggan.
- **Optimasi AJAX**: Performa super cepat dan responsif tanpa reload halaman.

## 🛠️ Teknologi yang Digunakan

- **Backend**: Laravel 11 (PHP 8.2+)
- **Database**: MySQL
- **Real-time**: Node.js + Socket.io
- **Frontend**: Vanilla JS (Modern Fetch API), CSS3 (Flexbox/Grid)
- **Library**: SweetAlert2 (Notifikasi), QRCode.js (Generator QR), Lucide Icons.

## 🚀 Panduan Instalasi

### 1. Persiapan Database
Buat database baru di MySQL (misal: `db_antrian`), lalu update file `.env`:
```env
DB_DATABASE=db_antrian
DB_USERNAME=root
DB_PASSWORD=
```

### 2. Instalasi Dependensi
Jalankan perintah berikut di folder project:
```bash
composer install
php artisan migrate
```

### 3. Menjalankan Server Real-time
Buka terminal baru dan jalankan server Socket.io:
```bash
cd realtime-server
npm install
node server.js
```

### 4. Menjalankan Aplikasi
Jalankan server Laravel:
```bash
php artisan serve
```

## 📖 Cara Penggunaan

1.  **Pelanggan**: Akses `http://localhost:8000/` untuk mengambil nomor antrian.
2.  **Admin**: Akses `http://localhost:8000/admin.html` untuk memantau dan mengatur lokasi/kuota.
3.  **Staff**: Akses `http://localhost:8000/frontend-staff.html` (atau scan QR dari pelanggan) untuk verifikasi antrian.

## 📄 Lisensi
Project ini dibuat untuk tujuan efisiensi manajemen antrian publik. Silakan dikembangkan lebih lanjut!

---
Dibuat dengan ❤️ untuk sistem pelayanan yang lebih baik.
