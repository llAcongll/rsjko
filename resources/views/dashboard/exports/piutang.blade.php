@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>

<head>
    <title>Laporan Piutang</title>
</head>

<body>
    <div style="text-align: center;">
        <h3>LAPORAN PIUTANG</h3>
        <p>Periode: {{ Carbon::parse($start)->translatedFormat('d F Y') }} s/d
            {{ Carbon::parse($end)->translatedFormat('d F Y') }}</p>
    </div>

    <table border="1">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th>Nama Perusahaan</th>
                <th>Jumlah Piutang</th>
                <th>Potongan</th>
                <th>Adm Bank</th>
                <th>Total Dibayar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    <td>{{ $item->nama_perusahaan }}</td>
                    <td align="right">Rp {{ number_format($item->total_piutang, 2, ',', '.') }}</td>
                    <td align="right">Rp {{ number_format($item->total_potongan, 2, ',', '.') }}</td>
                    <td align="right">Rp {{ number_format($item->total_adm_bank, 2, ',', '.') }}</td>
                    <td align="right"><b>Rp {{ number_format($item->total_dibayar, 2, ',', '.') }}</b></td>
                </tr>
            @endforeach
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td align="center">GRAND TOTAL</td>
                <td align="right">Rp {{ number_format($totals->piutang, 2, ',', '.') }}</td>
                <td align="right">Rp {{ number_format($totals->potongan, 2, ',', '.') }}</td>
                <td align="right">Rp {{ number_format($totals->adm_bank, 2, ',', '.') }}</td>
                <td align="right">Rp {{ number_format($totals->dibayar, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>