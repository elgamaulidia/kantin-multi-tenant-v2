<?php

namespace App\Modules\Catalog\Services;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Collection;

class PublicCatalogQuery
{
    /**
     * Ambil daftar menu aktif untuk satu canteen (lintas tenant),
     * dipakai oleh katalog publik pelanggan setelah scan QR meja.
     *
     * Bypass global scope tenant SENGAJA dipakai di sini karena
     * pelanggan perlu melihat menu dari semua tenant dalam satu canteen.
     * Filter pengganti: canteen_id (via relasi tenant) + status aktif.
     */
    public function menusForCanteen(int $canteenId): Collection
    {
        return Menu::withoutGlobalScopes()
            ->whereHas('tenant', function ($query) use ($canteenId): void {
                $query->where('canteen_id', $canteenId)
                    ->where('status', 'active');
            })
            ->where('is_available', true)
            ->with(['category', 'tenant'])
            ->get();
    }
}
