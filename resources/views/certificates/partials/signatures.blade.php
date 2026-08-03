@php
    $signatories = collect([1, 2, 3, 4])->filter(fn ($n) => $template->{"signatory{$n}_name"})->values();
    $signatureColumnWidth = $signatories->isEmpty() ? 100 : 100 / $signatories->count();
@endphp
<table class="signatures">
    <tr>
        @foreach ($signatories as $n)
            <td style="width: {{ $signatureColumnWidth }}%">
                @if ($signatureUri = $template->signatureDataUri($n))
                    <img src="{{ $signatureUri }}" class="sig-image" alt="">
                @endif
                <div class="sig-name">{{ $template->{"signatory{$n}_name"} }}</div>
                <div>{{ $template->{"signatory{$n}_title"} }}</div>
            </td>
        @endforeach
    </tr>
</table>
