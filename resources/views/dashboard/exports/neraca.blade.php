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
            border: 1px solid #000;
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
        <div style="font-size: 13pt; font-weight: bold; text-decoration: underline; margin: 5px 0;">LAPORAN NERACA</div>
        <div style="font-size: 11pt; font-weight: bold;">PER {{ $period['end_date_formatted'] }}</div>
    </div>

    <table>
        <thead>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <th style="width: 70%; text-align: left;">URAIAN</th>
                <th style="width: 30%; text-align: right;">JUMLAH (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="font-bold" style="background-color: #e2e8f0;">
                <td colspan="2">ASET</td>
            </tr>
            <tr class="font-bold">
                <td style="padding-left: 20px;">ASET LANCAR</td>
                <td class="text-right">{{ number_format($assets['lancar']['total'], 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 40px;">Kas dan Setara Kas</td>
                <td class="text-right">{{ number_format($assets['lancar']['kas'], 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 40px;">Piutang Pelayanan</td>
                <td class="text-right">{{ number_format($assets['lancar']['piutang'], 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 40px;">Persediaan</td>
                <td class="text-right">{{ number_format($assets['lancar']['persediaan'], 2, ',', '.') }}</td>
            </tr>

            <tr class="font-bold">
                <td style="padding-left: 20px;">ASET TETAP</td>
                <td class="text-right">{{ number_format($assets['tetap']['total'], 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 40px;">Aset Tetap (Netto)</td>
                <td class="text-right">{{ number_format($assets['tetap']['total'], 2, ',', '.') }}</td>
            </tr>

            <tr class="font-bold" style="background-color: #e2e8f0;">
                <td>TOTAL ASET</td>
                <td class="text-right">{{ number_format($assets['grand_total'], 2, ',', '.') }}</td>
            </tr>

            <!-- Spacer Row -->
            <tr style="border: none;">
                <td colspan="2" style="border: none; height: 15px;"></td>
            </tr>

            <tr class="font-bold" style="background-color: #e2e8f0;">
                <td colspan="2">KEWAJIBAN & EKUITAS</td>
            </tr>
            <tr class="font-bold">
                <td style="padding-left: 20px;">KEWAJIBAN</td>
                <td class="text-right">{{ number_format($liabilities['total'], 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 40px;">Kewajiban Jangka Pendek</td>
                <td class="text-right">{{ number_format($liabilities['total'], 2, ',', '.') }}</td>
            </tr>

            <tr class="font-bold">
                <td style="padding-left: 20px;">EKUITAS</td>
                <td class="text-right">{{ number_format($equity['total'], 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 40px;">Ekuitas</td>
                <td class="text-right">{{ number_format($equity['total'], 2, ',', '.') }}</td>
            </tr>

            <tr class="font-bold" style="background-color: #e2e8f0;">
                <td>TOTAL KEWAJIBAN & EKUITAS</td>
                <td class="text-right">{{ number_format($liabilities['total'] + $equity['total'], 2, ',', '.') }}</td>
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
                    Kepulauan Riau, {{ Carbon::now()->isoFormat('D MMMM Y') }}<br>
                    {{ $ptKanan->jabatan }}<br><br><br><br><br>
                    <strong>{{ $ptKanan->nama }}</strong><br>
                    NIP. {{ $ptKanan->nip }}
                @endif
            </td>
        </tr>
    </table>
</body>

</html>





