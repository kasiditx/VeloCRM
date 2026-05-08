<!DOCTYPE html>
<html lang="th">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Proposal - {{ $proposal->number }}</title>
    @php
        $regularFont = storage_path('fonts/THSarabunNew.ttf');
        $boldFont = storage_path('fonts/THSarabunNew-Bold.ttf');
        $regularFontUrl = file_exists($regularFont) ? 'file://' . $regularFont : null;
        $boldFontUrl = file_exists($boldFont) ? 'file://' . $boldFont : null;
    @endphp
    <style>
        @if($regularFontUrl)
            @font-face {
                font-family: 'THSarabunNew';
                font-style: normal;
                font-weight: normal;
                src: url("{{ $regularFontUrl }}") format('truetype');
            }
        @endif
        @if($boldFontUrl)
            @font-face {
                font-family: 'THSarabunNew';
                font-style: normal;
                font-weight: bold;
                src: url("{{ $boldFontUrl }}") format('truetype');
            }
        @endif
        body {
            font-family: 'THSarabunNew';
            font-size: 16pt;
            line-height: 1.4;
        }
        .header { text-align: center; margin-bottom: 40px; }
        .content { margin-bottom: 40px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 12pt; color: #777; }
        .signature-section { margin-top: 60px; display: table; width: 100%; }
        .signature { display: table-cell; width: 50%; text-align: center; border-top: 1px solid #333; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ข้อเสนอโครงการ / PROJECT PROPOSAL</h1>
        <p><strong>เลขที่:</strong> {{ $proposal->number }} | <strong>วันที่:</strong> {{ now()->format('d/m/Y') }}</p>
    </div>

    <div class="content">
        <p><strong>เรียน คุณ/บริษัท:</strong> {{ $proposal->customer ? $proposal->customer->name : $proposal->lead->name }}</p>
        <p><strong>เรื่อง:</strong> {{ $proposal->subject }}</p>

        <div style="margin-top: 20px;">
            {!! nl2br(e($proposal->content)) !!}
        </div>

        <p style="margin-top: 30px; font-weight: bold; font-size: 1.2em;">
            ยอดรวมงบประมาณที่เสนอ: {{ number_format($proposal->total, 2) }} บาท
        </p>
    </div>

    <div class="signature-section">
        <div class="signature" style="margin-right: 20px;">
            <p>ลงชื่อ ผู้เสนอโครงการ</p>
            <br><br><br>
            <p>( ................................................. )</p>
        </div>
        <div class="signature">
            <p>ลงชื่อ ผู้รับข้อเสนอ (ลูกค้า)</p>
            <br><br><br>
            <p>( ................................................. )</p>
        </div>
    </div>

    <div class="footer">
        {{ $company_name }}
        @if(! empty($company_address))
            - {{ $company_address }}
        @endif
        @if(! empty($company_url))
            - {{ $company_url }}
        @endif
    </div>
</body>
</html>
