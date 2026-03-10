<table>
    <tr>
        <th colspan="3" style="text-align: center; font-weight: bold;">RSJKO ENGKU HAJI DAUD</th>
    </tr>
    <tr>
        <th colspan="3" style="text-align: center; font-weight: bold;">RENCANA KERJA ANGGARAN (RKA)</th>
    </tr>
    <tr>
        <th colspan="3" style="text-align: center; font-weight: bold;">TAHUN ANGGARAN {{ $tahun }}</th>
    </tr>
    <tr></tr>
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th style="border: 1px solid #000; font-weight: bold; width: 150px;">KODE REKENING</th>
            <th style="border: 1px solid #000; font-weight: bold; width: 400px;">URAIAN</th>
            <th style="border: 1px solid #000; font-weight: bold; width: 150px; text-align: right;">ANGGARAN (RP)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $item)
            <tr>
                <td style="border: 1px solid #000;">{{ $item->kode }}</td>
                <td style="border: 1px solid #000; padding-left: {{ ($item->level - 1) * 20 }}px;">
                    {{ $item->nama }}
                </td>
                <td style="border: 1px solid #000; text-align: right;">
                    {{ number_format($item->anggaran, 2, ',', '.') }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@if(isset($ptKiri) || isset($ptTengah) || isset($ptKanan))
    <br>
    <table>
        <tr>
            <td style="width: 33%; text-align: center;">
                @if($ptKiri)
                    {{ $ptKiri->jabatan }}<br><br><br><br>
                    <b>{{ $ptKiri->nama }}</b><br>
                    NIP. {{ $ptKiri->nip }}
                @endif
            </td>
            <td style="width: 33%; text-align: center;">
                @if($ptTengah)
                    {{ $ptTengah->jabatan }}<br><br><br><br>
                    <b>{{ $ptTengah->nama }}</b><br>
                    NIP. {{ $ptTengah->nip }}
                @endif
            </td>
            <td style="width: 33%; text-align: center;">
                @if($ptKanan)
                    Tanjunguban, {{ Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    {{ $ptKanan->jabatan }}<br><br><br><br>
                    <b>{{ $ptKanan->nama }}</b><br>
                    NIP. {{ $ptKanan->nip }}
                @endif
            </td>
        </tr>
    </table>
@endif





