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
            font-family: Arial, sans-serif;
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
        <p>Tahun Anggaran: {{ session('tahun_anggaran') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Bank (Kredit)</th>
                <th>Modul Netto</th>
                <th>Selisih Harian</th>
                <th>Selisih Kumulatif</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    <td class="text-center">{{ $item->tanggal }}</td>
                    <td class="text-right">Rp {{ number_format($item->bank, 2, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->pendapatan, 2, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->selisih, 2, ',', '.') }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($item->kumulatif, 2, ',', '.') }}</td>
                </tr>
            @endforeach
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