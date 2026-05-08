<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Task;
use App\Models\TaxTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class Phase3Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();
        if (! $admin) {
            return;
        }

        // 1. Tax Templates
        TaxTemplate::updateOrCreate(
            ['name' => 'VAT 7%'],
            ['rate' => 7.00, 'is_default' => true, 'user_id' => $admin->id]
        );

        TaxTemplate::updateOrCreate(
            ['name' => 'No Tax'],
            ['rate' => 0.00, 'is_default' => false, 'user_id' => $admin->id]
        );

        // 2. Sample Project/Tasks
        $customer = Customer::first();
        if ($customer) {
            Task::create([
                'title' => 'Initial Meeting with Customer',
                'description' => 'Discuss project requirements and budget.',
                'due_date' => now()->addDays(7),
                'priority' => 'High',
                'status' => 'Todo',
                'relatable_type' => Customer::class,
                'relatable_id' => $customer->id,
                'user_id' => $admin->id,
            ]);
        }
    }
}
