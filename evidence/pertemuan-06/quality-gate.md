# Quality Gate - Pertemuan 6

- [x] Seluruh test lolos: 100 passed (228 assertions) pada commit e23ea5e.
- [x] QrTableSessionTest: 4 passed (sesi terikat, rotasi, 404 generik, token hanya hash).
- [x] CustomerQrScanTest: 6 passed (aktif, tidak dikenal, kedaluwarsa, dicabut, meja nonaktif, kantin nonaktif).
- [x] QrOperatingHoursTest: 4 passed (dalam jam, luar jam, batas tutup eksklusif, tanpa jam = buka).
- [x] Token opaque 32 byte (43 karakter base64url), hanya hash SHA-256 yang disimpan.
- [x] Satu token aktif per meja; token lama dicabut saat rotasi.
- [x] Format kode (Pint): 58 masalah gaya diperbaiki, test tetap 100 passed (228 assertions).