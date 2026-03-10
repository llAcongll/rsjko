@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>

<head>
    <style>
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 8px;
            font-family: sans-serif;
            font-size: 10pt;
        }

        .title-container {
            text-align: center;
            margin-bottom: 20px;
            font-family: sans-serif;
        }
    </style>
</head>

<body>
    <div class="title-container">
        <div style="font-size: 11pt; text-transform: uppercase;">RSJKO ENGKU HAJI DAUD</div>
        <div style="font-size: 13pt; font-weight: bold; text-decoration: underline; margin: 5px 0;">LAPORAN PERUBAHAN
            EKUITAS</div>
        <div style="font-size: 11pt; font-weight: bold;">PER {{ strtoupper($period['end_date_formatted']) }}</div>
    </div>

    <table>
        <thead>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <th style="width: 70%; text-align: left;">URAIAN</th>
                <th style="width: 30%; text-align: right;">JUMLAH (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Ekuitas Awal</td>
                <td class="text-right">{{ number_format($ekuitas_awal, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Surplus / Defisit LO</td>
                <td class="text-right">{{ number_format($surplus_defisit_lo, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Koreksi Ekuitas</td>
                <td class="text-right">{{ number_format($koreksi, 2, ',', '.') }}</td>
            </tr>
            <tr class="font-bold" style="background-color: #e2e8f0;">
                <td>EKUITAS AKHIR</td>
                <td class="text-right">{{ number_format($ekuitas_akhir, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <br><br>
    <table style="border: none;">
        <tr>
            <td style="border:none; width:33%; text-align:center;">
                @if($ptKiri)
                    {{ $ptKiri->jabatan }}<br><br><br><br><br>
                    <strong>{{ $ptKiri->nama }}</strong><br>
                    NIP. {{ $ptKiri->nip }}
                @endif
            </td>
            <td style="border:none; width:33%; text-align:center;">
                @if($ptTengah)
                    {{ $ptTengah->jabatan }}<br><br><br><br><br>
                    <strong>{{ $ptTengah->nama }}</strong><br>
                    NIP. {{ $ptTengah->nip }}
                @endif
            </td>
            <td style="border:none; width:33%; text-align:center;">
                @if($ptKanan)
                    Kepulauan Riau, {{ Carbon::now()->locale('id')->translatedFormat('d F Y') }}<br>
                    {{ $ptKanan->jabatan }}<br><br><br><br><br>
                    <strong>{{ $ptKanan->nama }}</strong><br>
                    NIP. {{ $ptKanan->nip }}
                @endif
            </td>
        </tr>
    </table>
</body>

</html>





