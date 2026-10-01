# Security Verification - Bypass Scope

| Jalur | Alasan bypass | Filter pengganti |
|---|---|---|
| Katalog publik pelanggan | Order lintas tenant dalam 1 canteen | filter canteen_id + status aktif |
| Laporan admin platform | Admin lihat semua tenant | filter eksplisit per kebutuhan laporan |