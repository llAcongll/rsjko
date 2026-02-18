@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>

<head>
    <title>Laporan Rekonsiliasi</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 10pt;
        }

        th {
            background-color: #f2f2f2;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div style="text-align: center; margin-bottom: 20px;">
        <h3>LAPORAN REKONSILIASI</h3>
        <p>Periode: {{ Carbon::parse($start)->translatedFormat('d F Y') }} s/d
            {{ Carbon::parse($end)->translatedFormat('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Bank (Kredit)</th>
                <th>Modul Netto</th>
                <th>Selisih Harian</th>
                <th>Selisih Kumulatif</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    <td class="text-center">{{ Carbon::parse($item->tanggal)->translatedFormat('d/m/Y') }}</td>
                    <td class="text-right">Rp {{ number_format($item->bank, 2, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->pendapatan, 2, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->selisih, 2, ',', '.') }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($item->kumulatif, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>