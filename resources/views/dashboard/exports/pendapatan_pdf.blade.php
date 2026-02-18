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
            font-family: 'Helvetica', sans-serif;
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
            border-bottom: 2px solid #000;
            margin-bottom: 10px;
            margin-top: 10px;
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
    <div class="header-kop">
        <h1>PEMERINTAH PROVINSI KEPULAUAN RIAU</h1>
        <h2>RUMAH SAKIT JIWA DAN KETERGANTUNGAN OBAT</h2>
        <h2>ENGKU HAJI DAUD</h2>
        <div class="address">
            Jalan Indun Suri – Simpang Busung Nomor 1 Tanjung Uban Kode Pos 29152<br>
            Telepon (0771) 482655, 482796 • Faksimile (0771) 482795<br>
            Pos-el: rskjoehd@kepriprov.go.id<br>
            Laman: www.rsuehd.kepriprov.go.id
        </div>
    </div>
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

    <!-- 2. METODE PEMBAYARAN -->
    <div class="section-title">2. RINCIAN METODE PEMBAYARAN</div>
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

    <!-- 3. RECON BANK -->
    <div class="section-title">3. RINCIAN PENERIMAAN BANK</div>
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

    <!-- 4. RUANGAN -->
    <div class="section-title">4. PENDAPATAN PER RUANGAN</div>
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

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}
    </div>
</body>

</html>