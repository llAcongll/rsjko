<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>LRKB - Triwulan {{ $lrkb->triwulan }}/{{ $lrkb->tahun }}</title>
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

        .section-title {
            background-color: #f2f2f2;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h3>PEMERINTAH PROVINSI SUMATERA SELATAN</h3>
        <h4>LAPORAN REKONSILIASI KAS BENDAHARA (LRKB)</h4>
        @php
            $months = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            $triwulans = ["", "I (SATU)", "II (DUA)", "III (TIGA)", "IV (EMPAT)"];
            $labelPeriode = $lrkb->triwulan ? "TRIWULAN " . $triwulans[$lrkb->triwulan] : "BULAN " . strtoupper($months[$lrkb->bulan]);
        @endphp
        <div style="margin-top: 5px; font-weight: bold;">
            PERIODE {{ $labelPeriode }} TAHUN {{ $lrkb->tahun }}
        </div>
    </div>

    <div style="margin-bottom: 15px;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 150px;">SKPD</td>
                <td>: RS KHUSUS JIWA DAN KETERGANTUNGAN OBAT</td>
            </tr>
            <tr>
                <td>KODE SKPD</td>
                <td>: 1.02.0.00.0.00.01.0000</td>
            </tr>
        </table>
    </div>

    <table class="content-table">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th>URAIAN PEMBUKUAN DAN KAS</th>
                <th style="width: 180px;">JUMLAH (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="section-title">
                <td>SALDO AWAL KAS</td>
                <td></td>
            </tr>
            <tr>
                <td style="padding-left: 25px;">Saldo Awal (Per 1 Jan / Periode Lalu)</td>
                <td class="text-right">{{ number_format($lrkb->saldo_awal, 2, ',', '.') }}</td>
            </tr>
            <tr class="font-bold">
                <td>JUMLAH SALDO AWAL</td>
                <td class="text-right">{{ number_format($lrkb->saldo_awal, 2, ',', '.') }}</td>
            </tr>

            <tr style="height: 10px;">
                <td colspan="2" style="border: none;"></td>
            </tr>
            <tr class="section-title">
                <td>PENERIMAAN KAS</td>
                <td></td>
            </tr>
            <tr>
                <td style="padding-left: 25px;">Penerimaan Selama Periode Ini</td>
                <td class="text-right">{{ number_format($lrkb->pendapatan, 2, ',', '.') }}</td>
            </tr>

            <tr style="height: 10px;">
                <td colspan="2" style="border: none;"></td>
            </tr>
            <tr class="section-title">
                <td>PENGELUARAN KAS</td>
                <td></td>
            </tr>
            <tr>
                <td style="padding-left: 25px;">Pengeluaran Selama Periode Ini</td>
                <td class="text-right">({{ number_format($lrkb->belanja, 2, ',', '.') }})</td>
            </tr>

            <tr style="height: 10px;">
                <td colspan="2" style="border: none;"></td>
            </tr>
            <tr class="font-bold" style="background-color: #f2f2f2; font-size: 12pt;">
                <td>SALDO AKHIR MENURUT PEMBUKUAN (BKU)</td>
                <td class="text-right" style="border-bottom: 3px double black;">
                    {{ number_format($lrkb->saldo_akhir_buku, 2, ',', '.') }}
                </td>
            </tr>

            <tr style="height: 20px;">
                <td colspan="2" style="border: none;"></td>
            </tr>
            <tr style="height: 15px;">
                <td colspan="2" style="border: none;"></td>
            </tr>
            <tr class="section-title">
                <td>POSISI KAS NYATA / FISIK (REAL)</td>
                <td></td>
            </tr>
            @php
                $detailsMap = $lrkb->details->pluck('jumlah', 'jenis');
                $bankIn = $detailsMap['bank_penerimaan'] ?? 0;
                $bankOut = $detailsMap['bank_pengeluaran'] ?? 0;
                $tunaiIn = $detailsMap['tunai_penerimaan'] ?? 0;
                $tunaiOut = $detailsMap['tunai_pengeluaran'] ?? 0;
            @endphp
            <tr>
                <td style="padding-left: 25px;">- Saldo Bank (Penerimaan)</td>
                <td class="text-right">{{ number_format($bankIn, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 25px;">- Saldo Bank (Pengeluaran)</td>
                <td class="text-right">({{ number_format($bankOut, 2, ',', '.') }})</td>
            </tr>
            <tr class="font-bold">
                <td style="padding-left: 25px;">Sub-Total Saldo Bank (Rekening Koran)</td>
                <td class="text-right" style="border-top: 1px solid black;">
                    {{ number_format($lrkb->saldo_bank, 2, ',', '.') }}
                </td>
            </tr>

            <tr style="height: 10px;">
                <td colspan="2" style="border: none;"></td>
            </tr>
            <tr>
                <td style="padding-left: 25px;">- Saldo Kas Tunai (Penerimaan)</td>
                <td class="text-right">{{ number_format($tunaiIn, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 25px;">- Saldo Kas Tunai (Pengeluaran)</td>
                <td class="text-right">({{ number_format($tunaiOut, 2, ',', '.') }})</td>
            </tr>
            <tr class="font-bold">
                <td style="padding-left: 25px;">Sub-Total Saldo Kas Tunai (di Brankas)</td>
                <td class="text-right" style="border-top: 1px solid black;">
                    {{ number_format($lrkb->saldo_tunai, 2, ',', '.') }}
                </td>
            </tr>

            <tr style="height: 10px;">
                <td colspan="2" style="border: none;"></td>
            </tr>
            <tr class="font-bold" style="background-color: #f2f2f2;">
                <td>TOTAL SALDO AKHIR MENURUT KAS FISIK</td>
                <td class="text-right" style="border-bottom: 3px double black;">
                    {{ number_format($lrkb->saldo_fisik, 2, ',', '.') }}
                </td>
            </tr>

            <tr style="height: 20px;">
                <td colspan="2" style="border: none;"></td>
            </tr>
            <tr class="font-bold"
                style="background-color: {{ round($lrkb->selisih, 2) == 0 ? '#ccffcc' : '#ffcccc' }}; font-size: 13pt;">
                <td>SELISIH KAS (PEMBUKUAN - FISIK)</td>
                <td class="text-right" style="border-bottom: 4px double black;">
                    {{ number_format($lrkb->selisih, 2, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

</body>

</html>