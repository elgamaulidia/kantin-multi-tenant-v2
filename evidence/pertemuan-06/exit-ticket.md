# Exit Ticket - Pertemuan 6

1. Mengapa UUID meja bukan secret QR?
UUID meja adalah ID yang dirancang untuk identifikasi, bukan rahasia: ia bisa muncul di URL, log, atau dibagikan, dan tidak bisa dicabut tanpa mengganti ID meja itu sendiri. Secret QR harus acak (CSPRNG, minimal 128 bit), hanya hash-nya disimpan, dan bisa dirotasi tanpa mengubah meja atau order historis.

2. Mengapa QR token dan session token berbeda?
QR token adalah kredensial publik pintu masuk meja yang bisa difoto dan disalin, sedangkan session token adalah identitas anonim satu kunjungan yang disimpan di cookie HttpOnly dan kedaluwarsa dalam 4 jam. Dipisah agar bocornya satu tidak membocorkan yang lain, dan sesi bisa dibuat atau dicabut tanpa mengganti QR yang sudah dicetak.

3. Apa dampak rotate terhadap QR lama?
Token lama langsung berstatus revoked dalam transaksi yang sama dengan penerbitan token baru, sehingga memindai QR lama menghasilkan 404 generik. ID meja dan order historis tidak berubah; sesi pelanggan yang sudah terbentuk tetap berlaku sampai kedaluwarsa 4 jam.