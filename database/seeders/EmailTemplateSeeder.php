<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Welcome Customer',
                'subject' => 'Welcome to {company_name}!',
                'body' => "Hi {customer_name},\n\nWe are thrilled to welcome you to {company_name}. We look forward to serving you!\n\nBest,\nThe Team",
            ],
            [
                'name' => 'Invoice Overdue',
                'subject' => 'Action Required: Your Invoice is Overdue',
                'body' => "Hi {customer_name},\n\nThis is a friendly reminder that invoice {invoice_number} for {invoice_amount} is now overdue. Please process payment at your earliest convenience.\n\nThank you,\n{company_name}",
            ],
            [
                'name' => 'Proposal Sent',
                'subject' => 'Project Proposal from {company_name}',
                'body' => "Hi {customer_name},\n\nPlease find attached the project proposal we discussed. Let us know if you have any questions.\n\nBest,\n{user_name}",
            ],
        ];

        foreach ($templates as $t) {
            EmailTemplate::updateOrCreate(['name' => $t['name']], $t);
        }
    }
}
