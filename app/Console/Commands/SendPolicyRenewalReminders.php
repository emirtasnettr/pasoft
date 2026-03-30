<?php

namespace App\Console\Commands;

use App\Models\InsurancePolicy;
use App\Notifications\PolicyRenewalDueNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendPolicyRenewalReminders extends Command
{
    protected $signature = 'policies:renewal-reminders';

    protected $description = 'Queue renewal reminder notifications for policies nearing end date';

    public function handle(): int
    {
        $today = Carbon::today();

        InsurancePolicy::query()
            ->with(['customer.assignedUser', 'owner'])
            ->where('renewal_status', '!=', 'renewed')
            ->whereDate('ends_at', '>=', $today)
            ->chunkById(100, function ($policies) use ($today): void {
                foreach ($policies as $policy) {
                    $end = Carbon::parse($policy->ends_at)->startOfDay();
                    $windowStart = $end->copy()->subDays((int) $policy->renewal_reminder_days);
                    if ($today->lt($windowStart) || $today->gt($end)) {
                        continue;
                    }

                    if ($policy->last_renewal_reminder_at?->isToday()) {
                        continue;
                    }

                    $notifiables = collect([$policy->owner, $policy->customer?->assignedUser])
                        ->filter()
                        ->unique('id');

                    foreach ($notifiables as $user) {
                        Notification::send($user, new PolicyRenewalDueNotification($policy->id));
                    }

                    $policy->update(['last_renewal_reminder_at' => now()]);
                }
            });

        $this->info('Renewal reminder pass completed.');

        return self::SUCCESS;
    }
}
