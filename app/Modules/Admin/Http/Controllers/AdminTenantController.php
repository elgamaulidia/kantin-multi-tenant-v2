<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Canteen;
use App\Models\Tenant;
use App\Models\UserCanteenRole;
use App\Modules\Admin\Http\Requests\StoreTenantRequest;
use App\Modules\Admin\Services\ChangeCommissionSchedule;
use App\Modules\Admin\Services\CreateTenant;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTenantController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private CreateTenant $createTenant,
        private ChangeCommissionSchedule $scheduleCommission
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Tenant::class);

        $canteenId = UserCanteenRole::query()
            ->where('user_id', $request->user()->id)
            ->value('canteen_id');

        abort_unless($canteenId !== null, 403);

        $canteen = Canteen::findOrFail($canteenId);

        $tenants = Tenant::query()
            ->where('canteen_id', $canteenId)
            ->latest()
            ->get();

        return view(
            'admin::tenants.index',
            compact('tenants', 'canteen')
        );
    }

    public function create(): View
    {
        $this->authorize('create', Tenant::class);

        return view('admin::tenants.create');
    }

    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $canteenId = $request->canteenId();

        abort_unless($canteenId !== null, 403);

        $canteen = Canteen::findOrFail($canteenId);

        $tenant = $this->createTenant->handle(
            $canteen,
            $request->validated()
        );

        return redirect()
            ->route('admin.tenants.index')
            ->with(
                'status',
                "Tenant {$tenant->display_name} berhasil dibuat."
            );
    }

    public function edit(Tenant $tenant): View
    {
        $this->authorize('update', $tenant);

        $commissions = $tenant->commissionSchemes()
            ->orderBy('valid_from')
            ->get();

        return view(
            'admin::tenants.edit',
            compact('tenant', 'commissions')
        );
    }

    public function scheduleCommission(
        Request $request,
        Tenant $tenant
    ): RedirectResponse {
        $this->authorize('scheduleCommission', $tenant);

        $validated = $request->validate([
            'commission_rate' => [
                'required',
                'numeric',
                'between:0,100',
            ],
            'effective_from' => [
                'required',
                'date',
            ],
        ]);

        $this->scheduleCommission->handle(
            $tenant,
            (float) $validated['commission_rate'],
            new \DateTimeImmutable($validated['effective_from'])
        );

        return redirect()
            ->route('admin.tenants.edit', $tenant)
            ->with(
                'status',
                'Skema komisi berhasil dijadwalkan.'
            );
    }
}