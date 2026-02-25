<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        .table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
            font-size: 10pt;
        }

        .table th {
            background-color: #f2f2f2;
            border: 1px solid #000;
            padding: 5px;
        }

        .table td {
            border: 1px solid #000;
            padding: 5px;
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
    <table class="table" style="border: none; margin-bottom: 20px;">
        <tr>
            <td colspan="11" class="text-center" style="border: none; font-size: 14pt;">PEMERINTAH PROVINSI KEPULAUAN
                RIAU</td>
        </tr>
        <tr>
            <td colspan="11" class="text-center" style="border: none; font-size: 13pt; font-weight: bold;">RUMAH SAKIT
                JIWA DAN KETERGANTUNGAN OBAT ENGKU HAJI DAUD</td>
        </tr>
        <tr>
            <td colspan="11" style="border-bottom: 2px solid #000; height: 10px;"></td>
        </tr>
    </table>

    <div style="text-align: center; font-size: 14pt; font-weight: bold; margin-top: 20px;">BUKU KAS UMUM</div>
    <div style="text-align: center; font-size: 11pt; margin-bottom: 20px;">Periode: {{ $period }}</div>

    <table class="table">
        <thead>
            <tr>
                <th width="30">No</th>
                <th width="80">Tanggal</th>
                <th width="120">No Bukti</th>
                <th width="200">Uraian</th>
                <th width="100">Kode Rek</th>
                <th width="120">Transfer Penerimaan</th>
                <th width="120">Pengajuan SP2D</th>
                <th width="120">Realisasi</th>
                <th width="120">Saldo Dana</th>
                <th width="120">Saldo Rekening Koran</th>
                <th width="130">Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">-</td>
                <td class="text-center">-</td>
                <td class="text-center">-</td>
                <td class="font-bold">SALDO AWAL</td>
                <td class="text-center">-</td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right">{{ number_format($opening_balance - $opening_bank, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($opening_bank, 2, ',', '.') }}</td>
                <td class="text-right font-bold">{{ number_format($opening_balance, 2, ',', '.') }}</td>
            </tr>
            @foreach($data as $i => $item)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item->date)->translatedFormat('d/m/Y') }}</td>
                    <td class="text-center">{{ $item->no_bukti ?: '-' }}</td>
                    <td>{{ $item->uraian ?: '-' }}</td>
                    <td class="text-center">{{ $item->kode_rekening ?: '-' }}</td>
                    <td class="text-right">
                        {{ $item->transfer_penerimaan > 0 ? number_format($item->transfer_penerimaan, 2, ',', '.') : '-' }}
                    </td>
                    <td class="text-right">
                        {{ $item->sp2d_penerimaan > 0 ? number_format($item->sp2d_penerimaan, 2, ',', '.') : '-' }}
                    </td>
                    <td class="text-right">{{ $item->realisasi > 0 ? number_format($item->realisasi, 2, ',', '.') : '-' }}
                    </td>
                    <td class="text-right">{{ number_format($item->saldo_tunai, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->saldo_bank, 2, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($item->saldo_akhir, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="5" class="text-center">TOTAL MUTASI & SALDO AKHIR</td>
                <td class="text-right">{{ number_format($summary['total_debit_transfer'] ?? 0, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($summary['total_debit_sp2d'] ?? 0, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($summary['total_credit_realisasi'] ?? 0, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($summary['final_tunai'] ?? 0, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($summary['final_bank'] ?? 0, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($summary['final_balance'] ?? 0, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%; border: none; margin-top: 50px;">
        <tr>
            <td colspan="2" align="center" style="border: none;">
                @if($ptKiri)
                    <br>{{ $ptKiri->jabatan }}<br><br><br><br>
                    {{ $ptKiri->nama }}<br>NIP. {{ $ptKiri->nip }}
                @endif
            </td>
            <td colspan="3" align="center" style="border: none;">
                @if($ptTengah)
                    <br>{{ $ptTengah->jabatan }}<br><br><br><br>
                    {{ $ptTengah->nama }}<br>NIP. {{ $ptTengah->nip }}
                @endif
            </td>
            <td colspan="2" align="center" style="border: none;">
                Tanjung Uban, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                @if($ptKanan)
                    {{ $ptKanan->jabatan }}<br><br><br><br>
                    {{ $ptKanan->nama }}<br>NIP. {{ $ptKanan->nip }}
                @else
                    &nbsp;<br><br><br><br>
                    ...................................<br>NIP. ...................................
                @endif
            </td>
        </tr>
    </table>
</body>

</html>