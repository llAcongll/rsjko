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
    <div class="title">LAPORAN PENDAPATAN PASIEN</div>
    <div class="subtitle">Periode: {{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }} s/d
        {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}</div>

    <table class="table">
        <!-- SECTION 1: RINGKASAN -->
        <thead>
            <tr>
                <th colspan="5" style="background-color: #d9ead3; font-size: 12pt;">1. RINGKASAN PENDAPATAN</th>
            </tr>
            <tr>
                <th>Kategori Pendapatan</th>
                <th>Jumlah Transaksi</th>
                <th>Jasa Rumah Sakit</th>
                <th>Jasa Pelayanan</th>
                <th>Total Pendapatan</th>
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
                <td>TOTAL KESELURUHAN</td>
                <td class="text-center">{{ number_format($totTrans, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totRs, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totPel, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totAll, 2, ',', '.') }}</td>
            </tr>
        </tbody>

        <!-- SPACER -->
        <tr>
            <td colspan="5" style="border: none; height: 30px;"></td>
        </tr>

        <!-- SECTION 2: METODE PEMBAYARAN -->
        <thead>
            <tr>
                <th colspan="5" style="background-color: #cfe2f3; font-size: 12pt;">2. RINCIAN METODE PEMBAYARAN (TUNAI
                    & NON-TUNAI)</th>
            </tr>
            <tr>
                <th>Kode Rekening</th>
                <th>Uraian Akun Pendapatan</th>
                <th>Tunai</th>
                <th>Non-Tunai</th>
                <th>Total</th>
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
                <td colspan="2" class="text-right">JUMLAH KESELURUHAN</td>
                <td class="text-right">{{ number_format($tTunai, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($tNon, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($tTotal, 2, ',', '.') }}</td>
            </tr>
        </tbody>

        <!-- SPACER -->
        <tr>
            <td colspan="5" style="border: none; height: 30px;"></td>
        </tr>

        <!-- SECTION 3: BANK -->
        <thead>
            <tr>
                <th colspan="5" style="background-color: #fff2cc; font-size: 12pt;">3. RINCIAN PENERIMAAN BANK (RECON)
                </th>
            </tr>
            <tr>
                <th>Kode Rekening</th>
                <th>Uraian Akun Pendapatan</th>
                <th>BRK (Tunai + Transfer)</th>
                <th>BSI (Transfer)</th>
                <th>Total</th>
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
                <td colspan="2" class="text-right">JUMLAH PENERIMAAN BANK</td>
                <td class="text-right">{{ number_format($tBrk, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($tBsi, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($tTotalB, 2, ',', '.') }}</td>
            </tr>
        </tbody>

        <!-- SPACER -->
        <tr>
            <td colspan="5" style="border: none; height: 30px;"></td>
        </tr>

        <!-- SECTION 4: ROOMS -->
        <thead>
            <tr>
                <th colspan="5" style="background-color: #ead1dc; font-size: 12pt;">4. PENDAPATAN & PASIEN PER RUANGAN
                </th>
            </tr>
            <tr>
                <th colspan="2">Nama Ruangan</th>
                <th>Jumlah Pasien</th>
                <th colspan="2">Total Pendapatan</th>
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
                <td colspan="2">TOTAL RUANGAN</td>
                <td class="text-center">{{ number_format($tRCount, 0, ',', '.') }}</td>
                <td colspan="2" class="text-right">{{ number_format($tRTotal, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>