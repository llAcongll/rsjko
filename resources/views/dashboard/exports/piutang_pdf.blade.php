@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>

<head>
    <title>Laporan Piutang</title>
    <style>
        @page {
            margin: 1cm;
        }

        body {
            font-family: sans-serif;
            font-size: 9pt;
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
        <h3>LAPORAN PIUTANG</h3>
        <p>Periode: {{ Carbon::parse($start)->translatedFormat('d F Y') }} s/d
            {{ Carbon::parse($end)->translatedFormat('d F Y') }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30%;">Perusahaan Penjamin</th>
                <th style="width: 17%;">Piutang</th>
                <th style="width: 17%;">Potongan</th>
                <th style="width: 17%;">Adm Bank</th>
                <th style="width: 19%;">Total Dibayar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    <td>{{ $item->nama_perusahaan }}</td>
                    <td>
                        <div class="curr-cell">
                            <span class="curr-rp">Rp</span>
                            <span class="curr-val">{{ number_format($item->total_piutang, 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="curr-cell">
                            <span class="curr-rp">Rp</span>
                            <span class="curr-val">{{ number_format($item->total_potongan, 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="curr-cell">
                            <span class="curr-rp">Rp</span>
                            <span class="curr-val">{{ number_format($item->total_adm_bank, 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td class="font-bold">
                        <div class="curr-cell">
                            <span class="curr-rp">Rp</span>
                            <span class="curr-val">{{ number_format($item->total_dibayar, 2, ',', '.') }}</span>
                        </div>
                    </td>
                </tr>
            @endforeach
            <tr style="background:#f1f5f9; font-weight:bold;">
                <td class="text-center">GRAND TOTAL</td>
                <td>
                    <div class="curr-cell">
                        <span class="curr-rp">Rp</span>
                        <span class="curr-val">{{ number_format($totals->piutang, 2, ',', '.') }}</span>
                    </div>
                </td>
                <td>
                    <div class="curr-cell">
                        <span class="curr-rp">Rp</span>
                        <span class="curr-val">{{ number_format($totals->potongan, 2, ',', '.') }}</span>
                    </div>
                </td>
                <td>
                    <div class="curr-cell">
                        <span class="curr-rp">Rp</span>
                        <span class="curr-val">{{ number_format($totals->adm_bank, 2, ',', '.') }}</span>
                    </div>
                </td>
                <td>
                    <div class="curr-cell">
                        <span class="curr-rp">Rp</span>
                        <span class="curr-val">{{ number_format($totals->dibayar, 2, ',', '.') }}</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>