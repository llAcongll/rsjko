<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        .title {
            font-family: Arial, sans-serif;
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
        }

        .subtitle {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            text-align: center;
            margin-bottom: 20px;
        }

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
            <td colspan="5" class="text-center" style="border: none; font-size: 8pt;">Jalan Indun Suri - Simpang Busung Nomor. 1 Tanjung Uban Kode Pos 29152</td>
        </tr>
        <tr>
            <td colspan="5" class="text-center" style="border: none; font-size: 8pt;">Telepon ( 0771 ) 482655, 482796 Faksimile. ( 0771 ) 482795</td>
        </tr>
        <tr>
            <td colspan="5" class="text-center" style="border: none; font-size: 8pt;">Pos-el: rsjkoehd@kepriprov.go.id</td>
        </tr>
        <tr>
            <td colspan="5" class="text-center" style="border: none; font-size: 8pt;">Laman : www.rsudehd.kepriprov.go.id</td>
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
    </table>

    <!-- ADDITIVE SECTIONS -->
    <div class="spacer"></div>
    <table class="table">
        <thead>
            <tr><th colspan="4" style="background-color: #fbbf24; text-align: center;">1. PENERIMAAN PASIEN TUNAI</th></tr>
            <tr>
                <th style="width: 50px; text-align: center;">NO</th>
                <th style="text-align: center;">UNIT</th>
                <th style="width: 150px; text-align: center;">TOTAL PASIEN</th>
                <th style="width: 200px; text-align: center;">JUMLAH (RP)</th>
            </tr>
        </thead>
        <tbody>
            @php $stTotal = 0; $stCount = 0; @endphp
            @foreach($additive_report['tunai'] as $idx => $item)
                @php $stTotal += $item->total; $stCount += $item->count; @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $item->unit }}</td>
                    <td class="text-center">{{ $item->count }}</td>
                    <td class="text-right">{{ number_format($item->total, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="font-bold bg-gray">
                <td colspan="2" class="text-center">TOTAL PENERIMAAN PASIEN TUNAI</td>
                <td class="text-center">{{ $stCount }}</td>
                <td class="text-right">{{ number_format($stTotal, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="spacer"></div>
    <table class="table">
        <thead>
            <tr><th colspan="8" style="background-color: #fbbf24; text-align: center;">2. PENERIMAAN PASIEN NON TUNAI</th></tr>
            <tr>
                <th style="width: 50px; text-align: center;">NO</th>
                <th style="text-align: center;">UNIT</th>
                <th style="width: 100px; text-align: center;">PASIEN QRIS</th>
                <th style="width: 100px; text-align: center;">PASIEN TRF</th>
                <th style="width: 100px; text-align: center;">TOTAL PASIEN</th>
                <th style="width: 150px; text-align: center;">QRIS (RP)</th>
                <th style="width: 150px; text-align: center;">TRANSFER (RP)</th>
                <th style="width: 150px; text-align: center;">TOTAL (RP)</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $sntQris = 0; $sntTrans = 0; $sntTotal = 0; 
                $sntPQris = 0; $sntPTrans = 0; $sntPAll = 0; 
            @endphp
            @foreach($additive_report['non_tunai'] as $idx => $item)
                @php 
                    $sntQris += $item->qris_amount; 
                    $sntTrans += $item->transfer_amount; 
                    $sntTotal += $item->total_amount; 
                    $sntPQris += $item->pasien_qris;
                    $sntPTrans += $item->pasien_transfer;
                    $sntPAll += $item->total_pasien;
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $item->unit }}</td>
                    <td class="text-center">{{ $item->pasien_qris }}</td>
                    <td class="text-center">{{ $item->pasien_transfer }}</td>
                    <td class="text-center">{{ $item->total_pasien }}</td>
                    <td class="text-right">{{ number_format($item->qris_amount, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->transfer_amount, 2, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($item->total_amount, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="font-bold bg-gray">
                <td colspan="2" class="text-center">TOTAL PENERIMAAN PASIEN NON TUNAI</td>
                <td class="text-center">{{ $sntPQris }}</td>
                <td class="text-center">{{ $sntPTrans }}</td>
                <td class="text-center">{{ $sntPAll }}</td>
                <td class="text-right">{{ number_format($sntQris, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($sntTrans, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($sntTotal, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="spacer"></div>
    <table class="table">
        <thead>
            <tr><th colspan="7" style="background-color: #fbbf24; text-align: center;">3. PENERIMAAN PASIEN BPJS KESEHATAN</th></tr>
            <tr>
                <th style="width: 50px; text-align: center;">NO</th>
                <th style="text-align: center;">UNIT</th>
                <th style="width: 120px; text-align: center;">TOTAL PASIEN</th>
                <th style="width: 120px; text-align: center;">BPJS (GROSS)</th>
                <th style="width: 120px; text-align: center;">VPK / POTONGAN</th>
                <th style="width: 120px; text-align: center;">ADM BANK</th>
                <th style="width: 120px; text-align: center;">JUMLAH (NET)</th>
            </tr>
        </thead>
        <tbody>
            @php $sbpTotal = 0; $sbpCount = 0; @endphp
            @foreach($additive_report['bpjs']['data'] as $idx => $item)
                @php $sbpTotal += $item->total; $sbpCount += $item->count; @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $item->unit }}</td>
                    <td class="text-center">{{ $item->count }}</td>
                    <td class="text-right">{{ number_format($item->total, 2, ',', '.') }}</td>
                    <td class="text-right">0,00</td>
                    <td class="text-right">0,00</td>
                    <td class="text-right font-bold">{{ number_format($item->total, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            @php 
                $vpk = $additive_report['bpjs']['deductions']->vpk ?? 0;
                $adm = $additive_report['bpjs']['deductions']->adm ?? 0;
                $net = $sbpTotal - $vpk - $adm;
            @endphp
            <tr class="font-bold bg-gray">
                <td colspan="2" class="text-center">TOTAL PENERIMAAN BPJS KESEHATAN</td>
                <td class="text-center">{{ $sbpCount }}</td>
                <td class="text-right">{{ number_format($sbpTotal, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($vpk, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($adm, 2, ',', '.') }}</td>
                <td class="text-right font-bold">{{ number_format($net, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="spacer"></div>
    <table class="table">
        <thead>
            <tr><th colspan="5" style="background-color: #fbbf24; text-align: center;">4. PENERIMAAN PASIEN JAMINAN (ASURANSI, PT, DLL)</th></tr>
            <tr>
                <th style="width: 50px; text-align: center;">NO</th>
                <th style="text-align: center;">PENJAMIN / PERUSAHAAN</th>
                <th style="text-align: center;">UNIT</th>
                <th style="width: 150px; text-align: center;">TOTAL PASIEN</th>
                <th style="width: 200px; text-align: center;">JUMLAH (RP)</th>
            </tr>
        </thead>
        <tbody>
            @php $sjTotal = 0; $sjCount = 0; @endphp
            @foreach($additive_report['jaminan'] as $idx => $item)
                @php $sjTotal += $item->total; $sjCount += $item->count; @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $item->penjamin }}</td>
                    <td>{{ $item->unit }}</td>
                    <td class="text-center">{{ $item->count }}</td>
                    <td class="text-right">{{ number_format($item->total, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="font-bold bg-gray">
                <td colspan="3" class="text-center">TOTAL PENERIMAAN PASIEN JAMINAN</td>
                <td class="text-center">{{ $sjCount }}</td>
                <td class="text-right">{{ number_format($sjTotal, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="spacer"></div>
    <table class="table">
        <thead>
            <tr><th colspan="4" style="background-color: #fbbf24; text-align: center;">5. PENERIMAAN KERJA SAMA (PKL, MAGANG, DLL)</th></tr>
            <tr>
                <th style="width: 50px; text-align: center;">NO</th>
                <th style="text-align: center;">KERJA SAMA (INSTANSI)</th>
                <th style="width: 150px; text-align: center;">JUMLAH KEGIATAN</th>
                <th style="width: 200px; text-align: center;">JUMLAH (RP)</th>
            </tr>
        </thead>
        <tbody>
            @php $skTotal = 0; $skCount = 0; @endphp
            @foreach($additive_report['kerjasama'] as $idx => $item)
                @php $skTotal += $item->total; $skCount += $item->count; @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $item->instansi }}</td>
                    <td class="text-center">{{ $item->count }}</td>
                    <td class="text-right">{{ number_format($item->total, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="font-bold bg-gray">
                <td colspan="2" class="text-center">TOTAL PENERIMAAN KERJA SAMA</td>
                <td class="text-center">{{ $skCount }}</td>
                <td class="text-right">{{ number_format($skTotal, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="spacer"></div>
    <table class="table">
        <thead>
            <tr><th colspan="4" style="background-color: #fbbf24; text-align: center;">6. PENERIMAAN LAIN-LAIN</th></tr>
            <tr>
                <th style="width: 50px; text-align: center;">NO</th>
                <th style="text-align: center;">KETERANGAN</th>
                <th style="width: 150px; text-align: center;">JUMLAH KEGIATAN</th>
                <th style="width: 200px; text-align: center;">JUMLAH (RP)</th>
            </tr>
        </thead>
        <tbody>
            @php $slTotal = 0; $slCount = 0; @endphp
            @foreach($additive_report['lain'] as $idx => $item)
                @php $slTotal += $item->total; $slCount += $item->count; @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $item->keterangan }}</td>
                    <td class="text-center">{{ $item->count }}</td>
                    <td class="text-right">{{ number_format($item->total, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="font-bold bg-gray">
                <td colspan="2" class="text-center">TOTAL PENERIMAAN LAIN-LAIN</td>
                <td class="text-center">{{ $slCount }}</td>
                <td class="text-right">{{ number_format($slTotal, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="spacer"></div>
    <table class="table">
        <thead>
            <tr><th colspan="4" style="background-color: #fbbf24; text-align: center;">REKAPITULASI PENERIMAAN PER BANK</th></tr>
            <tr>
                <th style="width: 50px; text-align: center;">NO</th>
                <th style="text-align: center;">NAMA BANK</th>
                <th style="width: 150px; text-align: center;">TOTAL TRANSAKSI</th>
                <th style="width: 200px; text-align: center;">JUMLAH (RP)</th>
            </tr>
        </thead>
        <tbody>
            @php $sbTotal = 0; $sbCount = 0; @endphp
            @foreach($additive_report['bank_summary'] as $idx => $item)
                @php $sbTotal += $item['total']; $sbCount += $item['count']; @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $item['bank'] }}</td>
                    <td class="text-center">{{ $item['count'] }}</td>
                    <td class="text-right">{{ number_format($item['total'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="font-bold bg-gray">
                <td colspan="2" class="text-center">TOTAL KESELURUHAN PENERIMAAN BANK</td>
                <td class="text-center">{{ $sbCount }}</td>
                <td class="text-right">{{ number_format($sbTotal, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="spacer"></div>
    <table class="table">
        <thead>
            <tr><th colspan="4" style="background-color: #fbbf24; text-align: center;">REKAPITULASI PENDAPATAN PER UNIT</th></tr>
            <tr>
                <th style="width: 50px; text-align: center;">NO</th>
                <th style="text-align: center;">UNIT</th>
                <th style="width: 150px; text-align: center;">TOTAL PASIEN</th>
                <th style="width: 200px; text-align: center;">JUMLAH (RP)</th>
            </tr>
        </thead>
        <tbody>
            @php $suTotal = 0; $suCount = 0; @endphp
            @foreach($additive_report['unit_summary'] as $idx => $item)
                @php $suTotal += $item['total']; $suCount += $item['count']; @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $item['unit'] }}</td>
                    <td class="text-center">{{ $item['count'] }}</td>
                    <td class="text-right">{{ number_format($item['total'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="font-bold bg-gray">
                <td colspan="2" class="text-center">TOTAL PENDAPATAN PER UNIT</td>
                <td class="text-center">{{ $suCount }}</td>
                <td class="text-right">{{ number_format($suTotal, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="spacer"></div>
    <table style="border: none;">
        <tr>
            <td align="center">
                @if($ptKiri)
                    <br>
                    {{ $ptKiri->jabatan }}<br><br><br><br>
                    {{ $ptKiri->nama }}<br>
                    NIP. {{ $ptKiri->nip }}
                @endif
            </td>
            <td></td>
            <td align="center">
                @if($ptTengah)
                    <br>
                    {{ $ptTengah->jabatan }}<br><br><br><br>
                    {{ $ptTengah->nama }}<br>
                    NIP. {{ $ptTengah->nip }}
                @endif
            </td>
            <td></td>
            <td align="center">
                Tanjung Uban, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                @if($ptKanan)
                    {{ $ptKanan->jabatan }}<br><br><br><br>
                    {{ $ptKanan->nama }}<br>
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





