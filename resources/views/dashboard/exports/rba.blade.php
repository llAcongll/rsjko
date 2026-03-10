<table>
    <tr>
        <th colspan="2" style="text-align: center; font-weight: bold;">RSJKO ENGKU HAJI DAUD</th>
    </tr>
    <tr>
        <th colspan="2" style="text-align: center; font-weight: bold;">RENCANA BISNIS ANGGARAN (RBA) BLUD</th>
    </tr>
    <tr>
        <th colspan="2" style="text-align: center; font-weight: bold;">TAHUN ANGGARAN {{ $tahun }}</th>
    </tr>
    <tr></tr>
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th style="border: 1px solid #000; font-weight: bold; width: 400px;">URAIAN</th>
            <th style="border: 1px solid #000; font-weight: bold; width: 200px; text-align: right;">JUMLAH (RP)</th>
        </tr>
    </thead>
    <tbody>
        <tr style="background-color: #f1f5f9; font-weight: bold;">
            <td style="border: 1px solid #000;">PENDAPATAN BLUD</td>
            <td style="border: 1px solid #000; text-align: right;">
                {{ number_format($summary['pendapatan'], 2, ',', '.') }}</td>
        </tr>
        @foreach($breakdown->where('category', 'PENDAPATAN') as $item)
            <tr>
                <td style="border: 1px solid #000; padding-left: {{ $item->level * 20 }}px;">{{ $item->nama }}</td>
                <td style="border: 1px solid #000; text-align: right;">{{ number_format($item->anggaran, 2, ',', '.') }}
                </td>
            </tr>
        @endforeach
        <tr style="background-color: #f1f5f9; font-weight: bold;">
            <td style="border: 1px solid #000;">BELANJA BLUD</td>
            <td style="border: 1px solid #000; text-align: right;">{{ number_format($summary['belanja'], 2, ',', '.') }}
            </td>
        </tr>
        @foreach($breakdown->where('category', 'PENGELUARAN') as $item)
            <tr>
                <td style="border: 1px solid #000; padding-left: {{ $item->level * 20 }}px;">{{ $item->nama }}</td>
                <td style="border: 1px solid #000; text-align: right;">{{ number_format($item->anggaran, 2, ',', '.') }}
                </td>
            </tr>
        @endforeach
        <tr style="background-color: #e2e8f0; font-weight: bold;">
            <td style="border: 1px solid #000;">SURPLUS / (DEFISIT)</td>
            <td style="border: 1px solid #000; text-align: right;">
                {{ number_format($summary['surplus_defisit'], 2, ',', '.') }}</td>
        </tr>
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





