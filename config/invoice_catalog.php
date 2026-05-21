<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Invoice item catalog seed
    |--------------------------------------------------------------------------
    |
    | VeloCRM does not have a dedicated products/services table yet. Keep the
    | starter catalog outside Livewire components so it can later be replaced
    | by Product or Service models without changing the invoice form contract.
    |
    */
    'items' => [
        [
            'key' => 'crm-implementation',
            'name' => 'CRM implementation service',
            'code' => 'CRM-IMPL',
            'sku' => 'SVC-CRM-001',
            'description' => 'CRM implementation service',
            'unit_price' => 68000,
            'unit' => 'project',
            'currency' => 'THB',
            'default_tax' => 'vat_7',
        ],
        [
            'key' => 'monthly-crm-retainer',
            'name' => 'Monthly CRM support retainer',
            'code' => 'CRM-SUPPORT',
            'sku' => 'SVC-CRM-002',
            'description' => 'Monthly CRM support and optimization retainer',
            'unit_price' => 12000,
            'unit' => 'month',
            'currency' => 'THB',
            'default_tax' => 'vat_7',
        ],
        [
            'key' => 'crm-customization',
            'name' => 'CRM customization',
            'code' => 'CRM-CUSTOM',
            'sku' => 'SVC-CRM-003',
            'description' => 'Custom CRM workflow, fields, reports, and automation setup',
            'unit_price' => 25000,
            'unit' => 'scope',
            'currency' => 'THB',
            'default_tax' => 'vat_7',
        ],
        [
            'key' => 'user-training',
            'name' => 'User training session',
            'code' => 'TRAINING',
            'sku' => 'SVC-TRN-001',
            'description' => 'Remote user training session with recorded handover',
            'unit_price' => 4500,
            'unit' => 'session',
            'currency' => 'THB',
            'default_tax' => 'vat_7',
        ],
        [
            'key' => 'data-import',
            'name' => 'Customer data import',
            'code' => 'DATA-IMPORT',
            'sku' => 'SVC-DATA-001',
            'description' => 'Clean and import customer, lead, and invoice records',
            'unit_price' => 15000,
            'unit' => 'batch',
            'currency' => 'THB',
            'default_tax' => 'vat_7',
        ],
    ],
];
