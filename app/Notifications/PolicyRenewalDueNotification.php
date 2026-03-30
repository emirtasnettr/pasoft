<?php

namespace App\Notifications;

use App\Models\InsurancePolicy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PolicyRenewalDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $policyId,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $policy = InsurancePolicy::query()->find($this->policyId);

        return [
            'policy_id' => $this->policyId,
            'policy_number' => $policy?->policy_number,
            'ends_at' => $policy?->ends_at?->toDateString(),
            'message' => 'Poliçe yenileme tarihi yaklaşıyor.',
        ];
    }
}
