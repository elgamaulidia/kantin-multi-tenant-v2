<?php

namespace App\Models\Concerns;

use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new class implements Scope {
            public function apply(Builder $builder, Model $model): void
            {
                $context = app(TenantContext::class);

                if ($context->has()) {
                    $builder->where(
                        $model->qualifyColumn('tenant_id'),
                        $context->id(),
                    );
                }
            }
        });

        static::creating(function (Model $model): void {
            $context = app(TenantContext::class);

            if (! $context->has()) {
                throw new \RuntimeException('TenantContext belum terisi, operasi tulis ditolak.');
            }

            $model->tenant_id = $context->id();
        });
    }
}