<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 50px 70px;
            border: 10px solid {{ $template->border_color }};
            color: #1e293b;
        }
        .header { text-align: center; margin-bottom: 20px; }
        .eyebrow {
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: {{ $template->border_color }};
            font-weight: bold;
        }
        .title { font-size: 26px; font-weight: bold; margin: 6px 0 2px; }
        .subtitle { font-size: 12px; color: #64748b; }
        .meta { font-size: 12px; margin: 20px 0 4px; }
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
        .signatures {
            width: 100%;
            margin-top: 50px;
        }
        .signatures td {
            width: 33.33%;
            text-align: center;
            font-size: 10px;
            color: #475569;
            padding-top: 6px;
            border-top: 1px solid #cbd5e1;
        }
        .sig-name { font-weight: bold; color: #1e293b; }
        .hash { margin-top: 30px; font-size: 8px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <p class="eyebrow">Official Test Certificate</p>
        <p class="title">{{ $template->title }}</p>
        <p class="subtitle">{{ $template->subtitle }}</p>
    </div>

    <p class="meta">ชื่อ-สกุล: <strong>{{ $user->name }}</strong></p>
    <p class="meta">สถานที่สอบ: <strong>{{ $exam->location ?? '-' }}</strong></p>
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
            @if ($template->signatory3_name)
                <td>
                    <div class="sig-name">{{ $template->signatory3_name }}</div>
                    <div>{{ $template->signatory3_title }}</div>
                </td>
            @endif
        </tr>
    </table>

    <p class="hash">CARS-HUSOC Certificate System — Verified</p>
</body>
</html>
