@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>

<head>
    <title>Laporan Piutang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }
    </style>
</head>

<body>
    <div style="text-align: center;">
        <h3>LAPORAN PIUTANG</h3>
        <p>Tahun Anggaran: {{ $tahun }}</p>
    </div>

    <table border="1">
        <thead>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <th rowspan="2" align="center">Nama Perusahaan</th>
                <th colspan="4" align="center">Saldo Awal (Tahun Lalu)</th>
                <th colspan="4" align="center">Tahun Berjalan</th>
                <th rowspan="2" align="center">Pelunasan Total</th>
                <th rowspan="2" align="center">Potongan Total</th>
                <th rowspan="2" align="center">Sisa Piutang 2025</th>
                <th rowspan="2" align="center">Saldo Akhir Piutang</th>
            </tr>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <th align="center">Piutang</th>
                <th align="center">Pelunasan</th>
                <th align="center">Potongan</th>
                <th align="center">Adm</th>
                <th align="center">Piutang</th>
                <th align="center">Pelunasan</th>
                <th align="center">Potongan</th>
                <th align="center">Adm</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    <td>{{ $item->nama_perusahaan }}</td>
                    <td align="right">{{ number_format($item->sa_piutang, 2, ',', '.') }}</td>
                    <td align="right">{{ number_format($item->sa_pelunasan, 2, ',', '.') }}</td>
                    <td align="right">{{ number_format($item->sa_potongan, 2, ',', '.') }}</td>
                    <td align="right">{{ number_format($item->sa_adm, 2, ',', '.') }}</td>
                    <td align="right">{{ number_format($item->berjalan_piutang, 2, ',', '.') }}</td>
                    <td align="right">{{ number_format($item->berjalan_pelunasan, 2, ',', '.') }}</td>
                    <td align="right">{{ number_format($item->berjalan_potongan, 2, ',', '.') }}</td>
                    <td align="right">{{ number_format($item->berjalan_adm, 2, ',', '.') }}</td>
                    <td align="right"><b>{{ number_format($item->total_pelunasan, 2, ',', '.') }}</b></td>
                    <td align="right">{{ number_format($item->total_potongan, 2, ',', '.') }}</td>
                    <td align="right" style="color: #ef4444;">{{ number_format($item->sisa_2025, 2, ',', '.') }}</td>
                    <td align="right" style="background-color: #f8fafc; font-weight: bold;">
                        {{ number_format($item->saldo_akhir, 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td align="center">GRAND TOTAL</td>
                <td align="right">{{ $totals->sa_piutang }}</td>
                <td align="right">{{ $totals->sa_pelunasan }}</td>
                <td align="right">{{ $totals->sa_potongan }}</td>
                <td align="right">{{ $totals->sa_adm }}</td>
                <td align="right">{{ $totals->berjalan_piutang }}</td>
                <td align="right">{{ $totals->berjalan_pelunasan }}</td>
                <td align="right">{{ $totals->berjalan_potongan }}</td>
                <td align="right">{{ $totals->berjalan_adm }}</td>
                <td align="right">{{ $totals->total_pelunasan }}</td>
                <td align="right">{{ $totals->total_potongan }}</td>
                <td align="right">{{ $totals->sisa_2025 }}</td>
                <td align="right">{{ $totals->saldo_akhir }}</td>
            </tr>
        </tbody>
        <table style="border: none;">
            <tr>
                <td align="center">
                    @if($ptKiri)
                        <br>
                        {{ $ptKiri->jabatan }}<br><br><br><br>
                        {{ $ptKiri->nama }}<br>
                        NIP. {{ $ptKiri->nip }}
                    @endif
                </td>
                <td></td>
                <td align="center">
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