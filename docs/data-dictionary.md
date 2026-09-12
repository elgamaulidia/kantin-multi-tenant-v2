# Data Dictionary — Kantin Multi-Tenant

## Audit ERD & Urutan Dependensi (Modul 3, Tahap 1)

Sumber: `docs/IMG_5362.PNG` (ERD Baseline Fase 0).

### Pemetaan Domain dan Scope

Legenda: P = platform-scoped, T = tenant-owned, M = mixed/append-only

#### A. Tenancy, Pengguna, Akses
| Tabel | Scope | Kolom Kunci (dari ERD) |
|---|---|---|
| canteens | P | UQ code, slug; tax_rate, service_fee_rate; status |
| users | P | UQ email, phone_e164; password_hash; status |
| tenants | P | FK canteen_id; UQ canteen_id+code/slug; status, deleted_at |
| user_canteen_roles | P | FK user_id, canteen_id; role |
| tenant_balances | T | PK/FK tenant_id; available_amount, held_amount; CHECK saldo >= 0 |
| user_tenant_roles | T | FK user_id, tenant_id; role -> TenantContext |
| tenant_bank_accounts | T | FK tenant_id, verified_by; account_number_cipher; status, is_primary |

#### B. Meja, QR, Sesi Anonim
| Tabel | Scope | Kolom Kunci (dari ERD) |
|---|---|---|
| dining_tables | P | FK canteen_id; UQ canteen_id+code; label, zone, status |
| table_qr_tokens | P | FK dining_table_id; UQ token_hash; status, issued/expires/revoked |
| customer_sessions | P | PK id (ULID); FK canteen/table/qr_token; UQ session_token_hash; status, expires_at |

#### C. Katalog dan Konfigurasi Tenant
| Tabel | Scope | Kolom Kunci (dari ERD) |
|---|---|---|
| tenant_operating_hours | T | FK tenant_id; UQ tenant+day; opens_at, closes_at |
| commission_schemes | T | FK tenant_id; commission_rate; valid_from, valid_to |
| menu_categories | T | FK tenant_id; UQ tenant+name; sort_order, is_active |
| modifier_groups | T | FK tenant_id; UQ tenant+name; min_select, max_select, is_active |
| menus | T | FK tenant_id, category_id; base_price, stock_qty; is_available, prep_minutes |
| modifier_options | T | FK tenant_id, group_id; price_delta, stock_qty, is_available |
| menu_modifier_groups (pivot) | T | PK/FK menu_id, modifier_group_id; FK tenant_id; sort_order |
| menu_stock_movements | T | FK menu_id, order_item_id; UQ idempotency_key; type, quantity_delta |

#### D. Order dan Snapshot
| Tabel | Scope | Kolom Kunci (dari ERD) |
|---|---|---|
| orders | P (sengaja tanpa tenant_id) | UQ public_id/order_number; FK canteen_id, customer_session_id; UQ checkout_key, tracking_token_hash; status, totals, snapshot |
| tenant_orders | T | FK order_id, tenant_id, commission_id; status, scheduled_at; snapshot subtotal/tax/fee/commission/net |
| order_items | T | FK tenant_order_id, menu_id; snapshot name/price/prep; quantity, modifier_total, line_total |
| order_item_modifiers | T | FK order_item/group/option; snapshot group/option; price_delta_snapshot |

#### E. Pembayaran dan Keuangan
| Tabel | Scope | Kolom Kunci (dari ERD) |
|---|---|---|
| payments | P | UQ order_id, payment_reference; FK canteen_id, customer_session_id; amount, status, settled_at |
| payment_attempts | P | FK payment_id; UQ provider_reference; status, scheduled_at |
| payment_events | P | FK payment/attempt; UQ provider_event_id; signature, payload, result (append-only) |
| withdrawals | T | FK tenant/bank/requester/reviewer; UQ active tenant, idempotency_key; amount, status, transfer snapshot |
| ledger_entries | M (append-only) | FK tenant/order/payment/withdrawal; UQ idempotency_key; type, available/held delta |

#### F. Integrasi Asinkron dan Audit
| Tabel | Scope | Kolom Kunci (dari ERD) |
|---|---|---|
| notification_deliveries | M | FK order/session/tenant_order; UQ event_key+channel; status, attempts, payload |
| outbox_events | M | FK tenant_id (nullable); UQ event_key; aggregate/event/payload/published_at |
| audit_logs | M (append-only) | FK actor/canteen/tenant; entity, action, request_id; before/after/metadata (append-only) |

Total: 30 tabel.

### Urutan Migrasi (Parent -> Child)

1. canteens
2. tenants
3. dining_tables
4. table_qr_tokens
5. customer_sessions
6. user_canteen_roles
7. tenant_balances
8. user_tenant_roles
9. tenant_bank_accounts
10. tenant_operating_hours
11. commission_schemes
12. menu_categories
13. modifier_groups
14. menus
15. modifier_options
16. menu_modifier_groups
17. orders
18. tenant_orders
19. order_items
20. order_item_modifiers
21. menu_stock_movements
22. payments
23. payment_attempts
24. payment_events
25. withdrawals
26. ledger_entries
27. notification_deliveries
28. outbox_events
29. audit_logs

### Composite Unique / Foreign Key Komposit (dari ERD)

- tenants: UQ (canteen_id, code), (canteen_id, slug)
- dining_tables: UQ (canteen_id, code)
- menu_categories: UQ (tenant_id, name)
- modifier_groups: UQ (tenant_id, name)
- menus: FK (tenant_id, category_id) -> menu_categories
- order_items: FK (tenant_order_id, menu_id) -> menus (relasi tenant-scoped)
- payments: UQ (order_id, payment_reference)
- payment_attempts: UQ (provider_reference)
- payment_events: UQ (provider_event_id)
- ledger_entries: UQ (idempotency_key)
- notification_deliveries: UQ (event_key, channel)
- outbox_events: UQ (event_key)

### Catatan Khusus

- Kolom `tenant_id` pada tabel `users` (ditambahkan Modul 2) akan disambungkan sebagai foreign key ke `tenants.id` setelah tabel `tenants` dibuat (Tahap 2).
- `orders` sengaja tidak memiliki `tenant_id` karena satu order dapat mencakup beberapa tenant sekaligus (checkout lintas-tenant), sesuai penanda scope "P" pada ERD panel D.
- `tenant_balances` memiliki CHECK constraint `saldo >= 0` langsung di database, bukan hanya validasi aplikasi.
- `ledger_entries`, `payment_events`, `audit_logs` bersifat append-only (hanya INSERT, tidak ada UPDATE/DELETE).


## Penyimpangan ERD vs Implementasi Nyata (Modul 3, Tahap 7)

Dicatat sesuai instruksi Tahap 7: perbedaan antara rencana ERD/data dictionary awal
dengan migration Laravel yang benar-benar dibuat dan dijalankan.

| Tabel | Rencana (dictionary awal) | Implementasi nyata | Alasan |
|---|---|---|---|
| `payments` | FK `order_id`; UQ `(order_id, payment_reference)`; ada kolom `canteen_id`, `customer_session_id` | FK ke `tenant_order_id`; UQ `(tenant_order_id, payment_reference)`; kolom `canteen_id`/`customer_session_id` tidak ada | Tenant scoping sudah tersedia lewat `tenant_orders`, jadi payment cukup merujuk ke sana |
| `payment_attempts` | field `scheduled_at` | field `expires_at`, tambahan `qris_payload` (json) | Menyesuaikan alur QRIS yang punya masa kedaluwarsa, bukan jadwal |
| `withdrawals` | UQ "active tenant" (tersirat sebagai constraint DB) | Tidak ada unique constraint bersyarat di DB; validasi "hanya 1 withdrawal aktif per tenant" dipindah ke Model/Service | Unique constraint bersyarat tidak bisa dibuat langsung dengan `unique()` bawaan Laravel/MySQL |
| `notification_deliveries` | UQ `(event_key, channel)` | FK `tenant_order_id`; UQ `idempotency_key` | Idempotency key dinilai cukup untuk mencegah notifikasi dobel, tanpa perlu kolom channel terpisah |
| `order_items` | FK komposit `(tenant_order_id, menu_id)` → `menus` | FK komposit `(tenant_id, menu_id)` → `menus(tenant_id, id)` | Konsisten dengan pola invariant utama modul: composite FK berbasis `tenant_id` |
| `tenant_orders` | — (tidak direncanakan) | Index tambahan `tenant_orders_kds_index (tenant_id, status, scheduled_at)` | Ditambahkan Tahap 6 setelah `EXPLAIN` menunjukkan `Using filesort` pada query Kitchen Display System |

### Bukti Verifikasi Tahap 6

- Constraint lintas tenant `(tenant_id, menu_id)` terbukti menolak insert salah:
  `SQLSTATE[23000]: ... foreign key constraint fails (order_items_tenant_id_menu_id_foreign)`
- Idempotency key ganda pada `payments` terbukti ditolak:
  `SQLSTATE[23000]: ... Duplicate entry 'idem-test-001' for key 'payments.payments_idempotency_key_unique'`
- Query katalog (`menus` filter `tenant_id`) memakai index `menus_tenant_id_id_unique`.
- Query Kitchen Display System memakai index `tenant_orders_tenant_id_foreign` dan `order_items_tenant_order_id_foreign`.
- Schema dump tersimpan di `database/schema/mysql-schema.sql` dan `evidence/pertemuan03/schema-dump.sql`.