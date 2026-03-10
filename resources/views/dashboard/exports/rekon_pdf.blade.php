@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>

<head>
    <title>Laporan Rekonsiliasi</title>
    <style>
        @page {
            margin: 1cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            color: #333;
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
            padding: 6px;
            font-weight: bold;
            text-align: center;
        }

        td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: middle;
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

        .section-title {
            background-color: #e9ecef;
            font-weight: bold;
            padding: 8px;
            border: 1px solid #000;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        .curr-cell {
            display: table;
            width: 100%;
        }

        .curr-rp {
            display: table-cell;
            text-align: left;
            width: 20px;
            vertical-align: middle;
        }

        .curr-val {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <div class="title-container" style="text-align:center; margin-bottom: 25px;">
        <div style="font-size: 11pt; text-transform: uppercase;">RSJKO ENGKU HAJI DAUD</div>
        <div style="font-size: 13pt; font-weight: bold; text-decoration: underline; margin: 5px 0;">BERITA ACARA REKONSILIASI DATA KEUANGAN</div>
        <div style="font-size: 11pt; font-weight: bold;">PERIODE {{ $label }}</div>
    </div>

    <p style="text-align: justify; margin-bottom: 20px; line-height: 1.6;">Telah dilakukan Rekonsiliasi Data Keuangan
        antara <strong>BADAN KEUANGAN DAN ASET DAERAH PROVINSI KEPRI</strong> dengan <strong>RSJKO ENGKU HAJI
            DAUD</strong> dengan hasil sebagai berikut:</p>

    <!-- BAGIAN A -->
    <div class="section-title">BAGIAN A - DATA KAS BENDAHARA PENERIMAAN</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="text-align: left;">Uraian</th>
                <th style="width: 20%;">Pendapatan Sistem</th>
                <th style="width: 20%;">Rekening Koran Bank</th>
                <th style="width: 20%;">Selisih</th>
            </tr>
        </thead>
        <tbody>
            @php $tSistem = 0;
            $tBank = 0; @endphp
            @foreach($recap as $index => $item)
                @php $tSistem += $item->pendapatan_modul;
                $tBank += $item->bank; @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>Pendapatan Periode {{ $item->bulan }}</td>
                    <td class="text-right">{{ number_format($item->pendapatan_modul, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->bank, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($item->selisih, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background-color: #f2f2f2;">
                <td colspan="2" class="text-center">JUMLAH TOTAL</td>
                <td class="text-right">{{ number_format($tSistem, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($tBank, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($tBank - $tSistem, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- BAGIAN B -->
    <div class="section-title">BAGIAN B - DATA SALDO REKENING KORAN</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="text-align: left;">Nama Bank</th>
                <th style="text-align: left;">Nama Rekening</th>
                <th style="text-align: center;">No Rekening</th>
                <th style="width: 20%;">Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($section_b as $index => $bank)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $bank->bank }}</td>
                    <td>{{ $bank->nama_rekening }}</td>
                    <td class="text-center">{{ $bank->no_rekening }}</td>
                    <td class="text-right font-bold">{{ number_format($bank->saldo_akhir, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- BAGIAN C -->
    <div class="section-title">BAGIAN C - ANALISIS SELISIH TRANSAKSI</div>
    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 17%;">Sistem</th>
                <th style="width: 17%;">Bank</th>
                <th style="width: 17%;">Selisih</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 25%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($analysis as $row)
                <tr>
                    <td class="text-center" style="font-size: 8pt;">{{ Carbon::parse($row->tanggal)->format('d/m/y') }}</td>
                    <td class="text-right" style="font-size: 8pt;">{{ number_format($row->nominal, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-size: 8pt;">{{ number_format($row->bank, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-size: 8pt;">{{ number_format($row->selisih, 0, ',', '.') }}</td>
                    <td class="text-center font-bold" style="font-size: 7pt;">{{ $row->status }}</td>
                    <td style="font-size: 7pt;">{{ $row->keterangan }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%; border: none; margin-top: 30px;">
        <tr>
            <td style="width: 33%; border: none; text-align: center; vertical-align: top;">
                @if($ptKiri)
                    <p style="margin: 0; min-height: 1.25em;">&nbsp;</p>
                    <p style="margin: 0;">{{ $ptKiri->jabatan }}</p>
                    <div style="height: 50px;"></div>
                    <p style="margin: 0;"><b>{{ $ptKiri->nama }}</b></p>
                    <p style="margin: 0;">NIP. {{ $ptKiri->nip }}</p>
                @endif
            </td>
            <td style="width: 34%; border: none; text-align: center; vertical-align: top;">
                @if($ptTengah)
                    <p style="margin: 0; min-height: 1.25em;">&nbsp;</p>
                    <p style="margin: 0;">{{ $ptTengah->jabatan }}</p>
                    <div style="height: 50px;"></div>
                    <p style="margin: 0;"><b>{{ $ptTengah->nama }}</b></p>
                    <p style="margin: 0;">NIP. {{ $ptTengah->nip }}</p>
                @endif
            </td>
            <td style="width: 33%; border: none; text-align: center; vertical-align: top;">
                @if($ptKanan)
                    <p style="margin: 0;">Tanjung Uban, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p style="margin: 0;">{{ $ptKanan->jabatan }}</p>
                    <div style="height: 50px;"></div>
                    <p style="margin: 0;"><b>{{ $ptKanan->nama }}</b></p>
                    <p style="margin: 0;">NIP. {{ $ptKanan->nip }}</p>
                @else
                    <p style="margin: 0;">Tanjung Uban, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p style="margin: 0;">&nbsp;</p>
                    <p style="margin: 0;">&nbsp;</p>
                    <div style="height: 50px;"></div>
                    ...................................</p>
                    <p style="margin: 0;">NIP. ...................................</p>
                @endif
            </td>
        </tr>
    </table>
</body>

</html>





