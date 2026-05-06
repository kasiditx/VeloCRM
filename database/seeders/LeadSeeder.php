<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Lead;
use App\Models\User;

class LeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@velocrm.com')->first();

        if (!$admin) return;

        $leads = [
            ['name' => 'John Doe', 'company' => 'Google', 'value' => 5000, 'status' => 'New'],
            ['name' => 'Jane Smith', 'company' => 'Meta', 'value' => 12000, 'status' => 'Contacted'],
            ['name' => 'Alice Johnson', 'company' => 'Apple', 'value' => 25000, 'status' => 'Qualified'],
            ['name' => 'Bob Brown', 'company' => 'Amazon', 'value' => 8000, 'status' => 'Lost'],
            ['name' => 'Charlie Davis', 'company' => 'Microsoft', 'value' => 45000, 'status' => 'Won'],
        ];

        foreach ($leads as $leadData) {
            Lead::create(array_merge($leadData, ['user_id' => $admin->id]));
        }
    }
}
