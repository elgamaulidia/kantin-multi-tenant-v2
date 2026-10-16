# Demo Script - Pertemuan 6

1. Jalankan php artisan serve, buka http://127.0.0.1:8000/login.
2. Login sebagai admin kantin (admin@kantin.test).
3. Buka /admin/tables, tambah meja (kode, nama, area).
4. Klik "Terbitkan QR Baru": muncul URL /q/... dan gambar QR (token hanya tampil sekali).
5. Buka URL /q/... di jendela incognito: diarahkan ke /{slug-kantin}/meja, tampil meja, zona, dan status tenant (BUKA/TUTUP).
6. Kembali ke /admin/tables, klik "Terbitkan QR Baru" lagi.
7. Buka URL lama: 404. Buka URL baru: halaman meja.