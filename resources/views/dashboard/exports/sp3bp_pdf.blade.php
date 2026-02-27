<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>SP3BP - {{ $sp3bp->periode->bulan }}/{{ $sp3bp->periode->tahun }}</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.3;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .header h3 {
            margin: 0;
            font-size: 14pt;
        }

        .header h4 {
            margin: 5px 0 0;
            font-size: 12pt;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .content-table th,
        .content-table td {
            border: 1px solid black;
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

        .summary-box {
            border: 1px solid black;
            padding: 10px;
            margin-bottom: 20px;
        }

        .summary-row {
            display: table;
            width: 100%;
        }

        .summary-label {
            display: table-cell;
            width: 60%;
        }

        .summary-value {
            display: table-cell;
            width: 40%;
            text-align: right;
        }

        .footer {
            margin-top: 30px;
        }

        .footer-table {
            width: 100%;
            border: none;
        }

        .footer-table td {
            border: none;
            text-align: center;
            width: 50%;
        }

        @page {
            margin: 1.5cm;
        }
    </style>
</head>

<body>
    <div class="header">
        <h3>PEMERINTAH PROVINSI SUMATERA SELATAN</h3>
        <h4>SURAT PERINTAH PENGESAHAN PENDAPATAN DAN BELANJA (SP3BP)</h4>
        <div style="margin-top: 5px;">
            @php
                $months = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                $triwulans = ["", "I (SATU)", "II (DUA)", "III (TIGA)", "IV (EMPAT)"];
                $label = $sp3bp->periode->triwulan
                    ? "TRIWULAN: " . $triwulans[$sp3bp->periode->triwulan]
                    : "BULAN: " . strtoupper($months[$sp3bp->periode->bulan]);
            @endphp
            PERIODE {{ $label }} {{ $sp3bp->periode->tahun }}
        </div>
    </div>

    <div style="margin-bottom: 15px;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 150px;">SKPD</td>
                <td>: RUMAH SAKIT KHUSUS JIWA DAN KETERGANTUNGAN OBAT</td>
            </tr>
            <tr>
                <td>KODE SKPD</td>
                <td>: 1.02.0.00.0.00.01.0000</td>
            </tr>
            </tr>
        </table>
    </div>

    <table class="content-table">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="width: 50px;">NO</th>
                <th>URAIAN</th>
                <th style="width: 150px;">JUMLAH (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>SALDO AWAL KAS</td>
                <td class="text-right">{{ number_format($sp3bp->saldo_awal, 2, ',', '.') }}</td>
            </tr>
            <tr class="font-bold">
                <td class="text-center">2</td>
                <td>PENDAPATAN</td>
                <td class="text-right">{{ number_format($sp3bp->pendapatan, 2, ',', '.') }}</td>
            </tr>
            @foreach($sp3bp->detailPendapatan as $p)
                <tr>
                    <td></td>
                    <td style="padding-left: 20px;">- {{ $p->uraian }} ({{ $p->kode_rekening }})</td>
                    <td class="text-right">{{ number_format($p->jumlah, 2, ',', '.') }}</td>
                </tr>
            @endforeach

            <tr class="font-bold">
                <td class="text-center">3</td>
                <td>BELANJA</td>
                <td class="text-right">({{ number_format($sp3bp->belanja, 2, ',', '.') }})</td>
            </tr>
            @foreach($sp3bp->detailBelanja as $b)
                <tr>
                    <td></td>
                    <td style="padding-left: 20px;">- {{ $b->uraian }} ({{ $b->kode_rekening }})</td>
                    <td class="text-right">{{ number_format($b->jumlah, 2, ',', '.') }}</td>
                </tr>
            @endforeach

            <tr class="font-bold">
                <td class="text-center">4</td>
                <td>PEMBIAYAAN (NETTO)</td>
                <td class="text-right">
                    {{ number_format($sp3bp->pembiayaan_terima - $sp3bp->pembiayaan_keluar, 2, ',', '.') }}
                </td>
            </tr>

            <tr class="font-bold" style="background-color: #f2f2f2;">
                <td class="text-center">5</td>
                <td>SALDO AKHIR KAS</td>
                <td class="text-right">{{ number_format($sp3bp->saldo_akhir, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 20px; border: 1px solid black; padding: 10px;">
        <div class="font-bold" style="margin-bottom: 10px; text-decoration: underline;">REKONSILIASI KAS:</div>
        <table style="width: 100%; border: none;">
            <tr>
                <td style="padding-left: 10px;">- Saldo Bank (Penerimaan)</td>
                <td class="text-right">{{ number_format($sp3bp->rekonsiliasi->bank_masuk, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 10px;">- Saldo Bank (Pengeluaran)</td>
                <td class="text-right">({{ number_format($sp3bp->rekonsiliasi->bank_keluar, 2, ',', '.') }})</td>
            </tr>
            <tr class="font-bold">
                <td>Sub-Total Saldo Bank (Rekening Koran)</td>
                <td class="text-right" style="border-top: 1px solid black;">
                    {{ number_format($sp3bp->rekonsiliasi->saldo_bank, 2, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td colspan="2" style="height: 5px;"></td>
            </tr>
            <tr>
                <td style="padding-left: 10px;">- Saldo Kas Tunai (Penerimaan)</td>
                <td class="text-right">{{ number_format($sp3bp->rekonsiliasi->tunai_masuk, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 10px;">- Saldo Kas Tunai (Pengeluaran)</td>
                <td class="text-right">({{ number_format($sp3bp->rekonsiliasi->tunai_keluar, 2, ',', '.') }})</td>
            </tr>
            <tr class="font-bold">
                <td>Sub-Total Saldo Kas Tunai (di Brankas)</td>
                <td class="text-right" style="border-top: 1px solid black;">
                    {{ number_format($sp3bp->rekonsiliasi->saldo_tunai, 2, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td colspan="2" style="height: 5px;"></td>
            </tr>
            <tr class="font-bold" style="background-color: #f2f2f2;">
                <td>Total Kas di Bendahara (Fisik)</td>
                <td class="text-right" style="border-bottom: 3px double black;">
                    {{ number_format($sp3bp->rekonsiliasi->saldo_buku, 2, ',', '.') }}
                </td>
            </tr>
            <tr style="color: {{ $sp3bp->selisih == 0 ? 'black' : 'red' }}">
                <td>Selisih Kas (Anggaran vs Fisik)</td>
                <td class="text-right">{{ number_format($sp3bp->selisih, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

</body>

</html>