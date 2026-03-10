<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            color: #333;
            line-height: 1.4;
        }

        .table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
        }

        .table th {
            background-color: #f8fafc;
            border: 1px solid #000;
            padding: 8px;
            font-size: 9pt;
            text-align: center;
        }

        .table td {
            border: 1px solid #000;
            padding: 6px;
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
            border-bottom: 3px solid #000;
            margin: 10px 0;
        }

        .kop-table {
            width: 100%;
            border: none;
            margin-bottom: 0;
        }

        .kop-logo {
            width: 100px;
        }

        .kop-text {
            text-align: center;
        }
    </style>
</head>

<body>
    <table class="kop-table">
        <tr>
            <td class="kop-logo" style="border:none;">
                <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w400"
                    style="height: 100px; width: auto;">
            </td>
            <td class="kop-text" style="border:none; padding-right: 100px;">
                <div style="font-size: 14pt;">PEMERINTAH PROVINSI KEPULAUAN RIAU</div>
                <div style="font-size: 13pt; font-weight: bold;">RUMAH SAKIT JIWA DAN KETERGANTUNGAN OBAT</div>
                <div style="font-size: 13pt; font-weight: bold;">ENGKU HAJI DAUD</div>
                <div style="font-size: 8pt; margin-top: 5px;">
                    Jalan Indun Suri - Simpang Busung Nomor. 1 Tanjung Uban Kode Pos 29152<br>
                    Telepon ( 0771 ) 482655, 482796 Faksimile. ( 0771 ) 482795<br>
                    Pos-el: rsjkoehd@kepriprov.go.id Laman: www.rsudehd.kepriprov.go.id
                </div>
            </td>
        </tr>
    </table>
    <div class="line"></div>

    <div style="text-align: center; margin: 20px 0;">
        <div style="font-size: 12pt; font-weight: bold; text-decoration: underline;">BUKU KAS UMUM PENDAPATAN</div>
        <div style="font-size: 10pt; margin-top: 5px;">Periode: {{ $period }}</div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th width="30">No</th>
                <th width="70">Tanggal</th>
                <th width="80">No Bukti</th>
                <th>Uraian / Keterangan</th>
                <th width="80">Sumber</th>
                <th width="100">Penerimaan</th>
                <th width="100">Pengeluaran</th>
                <th width="110">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $i => $item)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                    <td class="text-center" style="font-family: monospace; font-size: 7pt;">
                        {{ $item->reference_id > 0 ? 'TRX-' . $item->reference_id : '-' }}
                    </td>
                    <td>{{ $item->uraian }}</td>
                    <td class="text-center" style="font-size: 8pt;">{{ $item->sumber }}</td>
                    <td class="text-right" style="color: #059669;">
                        {{ $item->penerimaan > 0 ? number_format($item->penerimaan, 2, ',', '.') : '-' }}
                    </td>
                    <td class="text-right" style="color: #dc2626;">
                        {{ $item->pengeluaran > 0 ? number_format($item->pengeluaran, 2, ',', '.') : '-' }}
                    </td>
                    <td class="text-right font-bold">{{ number_format($item->saldo, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="5" class="text-center">TOTAL MUTASI & SALDO AKHIR</td>
                <td class="text-right">{{ number_format($summary['total_penerimaan'] ?? 0, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($summary['total_pengeluaran'] ?? 0, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($summary['final_saldo'] ?? 0, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top:20px; font-size: 8pt; font-family: Arial, sans-serif;">
        <table style="border: none; width: 100%;">
            <tr>
                <td style="border: none; width: 250px;">Jumlah Penerimaan Tunai sampai periode ini</td>
                <td style="border: none; width: 20px;">:</td>
                <td style="border: none; font-weight: bold;">Rp
                    {{ number_format($summary['cumulative_penerimaan'] ?? 0, 2, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td style="border: none;">Jumlah Setoran ke Bank sampai periode ini</td>
                <td style="border: none;">:</td>
                <td style="border: none; font-weight: bold;">Rp
                    {{ number_format($summary['cumulative_pengeluaran'] ?? 0, 2, ',', '.') }}
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
                <td style="border: none;">Saldo Kas Bendahara per akhir bulan</td>
                <td style="border: none;">:</td>
                <td style="border: none; font-weight: bold;">Rp
                    {{ number_format($summary['final_saldo'] ?? 0, 2, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td style="border: none;">Saldo Rekening per akhir bulan</td>
                <td style="border: none;">:</td>
                <td style="border: none; font-weight: bold;">&nbsp;</td>
            </tr>
            <tr style="font-style: italic; color: #4b5563; font-size: 7.5pt;">
                <td style="border: none; padding-left: 20px;">- Bank Riau Kepri Syariah</td>
                <td style="border: none;">:</td>
                <td style="border: none;">Rp
                    {{ number_format($summary['bank_brk'] ?? 0, 2, ',', '.') }}
                </td>
            </tr>
            <tr style="font-style: italic; color: #4b5563; font-size: 7.5pt;">
                <td style="border: none; padding-left: 20px;">- Bank Syariah Indonesia</td>
                <td style="border: none;">:</td>
                <td style="border: none;">Rp
                    {{ number_format($summary['bank_bsi'] ?? 0, 2, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>

    {{-- Signatures --}}
    <table style="width: 100%; border: none; margin-top: 50px;">
        <tr>
            <td style="width: 33%; border: none; text-align: center; vertical-align: top;">
                @if($ptKiri)
                    <p style="margin: 0; min-height: 1.2em;">&nbsp;</p>
                    <p style="margin: 0;">{{ $ptKiri->jabatan }}</p>
                    <div style="height: 60px;"></div>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $ptKiri->nama }}</p>
                    <p style="margin: 0;">NIP. {{ $ptKiri->nip }}</p>
                @endif
            </td>
            <td style="width: 34%; border: none; text-align: center; vertical-align: top;">
                @if($ptTengah)
                    <p style="margin: 0; min-height: 1.2em;">&nbsp;</p>
                    <p style="margin: 0;">{{ $ptTengah->jabatan }}</p>
                    <div style="height: 60px;"></div>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $ptTengah->nama }}</p>
                    <p style="margin: 0;">NIP. {{ $ptTengah->nip }}</p>
                @endif
            </td>
            <td style="width: 33%; border: none; text-align: center; vertical-align: top;">
                <p style="margin: 0;">Tanjung Uban, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                @if($ptKanan)
                    <p style="margin: 0;">{{ $ptKanan->jabatan }}</p>
                    <div style="height: 60px;"></div>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $ptKanan->nama }}</p>
                    <p style="margin: 0;">NIP. {{ $ptKanan->nip }}</p>
                @else
                    <p style="margin: 0;">&nbsp;</p>
                    <div style="height: 60px;"></div>
                    <p style="margin: 0; font-weight: bold;">...................................</p>
                    <p style="margin: 0;">NIP. ...................................</p>
                @endif
            </td>
        </tr>
    </table>
</body>

</html>





