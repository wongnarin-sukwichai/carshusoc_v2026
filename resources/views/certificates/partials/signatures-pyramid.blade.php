{{-- Exam-center signature layout: rows of 2, so 3 signatories render as
     2 on top + 1 centered below, and 4 as 2-over-2. Different from
     partials/signatures.blade.php (training's single row of up to 4) — kept
     as a separate partial rather than a parameterized shared one since the
     two centers' visual conventions genuinely diverge, not just a spacing
     tweak. --}}
@php
    $signatories = collect([1, 2, 3, 4])->filter(fn ($n) => $template->{"signatory{$n}_name"})->values();
    $rows = $signatories->chunk(2);
@endphp
@foreach ($rows as $row)
    <table class="signatures-row" @if ($row->count() === 1) style="width: 50%; margin-left: auto; margin-right: auto;" @endif>
        <tr>
            @foreach ($row as $n)
                <td style="width: {{ 100 / $row->count() }}%">
                    @if ($signatureUri = $template->signatureDataUri($n))
                        <img src="{{ $signatureUri }}" class="sig-image" alt="">
                    @endif
                    <div class="sig-name">{{ $template->{"signatory{$n}_name"} }}</div>
                    <div>{{ $template->{"signatory{$n}_title"} }}</div>
                </td>
            @endforeach
        </tr>
    </table>
@endforeach
