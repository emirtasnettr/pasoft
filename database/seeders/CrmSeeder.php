<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\InsuranceCompany;
use App\Models\InsurancePolicy;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\PolicyType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CrmSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
        $salesRole = Role::query()->where('slug', 'sales')->firstOrFail();
        $operationsRole = Role::query()->where('slug', 'operations')->firstOrFail();

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@policeasist.test'],
            [
                'name' => 'Admin Kullanıcı',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'phone' => '+90 555 000 0001',
                'email_verified_at' => now(),
            ],
        );

        $sales = User::query()->updateOrCreate(
            ['email' => 'satis@policeasist.test'],
            [
                'name' => 'Satış Temsilcisi',
                'password' => Hash::make('password'),
                'role_id' => $salesRole->id,
                'phone' => '+90 555 000 0002',
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'operasyon@policeasist.test'],
            [
                'name' => 'Operasyon Uzmanı',
                'password' => Hash::make('password'),
                'role_id' => $operationsRole->id,
                'phone' => '+90 555 000 0003',
                'email_verified_at' => now(),
            ],
        );

        $customer = Customer::query()->create([
            'type' => 'individual',
            'name' => 'Ayşe Yılmaz',
            'national_id' => '12345678901',
            'phone' => '+90 532 111 2233',
            'email' => 'ayse@example.com',
            'segment' => 'vip',
            'assigned_user_id' => $sales->id,
        ]);

        $customer->addresses()->create([
            'label' => 'Ev',
            'line1' => 'Bağdat Cd. No:12',
            'district' => 'Kadıköy',
            'city' => 'İstanbul',
            'is_primary' => true,
        ]);

        Lead::query()->create([
            'title' => 'Kasko talebi — Ahmet K.',
            'email' => 'ahmet@example.com',
            'phone' => '+90 533 444 5566',
            'source' => 'Google Ads',
            'stage' => 'proposal',
            'estimated_value' => 18500,
            'assigned_user_id' => $sales->id,
            'customer_id' => $customer->id,
            'next_follow_up_at' => now()->addDays(2),
        ]);

        $policyType = PolicyType::query()->where('slug', 'kasko')->firstOrFail();
        $company = InsuranceCompany::query()->where('code', 'ALLIANZ')->firstOrFail();

        $policy = InsurancePolicy::query()->create([
            'customer_id' => $customer->id,
            'policy_type_id' => $policyType->id,
            'insurance_company_id' => $company->id,
            'policy_number' => 'ACM-2026-0001',
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addMonths(11),
            'premium_amount' => 12400,
            'commission_amount' => 1860,
            'commission_collected' => 930,
            'premium_payment_status' => 'partial',
            'coverage_details' => 'Tam kasko, mini onarım dahil.',
            'renewal_status' => 'active',
            'owner_user_id' => $sales->id,
        ]);

        Payment::query()->create([
            'customer_id' => $customer->id,
            'policy_id' => $policy->id,
            'amount' => 6200,
            'method' => 'card',
            'paid_at' => now()->subDays(3),
            'reference' => 'PAY-1001',
            'recorded_by_user_id' => $admin->id,
        ]);
    }
}
