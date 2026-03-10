<!DOCTYPE html>
<html>

<head>
    <title>LAPORAN PERUBAHAN SAL</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
            color: #000;
        }

        .header h2 {
            margin: 5px 0;
            font-size: 14px;
            font-weight: normal;
        }

        .header h3 {
            margin: 0;
            font-size: 12px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 12px 10px;
            line-height: 1.5;
        }

        th {
            background: #f2f2f2;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .subtotal {
            background-color: #f9f9f9;
            font-weight: bold;
        }

        .total-row {
            background-color: #e2e8f0;
            font-weight: bold;
            font-size: 12px;
        }

        .footer {
            margin-top: 40px;
        }

        .footer-table {
            width: 100%;
            border: none;
        }

        .footer-table td {
            border: none;
            text-align: center;
            width: 33%;
            vertical-align: top;
            padding: 10px;
        }

        .signature-space {
            height: 80px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>RSJKO ENGKU HAJI DAUD</h1>
        <h2>LAPORAN PERUBAHAN SISA ANGGARAN LEBIH (LPSAL)</h2>
        <h3>PER {{ strtoupper($period['end_date_formatted']) }}</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th>URAIAN</th>
                <th style="width: 25%;">JUMLAH (RP)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Sisa Anggaran Lebih Awal (SAL Awal)</td>
                <td class="text-right">{{ number_format($sal_awal, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Penggunaan SAL sebagai Penerimaan Pembiayaan Tahun Berjalan</td>
                <td class="text-right">({{ number_format($penggunaan_sal, 2, ',', '.') }})</td>
            </tr>
            <tr class="subtotal">
                <td style="padding-left: 40px;">Subtotal</td>
                <td class="text-right">{{ number_format($sal_awal - $penggunaan_sal, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Sisa Lebih/Kurang Pembiayaan Anggaran (SiLPA/SiKPA)</td>
                <td class="text-right">{{ number_format($silpa, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Koreksi Kesalahan Pembukuan Tahun Sebelumnya</td>
                <td class="text-right">{{ number_format($koreksi, 2, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td>SISA ANGGARAN LEBIH AKHIR (SAL AKHIR)</td>
                <td class="text-right">{{ number_format($sal_akhir, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>
                    @if(isset($ptKiri))
                        {{ $ptKiri->jabatan }}<br><br>
                        <div class="signature-space"></div>
                        <b>{{ $ptKiri->nama }}</b><br>
                        NIP. {{ $ptKiri->nip }}
                    @endif
                </td>
                <td>
                    @if(isset($ptTengah))
                        {{ $ptTengah->jabatan }}<br><br>
                        <div class="signature-space"></div>
                        <b>{{ $ptTengah->nama }}</b><br>
                        NIP. {{ $ptTengah->nip }}
                    @endif
                </td>
                <td>
                    @if(isset($ptKanan))
                        Tanjunguban, {{ Carbon\Carbon::parse($period['end_date'])->translatedFormat('d F Y') }}<br>
                        {{ $ptKanan->jabatan }}<br><br>
                        <div class="signature-space"></div>
                        <b>{{ $ptKanan->nama }}</b><br>
                        NIP. {{ $ptKanan->nip }}
                    @endif
                </td>
            </tr>
        </table>
    </div>
</body>

</html>





