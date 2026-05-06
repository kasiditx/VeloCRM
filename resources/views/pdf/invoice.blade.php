<!DOCTYPE html>
<html lang="th">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice - {{ $invoice->number }}</title>
    <style>
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path('fonts/THSarabunNew.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: bold;
            src: url("{{ public_path('fonts/THSarabunNew-Bold.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'THSarabunNew';
            font-style: italic;
            font-weight: normal;
            src: url("{{ public_path('fonts/THSarabunNew-Italic.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'THSarabunNew';
            font-style: italic;
            font-weight: bold;
            src: url("{{ public_path('fonts/THSarabunNew-BoldItalic.ttf') }}") format('truetype');
        }
        body {
            font-family: 'THSarabunNew';
            font-size: 16pt;
        }
        .header { text-align: center; margin-bottom: 20px; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border-bottom: 1px solid #eee; padding: 10px; text-align: left; }
        .totals { float: right; width: 300px; margin-top: 20px; }
        .totals div { display: flex; justify-content: space-between; padding: 5px 0; }
        .footer { margin-top: 50px; font-size: 12pt; text-align: center; color: #777; }
        .company-logo { max-width: 150px; float: left; }
        .invoice-info { float: right; text-align: right; }
        .clear { clear: both; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            @if(isset($logo_base64))
                <img src="{{ $logo_base64 }}" class="company-logo">
            @endif
            <div class="invoice-info">
                <h2>ใบแจ้งหนี้ / INVOICE</h2>
                <p>เลขที่: {{ $invoice->number }}</p>
                <p>วันที่: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</p>
            </div>
            <div class="clear"></div>
        </div>

        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <strong>นามลูกค้า:</strong><br>
                    {{ $invoice->customer->name }}<br>
                    {{ $invoice->customer->company }}<br>
                    {{ $invoice->customer->address }}
                </td>
                <td style="width: 50%; text-align: right;">
                    <strong>ออกโดย:</strong><br>
                    {{ $company_name }}<br>
                    @if(! empty($company_address))
                        {{ $company_address }}<br>
                    @endif
                    @if(! empty($company_url))
                        {{ $company_url }}
                    @endif
                </td>
            </tr>
        </table>

        <table class="table">
            <thead>
                <tr>
                    <th>รายการ</th>
                    <th style="text-align: center;">จำนวน</th>
                    <th style="text-align: right;">ราคาต่อหน่วย</th>
                    <th style="text-align: right;">จำนวนเงิน</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td style="text-align: center;">{{ number_format($item->quantity, 2) }}</td>
                    <td style="text-align: right;">{{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align: right;">{{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div>
                <span>มูลค่าพื้นฐาน (Subtotal):</span>
                <span>{{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            <div>
                <span>ภาษี (Tax):</span>
                <span>{{ number_format($invoice->tax_total, 2) }}</span>
            </div>
            <div style="font-weight: bold; font-size: 1.2em; border-top: 2px solid #333; margin-top: 10px; padding-top: 10px;">
                <span>ยอดรวมสุทธิ (Total):</span>
                <span>{{ number_format($invoice->total, 2) }}</span>
            </div>
            @if($invoice->amount_paid > 0)
            <div>
                <span>ชำระแล้ว (Paid):</span>
                <span>{{ number_format($invoice->amount_paid, 2) }}</span>
            </div>
            <div style="color: red;">
                <span>ยอดค้างชำระ (Balance):</span>
                <span>{{ number_format($invoice->balance_due, 2) }}</span>
            </div>
            @endif
        </div>
        <div class="clear"></div>

        <div class="footer">
            ขอขอบคุณที่ใช้บริการ / Thank you for your business.
        </div>
    </div>
</body>
</html>
