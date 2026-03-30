<?php

namespace App\Services;

use App\Models\PolicyType;
use Illuminate\Support\Facades\DB;

class PolicyDefaultsService
{
    /**
     * Poliçe oluşturma formu: firma + tür pivot komisyonu ve tür yenileme günü.
     *
     * @return array{commission_rate: float|null, min_commission: float|null, renewal_reminder_days: int|null}
     */
    public function forForm(?int $insuranceCompanyId, ?int $policyTypeId): array
    {
        return [
            'commission_rate' => $this->resolveCommissionRate($insuranceCompanyId, $policyTypeId),
            'min_commission' => $this->resolveMinCommission($insuranceCompanyId, $policyTypeId),
            'renewal_reminder_days' => $this->resolveRenewalReminderDays($policyTypeId),
        ];
    }

    public function resolveCommissionRate(?int $insuranceCompanyId, ?int $policyTypeId): ?float
    {
        $row = $this->pivotRow($insuranceCompanyId, $policyTypeId);

        return $row ? (float) $row->commission_rate : null;
    }

    public function resolveMinCommission(?int $insuranceCompanyId, ?int $policyTypeId): ?float
    {
        $row = $this->pivotRow($insuranceCompanyId, $policyTypeId);

        if (! $row || $row->min_commission === null) {
            return null;
        }

        return (float) $row->min_commission;
    }

    private function pivotRow(?int $insuranceCompanyId, ?int $policyTypeId): ?object
    {
        if (! $insuranceCompanyId || ! $policyTypeId) {
            return null;
        }

        return DB::table('company_policy_type')
            ->where('insurance_company_id', $insuranceCompanyId)
            ->where('policy_type_id', $policyTypeId)
            ->first();
    }

    public function resolveRenewalReminderDays(?int $policyTypeId): ?int
    {
        if (! $policyTypeId) {
            return null;
        }

        $t = PolicyType::query()->find($policyTypeId);

        return $t?->renewal_reminder_days;
    }
}
