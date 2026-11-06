<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'hdayaaslam34@gmail.com'],
            [
                'name' => 'مدير الصالة',
                'password' => Hash::make('123456'),
                'role' => 'admin',
                'email_verified_at' => null,
            ]
        );

        $plans = [
            ['name' => 'باقة الشهر الواحد', 'days' => 30, 'price' => 150000, 'is_active' => true],
            ['name' => 'باقة 3 أشهر', 'days' => 90, 'price' => 400000, 'is_active' => true],
            ['name' => 'الباقة السنوية VIP', 'days' => 365, 'price' => 1400000, 'is_active' => true],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['name' => $plan['name']], $plan);
        }
    }
}
