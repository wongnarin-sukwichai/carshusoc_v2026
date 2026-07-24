<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 60px 70px;
            border: 10px solid {{ $template->border_color }};
            text-align: center;
        }
        .eyebrow {
            font-size: 12px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: {{ $template->border_color }};
            font-weight: bold;
        }
        .title {
            font-size: 34px;
            font-weight: bold;
            margin: 10px 0 4px;
        }
        .subtitle {
            font-size: 13px;
            color: #475569;
            max-width: 560px;
            margin: 0 auto 24px;
        }
        .statement {
            font-size: 12px;
            color: #475569;
            max-width: 620px;
            margin: 0 auto;
        }
        .name {
            font-size: 24px;
            font-weight: bold;
            text-decoration: underline;
            margin: 18px 0;
        }
        .detail {
            font-size: 13px;
            color: #334155;
            max-width: 620px;
            margin: 0 auto 40px;
            line-height: 1.6;
        }
        .signatures {
            width: 100%;
            margin-top: 30px;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            font-size: 11px;
            color: #475569;
            padding-top: 6px;
            border-top: 1px solid #cbd5e1;
        }
        .sig-name {
            font-weight: bold;
            color: #1e293b;
        }
        .hash {
            margin-top: 30px;
            font-size: 8px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <p class="eyebrow">Certificate of Achievement</p>
    <p class="title">{{ $template->title }}</p>
    <p class="subtitle">{{ $template->subtitle }}</p>

    <p class="statement">ขอมอบประกาศนียบัตรฉบับนี้ให้ไว้เพื่อแสดงว่า</p>
    <p class="name">{{ $user->name }}</p>
    <p class="detail">
        ได้ "ผ่าน" โครงการอบรมภาษา หลักสูตร {{ $course->name_th }} ({{ $course->code }})
        @if ($enrollment->graded_at)
            เมื่อวันที่ {{ $enrollment->graded_at->translatedFormat('d F Y') }}
        @endif
    </p>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-name">{{ $template->signatory1_name }}</div>
                <div>{{ $template->signatory1_title }}</div>
            </td>
            @if ($template->signatory2_name)
                <td>
                    <div class="sig-name">{{ $template->signatory2_name }}</div>
                    <div>{{ $template->signatory2_title }}</div>
                </td>
            @endif
        </tr>
    </table>

    <p class="hash">CARS-HUSOC Certificate System — Verified</p>
</body>
</html>
