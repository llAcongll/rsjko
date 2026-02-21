<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        .title {
            font-family: sans-serif;
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
        }

        .subtitle {
            font-family: sans-serif;
            font-size: 11pt;
            text-align: center;
            margin-bottom: 20px;
        }

        .table {
            border-collapse: collapse;
            width: 100%;
            font-family: sans-serif;
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

        .bg-gray {
            background-color: #f9f9f9;
        }

        .section-header {
            background-color: #d9ead3;
            font-weight: bold;
            text-align: center;
        }

        .spacer {
            height: 20px;
            border: none !important;
        }
    </style>
</head>

<body>
    <table class="table" style="border: none; margin-bottom: 20px;">
        <tr>
            <td colspan="5" class="text-center" style="border: none; font-size: 14pt;">PEMERINTAH PROVINSI KEPULAUAN RIAU</td>
        </tr>
        <tr>
            <td colspan="5" class="text-center" style="border: none; font-size: 13pt; font-weight: bold;">RUMAH SAKIT JIWA DAN KETERGANTUNGAN OBAT</td>
        </tr>
        <tr>
            <td colspan="5" class="text-center" style="border: none; font-size: 13pt; font-weight: bold;">ENGKU HAJI DAUD</td>
        </tr>
        <tr>
            <td colspan="5" class="text-center" style="border: none; font-size: 8pt;">Jalan Indun Suri – Simpang Busung Nomor 1 Tanjung Uban Kode Pos 29152</td>
        </tr>
        <tr>
            <td colspan="5" class="text-center" style="border: none; font-size: 8pt;">Telepon (0771) 482655, 482796 • Faksimile (0771) 482795</td>
        </tr>
        <tr>
            <td colspan="5" class="text-center" style="border: none; font-size: 8pt;">Pos-el: rskjoehd@kepriprov.go.id</td>
        </tr>
        <tr>
            <td colspan="5" class="text-center" style="border: none; font-size: 8pt;">Laman: www.rsuehd.kepriprov.go.id</td>
        </tr>
        <tr>
            <td colspan="5" style="border-bottom: 2px solid #000; height: 10px;"></td>
        </tr>
    </table>

    <div class="title" style="margin-top: 20px;">LAPORAN PENDAPATAN PASIEN</div>
    <div class="subtitle" style="text-decoration: underline;">Periode: {{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }} s/d
        {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}</div>

    <table class="table">
        <!-- SECTION 1: RINGKASAN -->
        <thead>
            <tr>
                <th colspan="5" style="background-color: #d9ead3; font-size: 12pt; text-align: center;">1. RINGKASAN PENDAPATAN</th>
            </tr>
            <tr>
                <th style="text-align: center;">Kategori Pendapatan</th>
                <th style="text-align: center;">Jumlah Transaksi</th>
                <th style="text-align: center;">Jasa Rumah Sakit</th>
                <th style="text-align: center;">Jasa Pelayanan</th>
                <th style="text-align: center;">Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @php $totTrans = 0;
                $totRs = 0;
                $totPel = 0;
            $totAll = 0; @endphp
            @foreach(['UMUM' => 'Pasien Umum', 'BPJS' => 'BPJS', 'JAMINAN' => 'Jaminan', 'KERJASAMA' => 'Kerjasama', 'LAIN' => 'Lain-lain'] as $key => $label)
                @php
                    $item = $summary[$key] ?? ['count' => 0, 'rs' => 0, 'pelayanan' => 0, 'total' => 0];
                    $totTrans += $item['count'];
                    $totRs += $item['rs'];
                    $totPel += $item['pelayanan'];
                    $totAll += $item['total'];
                @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td class="text-center">{{ number_format($item['count'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item['rs'], 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item['pelayanan'], 2, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($item['total'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="font-bold" style="background-color: #f2f2f2;">
                <td style="text-align: center;">TOTAL</td>
                <td style="text-align: center;">{{ number_format($totTrans, 0, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($totRs, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($totPel, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($totAll, 2, ',', '.') }}</td>
            </tr>
        </tbody>

        <!-- SPACER -->
        <tr>
            <td colspan="5" style="border: none; height: 30px;"></td>
        </tr>

        <!-- SECTION 2: METODE JASA -->
        <thead>
            <tr>
                <th colspan="5" style="background-color: #fce5cd; font-size: 12pt; text-align: center;">2. RINCIAN METODE JASA (RS & PELAYANAN)</th>
            </tr>
            <tr>
                <th style="text-align: center;">Kode Rekening</th>
                <th style="text-align: center;">Uraian Akun Pendapatan</th>
                <th style="text-align: center;">Jasa Rumah Sakit</th>
                <th style="text-align: center;">Jasa Pelayanan</th>
                <th style="text-align: center;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $tJrs = 0;
                $tJpel = 0;
            $tJTotal = 0; @endphp
            @foreach($breakdown as $key => $item)
                @php
                    $jrs = $item['jasa']['RS'] ?? 0;
                    $jpel = $item['jasa']['PELAYANAN'] ?? 0;
                    $jtot = $item['jasa']['TOTAL'] ?? 0;
                    $tJrs += $jrs;
                    $tJpel += $jpel;
                    $tJTotal += $jtot;
                @endphp
                <tr>
                    <td class="text-center">{{ $item['kode'] }}</td>
                    <td>{{ $item['nama'] }}</td>
                    <td class="text-right">{{ number_format($jrs, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($jpel, 2, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($jtot, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="font-bold bg-gray">
                <td colspan="2" style="text-align: center;">JUMLAH KESELURUHAN</td>
                <td style="text-align: right;">{{ number_format($tJrs, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($tJpel, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($tJTotal, 2, ',', '.') }}</td>
            </tr>
        </tbody>

        <!-- SPACER -->
        <tr>
            <td colspan="5" style="border: none; height: 30px;"></td>
        </tr>

        <!-- SECTION 3: METODE PEMBAYARAN -->
        <thead>
            <tr>
                <th colspan="5" style="background-color: #cfe2f3; font-size: 12pt; text-align: center;">3. RINCIAN METODE PEMBAYARAN (TUNAI
                    & NON-TUNAI)</th>
            </tr>
            <tr>
                <th style="text-align: center;">Kode Rekening</th>
                <th style="text-align: center;">Uraian Akun Pendapatan</th>
                <th style="text-align: center;">Tunai</th>
                <th style="text-align: center;">Non-Tunai</th>
                <th style="text-align: center;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $tTunai = 0;
                $tNon = 0;
            $tTotal = 0; @endphp
            @foreach($breakdown as $key => $item)
                @php
                    $tTunai += $item['payments']['TUNAI'];
                    $tNon += $item['payments']['NON_TUNAI'];
                    $tTotal += $item['payments']['TOTAL'];
                @endphp
                <tr>
                    <td class="text-center">{{ $item['kode'] }}</td>
                    <td>{{ $item['nama'] }}</td>
                    <td class="text-right">{{ number_format($item['payments']['TUNAI'], 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item['payments']['NON_TUNAI'], 2, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($item['payments']['TOTAL'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="font-bold bg-gray">
                <td colspan="2" style="text-align: center;">JUMLAH KESELURUHAN</td>
                <td style="text-align: right;">{{ number_format($tTunai, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($tNon, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($tTotal, 2, ',', '.') }}</td>
            </tr>
        </tbody>

        <!-- SPACER -->
        <tr>
            <td colspan="5" style="border: none; height: 30px;"></td>
        </tr>

        <!-- SECTION 4: BANK -->
        <thead>
            <tr>
                <th colspan="5" style="background-color: #fff2cc; font-size: 12pt; text-align: center;">4. RINCIAN PENERIMAAN BANK (RECON)
                </th>
            </tr>
            <tr>
                <th style="text-align: center;">Kode Rekening</th>
                <th style="text-align: center;">Uraian Akun Pendapatan</th>
                <th style="text-align: center;">BRK (Tunai + Transfer)</th>
                <th style="text-align: center;">BSI (Transfer)</th>
                <th style="text-align: center;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $tBrk = 0;
                $tBsi = 0;
            $tTotalB = 0; @endphp
            @foreach($breakdown as $key => $item)
                @php
                    $tBrk += $item['banks']['BRK'];
                    $tBsi += $item['banks']['BSI'];
                    $tTotalB += $item['banks']['TOTAL'];
                @endphp
                <tr>
                    <td class="text-center">{{ $item['kode'] }}</td>
                    <td>{{ $item['nama'] }}</td>
                    <td class="text-right">{{ number_format($item['banks']['BRK'], 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item['banks']['BSI'], 2, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($item['banks']['TOTAL'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="font-bold bg-gray">
                <td colspan="2" style="text-align: center;">JUMLAH PENERIMAAN BANK</td>
                <td style="text-align: right;">{{ number_format($tBrk, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($tBsi, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($tTotalB, 2, ',', '.') }}</td>
            </tr>
        </tbody>

        <!-- SPACER -->
        <tr>
            <td colspan="5" style="border: none; height: 30px;"></td>
        </tr>

        <!-- SECTION 5: ROOMS -->
        <thead>
            <tr>
                <th colspan="5" style="background-color: #ead1dc; font-size: 12pt; text-align: center;">5. PENDAPATAN & PASIEN PER RUANGAN
                </th>
            </tr>
            <tr>
                <th colspan="2" style="text-align: center;">Nama Ruangan</th>
                <th style="text-align: center;">Jumlah Pasien</th>
                <th colspan="2" style="text-align: center;">Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @php $tRCount = 0;
            $tRTotal = 0; @endphp
            @foreach($rooms as $name => $data)
                @php $tRCount += $data['count'];
                $tRTotal += $data['total']; @endphp
                <tr>
                    <td colspan="2">{{ $name }}</td>
                    <td class="text-center">{{ number_format($data['count'], 0, ',', '.') }}</td>
                    <td colspan="2" class="text-right">{{ number_format($data['total'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="font-bold bg-gray">
                <td colspan="2" style="text-align: center;">GRAND TOTAL (SEMUA RUANGAN)</td>
                <td style="text-align: center;">{{ number_format($tRCount, 0, ',', '.') }}</td>
                <td colspan="2" style="text-align: right;">{{ number_format($tRTotal, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    <table style="border: none;">
        <tr>
            <td align="center">
                @if($ptKiri)
                    <br>
                    <b>{{ $ptKiri->jabatan }}</b><br><br><br><br>
                    <b>{{ $ptKiri->nama }}</b><br>
                    NIP. {{ $ptKiri->nip }}
                @endif
            </td>
            <td></td>
            <td align="center">
                @if($ptTengah)
                    <br>
                    <b>{{ $ptTengah->jabatan }}</b><br><br><br><br>
                    <b>{{ $ptTengah->nama }}</b><br>
                    NIP. {{ $ptTengah->nip }}
                @endif
            </td>
            <td></td>
            <td align="center">
                Tanjung Uban, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                @if($ptKanan)
                    <b>{{ $ptKanan->jabatan }}</b><br><br><br><br>
                    <b>{{ $ptKanan->nama }}</b><br>
                    NIP. {{ $ptKanan->nip }}
                @else
                    <b>&nbsp;</b><br>
                    &nbsp;<br><br><br><br>
                    <b>...................................</b><br>
                    NIP. ...................................
                @endif
            </td>
        </tr>
    </table>
</body>

</html>