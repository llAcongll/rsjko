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

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 16pt;
            color: #000;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 10pt;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
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
            word-wrap: break-word;
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

        @page {
            margin: 1cm;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>LAPORAN PENDAPATAN PASIEN</h1>
        <p>RSJ TAMPAN PROVINSI RIAU</p>
        <p>Periode: {{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }} s/d
            {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}</p>
    </div>

    <!-- 1. RINGKASAN -->
    <div class="section-title">1. RINGKASAN PENDAPATAN</div>
    <table>
        <thead>
            <tr>
                <th style="width: 30%;">Kategori</th>
                <th style="width: 15%;">Transaksi</th>
                <th style="width: 20%;">Jasa RS</th>
                <th style="width: 20%;">Jasa Pelayanan</th>
                <th style="width: 15%;">Total</th>
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
                    <td class="text-right">{{ number_format($item['rs'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item['pelayanan'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($item['total'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="text-center">{{ number_format($tTrans, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($tRs, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($tPel, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($tAll, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- 2. METODE PEMBAYARAN -->
    <div class="section-title">2. RINCIAN METODE PEMBAYARAN</div>
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Kode</th>
                <th style="width: 40%;">Uraian Akun</th>
                <th style="width: 13%;">Tunai</th>
                <th style="width: 13%;">Non-Tunai</th>
                <th style="width: 14%;">Total</th>
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
                    <td class="text-right">{{ number_format($item['payments']['TUNAI'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item['payments']['NON_TUNAI'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($item['payments']['TOTAL'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="text-right">JUMLAH KESELURUHAN</td>
                <td class="text-right">{{ number_format($tTunai, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($tNon, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($tTotal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- 3. RECON BANK -->
    <div class="section-title">3. RINCIAN PENERIMAAN BANK</div>
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Kode</th>
                <th style="width: 40%;">Uraian Akun</th>
                <th style="width: 13%;">BRK</th>
                <th style="width: 13%;">BSI</th>
                <th style="width: 14%;">Total</th>
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
                    <td class="text-right">{{ number_format($item['banks']['BRK'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item['banks']['BSI'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($item['banks']['TOTAL'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="text-right">JUMLAH PENERIMAAN BANK</td>
                <td class="text-right">{{ number_format($tBrk, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($tBsi, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($tTotalB, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- 4. RUANGAN (TOP 15 only to avoid bloating PDF) -->
    <div class="section-title">4. PENDAPATAN PER RUANGAN (Top 15)</div>
    <table>
        <thead>
            <tr>
                <th style="width: 60%;">Nama Ruangan</th>
                <th style="width: 15%;">Pasien</th>
                <th style="width: 25%;">Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @php $tRCount = 0;
                $tRTotal = 0;
            $count = 0; @endphp
            @foreach($rooms as $name => $data)
                @php $tRCount += $data['count'];
                    $tRTotal += $data['total'];
                $count++; @endphp
                @if($count <= 15)
                    <tr>
                        <td>{{ $name }}</td>
                        <td class="text-center">{{ number_format($data['count'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($data['total'], 0, ',', '.') }}</td>
                    </tr>
                @endif
            @endforeach
            <tr class="total-row">
                <td>GRAND TOTAL (SEMUA RUANGAN)</td>
                <td class="text-center">{{ number_format($tRCount, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($tRTotal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}
    </div>
</body>

</html>