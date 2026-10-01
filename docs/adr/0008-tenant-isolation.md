# ADR 0008 - Tenant Isolation

## Model tenant-owned (wajib global scope tenant_id)
- Menu
- MenuCategory
- ModifierGroup
- ModifierOption
- OrderItem
- TenantOrder
- TenantBalance
- CommissionScheme
- Withdrawal
- TenantBankAccount

## Model platform/canteen-scoped (TIDAK pakai scope tenant)
- Order (canteen-level, berisi item lintas tenant)
- DiningTable, TableQrToken, CustomerSession (canteen-level)
- Tenant, Canteen, User (entitas induk)
- UserCanteenRole (role level canteen)
- UserTenantRole (role level tenant - dipakai Tahap 3)

## Sumber resolver tenant aktif
Route parameter {tenant:slug}


## Aturan TenantContext pada queue job
- Job TIDAK boleh membawa object TenantContext atau model Tenant secara langsung (serialisasi berubah/stale).
- Job WAJIB menyimpan tenant_id (dan ID resource minimum) di payload.
- Pada method handle(), job harus:
  1. Muat tenant dari tenant_id
  2. Verifikasi tenant masih aktif
  3. Set TenantContext sebelum query domain
  4. Clear TenantContext setelah selesai (agar worker tidak membawa tenant sebelumnya ke job berikutnya)

  
## Aturan Livewire action pada domain tenant (menu/order/withdrawal)
- Setiap Livewire component yang memuat/mengubah data tenant-owned (Menu, TenantOrder, Withdrawal) WAJIB memanggil $this->authorize() terhadap policy yang sesuai sebelum eksekusi action.
- Parameter komponen (public property) yang berisi ID resource dianggap sebagai input pengguna, sama seperti parameter route - tidak boleh dipercaya begitu saja tanpa policy check.
- Livewire component belum dibuat pada milestone ini; aturan ini berlaku untuk implementasi domain menu/order/withdrawal di modul berikutnya.


## Aturan bypass scope (withoutGlobalScopes)
- withoutGlobalScopes() HANYA boleh dipakai di dalam app/Modules/Catalog/Services/PublicCatalogQuery.php dan class sejenis yang khusus dibuat untuk query lintas tenant terotorisasi.
- Setiap Pull Request WAJIB di-review manual: cari penggunaan withoutGlobalScopes() atau withoutGlobalScope() di luar folder app/Modules/*/Services/ menggunakan:
  git grep -n "withoutGlobalScope" -- app/ | grep -v "app/Modules"
- Ditemukan penggunaan di luar folder tersebut = PR ditolak sampai dipindahkan ke service class yang sesuai dengan filter pengganti eksplisit.