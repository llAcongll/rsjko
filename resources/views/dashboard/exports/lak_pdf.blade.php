@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>

<head>
    <title>Laporan Arus Kas</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h3 {
            margin: 0;
            text-transform: uppercase;
            font-size: 14pt;
        }

        .header p {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
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

        .indent {
            padding-left: 25px;
        }

        .total-row {
            background-color: #f9fafb;
            font-weight: bold;
        }

        .footer {
            margin-top: 50px;
        }

        .footer table {
            border: none;
        }

        .footer td {
            border: none;
            text-align: center;
        }
    </style>
</head>

<body>
    <div style="text-align:center; margin-bottom: 20px;">
        <div style="font-size: 11pt; text-transform: uppercase;">RSJKO ENGKU HAJI DAUD</div>
        <div style="font-size: 13pt; font-weight: bold; text-decoration: underline; margin: 4px 0;">LAPORAN ARUS KAS
            (LAK)</div>
        <div style="font-size: 11pt; font-weight: bold;">PERIODE
            {{ Carbon::parse($period['start'])->translatedFormat('d F Y') }} s.d
            {{ Carbon::parse($period['end'])->translatedFormat('d F Y') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 70%;">URAIAN</th>
                <th style="width: 30%;">JUMLAH (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sections = [
                    'OPERASI' => 'A. ARUS KAS DARI AKTIVITAS OPERASI',
                    'INVESTASI' => 'B. ARUS KAS DARI AKTIVITAS INVESTASI',
                    'PENDANAAN' => 'C. ARUS KAS DARI AKTIVITAS PENDANAAN',
                    'UNMAPPED' => 'D. TRANSAKSI BELUM TERKLASIFIKASI'
                ];
            @endphp

            @foreach($sections as $key => $label)
                @php $cat = $categories[$key]; @endphp
                <tr class="font-bold" style="background-color: #f1f5f9;">
                    <td colspan="2">{{ $label }}</td>
                </tr>

                @foreach($cat['in'] as $item)
                    <tr>
                        <td class="indent">Arus Kas Masuk: {{ $item->uraian }}</td>
                        <td class="text-right">{{ number_format($item->total, 2, ',', '.') }}</td>
                    </tr>
                @endforeach

                @foreach($cat['out'] as $item)
                    <tr>
                        <td class="indent">Arus Kas Keluar: {{ $item->uraian }}</td>
                        <td class="text-right">({{ number_format($item->total, 2, ',', '.') }})</td>
                    </tr>
                @endforeach

                <tr class="total-row">
                    <td>Arus Kas Bersih dari Aktivitas {{ ucfirst(strtolower($key)) }}</td>
                    <td class="text-right" style="border-top: 2px solid #000;">
                        {{ number_format($cat['total_in'] - $cat['total_out'], 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach

            <tr style="height: 15px;">
                <td colspan="2" style="border: none;"></td>
            </tr>
            <tr class="font-bold">
                <td>KENAIKAN / (PENURUNAN) KAS BERSIH</td>
                <td class="text-right">{{ number_format($kenaikan, 2, ',', '.') }}</td>
            </tr>
            <tr class="font-bold">
                <td>SALDO KAS AWAL PERIODE</td>
                <td class="text-right">{{ number_format($saldo_awal, 2, ',', '.') }}</td>
            </tr>
            <tr class="font-bold" style="background-color: #eff6ff; font-size: 11pt;">
                <td>SALDO KAS AKHIR PERIODE</td>
                <td class="text-right">{{ number_format($saldo_akhir, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <table>
            <tr>
                <td style="width: 33%;">
                    @if($ptKiri)
                        {{ $ptKiri->jabatan }}<br><br><br><br><br>
                        <strong>{{ $ptKiri->nama }}</strong><br>
                        NIP. {{ $ptKiri->nip }}
                    @endif
                </td>
                <td style="width: 33%;">
                    @if($ptTengah)
                        {{ $ptTengah->jabatan }}<br><br><br><br><br>
                        <strong>{{ $ptTengah->nama }}</strong><br>
                        NIP. {{ $ptTengah->nip }}
                    @endif
                </td>
                <td style="width: 33%;">
                    @if($ptKanan)
                        Tanjungpinang, {{ Carbon::now()->isoFormat('D MMMM Y') }}<br>
                        {{ $ptKanan->jabatan }}<br><br><br><br><br>
                        <strong>{{ $ptKanan->nama }}</strong><br>
                        NIP. {{ $ptKanan->nip }}
                    @endif
                </td>
            </tr>
        </table>
    </div>
</body>

</html>





