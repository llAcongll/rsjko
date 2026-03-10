@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Pendapatan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            color: #333;
            margin: 0;
            padding: 0;
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
            margin-bottom: 10px;
            margin-top: -10px;
        }

        .report-title {
            text-align: center;
            margin-bottom: 20px;
            width: 100%;
        }

        .report-title h3 {
            margin: 0 auto;
            padding: 0;
            font-size: 14pt;
            font-weight: bold;
            color: #000;
            text-decoration: underline;
            text-align: center;
        }

        .report-title .subtitle {
            font-size: 10pt;
            margin-top: 5px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background-color: #f2f2f2;
            border: 1px solid #ccc;
            padding: 6px;
            font-weight: bold;
            text-align: center;
        }

        td {
            border: 1px solid #ccc;
            padding: 6px;
            vertical-align: middle;
        }

        .section-title {
            background-color: #eee;
            font-weight: bold;
            padding: 8px;
            border: 1px solid #ccc;
            margin-top: 10px;
            font-size: 10pt;
        }

        .text-right {
            text-align: right;
            white-space: nowrap;
        }
        
        .text-nowrap {
            white-space: nowrap;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: right;
            font-size: 8pt;
            color: #aaa;
        }

        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }

        .currency-box {
            display: table;
            width: 100%;
        }

        .currency-symbol {
            display: table-cell;
            text-align: left;
            vertical-align: middle;
            width: 30px;
        }

        .currency-value {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
        }

        @page {
            margin: 1cm;
        }
    </style>
</head>

<body>
    <table style="width: 100%; border: none; margin-bottom: 0;">
        <tr>
            <td style="width: 165px; border: none; vertical-align: top;">
                <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w400"
                    style="height: 165px; width: auto; object-fit: contain;">
            </td>
            <td style="border: none; text-align: center; vertical-align: top; padding-right: 165px;">
                <h1 style="margin: 0; padding: 0; font-size: 14pt; font-weight: normal; color: #000; line-height: 1.2;">
                    PEMERINTAH PROVINSI KEPULAUAN RIAU</h1>
                <h2 style="margin: 0; padding: 0; font-size: 13pt; font-weight: bold; color: #000; line-height: 1.2;">RUMAH SAKIT
                    JIWA DAN KETERGANTUNGAN OBAT</h2>
                <h2 style="margin: 0; padding: 0; font-size: 13pt; font-weight: bold; color: #000; line-height: 1.2;">ENGKU HAJI
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
        <h3>LAPORAN PENDAPATAN</h3>
        <p class="subtitle">Periode: {{ Carbon::parse($start)->translatedFormat('d F Y') }} s/d {{ Carbon::parse($end)->translatedFormat('d F Y') }}</p>
    </div>

    <!-- 1. RINGKASAN -->
    <div class="section-title">1. RINGKASAN PENDAPATAN</div>
    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Kategori</th>
                <th style="width: 10%;">Transaksi</th>
                <th style="width: 22%;">Jasa RS</th>
                <th style="width: 22%;">Jasa Pelayanan</th>
                <th style="width: 21%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $tTrans = 0;
                $tRs = 0;
                $tPel = 0;
            $tAll = 0; @endphp
            @foreach(['UMUM' => 'Pasien Umum', 'BPJS' => 'BPJS', 'JAMINAN' => 'Jaminan', 'KERJASAMA' => 'Kerjasama', 'LAIN' => 'Lain-lain'] as $key => $label)
                @php
                    $item = $summary[$key] ?? ['count' => 0, 'rs' => 0, 'pelayanan' => 0, 'total' => 0];
                    $tTrans += $item['count'];
                    $tRs += $item['rs'];
                    $tPel += $item['pelayanan'];
                    $tAll += $item['total'];
                @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td class="text-center">{{ number_format($item['count'], 0, ',', '.') }}</td>
                    <td class="text-right">
                        <div class="currency-box">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($item['rs'], 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td class="text-right">
                        <div class="currency-box">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($item['pelayanan'], 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td class="text-right font-bold">
                        <div class="currency-box">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($item['total'], 2, ',', '.') }}</span>
                        </div>
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td style="text-align: center;">TOTAL</td>
                <td style="text-align: center;">{{ number_format($tTrans, 0, ',', '.') }}</td>
                <td class="text-right">
                    <div class="currency-box">
                        <span class="currency-symbol">Rp</span>
                        <span class="currency-value">{{ number_format($tRs, 2, ',', '.') }}</span>
                    </div>
                </td>
                <td class="text-right">
                    <div class="currency-box">
                        <span class="currency-symbol">Rp</span>
                        <span class="currency-value">{{ number_format($tPel, 2, ',', '.') }}</span>
                    </div>
                </td>
                <td class="text-right">
                    <div class="currency-box">
                        <span class="currency-symbol">Rp</span>
                        <span class="currency-value">{{ number_format($tAll, 2, ',', '.') }}</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- 2. METODE JASA -->
    <div class="section-title">2. RINCIAN METODE JASA (RS & PELAYANAN)</div>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Kode</th>
                <th style="width: 31%;">Uraian Akun</th>
                <th style="width: 18%;">Jasa RS</th>
                <th style="width: 18%;">Jasa Pelayanan</th>
                <th style="width: 18%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $tJra = 0;
                $tJpel = 0;
            $tJtotal = 0; @endphp
            @foreach($breakdown as $key => $item)
                @php
                    $jrs = $item['jasa']['RS'] ?? 0;
                    $jpel = $item['jasa']['PELAYANAN'] ?? 0;
                    $jtot = $item['jasa']['TOTAL'] ?? 0;
                    $tJra += $jrs;
                    $tJpel += $jpel;
                    $tJtotal += $jtot;
                @endphp
                <tr>
                    <td class="text-center" style="font-size: 8pt;">{{ $item['kode'] }}</td>
                    <td style="font-size: 8pt;">{{ $item['nama'] }}</td>
                    <td class="text-right">
                        <div class="currency-box">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($jrs, 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td class="text-right">
                        <div class="currency-box">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($jpel, 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td class="text-right font-bold">
                        <div class="currency-box">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($jtot, 2, ',', '.') }}</span>
                        </div>
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" style="text-align: center;">JUMLAH KESELURUHAN</td>
                <td class="text-right">
                    <div class="currency-box">
                        <span class="currency-symbol">Rp</span>
                        <span class="currency-value">{{ number_format($tJra, 2, ',', '.') }}</span>
                    </div>
                </td>
                <td class="text-right">
                    <div class="currency-box">
                        <span class="currency-symbol">Rp</span>
                        <span class="currency-value">{{ number_format($tJpel, 2, ',', '.') }}</span>
                    </div>
                </td>
                <td class="text-right">
                    <div class="currency-box">
                        <span class="currency-symbol">Rp</span>
                        <span class="currency-value">{{ number_format($tJtotal, 2, ',', '.') }}</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- 3. METODE PEMBAYARAN -->
    <div class="section-title">3. RINCIAN METODE PEMBAYARAN</div>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Kode</th>
                <th style="width: 31%;">Uraian Akun</th>
                <th style="width: 18%;">Tunai</th>
                <th style="width: 18%;">Non-Tunai</th>
                <th style="width: 18%;">Total</th>
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
                    <td class="text-center" style="font-size: 8pt;">{{ $item['kode'] }}</td>
                    <td style="font-size: 8pt;">{{ $item['nama'] }}</td>
                    <td class="text-right">
                        <div class="currency-box">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($item['payments']['TUNAI'], 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td class="text-right">
                        <div class="currency-box">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($item['payments']['NON_TUNAI'], 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td class="text-right font-bold">
                        <div class="currency-box">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($item['payments']['TOTAL'], 2, ',', '.') }}</span>
                        </div>
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" style="text-align: center;">JUMLAH KESELURUHAN</td>
                <td class="text-right">
                    <div class="currency-box">
                        <span class="currency-symbol">Rp</span>
                        <span class="currency-value">{{ number_format($tTunai, 2, ',', '.') }}</span>
                    </div>
                </td>
                <td class="text-right">
                    <div class="currency-box">
                        <span class="currency-symbol">Rp</span>
                        <span class="currency-value">{{ number_format($tNon, 2, ',', '.') }}</span>
                    </div>
                </td>
                <td class="text-right">
                    <div class="currency-box">
                        <span class="currency-symbol">Rp</span>
                        <span class="currency-value">{{ number_format($tTotal, 2, ',', '.') }}</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- 4. RECON BANK -->
    <div class="section-title">4. RINCIAN PENERIMAAN BANK</div>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Kode</th>
                <th style="width: 31%;">Uraian Akun</th>
                <th style="width: 18%;">BRK</th>
                <th style="width: 18%;">BSI</th>
                <th style="width: 18%;">Total</th>
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
                    <td class="text-center" style="font-size: 8pt;">{{ $item['kode'] }}</td>
                    <td style="font-size: 8pt;">{{ $item['nama'] }}</td>
                    <td class="text-right">
                        <div class="currency-box">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($item['banks']['BRK'], 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td class="text-right">
                        <div class="currency-box">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($item['banks']['BSI'], 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td class="text-right font-bold">
                        <div class="currency-box">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($item['banks']['TOTAL'], 2, ',', '.') }}</span>
                        </div>
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" style="text-align: center;">JUMLAH PENERIMAAN BANK</td>
                <td class="text-right">
                    <div class="currency-box">
                        <span class="currency-symbol">Rp</span>
                        <span class="currency-value">{{ number_format($tBrk, 2, ',', '.') }}</span>
                    </div>
                </td>
                <td class="text-right">
                    <div class="currency-box">
                        <span class="currency-symbol">Rp</span>
                        <span class="currency-value">{{ number_format($tBsi, 2, ',', '.') }}</span>
                    </div>
                </td>
                <td class="text-right">
                    <div class="currency-box">
                        <span class="currency-symbol">Rp</span>
                        <span class="currency-value">{{ number_format($tTotalB, 2, ',', '.') }}</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- 5. RUANGAN -->
    <div class="section-title">5. PENDAPATAN PER RUANGAN</div>
    <table>
        <thead>
            <tr>
                <th style="width: 50%;">Nama Ruangan</th>
                <th style="width: 15%;">Pasien</th>
                <th style="width: 35%;">Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @php $tRCount = 0;
                $tRTotal = 0; @endphp
            @foreach($rooms as $name => $data)
                @php $tRCount += $data['count'];
                    $tRTotal += $data['total']; @endphp
                <tr>
                    <td>{{ $name }}</td>
                    <td class="text-center">{{ number_format($data['count'], 0, ',', '.') }}</td>
                    <td class="text-right">
                        <div class="currency-box">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($data['total'], 2, ',', '.') }}</span>
                        </div>
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td style="text-align: center;">GRAND TOTAL (SEMUA RUANGAN)</td>
                <td style="text-align: center;">{{ number_format($tRCount, 0, ',', '.') }}</td>
                <td class="text-right">
                    <div class="currency-box">
                        <span class="currency-symbol">Rp</span>
                        <span class="currency-value">{{ number_format($tRTotal, 2, ',', '.') }}</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- ADDITIVE SECTIONS -->
    <!-- 1. TUNAI -->
    <div class="section-title" style="background-color: #fbbf24; color: #000;">1. PENERIMAAN PASIEN TUNAI</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 50%;">UNIT</th>
                <th style="width: 15%;">TOTAL PASIEN</th>
                <th style="width: 30%;">JUMLAH (RP)</th>
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
                    <td class="text-right">
                        <div class="currency-box">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($item->total, 2, ',', '.') }}</span>
                        </div>
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="text-center">TOTAL PENERIMAAN PASIEN TUNAI</td>
                <td class="text-center">{{ $stCount }}</td>
                <td class="text-right">
                    <div class="currency-box">
                        <span class="currency-symbol">Rp</span>
                        <span class="currency-value">{{ number_format($stTotal, 2, ',', '.') }}</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- 2. NON TUNAI -->
    <div class="section-title" style="background-color: #fbbf24; color: #000;">2. PENERIMAAN PASIEN NON TUNAI</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 25%;">UNIT</th>
                <th style="width: 10%;">PSN QRIS</th>
                <th style="width: 10%;">PSN TRF</th>
                <th style="width: 10%;">TOT PSN</th>
                <th style="width: 13%;">QRIS (RP)</th>
                <th style="width: 13%;">TRF (RP)</th>
                <th style="width: 14%;">TOTAL (RP)</th>
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
            <tr class="total-row">
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

    <!-- 3. BPJS -->
    <div class="section-title" style="background-color: #fbbf24; color: #000;">3. PENERIMAAN PASIEN BPJS KESEHATAN</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 20%;">UNIT</th>
                <th style="width: 10%;">TOTAL PASIEN</th>
                <th style="width: 17%;">BPJS (GROSS)</th>
                <th style="width: 16%;">VPK / POTONGAN</th>
                <th style="width: 16%;">ADM BANK</th>
                <th style="width: 16%;">JUMLAH (NET)</th>
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
                    <td class="text-right">
                         <div class="currency-box">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($item->total, 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td class="text-right">0,00</td>
                    <td class="text-right">0,00</td>
                    <td class="text-right font-bold">
                        <div class="currency-box">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($item->total, 2, ',', '.') }}</span>
                        </div>
                    </td>
                </tr>
            @endforeach
            @php 
                $vpk = $additive_report['bpjs']['deductions']->vpk ?? 0;
                $adm = $additive_report['bpjs']['deductions']->adm ?? 0;
                $net = $sbpTotal - $vpk - $adm;
            @endphp
            <tr class="total-row">
                <td colspan="2" class="text-center">TOTAL PENERIMAAN BPJS KESEHATAN</td>
                <td class="text-center">{{ $sbpCount }}</td>
                <td class="text-right">{{ number_format($sbpTotal, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($vpk, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($adm, 2, ',', '.') }}</td>
                <td class="text-right font-bold">{{ number_format($net, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- 4. JAMINAN -->
    <div class="section-title" style="background-color: #fbbf24; color: #000;">4. PENERIMAAN PASIEN JAMINAN (ASURANSI, PT, DLL)</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 30%;">PENJAMIN / PERUSAHAAN</th>
                <th style="width: 30%;">UNIT</th>
                <th style="width: 10%;">TOTAL PASIEN</th>
                <th style="width: 25%;">JUMLAH (RP)</th>
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
                    <td class="text-right">
                        <div class="currency-box">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($item->total, 2, ',', '.') }}</span>
                        </div>
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" class="text-center">TOTAL PENERIMAAN PASIEN JAMINAN</td>
                <td class="text-center">{{ $sjCount }}</td>
                <td class="text-right">
                    <div class="currency-box">
                        <span class="currency-symbol">Rp</span>
                        <span class="currency-value">{{ number_format($sjTotal, 2, ',', '.') }}</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- 5. KERJASAMA -->
    <div class="section-title" style="background-color: #fbbf24; color: #000;">5. PENERIMAAN KERJA SAMA (PKL, MAGANG, DLL)</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 55%;">KERJA SAMA (INSTANSI)</th>
                <th style="width: 15%;">JUMLAH KEGIATAN</th>
                <th style="width: 25%;">JUMLAH (RP)</th>
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
                    <td class="text-right">
                        <div class="currency-box">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($item->total, 2, ',', '.') }}</span>
                        </div>
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="text-center">TOTAL PENERIMAAN KERJA SAMA</td>
                <td class="text-center">{{ $skCount }}</td>
                <td class="text-right">
                    <div class="currency-box">
                        <span class="currency-symbol">Rp</span>
                        <span class="currency-value">{{ number_format($skTotal, 2, ',', '.') }}</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- 6. LAIN-LAIN -->
    <div class="section-title" style="background-color: #fbbf24; color: #000;">6. PENERIMAAN LAIN-LAIN</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 55%;">KETERANGAN</th>
                <th style="width: 15%;">JUMLAH KEGIATAN</th>
                <th style="width: 25%;">JUMLAH (RP)</th>
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
                    <td class="text-right">
                        <div class="currency-box">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($item->total, 2, ',', '.') }}</span>
                        </div>
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="text-center">TOTAL PENERIMAAN LAIN-LAIN</td>
                <td class="text-center">{{ $slCount }}</td>
                <td class="text-right">
                    <div class="currency-box">
                        <span class="currency-symbol">Rp</span>
                        <span class="currency-value">{{ number_format($slTotal, 2, ',', '.') }}</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- REKAP BANK -->
    <div class="section-title" style="background-color: #fbbf24; color: #000;">REKAPITULASI PENERIMAAN PER BANK</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 55%;">NAMA BANK</th>
                <th style="width: 15%;">TOTAL TRANSAKSI</th>
                <th style="width: 25%;">JUMLAH (RP)</th>
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
                    <td class="text-right">
                        <div class="currency-box">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($item['total'], 2, ',', '.') }}</span>
                        </div>
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="text-center">TOTAL KESELURUHAN PENERIMAAN BANK</td>
                <td class="text-center">{{ $sbCount }}</td>
                <td class="text-right">
                    <div class="currency-box">
                        <span class="currency-symbol">Rp</span>
                        <span class="currency-value">{{ number_format($sbTotal, 2, ',', '.') }}</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- REKAP UNIT -->
    <div class="section-title" style="background-color: #fbbf24; color: #000;">REKAPITULASI PENDAPATAN PER UNIT</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 55%;">UNIT</th>
                <th style="width: 15%;">TOTAL PASIEN</th>
                <th style="width: 25%;">JUMLAH (RP)</th>
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
                    <td class="text-right">
                        <div class="currency-box">
                            <span class="currency-symbol">Rp</span>
                            <span class="currency-value">{{ number_format($item['total'], 2, ',', '.') }}</span>
                        </div>
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="text-center">TOTAL PENDAPATAN PER UNIT</td>
                <td class="text-center">{{ $suCount }}</td>
                <td class="text-right">
                    <div class="currency-box">
                        <span class="currency-symbol">Rp</span>
                        <span class="currency-value">{{ number_format($suTotal, 2, ',', '.') }}</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%; border: none; margin-top: 50px;">
        <tr>
            <td style="width: 33%; border: none; text-align: center; vertical-align: top;">
                @if($ptKiri)
                    <p style="margin: 0; min-height: 1.2em;">&nbsp;</p>
                    <p style="margin: 0;">{{ $ptKiri->jabatan }}</p>
                    <div style="height: 60px;"></div>
                    <p style="margin: 0;">{{ $ptKiri->nama }}</p>
                    <p style="margin: 0;">NIP. {{ $ptKiri->nip }}</p>
                @endif
            </td>
            <td style="width: 34%; border: none; text-align: center; vertical-align: top;">
                @if($ptTengah)
                    <p style="margin: 0; min-height: 1.2em;">&nbsp;</p>
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
                    <p style="margin: 0;">...................................</p>
                    <p style="margin: 0;">NIP. ...................................</p>
                @endif
            </td>
        </tr>
    </table>

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}
    </div>
</body>

</html>





