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
            padding: 5px;
        }

        .title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
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
            {{ Carbon::parse($period['end'])->translatedFormat('d F Y') }}
        </div>
    </div>

    <table>
        <tr>
            <td colspan="2" style="border: none; height: 10px;"></td>
        </tr>
        <tr class="font-bold" style="background-color: #f2f2f2;">
            <td style="width: 70%;">URAIAN</td>
            <td style="width: 30%; text-align: right;">JUMLAH (Rp)</td>
        </tr>

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
            <tr class="font-bold">
                <td colspan="2">{{ $label }}</td>
            </tr>

            @foreach($cat['in'] as $item)
                <tr>
                    <td style="padding-left: 20px;">Arus Kas Masuk: {{ $item->uraian }}</td>
                    <td class="text-right">{{ number_format($item->total, 2, ',', '.') }}</td>
                </tr>
            @endforeach

            @foreach($cat['out'] as $item)
                <tr>
                    <td style="padding-left: 20px;">Arus Kas Keluar: {{ $item->uraian }}</td>
                    <td class="text-right">({{ number_format($item->total, 2, ',', '.') }})</td>
                </tr>
            @endforeach

            <tr class="font-bold">
                <td>Arus Kas Bersih dari Aktivitas {{ ucfirst(strtolower($key)) }}</td>
                <td class="text-right">{{ number_format($cat['total_in'] - $cat['total_out'], 2, ',', '.') }}</td>
            </tr>
        @endforeach

        <tr>
            <td colspan="2"></td>
        </tr>
        <tr class="font-bold">
            <td>KENAIKAN / (PENURUNAN) KAS BERSIH</td>
            <td class="text-right">{{ number_format($kenaikan, 2, ',', '.') }}</td>
        </tr>
        <tr class="font-bold">
            <td>SALDO KAS AWAL PERIODE</td>
            <td class="text-right">{{ number_format($saldo_awal, 2, ',', '.') }}</td>
        </tr>
        <tr class="font-bold" style="background-color: #e2e8f0;">
            <td>SALDO KAS AKHIR PERIODE</td>
            <td class="text-right">{{ number_format($saldo_akhir, 2, ',', '.') }}</td>
        </tr>
    </table>

    <br><br>
    <table>
        <tr>
            <td style="border:none; width:33%; text-align:center;">
                @if($ptKiri)
                    {{ $ptKiri->jabatan }}<br><br><br><br>
                    <strong>{{ $ptKiri->nama }}</strong><br>
                    NIP. {{ $ptKiri->nip }}
                @endif
            </td>
            <td style="border:none; width:33%; text-align:center;">
                @if($ptTengah)
                    {{ $ptTengah->jabatan }}<br><br><br><br>
                    <strong>{{ $ptTengah->nama }}</strong><br>
                    NIP. {{ $ptTengah->nip }}
                @endif
            </td>
            <td style="border:none; width:33%; text-align:center;">
                @if($ptKanan)
                    Kepulauan Riau, {{ Carbon::now()->isoFormat('D MMMM Y') }}<br>
                    {{ $ptKanan->jabatan }}<br><br><br><br>
                    <strong>{{ $ptKanan->nama }}</strong><br>
                    NIP. {{ $ptKanan->nip }}
                @endif
            </td>
        </tr>
    </table>
</body>

</html>





