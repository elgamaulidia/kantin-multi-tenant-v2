# Security Verification - Pertemuan 6

| Pemeriksaan | Hasil | Bukti |
|---|---|---|
| Token tidak dikenal | 404 generik | curl /q/TOKEN_TIDAK_ADA -> 404; test "QR yang tidak dikenal ditolak" |
| Token dicabut setelah rotasi | 302 sebelum, 404 sesudah | token id=23: active -> revoked (diuji dengan curl dan query database) |
| Token hanya disimpan sebagai hash | Lolos | test "token is opaque and stored only as hash" |
| Cookie sesi pelanggan | HttpOnly, SameSite=Lax, Secure di HTTPS/production, 240 menit | ResolveTableQrController; test "valid scan creates bound session with secure cookie" |
| ID internal tidak ada di URL | Lolos | URL berbentuk /q/{token acak 43 karakter} |
| Kantin tutup | Halaman tutup, sesi tidak dibuat | test QrOperatingHoursTest |
| Rate limit /q/{token} | Terpasang (throttle:30,1) | Belum diuji manual sampai 429 |