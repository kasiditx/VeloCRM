<!DOCTYPE html>
<html lang="th">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice - {{ $invoice->number }}</title>
    @php
        $regularFont = storage_path('fonts/THSarabunNew.ttf');
        $boldFont = storage_path('fonts/THSarabunNew-Bold.ttf');
        $regularFontUrl = file_exists($regularFont) ? 'file://' . $regularFont : null;
        $boldFontUrl = file_exists($boldFont) ? 'file://' . $boldFont : null;
    @endphp
    <style>
        @if($regularFontUrl)
            @font-face {
                font-family: 'thsarabunnew';
                font-style: normal;
                font-weight: normal;
                src: url("{{ $regularFontUrl }}") format('truetype');
            }
        @endif
        @if($boldFontUrl)
            @font-face {
                font-family: 'thsarabunnew';
                font-style: normal;
                font-weight: bold;
                src: url("{{ $boldFontUrl }}") format('truetype');
            }
        @endif
        @page { margin: 30px 34px; }
        body {
            color: #111827;
            font-family: 'thsarabunnew', sans-serif;
            font-size: 14pt;
            line-height: 1.12;
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
            font-size: 23pt;
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
        .col-description { width: 38%; }
        .col-qty { width: 14%; }
        .col-price { width: 24%; }
        .col-amount { width: 24%; }
        .totals-wrap {
            margin-left: auto;
            margin-top: 16px;
            width: 250px;
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
                    <div class="doc-type">ใบแจ้งหนี้</div>
                    <div class="invoice-title">INVOICE</div>
                    <div><span class="muted">เลขที่:</span> <span class="strong">{{ $invoice->number }}</span></div>
                    <div><span class="muted">วันที่:</span> {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</div>
                    <div><span class="muted">กำหนดชำระ:</span> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</div>
                    <div><span class="muted">สกุลเงิน:</span> {{ $invoice->currency ?? velocrm_currency_code() }}</div>
                </td>
            </tr>
        </table>

        <table class="meta-table">
            <tr>
                <td>
                    <div class="section-label">ลูกค้า / Bill To</div>
                    <div class="strong">{{ $invoice->customer?->name ?? '-' }}</div>
                    @if($invoice->customer?->company)
                        <div>{{ $invoice->customer->company }}</div>
                    @endif
                    @if($invoice->customer?->address)
                        <div class="muted">{{ $invoice->customer->address }}</div>
                    @endif
                    @if($invoice->tax_id)
                        <div class="muted">เลขประจำตัวผู้เสียภาษี: {{ $invoice->tax_id }}</div>
                    @endif
                    @if($invoice->branch)
                        <div class="muted">สาขา: {{ $invoice->branch }}</div>
                    @endif
                </td>
                <td class="text-right">
                    <div class="section-label">ออกโดย / From</div>
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
                    <th class="col-description">รายการ</th>
                    <th class="col-qty" style="text-align: center;">จำนวน</th>
                    <th class="col-price" style="text-align: right;">ราคาต่อหน่วย</th>
                    <th class="col-amount" style="text-align: right;">จำนวนเงิน</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                    <td style="text-align: right;">{{ $invoice->money($item->unit_price) }}</td>
                    <td style="text-align: right;">{{ $invoice->money($item->amount) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-wrap">
            <table class="totals-table">
                <tr>
                    <td>มูลค่าพื้นฐาน (Subtotal):</td>
                    <td class="text-right">{{ $invoice->money($invoice->subtotal) }}</td>
                </tr>
                <tr>
                    <td>ภาษี (Tax):</td>
                    <td class="text-right">{{ $invoice->money($invoice->tax_total) }}</td>
                </tr>
                @if($invoice->discount > 0)
                    <tr>
                        <td>ส่วนลด (Discount):</td>
                        <td class="text-right">{{ $invoice->money($invoice->discount) }}</td>
                    </tr>
                @endif
                <tr class="grand-total">
                    <td>ยอดรวมสุทธิ (Total):</td>
                    <td class="text-right">{{ $invoice->money($invoice->total) }}</td>
                </tr>
                @if($invoice->amount_paid > 0)
                    <tr>
                        <td>ชำระแล้ว (Paid):</td>
                        <td class="text-right">{{ $invoice->money($invoice->amount_paid) }}</td>
                    </tr>
                    <tr class="balance-due">
                        <td>ยอดค้างชำระ (Balance):</td>
                        <td class="text-right">{{ $invoice->money($invoice->balance_due) }}</td>
                    </tr>
                @endif
            </table>
        </div>

        @if($invoice->notes)
            <div class="notes">
                <span class="strong">หมายเหตุ / Notes:</span><br>
                {!! nl2br(e($invoice->notes)) !!}
            </div>
        @endif

        <div class="footer">
            ขอขอบคุณที่ใช้บริการ / Thank you for your business.
        </div>
    </div>
</body>
</html>
