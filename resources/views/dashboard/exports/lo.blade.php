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

        .bg-gray {
            background-color: #f2f2f2;
        }

        .bg-header {
            background-color: #e2e8f0;
        }
    </style>
</head>

<body>
    <div class="title-container">
        <div style="font-size: 11pt; text-transform: uppercase;">RSJKO ENGKU HAJI DAUD</div>
        <div style="font-size: 13pt; font-weight: bold; text-decoration: underline; margin: 5px 0;">LAPORAN OPERASIONAL
            (LO)</div>
        <div style="font-size: 11pt; font-weight: bold;">PERIODE {{ $period['start_formatted'] }} s.d
            {{ $period['end_formatted'] }}</div>
    </div>

    <table>
        <thead>
            <tr class="bg-gray font-bold">
                <th style="width: 70%; text-align: left;">URAIAN</th>
                <th style="width: 30%; text-align: right;">JUMLAH (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="font-bold bg-header">
                <td colspan="2">PENDAPATAN DARI KEGIATAN OPERASIONAL</td>
            </tr>
            @foreach($revenue['items'] as $item)
                <tr>
                    <td style="padding-left: 20px;">{{ $item['label'] }}</td>
                    <td class="text-right">{{ number_format($item['value'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="font-bold bg-header">
                <td>JUMLAH PENDAPATAN OPERASIONAL</td>
                <td class="text-right">{{ number_format($revenue['total'], 2, ',', '.') }}</td>
            </tr>

            <tr style="border: none;">
                <td colspan="2" style="border: none; height: 15px;"></td>
            </tr>

            <tr class="font-bold bg-header">
                <td colspan="2">BEBAN OPERASIONAL</td>
            </tr>
            @foreach($expenses['items'] as $item)
                <tr>
                    <td style="padding-left: 20px;">{{ $item['label'] }}</td>
                    <td class="text-right">{{ number_format($item['value'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="font-bold bg-header">
                <td>JUMLAH BEBAN OPERASIONAL</td>
                <td class="text-right">{{ number_format($expenses['total'], 2, ',', '.') }}</td>
            </tr>

            <tr style="border: none;">
                <td colspan="2" style="border: none; height: 15px;"></td>
            </tr>

            <tr class="font-bold" style="background-color: {{ $surplus_defisit >= 0 ? '#dcfce7' : '#fee2e2' }};">
                <td>{{ $surplus_defisit >= 0 ? 'SURPLUS' : 'DEFISIT' }} OPERASIONAL (LO)</td>
                <td class="text-right">{{ number_format($surplus_defisit, 2, ',', '.') }}</td>
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





