<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            color: #333;
        }

        .table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }

        .table th {
            background-color: #f8fafc;
            border: 1px solid #000;
            padding: 4px;
            font-size: 8pt;
        }

        .table td {
            border: 1px solid #000;
            padding: 4px;
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

        .line {
            border-bottom: 4px solid #000;
            margin: -10px 0 10px;
        }
    </style>
</head>

<body>
    <table style="width: 100%; border: none; margin-bottom: 0;">
        <tr>
            <td style="width: 120px; border: none; vertical-align: top;">
                <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w400"
                    style="height: 120px; width: auto; object-fit: contain;">
            </td>
            <td style="border: none; text-align: center; vertical-align: top; padding-right: 120px;">
                <div style="font-size: 14pt; line-height: 1.2;">PEMERINTAH PROVINSI KEPULAUAN RIAU</div>
                <div style="font-size: 13pt; font-weight: bold; line-height: 1.2;">RUMAH SAKIT JIWA DAN KETERGANTUNGAN
                    OBAT</div>
                <div style="font-size: 13pt; font-weight: bold; line-height: 1.2;">ENGKU HAJI DAUD</div>
                <div style="font-size: 8pt; margin-top: 10px; line-height: 1.4;">
                    Jalan Indun Suri - Simpang Busung Nomor. 1 Tanjung Uban Kode Pos 29152<br>
                    Telepon ( 0771 ) 482655, 482796 Faksimile. ( 0771 ) 482795 "¢ Pos-el: rsjkoehd@kepriprov.go.id Laman
                    : www.rsudehd.kepriprov.go.id
                </div>
            </td>
        </tr>
    </table>
    <div class="line"></div>
    <div style="text-align: center; margin-bottom: 20px;">
        <div style="font-size: 12pt; font-weight: bold; text-decoration: underline; margin-top: 10px;">BUKU KAS UMUM
            PENGELUARAN
        </div>
        <div style="font-size: 9pt; margin-top: 5px;">Periode: {{ $period }}</div>
    </div>

    <table class="table" style="font-size: 7pt;">
        <thead>
            <tr>
                <th width="20">No</th>
                <th width="50">Tanggal</th>
                <th width="70">No Bukti</th>
                <th>Uraian</th>
                <th width="80">Kode Rek</th>
                <th width="65">Transfer Penerimaan</th>
                <th width="65">Pengajuan SP2D</th>
                <th width="65">Realisasi</th>
                <th width="65">Saldo Dana</th>
                <th width="65">Saldo Rekening Koran</th>
                <th width="70">Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $i => $item)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item->date)->translatedFormat('d/m/Y') }}</td>
                    <td class="text-center" style="font-size: 6.5pt;">{{ $item->no_bukti ?: '-' }}</td>
                    <td style="font-size: 6.5pt;">{{ $item->uraian ?: '-' }}</td>
                    <td class="text-center" style="font-size: 6.5pt;">{{ $item->kode_rekening ?: '-' }}</td>
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
            <tr style="background-color: #f8fafc; font-weight: bold;">
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

    <div style="margin-top:20px; font-size: 8pt; font-family: Arial, sans-serif;">
        <table style="border: none; width: 100%;">
            <tr>
                <td style="border: none; width: 220px;">Jumlah Penarikan Cek sampai periode ini</td>
                <td style="border: none; width: 20px;">:</td>
                <td style="border: none; font-weight: bold;">Rp
                    {{ number_format($summary['ytd_receipts'] ?? 0, 2, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td style="border: none;">Jumlah Pengeluaran sampai periode ini</td>
                <td style="border: none;">:</td>
                <td style="border: none; font-weight: bold;">Rp
                    {{ number_format($summary['ytd_expenditures'] ?? 0, 2, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td style="border: none; height: 10px;" colspan="3"></td>
            </tr>
            <tr>
                <td style="border: none; font-weight: bold; text-decoration: underline;">Catatan :</td>
                <td style="border: none;" colspan="2"></td>
            </tr>
            <tr>
                <td style="border: none;">Saldo Rekening Per akhir bulan</td>
                <td style="border: none;">:</td>
                <td style="border: none; font-weight: bold;">Rp
                    {{ number_format($summary['final_bank'] ?? 0, 2, ',', '.') }}
                </td>
            </tr>
            <tr style="font-style: italic; color: #4b5563; font-size: 7.5pt;">
                <td style="border: none; padding-left: 20px;">- Bank Riau Kepri Syariah</td>
                <td style="border: none;">:</td>
                <td style="border: none;">Rp
                    {{ number_format($summary['final_bank_brk'] ?? 0, 2, ',', '.') }}
                </td>
            </tr>
            <tr style="font-style: italic; color: #4b5563; font-size: 7.5pt;">
                <td style="border: none; padding-left: 20px;">- Bank Syariah Indonesia</td>
                <td style="border: none;">:</td>
                <td style="border: none;">Rp
                    {{ number_format($summary['final_bank_bsi'] ?? 0, 2, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>

    <table style="width: 100%; border: none; margin-top: 50px;">
        <tr>
            <td style="width: 33%; border: none; text-align: center; vertical-align: top;">
                @if($ptKiri)
                    <p style="margin: 0; min-height: 1.25em;">&nbsp;</p>
                    <p style="margin: 0;">{{ $ptKiri->jabatan }}</p>
                    <div style="height: 50px;"></div>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $ptKiri->nama }}</p>
                    <p style="margin: 0;">NIP. {{ $ptKiri->nip }}</p>
                @endif
            </td>
            <td style="width: 34%; border: none; text-align: center; vertical-align: top;">
                @if($ptTengah)
                    <p style="margin: 0; min-height: 1.25em;">&nbsp;</p>
                    <p style="margin: 0;">{{ $ptTengah->jabatan }}</p>
                    <div style="height: 50px;"></div>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $ptTengah->nama }}</p>
                    <p style="margin: 0;">NIP. {{ $ptTengah->nip }}</p>
                @endif
            </td>
            <td style="width: 33%; border: none; text-align: center; vertical-align: top;">
                <p style="margin: 0;">Tanjung Uban, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                @if($ptKanan)
                    <p style="margin: 0;">{{ $ptKanan->jabatan }}</p>
                    <div style="height: 50px;"></div>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $ptKanan->nama }}</p>
                    <p style="margin: 0;">NIP. {{ $ptKanan->nip }}</p>
                @else
                    <p style="margin: 0;">&nbsp;</p>
                    <div style="height: 50px;"></div>
                    <p style="margin: 0;">...................................</p>
                    <p style="margin: 0;">NIP. ...................................</p>
                @endif
            </td>
        </tr>
    </table>
</body>

</html>





