<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            /* Portrait background is a fixed, standardized layout too (logo
               top-left + decorative corner only) — top padding is calibrated
               to clear that logo area, same idea as training.blade.php's
               .name margin-top. Adjust here if the reference design changes. */
            padding: 130px 60px 50px;
            @if (! $template->backgroundImageDataUri())
                border: 10px solid {{ $template->border_color }};
            @endif
            color: #1e293b;
            position: relative;
        }
        .background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            object-fit: cover;
        }
        .header { text-align: center; margin-bottom: 18px; margin-top: 130px;}
        .title { font-size: 20px; font-weight: bold; margin: 0 0 2px; color: {{ $template->border_color }}; }
        .subtitle { font-size: 12px; color: #64748b; }
        .meta { font-size: 12px; margin: 4px 0; }
        .meta strong { color: #0f172a; }
        table.scores {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            font-size: 12px;
        }
        table.scores th {
            background: {{ $template->border_color }};
            color: #ffffff;
            padding: 8px 10px;
            text-align: left;
        }
        table.scores td {
            padding: 7px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        table.scores tr.total td {
            font-weight: bold;
            background: #f8fafc;
        }
        .signatures-row {
            width: 100%;
            margin-top: 36px;
        }
        .signatures-row + .signatures-row {
            margin-top: 14px;
        }
        .signatures-row td {
            text-align: center;
            font-size: 10px;
            color: #475569;
            padding-top: 6px;
            border-top: 1px solid #cbd5e1;
        }
        .sig-name { font-weight: bold; color: #1e293b; }
        .sig-image {
            max-height: 36px;
            max-width: 120px;
            margin: 0 auto 4px;
            display: block;
        }
        .hash { margin-top: 20px; font-size: 8px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    @if ($backgroundUri = $template->backgroundImageDataUri())
        <img src="{{ $backgroundUri }}" class="background" alt="">
    @endif
    <div class="header">
        <p class="title">{{ $template->title }}</p>
        <p class="subtitle">{{ $template->subtitle }}</p>
    </div>

    <p class="meta">ชื่อ-สกุล: <strong>{{ $user->name }}</strong></p>
    <p class="meta">สถานที่สอบ: <strong>{{ $exam->location ?? '-' }}</strong></p>
    @if ($registration->room)
        <p class="meta">ห้องสอบ: <strong>{{ $registration->room }}</strong></p>
    @endif
    @if ($registration->seat_number)
        <p class="meta">เลขที่นั่งสอบ: <strong>{{ $registration->seat_number }}</strong></p>
    @endif
    <p class="meta">วันที่สอบ: <strong>{{ $exam->exam_date->translatedFormat('d F Y') }}</strong></p>

    <table class="scores">
        <thead>
            <tr><th>ผลการทดสอบ</th><th>คะแนน</th></tr>
        </thead>
        <tbody>
            <tr><td>Listening (25)</td><td>{{ $registration->listening_score }}</td></tr>
            <tr><td>Reading (25)</td><td>{{ $registration->reading_score }}</td></tr>
            <tr><td>Conversations (25)</td><td>{{ $registration->conversation_score }}</td></tr>
            <tr><td>Grammar (25)</td><td>{{ $registration->grammar_score }}</td></tr>
            <tr class="total"><td>Total (100)</td><td>{{ $registration->total_score }}</td></tr>
            <tr class="total"><td>CEFR</td><td>{{ $registration->cefr_level ?? '-' }}</td></tr>
        </tbody>
    </table>

    @include('certificates.partials.signatures-pyramid', ['template' => $template])

    <p class="hash">CARS-HUSOC Certificate System — Verified</p>
</body>
</html>
