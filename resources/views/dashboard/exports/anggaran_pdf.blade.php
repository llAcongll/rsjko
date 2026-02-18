@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>

<head>
    <title>Laporan Realisasi Anggaran</title>
    <style>
        @page {
            margin: 1cm;
        }

        body {
            font-family: sans-serif;
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
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
            margin-top: 10px;
        }

        .report-title {
            text-align: center;
            margin-bottom: 20px;
            width: 100%;
            position: relative;
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
        }

        td {
            border: 1px solid #000;
            padding: 4px;
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

        .curr-cell {
            display: table;
            width: 100%;
        }

        .curr-rp {
            display: table-cell;
            text-align: left;
            width: 15px;
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
        <h3>LAPORAN REALISASI ANGGARAN</h3>
        <p>Periode: {{ Carbon::parse($start)->translatedFormat('d F Y') }} s/d
            {{ Carbon::parse($end)->translatedFormat('d F Y') }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Kode Rekening</th>
                <th style="width: 35%;">Uraian</th>
                <th style="width: 20%;">Target Anggaran</th>
                <th style="width: 20%;">Realisasi</th>
                <th style="width: 10%;">%</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                @php $isBold = $item->level < 5; @endphp
                <tr style="{{ $isBold ? 'font-weight:bold; background-color:#f8fafc;' : '' }}">
                    <td>{{ $item->kode }}</td>
                    <td>{{ $item->nama }}</td>
                    <td>
                        <div class="curr-cell">
                            <span class="curr-rp">Rp</span>
                            <span class="curr-val">{{ number_format($item->target, 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="curr-cell">
                            <span class="curr-rp">Rp</span>
                            <span class="curr-val">{{ number_format($item->realisasi, 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td class="text-center">{{ number_format($item->persen, 2, ',', '.') }}%</td>
                </tr>
            @endforeach
            <tr style="background:#f1f5f9; font-weight:bold;">
                <td colspan="2" class="text-center">GRAND TOTAL</td>
                <td>
                    <div class="curr-cell">
                        <span class="curr-rp">Rp</span>
                        <span class="curr-val">{{ number_format($totals->target, 2, ',', '.') }}</span>
                    </div>
                </td>
                <td>
                    <div class="curr-cell">
                        <span class="curr-rp">Rp</span>
                        <span class="curr-val">{{ number_format($totals->realisasi, 2, ',', '.') }}</span>
                    </div>
                </td>
                <td class="text-center">{{ number_format($totals->persen, 2, ',', '.') }}%</td>
            </tr>
        </tbody>
    </table>
</body>

</html>