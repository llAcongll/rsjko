@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>

<head>
    <title>Laporan Piutang</title>
    <style>
        @page {
            size: landscape;
            margin: 1cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            color: #333;
        }

        .header-kop {
            text-align: center;
            margin-bottom: 5px;
        }

        .header-kop h1 {
            margin: 0;
            padding: 0;
            font-size: 14pt;
            font-weight: normal;
            color: #000;
        }

        .header-kop h2 {
            margin: 0;
            padding: 0;
            font-size: 13pt;
            font-weight: bold;
            color: #000;
        }

        .header-kop .address {
            line-height: 1.4;
            margin-top: 5px;
            font-size: 8pt;
            font-weight: normal;
            color: #000;
        }

        .header-line {
            border-bottom: 4px solid #000;
            margin-bottom: 20px;
            margin-top: -10px;
        }

        .report-title {
            text-align: center;
            margin-bottom: 20px;
            width: 100%;
        }

        .report-title h3 {
            margin: 0 auto;
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            text-align: center;
        }

        .report-title p {
            margin: 5px 0 0;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background: #f2f2f2;
            border: 1px solid #000;
            padding: 4px;
            font-weight: bold;
            text-align: center;
            font-size: 7.5pt;
        }

        td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: middle;
            font-size: 7.5pt;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .curr-cell {
            text-align: right;
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <table style="width: 100%; border: none; margin-bottom: 0;">
        <tr>
            <td style="width: 165px; border: none; vertical-align: top;">
                <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w400"
                    style="height: 165px; width: auto; object-fit: contain;" alt="Logo Prov Kepri">
            </td>
            <td style="border: none; text-align: center; vertical-align: top; padding-right: 165px;">
                <h1 style="margin: 0; padding: 0; font-size: 14pt; font-weight: normal; color: #000; line-height: 1.2;">
                    PEMERINTAH PROVINSI KEPULAUAN RIAU</h1>
                <h2 style="margin: 0; padding: 0; font-size: 13pt; font-weight: bold; color: #000; line-height: 1.2;">
                    RUMAH SAKIT
                    JIWA DAN KETERGANTUNGAN OBAT</h2>
                <h2 style="margin: 0; padding: 0; font-size: 13pt; font-weight: bold; color: #000; line-height: 1.2;">
                    ENGKU HAJI
                    DAUD</h2>
                <div style="line-height: 1.4; margin-top: 10px; font-size: 8pt; font-weight: normal; color: #000;">
                    Jalan Indun Suri - Simpang Busung Nomor. 1 Tanjung Uban Kode Pos 29152<br>
                    Telepon ( 0771 ) 482655, 482796 Faksimile. ( 0771 ) 482795<br>
                    Pos-el: rsjkoehd@kepriprov.go.id Laman : www.rsudehd.kepriprov.go.id
                </div>
            </td>
        </tr>
    </table>
    <div class="header-line"></div>

    <div class="report-title">
        <h3>LAPORAN PIUTANG</h3>
        <p>Tahun Anggaran: {{ $tahun }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 15%; text-align: center;">Nama Perusahaan</th>
                <th colspan="4" style="width: 28%; text-align: center;">Saldo Awal (Tahun Lalu)</th>
                <th colspan="4" style="width: 28%; text-align: center;">Tahun Berjalan</th>
                <th rowspan="2" style="width: 7.5%; text-align: center;">Pelunasan Total</th>
                <th rowspan="2" style="width: 7.5%; text-align: center;">Potongan Total</th>
                <th rowspan="2" style="width: 7.5%; text-align: center;">Sisa 2025</th>
                <th rowspan="2" style="width: 6.5%; text-align: center;">S. Akhir</th>
            </tr>
            <tr>
                <th style="text-align: center;">Piutang</th>
                <th style="text-align: center;">Pelunasan</th>
                <th style="text-align: center;">Pot</th>
                <th style="text-align: center;">Adm</th>
                <th style="text-align: center;">Piutang</th>
                <th style="text-align: center;">Pelunasan</th>
                <th style="text-align: center;">Pot</th>
                <th style="text-align: center;">Adm</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    <td>{{ $item->nama_perusahaan }}</td>
                    <td class="text-right">{{ number_format($item->sa_piutang, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->sa_pelunasan, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->sa_potongan, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->sa_adm, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->berjalan_piutang, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->berjalan_pelunasan, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->berjalan_potongan, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->berjalan_adm, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight:bold;">
                        {{ number_format($item->total_pelunasan, 0, ',', '.') }}
                    </td>
                    <td class="text-right">{{ number_format($item->total_potongan, 0, ',', '.') }}</td>
                    <td class="text-right" style="color:#ef4444;">{{ number_format($item->sisa_2025, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight:bold; background:#f8fafc;">
                        {{ number_format($item->saldo_akhir, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
            <tr style="background:#f1f5f9; font-weight:bold;">
                <td class="text-center">GRAND TOTAL</td>
                <td class="text-right">{{ number_format($totals->sa_piutang, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals->sa_pelunasan, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals->sa_potongan, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals->sa_adm, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals->berjalan_piutang, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals->berjalan_pelunasan, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals->berjalan_potongan, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals->berjalan_adm, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals->total_pelunasan, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals->total_potongan, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals->sisa_2025, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals->saldo_akhir, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%; border: none; margin-top: 50px;">
        <tr>
            <td style="width: 33%; border: none; text-align: center; vertical-align: top;">
                @if($ptKiri)
                    <p style="margin: 0; min-height: 1.25em;">&nbsp;</p>
                    <p style="margin: 0;">{{ $ptKiri->jabatan }}</p>
                    <div style="height: 60px;"></div>
                    <p style="margin: 0;">{{ $ptKiri->nama }}</p>
                    <p style="margin: 0;">NIP. {{ $ptKiri->nip }}</p>
                @endif
            </td>
            <td style="width: 34%; border: none; text-align: center; vertical-align: top;">
                @if($ptTengah)
                    <p style="margin: 0; min-height: 1.25em;">&nbsp;</p>
                    <p style="margin: 0;">{{ $ptTengah->jabatan }}</p>
                    <div style="height: 60px;"></div>
                    <p style="margin: 0;">{{ $ptTengah->nama }}</p>
                    <p style="margin: 0;">NIP. {{ $ptTengah->nip }}</p>
                @endif
            </td>
            <td style="width: 33%; border: none; text-align: center; vertical-align: top;">
                @if($ptKanan)
                    <p style="margin: 0;">Tanjung Uban, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p style="margin: 0;">{{ $ptKanan->jabatan }}</p>
                    <div style="height: 60px;"></div>
                    <p style="margin: 0;">{{ $ptKanan->nama }}</p>
                    <p style="margin: 0;">NIP. {{ $ptKanan->nip }}</p>
                @else
                    <p style="margin: 0;">Tanjung Uban, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p style="margin: 0;">&nbsp;</p>
                    <p style="margin: 0;">&nbsp;</p>
                    <div style="height: 60px;"></div>
                    ...................................</p>
                    <p style="margin: 0;">NIP. ...................................</p>
                @endif
            </td>
        </tr>
    </table>
</body>

</html>