## Arsitektur Modular Monolith — Modul 2

### Pemetaan Bounded Context

Setiap kebutuhan fungsional (FR) pada SRS dipetakan ke satu modul pemilik tunggal, sesuai prinsip modular monolith: satu deployment, satu database, tapi batas tanggung jawab antarmodul tegas.

| Modul | FR yang Dimiliki | Use Case Terkait |
|---|---|---|
| **Admin** | FR-ADM-01, FR-ADM-02, FR-ADM-03 | UC-21, UC-22, UC-23 |
| **Catalog** | FR-CUS-02, FR-CUS-04, FR-TEN-02, FR-TEN-03 | UC-01, UC-04, UC-13, UC-14 |
| **Ordering** | FR-CUS-01, FR-CUS-03, FR-CUS-05, FR-CUS-06, FR-CUS-07, FR-CUS-08 | UC-02, UC-03, UC-11, UC-05, UC-06, UC-09 |
| **Payments** | FR-PAY-01, FR-PAY-02, FR-PAY-03, FR-PAY-04, FR-TEN-08 | UC-07, UC-08, UC-10, UC-12, UC-20 |
| **Kitchen** | FR-TEN-04 | UC-15 |
| **Reporting** | FR-TEN-05, FR-TEN-06, FR-TEN-07 | UC-16, UC-17, UC-18 |

**Catatan khusus - FR-TEN-01 / UC-19 (Autentikasi):** tidak dimiliki satu modul manapun karena bersifat *cross-cutting concern* - dipakai bersama oleh seluruh use case tenant dan pengelola (sesuai SRS 3.2.5). Diimplementasikan sebagai layanan inti melalui starter kit Fortify + middleware `role`, bukan sebagai folder `app/Modules/*`.

### Diagram Dependency Antarmodul

**Arah dependency:** satu arah, menuju domain inti.
- `Ordering` men-include `Payments` (checkout selalu diikuti pembayaran - UC-05 -> UC-07).
- `Payments` men-include `Kitchen` (settlement melepas pesanan ke antrean dapur - UC-08 -> UC-15).
- `Kitchen` **tidak pernah** memanggil balik ke `Payments` atau `Ordering` - ini mencegah Payment mengendalikan UI Kitchen secara langsung (sesuai peringatan Modul 2 Tahap 1).
- `Reporting` hanya membaca data hasil transaksi (read-only), tidak memodifikasi domain lain.

### Konvensi Penamaan

**Route:**
- Prefix nama route sesuai konteks: `customer.*`, `tenant.*`, `admin.*`
- File terpisah per konteks: `routes/customer.php`, `routes/tenant.php`, `routes/admin.php`
- Route tenant wajib menggunakan `scopeBindings()` pada prefix `tenant/{tenant:slug}` untuk isolasi data antar-tenant (NFR-SEC-03, NFR-DAT-02)

**Namespace Modul:**
- Struktur folder: `app/Modules/{Module}/{Actions,Data,Services}`
- Provider modul: `app/Providers/{Module}ServiceProvider.php` (flat, bukan di dalam `app/Modules/`)
- 6 modul: `Admin`, `Catalog`, `Ordering`, `Payments`, `Kitchen`, `Reporting`

**Komponen Blade:**
- Layout per konteks: `resources/views/components/layouts/{customer,tenant,admin}.blade.php`
- Komponen reusable: `x-button`, `x-input`, `x-status-badge`, `x-empty-state`
- Layout customer **tidak** menggunakan `<x-layouts::app>` bawaan (memanggil `auth()->user()` yang crash untuk pelanggan anonim)

**Kontrol Akses:**
- Middleware `role:{admin|tenant}` menolak akses dengan 403 untuk role yang tidak sesuai
- Menyembunyikan menu di UI bukan pengganti otorisasi server-side (FR-TEN-01/UC-19)

### Bukti Verifikasi

- `php artisan about` - seluruh provider termuat tanpa circular dependency
- `php artisan route:list` - 65 route terdaftar, tidak ada nama duplikat
- `php artisan test` - 75 test lulus (166 assertions), termasuk 4 test `AuthorizationTest`
- `./vendor/bin/pint --test` - 90 file lulus standar PSR-12