@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Kerjasama / MOU</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { border-collapse: collapse; width: 100%; }
    </style>
</head>
<body>
    <div style="text-align: center;">
        <h3>LAPORAN KERJASAMA / MOU</h3>
        <p>Periode: {{ Carbon::parse($start)->translatedFormat('d F Y') }} s/d {{ Carbon::parse($end)->translatedFormat('d F Y') }}</p>
    </div>

    <table border="1">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th>Nama MOU / Instansi</th>
                <th>Trans</th>
                <th>Jasa RS</th>
                <th>Jasa Pelayanan</th>
                <th>Potongan</th>
                <th>Adm Bank</th>
                <th>Total Netto</th>
            </tr>
        </thead>
        <tbody>
            @php $tT=0; $tR=0; $tP=0; $tPot=0; $tA=0; $tNet=0; @endphp
            @foreach($data as $item)
                @php 
                    $tT += $item->count; $tR += $item->rs; $tP += $item->pelayanan;
                    $tPot += $item->potongan; $tA += $item->adm_bank; $tNet += $item->total;
                @endphp
                <tr>
                    <td>{{ $item->nama_mou }}</td>
                    <td align="center">{{ $item->count }}</td>
                    <td align="right">Rp {{ number_format($item->rs, 2, ',', '.') }}</td>
                    <td align="right">Rp {{ number_format($item->pelayanan, 2, ',', '.') }}</td>
                    <td align="right">Rp {{ number_format($item->potongan, 2, ',', '.') }}</td>
                    <td align="right">Rp {{ number_format($item->adm_bank, 2, ',', '.') }}</td>
                    <td align="right"><b>Rp {{ number_format($item->total, 2, ',', '.') }}</b></td>
                </tr>
            @endforeach
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td align="center">TOTAL</td>
                <td align="center">{{ $tT }}</td>
                <td align="right">Rp {{ number_format($tR, 2, ',', '.') }}</td>
                <td align="right">Rp {{ number_format($tP, 2, ',', '.') }}</td>
                <td align="right">Rp {{ number_format($tPot, 2, ',', '.') }}</td>
                <td align="right">Rp {{ number_format($tA, 2, ',', '.') }}</td>
                <td align="right">Rp {{ number_format($tNet, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    <table style="border: none;">
        <tr>
            <td colspan="2" align="center">
                @if($ptKiri)
                    <br>
                    {{ $ptKiri->jabatan }}<br><br><br><br>
                    {{ $ptKiri->nama }}<br>
                    NIP. {{ $ptKiri->nip }}
                @endif
            </td>
            <td></td>
            <td colspan="2" align="center">
                @if($ptTengah)
                    <br>
                    {{ $ptTengah->jabatan }}<br><br><br><br>
                    {{ $ptTengah->nama }}<br>
                    NIP. {{ $ptTengah->nip }}
                @endif
            </td>
            <td></td>
            <td align="center">
                Tanjung Uban, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                @if($ptKanan)
                    {{ $ptKanan->jabatan }}<br><br><br><br>
                    {{ $ptKanan->nama }}<br>
                    NIP. {{ $ptKanan->nip }}
                @else
                    &nbsp;<br>
                    &nbsp;<br><br><br><br>
                    ...................................<br>
                    NIP. ...................................
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
