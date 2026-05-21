<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale ?? app()->getLocale()) }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    @php
        $requestedLocale = $locale ?? app()->getLocale();
        $currentLocale = in_array($requestedLocale, ['en', 'th'], true)
            ? $requestedLocale
            : config('app.fallback_locale', 'en');
        app()->setLocale($currentLocale);
        $isThai = $currentLocale === 'th';
        $documentTitle = $isThai ? $invoice->documentTypeLabel() : $invoice->documentTypeEnglishLabel();
        $regularFont = storage_path('fonts/Sarabun-Regular.ttf');
        $boldFont = storage_path('fonts/Sarabun-Bold.ttf');
        $regularFontUrl = file_exists($regularFont) ? $regularFont : null;
        $boldFontUrl = file_exists($boldFont) ? $boldFont : null;
    @endphp
    <title>{{ $documentTitle }} - {{ $invoice->number }}</title>
    <style>
        @if($regularFontUrl)
            @font-face {
                font-family: SarabunPdf;
                font-style: normal;
                font-weight: normal;
                src: url("{{ $regularFontUrl }}") format('truetype');
            }
        @endif
        @if($boldFontUrl)
            @font-face {
                font-family: SarabunPdf;
                font-style: normal;
                font-weight: bold;
                src: url("{{ $boldFontUrl }}") format('truetype');
            }
        @endif
        @page { margin: 30px 34px; }
        body {
            color: #111827;
            font-family: SarabunPdf, sans-serif;
            font-size: {{ $isThai ? '14pt' : '11pt' }};
            line-height: {{ $isThai ? '1.12' : '1.35' }};
            margin: 0;
        }
        .invoice-box {
            border: 1px solid #e5e7eb;
            padding: 18px 20px;
            width: 660px;
        }
        .header-table,
        .meta-table,
        .items-table,
        .totals-table {
            border-collapse: collapse;
            width: 100%;
        }
        .logo {
            max-height: 70px;
            max-width: 116px;
        }
        .title {
            font-size: 24pt;
            font-weight: bold;
            line-height: 1;
            margin: 0 0 6px;
            text-align: right;
            white-space: nowrap;
        }
        .doc-type {
            color: #6b7280;
            font-size: 14pt;
            font-weight: bold;
            text-align: right;
        }
        .invoice-title {
            font-size: {{ $isThai ? '23pt' : '20pt' }};
            font-weight: bold;
            text-align: right;
        }
        .muted { color: #6b7280; }
        .strong { font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .meta-table {
            margin-top: 22px;
        }
        .meta-table td {
            vertical-align: top;
            width: 50%;
        }
        .section-label {
            color: #4b5563;
            font-size: 13.5pt;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .items-table {
            margin-top: 18px;
            table-layout: fixed;
        }
        .items-table th {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
            font-size: 13pt;
            font-weight: bold;
            padding: 6px 7px;
        }
        .items-table td {
            border-bottom: 1px solid #eeeeee;
            padding: 6px 7px;
            vertical-align: top;
            word-wrap: break-word;
        }
        .col-description { width: 34%; }
        .col-qty { width: 12%; }
        .col-price { width: 20%; }
        .col-wht { width: 14%; }
        .col-amount { width: 20%; }
        .totals-wrap {
            margin-left: auto;
            margin-top: 16px;
            width: 320px;
        }
        .totals-table td {
            padding: 4px 0;
        }
        .grand-total td {
            border-top: 2px solid #111827;
            font-size: 16pt;
            font-weight: bold;
            padding-top: 8px;
        }
        .baht-text {
            color: #374151;
            font-size: 13pt;
            font-weight: bold;
            margin-top: 8px;
            text-align: right;
        }
        .balance-due {
            color: #b91c1c;
            font-weight: bold;
        }
        .notes {
            border-top: 1px solid #e5e7eb;
            color: #4b5563;
            font-size: 13pt;
            margin-top: 22px;
            padding-top: 12px;
        }
        .promptpay-box {
            border: 1px solid #e5e7eb;
            margin-top: 20px;
            padding: 10px;
            width: 205px;
        }
        .promptpay-qr {
            display: block;
            height: 132px;
            margin: 6px auto;
            width: 132px;
        }
        .footer {
            color: #6b7280;
            font-size: 12.5pt;
            margin-top: 28px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table class="header-table">
            <tr>
                <td style="width: 45%; vertical-align: top;">
                    @if(isset($logo_base64))
                        <img src="{{ $logo_base64 }}" class="logo">
                    @else
                        <div class="strong" style="font-size: 22pt;">{{ $company_name }}</div>
                    @endif
                </td>
                <td class="text-right" style="width: 55%; vertical-align: top;">
                    <div class="invoice-title">{{ $documentTitle }}</div>
                    <div><span class="muted">{{ __('Invoice No.') }}:</span> <span class="strong">{{ $invoice->number }}</span></div>
                    <div><span class="muted">{{ __('Issue Date') }}:</span> {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</div>
                    <div><span class="muted">{{ __('Due Date') }}:</span> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</div>
                    <div><span class="muted">{{ __('Currency') }}:</span> {{ $invoice->currency ?? velocrm_currency_code() }}</div>
                </td>
            </tr>
        </table>

        <table class="meta-table">
            <tr>
                <td>
                    <div class="section-label">{{ __('Bill To') }}</div>
                    <div class="strong">{{ $invoice->customer?->name ?? '-' }}</div>
                    @if($invoice->customer?->company)
                        <div>{{ $invoice->customer->company }}</div>
                    @endif
                    @if($invoice->customer?->address)
                        <div class="muted">{{ $invoice->customer->address }}</div>
                    @endif
                    @if($invoice->tax_id)
                        <div class="muted">{{ __('Tax ID') }}: {{ $invoice->tax_id }}</div>
                    @endif
                    @if($invoice->branch)
                        <div class="muted">{{ __('Branch') }}: {{ $invoice->branch }}</div>
                    @endif
                </td>
                <td class="text-right">
                    <div class="section-label">{{ __('From') }}</div>
                    <div class="strong">{{ $company_name }}</div>
                    @if(! empty($company_address))
                        <div>{{ $company_address }}</div>
                    @endif
                    @if(! empty($company_url))
                        <div class="muted">{{ $company_url }}</div>
                    @endif
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-description">{{ __('Description') }}</th>
                    <th class="col-qty" style="text-align: center;">{{ __('Quantity') }}</th>
                    <th class="col-price" style="text-align: right;">{{ __('Unit Price') }}</th>
                    <th class="col-wht" style="text-align: right;">{{ __('Withholding Tax') }}</th>
                    <th class="col-amount" style="text-align: right;">{{ __('Amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                    <td style="text-align: right;">{{ $invoice->money($item->unit_price) }}</td>
                    <td style="text-align: right;">
                        @if((float) $item->wht_amount > 0)
                            {{ rtrim(rtrim(number_format((float) $item->wht_rate, 2), '0'), '.') }}%<br>
                            <span class="muted">-{{ $invoice->money($item->wht_amount) }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align: right;">{{ $invoice->money($item->amount) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-wrap">
            <table class="totals-table">
                <tr>
                    <td>{{ __('Subtotal') }}:</td>
                    <td class="text-right">{{ $invoice->money($invoice->subtotal) }}</td>
                </tr>
                <tr>
                    <td>{{ __('Tax') }}:</td>
                    <td class="text-right">{{ $invoice->money($invoice->tax_total) }}</td>
                </tr>
                @if($invoice->discount > 0)
                    <tr>
                        <td>{{ __('Discount') }}:</td>
                        <td class="text-right">{{ $invoice->money($invoice->discount) }}</td>
                    </tr>
                @endif
                @if($invoice->wht_total > 0)
                    <tr>
                        <td>{{ __('Withholding Tax') }}{{ $invoice->withholdingTaxRateLabel() ? ' ('.$invoice->withholdingTaxRateLabel().')' : '' }}:</td>
                        <td class="text-right">-{{ $invoice->money($invoice->wht_total) }}</td>
                    </tr>
                @endif
                <tr class="grand-total">
                    <td>{{ __('Net Total') }}:</td>
                    <td class="text-right">{{ $invoice->money($invoice->total) }}</td>
                </tr>
                @if($invoice->amount_paid > 0)
                    <tr>
                        <td>{{ __('Paid') }}:</td>
                        <td class="text-right">{{ $invoice->money($invoice->amount_paid) }}</td>
                    </tr>
                    <tr class="balance-due">
                        <td>{{ __('Balance Due') }}:</td>
                        <td class="text-right">{{ $invoice->money($invoice->balance_due) }}</td>
                    </tr>
                @endif
            </table>
            @if(strtoupper((string) ($invoice->currency ?? velocrm_currency_code())) === 'THB')
                <div class="baht-text">{{ velocrm_baht_text($invoice->total) }}</div>
            @endif
        </div>

        @if($invoice->notes)
            <div class="notes">
                <span class="strong">{{ __('Notes') }}:</span><br>
                {!! nl2br(e($invoice->notes)) !!}
            </div>
        @endif

        @if(! empty($promptpay_qr_data_uri))
            <div class="promptpay-box">
                <div class="strong">PromptPay QR</div>
                <img src="{{ $promptpay_qr_data_uri }}" class="promptpay-qr">
                <div class="muted">{{ __('Receiver') }}: {{ $promptpay_receiver }}</div>
                <div class="muted">{{ __('Amount') }}: {{ $promptpay_amount }}</div>
            </div>
        @endif

        <div class="footer">
            {{ $isThai ? $invoice->documentTypeFooter() : __('This document was generated by VeloCRM.') }}<br>
            {{ __('Thank you for your business.') }}
        </div>
    </div>
</body>
</html>
