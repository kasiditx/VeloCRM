<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Proposal;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class CrmDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->where('email', 'admin@velocrm.com')->first() ?? User::query()->first();

        if (! $owner) {
            return;
        }

        $leads = $this->seedLeads($owner);
        $customers = $this->seedCustomers($owner);

        $this->seedInvoices($owner, $customers);
        $this->seedProposals($owner, $customers, $leads);
        $this->seedTasks($owner, $customers, $leads);
    }

    /**
     * @return array<string, Lead>
     */
    private function seedLeads(User $owner): array
    {
        $records = [
            'siam-retail' => [
                'name' => 'Narin Chaiyaporn',
                'email' => 'narin@siamretail.example',
                'phone' => '081-234-1001',
                'company' => 'Siam Retail Group',
                'status' => 'New',
                'source' => 'Website',
                'value' => 180000,
                'notes' => 'Interested in CRM rollout for 3 retail branches.',
            ],
            'andaman-hospitality' => [
                'name' => 'Pimchanok Waree',
                'email' => 'pim@andamanhotel.example',
                'phone' => '082-234-1002',
                'company' => 'Andaman Hospitality',
                'status' => 'Contacted',
                'source' => 'Referral',
                'value' => 260000,
                'notes' => 'Needs proposal for customer portal and invoice workflow.',
            ],
            'bkk-logistics' => [
                'name' => 'Krit Srisuk',
                'email' => 'krit@bkklogistics.example',
                'phone' => '083-234-1003',
                'company' => 'BKK Logistics',
                'status' => 'Qualified',
                'source' => 'LinkedIn',
                'value' => 320000,
                'notes' => 'Qualified buyer. Decision expected this month.',
            ],
            'chiangmai-foods' => [
                'name' => 'Mallika Inta',
                'email' => 'mallika@cmfoods.example',
                'phone' => '084-234-1004',
                'company' => 'Chiang Mai Foods',
                'status' => 'Proposal',
                'source' => 'Event',
                'value' => 145000,
                'notes' => 'Waiting for revised proposal with Thai tax fields.',
            ],
            'eastern-automation' => [
                'name' => 'Thanawat Boonmee',
                'email' => 'thanawat@easternauto.example',
                'phone' => '085-234-1005',
                'company' => 'Eastern Automation',
                'status' => 'Won',
                'source' => 'Partner',
                'value' => 410000,
                'notes' => 'Converted to customer after pilot approval.',
            ],
            'river-tech' => [
                'name' => 'Sasithorn Meechai',
                'email' => 'sasithorn@rivertech.example',
                'phone' => '086-234-1006',
                'company' => 'River Tech',
                'status' => 'Lost',
                'source' => 'Outbound',
                'value' => 95000,
                'notes' => 'Lost to internal build. Follow up next quarter.',
            ],
        ];

        $leads = [];

        foreach ($records as $key => $data) {
            $lead = Lead::query()->firstOrNew(['email' => $data['email']]);
            $lead->forceFill($data + ['user_id' => $owner->id])->save();
            $leads[$key] = $lead;
        }

        return $leads;
    }

    /**
     * @return array<string, Customer>
     */
    private function seedCustomers(User $owner): array
    {
        $records = [
            'northstar' => [
                'name' => 'Northstar Distribution Co., Ltd.',
                'email' => 'finance@northstar.example',
                'phone' => '02-100-2001',
                'company' => 'Northstar Distribution',
                'address' => 'Bangkok, Thailand',
                'tax_id' => '0105550000000',
                'branch' => '00000',
            ],
            'lotus-medical' => [
                'name' => 'Lotus Medical Supplies',
                'email' => 'ap@lotusmedical.example',
                'phone' => '02-100-2002',
                'company' => 'Lotus Medical Supplies',
                'address' => 'Nonthaburi, Thailand',
                'tax_id' => '0105550000018',
                'branch' => '00000',
            ],
            'greenfield' => [
                'name' => 'Greenfield Foods',
                'email' => 'billing@greenfield.example',
                'phone' => '02-100-2003',
                'company' => 'Greenfield Foods',
                'address' => 'Pathum Thani, Thailand',
                'tax_id' => '0105550000026',
                'branch' => '00000',
            ],
            'metro-design' => [
                'name' => 'Metro Design Studio',
                'email' => 'ops@metrodesign.example',
                'phone' => '02-100-2004',
                'company' => 'Metro Design Studio',
                'address' => 'Bangkok, Thailand',
                'tax_id' => '0105550000034',
                'branch' => '00000',
            ],
        ];

        $customers = [];

        foreach ($records as $key => $data) {
            $customer = Customer::query()->firstOrNew(['email' => $data['email']]);
            $customer->forceFill($data + ['user_id' => $owner->id])->save();
            $customers[$key] = $customer;
        }

        return $customers;
    }

    /**
     * @param  array<string, Customer>  $customers
     */
    private function seedInvoices(User $owner, array $customers): void
    {
        $records = [
            ['number' => 'INV-DEMO-1001', 'customer' => 'northstar', 'status' => 'Draft', 'total' => 68000, 'due_days' => 7],
            ['number' => 'INV-DEMO-1002', 'customer' => 'lotus-medical', 'status' => 'Sent', 'total' => 124500, 'due_days' => 14],
            ['number' => 'INV-DEMO-1003', 'customer' => 'greenfield', 'status' => 'Overdue', 'total' => 39200, 'due_days' => -5],
            ['number' => 'INV-DEMO-1004', 'customer' => 'metro-design', 'status' => 'Paid', 'total' => 85000, 'due_days' => -10],
        ];

        foreach ($records as $data) {
            $customer = $customers[$data['customer']] ?? null;

            if (! $customer) {
                continue;
            }

            $paidAmount = $data['status'] === 'Paid' ? $data['total'] : 0;
            $invoice = Invoice::query()->firstOrNew(['number' => $data['number']]);
            $invoice->forceFill([
                'document_type' => 'invoice',
                'customer_id' => $customer->id,
                'tax_id' => $customer->tax_id,
                'branch' => $customer->branch,
                'invoice_date' => now()->subDays(3)->toDateString(),
                'due_date' => now()->addDays($data['due_days'])->toDateString(),
                'subtotal' => $data['total'],
                'tax_total' => 0,
                'wht_total' => 0,
                'discount' => 0,
                'total' => $data['total'],
                'amount_paid' => $paidAmount,
                'balance_due' => $data['total'] - $paidAmount,
                'status' => $data['status'],
                'currency' => 'THB',
                'exchange_rate' => 1,
                'notes' => 'Demo invoice for CRM navigation and dashboard data.',
                'user_id' => $owner->id,
            ])->save();

            $invoice->items()->delete();
            $invoice->items()->create([
                'description' => 'CRM implementation service',
                'quantity' => 1,
                'unit_price' => $data['total'],
                'amount' => $data['total'],
                'wht_rate' => 0,
                'wht_amount' => 0,
            ]);
        }
    }

    /**
     * @param  array<string, Customer>  $customers
     * @param  array<string, Lead>  $leads
     */
    private function seedProposals(User $owner, array $customers, array $leads): void
    {
        $records = [
            ['number' => 'PROP-DEMO-2001', 'customer' => 'northstar', 'lead' => 'siam-retail', 'status' => 'Draft', 'total' => 185000],
            ['number' => 'PROP-DEMO-2002', 'customer' => 'lotus-medical', 'lead' => 'andaman-hospitality', 'status' => 'Sent', 'total' => 260000],
            ['number' => 'PROP-DEMO-2003', 'customer' => 'greenfield', 'lead' => 'bkk-logistics', 'status' => 'Open', 'total' => 320000],
            ['number' => 'PROP-DEMO-2004', 'customer' => 'metro-design', 'lead' => 'chiangmai-foods', 'status' => 'Accepted', 'total' => 145000],
        ];

        foreach ($records as $data) {
            $proposal = Proposal::query()->firstOrNew(['number' => $data['number']]);
            $proposal->forceFill([
                'customer_id' => ($customers[$data['customer']] ?? null)?->id,
                'lead_id' => ($leads[$data['lead']] ?? null)?->id,
                'subject' => 'VeloCRM rollout proposal',
                'content' => 'Implementation scope, customer portal, invoice workflow, PromptPay payment, and Thai tax readiness.',
                'total' => $data['total'],
                'status' => $data['status'],
                'user_id' => $owner->id,
            ])->save();
        }
    }

    /**
     * @param  array<string, Customer>  $customers
     * @param  array<string, Lead>  $leads
     */
    private function seedTasks(User $owner, array $customers, array $leads): void
    {
        $records = [
            ['title' => 'Call Siam Retail about branch rollout', 'status' => 'Todo', 'priority' => 'High', 'days' => 1, 'relatable' => $leads['siam-retail'] ?? null],
            ['title' => 'Prepare revised proposal for Andaman Hospitality', 'status' => 'In Progress', 'priority' => 'Urgent', 'days' => 2, 'relatable' => $leads['andaman-hospitality'] ?? null],
            ['title' => 'Review overdue invoice with Greenfield Foods', 'status' => 'Todo', 'priority' => 'High', 'days' => 0, 'relatable' => $customers['greenfield'] ?? null],
            ['title' => 'Confirm payment receipt with Metro Design', 'status' => 'Done', 'priority' => 'Medium', 'days' => -1, 'relatable' => $customers['metro-design'] ?? null],
            ['title' => 'Schedule demo for BKK Logistics operations team', 'status' => 'In Progress', 'priority' => 'Medium', 'days' => 5, 'relatable' => $leads['bkk-logistics'] ?? null],
        ];

        foreach ($records as $data) {
            $task = Task::query()->firstOrNew(['title' => $data['title']]);
            $relatable = $data['relatable'];
            $task->forceFill([
                'description' => 'Demo task for pipeline, task list, and calendar sample data.',
                'due_date' => now()->addDays($data['days'])->toDateString(),
                'priority' => $data['priority'],
                'status' => $data['status'],
                'relatable_type' => $relatable ? $relatable::class : null,
                'relatable_id' => $relatable?->id,
                'user_id' => $owner->id,
                'assigned_to' => $owner->id,
            ])->save();
        }
    }
}
