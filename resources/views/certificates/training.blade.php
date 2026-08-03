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
            @if (! $template->backgroundImageDataUri())
                border: 10px solid {{ $template->border_color }};
            @endif
            text-align: center;
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
        /* The 300px margin-top is a fixed value, not a guess — every training
           background is now required to follow one standard layout (logos +
           "ประกาศนียบัตร" + "ฉบับนี้ให้ไว้เพื่อแสดงว่า" baked in, blank space
           starting right under that), so this always lines up with the top
           of that blank space. If the standard layout ever changes, this
           needs to be re-measured against the new reference design. */
        .name {
            font-size: 24px;
            font-weight: bold;
            text-decoration: underline;
            color: {{ $template->border_color }};
            margin: 300px 0 12px;
        }
        .course-name {
            font-size: 16px;
            font-weight: bold;
            color: {{ $template->border_color }};
            max-width: 620px;
            margin: 0 auto 10px;
        }
        .detail {
            font-size: 13px;
            color: #334155;
            max-width: 620px;
            margin: 0 auto 10px;
            line-height: 1.5;
        }
        .dates {
            font-size: 12px;
            color: #475569;
            margin: 0 auto 4px;
        }
        /* Kept tight on purpose — with 4 signatories and long subtitle/course-name
           text this section is what's most at risk of pushing onto a second
           PDF page (see test_training_certificate_stays_on_one_page_even_with_maximum_content
           in tests/Feature/CertificateTemplateManagementTest.php — rerun it after
           touching any spacing here). The 300px .name margin-top is the one
           value that must stay fixed to match the background artwork; everything
           below it can flex to make room. */
        .signatures {
            width: 100%;
            margin-top: 16px;
        }
        .signatures td {
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
        .sig-image {
            max-height: 40px;
            max-width: 140px;
            margin: 0 auto 4px;
            display: block;
        }
        .hash {
            margin-top: 14px;
            font-size: 8px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    @if ($backgroundUri = $template->backgroundImageDataUri())
        <img src="{{ $backgroundUri }}" class="background" alt="">
    @endif

    {{-- The heading ("ประกาศนียบัตร" / "ฉบับนี้ให้ไว้เพื่อแสดงว่า") is expected to
         already be part of the background artwork (a fixed, standardized
         Canva layout — see admin/CertificateTemplates.vue's mock-data
         preview) — so only the truly per-recipient content is rendered
         live here. --}}
    <p class="name">{{ $user->name }}</p>
    <p class="detail">{{ $template->subtitle }}</p>
    <p class="course-name">{{ $course->name_th }}</p>

    @if ($course->start_date && $course->end_date)
        <p class="dates">
            อบรมระหว่างวันที่ {{ $course->start_date->translatedFormat('d F Y') }} ถึง {{ $course->end_date->translatedFormat('d F Y') }}
        </p>
    @endif
    <p class="dates">ให้ไว้ ณ วันที่ {{ $issuedAt->translatedFormat('d F Y') }}</p>

    @include('certificates.partials.signatures', ['template' => $template])

    <p class="hash">CARS-HUSOC Certificate System — Verified</p>
</body>
</html>
