<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        .title {
            font-family: Arial, sans-serif;
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
        }

        .subtitle {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            text-align: center;
        }

        .table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
            font-size: 10pt;
        }

        .table th {
            background-color: #f2f2f2;
            border: 1px solid #000000;
            padding: 5px;
            font-weight: bold;
            text-align: center;
        }

        .table td {
            border: 1px solid #000000;
            padding: 5px;
            vertical-align: top;
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
    <table>
        <tr>
            <td colspan="7" class="title">PEMERINTAH PROVINSI KEPULAUAN RIAU</td>
        </tr>
        <tr>
            <td colspan="7" class="title">RUMAH SAKIT JIWA DAN KETERGANTUNGAN OBAT ENGKU HAJI DAUD</td>
        </tr>
        <tr>
            <td colspan="7" class="subtitle">Jalan Indun Suri - Simpang Busung Nomor. 1 Tanjung Uban Kode Pos 29152</td>
        </tr>
        <tr>
            <td colspan="7" class="subtitle">Telepon ( 0771 ) 482655, 482796 Faksimile. ( 0771 ) 482795</td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <tr>
            <td colspan="7" class="title" style="text-decoration: underline;">LAPORAN REKENING KORAN</td>
        </tr>
        <tr>
            <td colspan="7" class="subtitle">Bank: {{ $bank }}</td>
        </tr>
        @if($start && $end)
            <tr>
                <td colspan="7" class="subtitle">Periode: {{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }} s/d
                    {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}
                </td>
            </tr>
        @endif
        <tr>
            <td></td>
        </tr>
    </table>

    <table class="table">
        <tr>
            <td colspan="4" style="border: none; font-weight: bold; font-size: 11pt;">SALDO AWAL KAS</td>
            <td colspan="3" style="border: none; font-weight: bold; font-size: 11pt; text-align: right;">Rp
                {{ number_format($saldoAwal, 2, ',', '.') }}
            </td>
        </tr>
        <tr>
            <td colspan="7" style="border: none; height: 10px;"></td>
        </tr>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Uraian / Keterangan</th>
                <th>Bank</th>
                <th>Penerimaan (D)</th>
                <th>Pengeluaran (K)</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($items as $item)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                    <td>{{ $item->keterangan }}</td>
                    <td class="text-center">{{ $item->bank }}</td>
                    <td class="text-right">{{ $item->cd === 'C' ? number_format($item->jumlah, 2, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $item->cd === 'D' ? number_format($item->jumlah, 2, ',', '.') : '-' }}</td>
                    <td class="text-right" style="font-weight: bold;">{{ number_format($item->saldo_running, 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%; border: none; margin-top: 30px;">
        <tr>
            <td colspan="2" style="text-align: center; vertical-align: top;">
                @if($ptKiri)
                    <p style="margin: 0; min-height: 1.25em;">&nbsp;</p>
                    <p style="margin: 0;">{{ $ptKiri->jabatan }}</p>
                    <div style="height: 50px;"></div>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">({{ $ptKiri->nama }})</p>
                    <p style="margin: 0;">NIP. {{ $ptKiri->nip }}</p>
                @endif
            </td>
            <td colspan="3" style="text-align: center; vertical-align: top;">
                @if($ptTengah)
                    <p style="margin: 0; min-height: 1.25em;">&nbsp;</p>
                    <p style="margin: 0;">{{ $ptTengah->jabatan }}</p>
                    <div style="height: 50px;"></div>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">({{ $ptTengah->nama }})</p>
                    <p style="margin: 0;">NIP. {{ $ptTengah->nip }}</p>
                @endif
            </td>
            <td colspan="2" style="text-align: center; vertical-align: top;">
                <p style="margin: 0;">Tanjung Uban, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p style="margin: 0;">{{ $ptKanan ? $ptKanan->jabatan : 'Bendahara Penerimaan' }}</p>
                <div style="height: 50px;"></div>
                <p style="margin: 0; font-weight: bold; text-decoration: underline;">
                    {{ $ptKanan ? '(' . $ptKanan->nama . ')' : '( ......................................... )' }}
                </p>
                <p style="margin: 0;">NIP. {{ $ptKanan ? $ptKanan->nip : '.........................................' }}
                </p>
            </td>
        </tr>
    </table>
</body>

</html>





