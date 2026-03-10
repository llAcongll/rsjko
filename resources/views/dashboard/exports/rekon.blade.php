@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>

<head>
    <title>Laporan Rekonsiliasi</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 10pt;
        }

        th {
            background-color: #f2f2f2;
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

        .section-title {
            background-color: #e9ecef;
            font-weight: bold;
            padding: 10px;
            border: 1px solid #000;
            margin-top: 20px;
        }

        /* Status Colors */
        .status-match {
            color: green;
        }

        .status-delay {
            color: orange;
        }

        .status-missing {
            color: red;
        }
    </style>
</head>

<body>
    <div style="text-align: center; margin-bottom: 20px;">
        <h3 style="margin:0;">BERITA ACARA REKONSILIASI DATA KEUANGAN</h3>
        <p style="margin:5px 0;">PERIODE {{ $label }}</p>
        <p style="margin:0;">TAHUN ANGGARAN {{ $tahun }}</p>
    </div>

    <p style="text-align: justify; margin-bottom: 20px;">Telah dilakukan Rekonsiliasi Data Keuangan antara <strong>BADAN
            KEUANGAN DAN ASET DAERAH PROVINSI KEPRI</strong> dengan <strong>RSJKO ENGKU HAJI DAUD</strong> dengan hasil
        sebagai berikut:</p>

    <!-- BAGIAN A -->
    <div class="section-title">BAGIAN A - DATA KAS BENDAHARA PENERIMAAN</div>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Uraian</th>
                <th width="20%">Pendapatan Sistem</th>
                <th width="20%">Rekening Koran Bank</th>
                <th width="20%">Selisih</th>
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
                <th width="5%">No</th>
                <th>Nama Bank</th>
                <th>Nama Rekening</th>
                <th>No Rekening</th>
                <th width="20%">Saldo Akhir</th>
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
                <th>Tanggal</th>
                <th>Sistem</th>
                <th>Bank</th>
                <th>Selisih</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($analysis as $row)
                <tr>
                    <td class="text-center">{{ Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                    <td class="text-right">{{ number_format($row->nominal, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row->bank, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row->selisih, 0, ',', '.') }}</td>
                    <td class="text-center font-bold">{{ $row->status }}</td>
                    <td>{{ $row->keterangan }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="border: none; margin-top: 30px;">
        <tr>
            <td align="center" style="border: none;">
                @if($ptKiri)
                    <br>
                    {{ $ptKiri->jabatan }}<br><br><br><br>
                    <b>{{ $ptKiri->nama }}</b><br>
                    NIP. {{ $ptKiri->nip }}
                @endif
            </td>
            <td style="border: none;"></td>
            <td align="center" style="border: none;">
                @if($ptTengah)
                    <br>
                    {{ $ptTengah->jabatan }}<br><br><br><br>
                    <b>{{ $ptTengah->nama }}</b><br>
                    NIP. {{ $ptTengah->nip }}
                @endif
            </td>
            <td style="border: none;"></td>
            <td align="center" style="border: none;">
                Tanjung Uban, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                @if($ptKanan)
                    {{ $ptKanan->jabatan }}<br><br><br><br>
                    <b>{{ $ptKanan->nama }}</b><br>
                    NIP. {{ $ptKanan->nip }}
                @else
                    &nbsp;<br>
                    &nbsp;<br><br><br><br>
                    ...................................<br>
                    NIP. ...................................
                @endif
            </td>
        </tr>
    </table>
</body>

</html>





