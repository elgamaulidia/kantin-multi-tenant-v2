# Kantin Multi-Tenant

Aplikasi kantin multi-tenant berbasis Laravel 13 + Livewire 4.

## Requirements

- PHP 8.3+ (terpasang: PHP 8.4.24)
- Composer 2.x
- Node.js & NPM
- MariaDB / MySQL (via ServBay)
- Redis (via ServBay)
- Git

## Setup

1. Clone repository ini
2. Copy `.env.example` menjadi `.env`
3. Generate application key:
php artisan key:generate
4. Isi variabel DB_*, REDIS_*, SESSION_DRIVER, CACHE_STORE, dan QUEUE_CONNECTION dalam .env
5. Install dependency PHP: composer install
6. Jalankan migrasi pada database kosong: php artisan migrate:fresh
7. Install dan build dependency frontend: npm install lalu npm run build

## Run

composer run dev menjalankan server lokal, worker queue (proses latar belakang yang 
mengerjakan pekerjaan asinkron), dan Vite (alat pembangun aset frontend yang mengolah 
CSS dan JavaScript) dalam satu proses pengembangan.

composer run dev

Aplikasi dapat diakses di http://localhost:8000

## Test

php artisan test
./vendor/bin/pint --test
npm run build

## Troubleshooting

| Gejala | Penyebab mungkin | Perbaikan | Bukti pulih |
|---|---|---|---|
| could not find driver | pdo_mysql belum aktif | Aktifkan ekstensi lalu restart PHP | php -m memuat pdo_mysql |
| Port 3306 atau 6379 bentrok | Service lain masih aktif | Ganti port Compose dan .env | db:show dan redis ping berhasil |
| Vite manifest tidak ada | Asset belum diinstal atau dibangun | Jalankan npm install dan npm run build | Halaman memuat CSS dan JS |