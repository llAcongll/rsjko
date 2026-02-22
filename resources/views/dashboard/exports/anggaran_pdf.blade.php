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
            font-family: Arial, sans-serif;
            font-size: 7pt;
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
            border-bottom: 4px solid #000;
            margin-bottom: 10px;
            margin-top: -10px;
        }

        .line { border-bottom: 4px solid #000; margin: -10px 0 10px; }

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
    <table style="width: 100%; border: none; margin-bottom: 0;">
        <tr>
            <td style="width: 165px; border: none; vertical-align: top;">
                <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w400"
                    style="height: 165px; width: auto; object-fit: contain;">
            </td>
            <td style="border: none; text-align: center; vertical-align: top; padding-right: 165px;">
                <div style="font-size: 14pt; line-height: 1.2;">PEMERINTAH PROVINSI KEPULAUAN RIAU</div>
                <div style="font-size: 13pt; font-weight: bold; line-height: 1.2;">RUMAH SAKIT JIWA DAN KETERGANTUNGAN OBAT</div>
                <div style="font-size: 13pt; font-weight: bold; line-height: 1.2;">ENGKU HAJI DAUD</div>
                <div style="font-size: 8pt; margin-top: 10px; line-height: 1.4;">
                    Jalan Indun Suri – Simpang Busung Nomor. 1 Tanjung Uban Kode Pos 29152<br>
                    Telepon ( 0771 ) 482655, 482796 Faksimile. ( 0771 ) 482795 • Pos-el: rsjkoehd@kepriprov.go.id Laman : www.rsudehd.kepriprov.go.id
                </div>
            </td>
        </tr>
    </table>
    <div class="header-line"></div>

    <div class="report-title">
        <h3>LAPORAN REALISASI ANGGARAN
            {{ $category === 'SEMUA' ? 'PENDAPATAN DAN BELANJA' : ($category === 'PENGELUARAN' ? 'BELANJA' : 'PENDAPATAN') }}
        </h3>
        <p>Periode: {{ Carbon::parse($start)->translatedFormat('d F Y') }} s/d
            {{ Carbon::parse($end)->translatedFormat('d F Y') }}
        </p>
    </div>

    @php
    $tables = [];
    if ($category === 'SEMUA') {
        $tables[] = ['title' => 'PENDAPATAN', 'items' => $data_pendapatan, 'totals' => (object)$sub_totals['pendapatan']];
        $tables[] = ['title' => 'BELANJA (PENGELUARAN)', 'items' => $data_pengeluaran, 'totals' => (object)$sub_totals['pengeluaran']];
    } else {
        $tables[] = ['title' => $category === 'PENGELUARAN' ? 'BELANJA' : 'PENDAPATAN', 'items' => $data, 'totals' => (object)$totals];
    }
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width: 7%; text-align:center;">Kode</th>
                <th style="width: 25%; text-align:center;">Uraian</th>
                <th style="width: 12%; text-align:center;">Target</th>
                <th style="width: 10%; text-align:center;">Real. Lalu</th>
                <th style="width: 10%; text-align:center;">Real. Kini</th>
                <th style="width: 11%; text-align:center;">Real. Total</th>
                <th style="width: 11%; text-align:center;">Selisih</th>
                <th style="width: 5%; text-align:center;">%</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tables as $table)
                @foreach($table['items'] as $item)
                    @php 
                        $item = (object)$item;
                        $isBold = $item->level < 5; 
                        $isRoot = str_contains($item->nama, 'Rumah Sakit Khusus Jiwa dan Ketergantungan Obat');
                    @endphp
                    <tr style="{{ $isBold ? 'font-weight:bold; background-color:#f8fafc;' : '' }}">
                        <td>{{ $item->kode }}</td>
                        <td>{{ $item->nama }}</td>
                        <td>
                            @if(!$isRoot)
                            <div class="curr-cell">
                                <span class="curr-rp">Rp</span>
                                <span class="curr-val">{{ number_format($item->target, 2, ',', '.') }}</span>
                            </div>
                            @endif
                        </td>
                        <td>
                            @if(!$isRoot)
                            <div class="curr-cell">
                                <span class="curr-rp">Rp</span>
                                <span class="curr-val">{{ number_format($item->realisasi_lalu, 2, ',', '.') }}</span>
                            </div>
                            @endif
                        </td>
                        <td>
                            @if(!$isRoot)
                            <div class="curr-cell">
                                <span class="curr-rp">Rp</span>
                                <span class="curr-val">{{ number_format($item->realisasi_kini, 2, ',', '.') }}</span>
                            </div>
                            @endif
                        </td>
                        <td>
                            @if(!$isRoot)
                            <div class="curr-cell">
                                <span class="curr-rp">Rp</span>
                                <span class="curr-val">{{ number_format($item->realisasi_total, 2, ',', '.') }}</span>
                            </div>
                            @endif
                        </td>
                        <td>
                            @if(!$isRoot)
                            <div class="curr-cell">
                                <span class="curr-rp">Rp</span>
                                <span class="curr-val">{{ number_format($item->selisih, 2, ',', '.') }}</span>
                            </div>
                            @endif
                        </td>
                        <td class="text-center">{{ $isRoot ? '' : number_format($item->persen, 2, ',', '.') . '%' }}</td>
                    </tr>
                @endforeach
                <tr style="background:#f1f5f9; font-weight:bold;">
                    <td colspan="2" class="text-center">TOTAL {{ $table['title'] }}</td>
                    <td>
                        <div class="curr-cell">
                            <span class="curr-rp">Rp</span>
                            <span class="curr-val">{{ number_format($table['totals']->target ?? 0, 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="curr-cell">
                            <span class="curr-rp">Rp</span>
                            <span class="curr-val">
                               {{ number_format($table['totals']->real_lalu ?? 0, 2, ',', '.') }}
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="curr-cell">
                            <span class="curr-rp">Rp</span>
                            <span class="curr-val">
                               {{ number_format($table['totals']->real_kini ?? 0, 2, ',', '.') }}
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="curr-cell">
                            <span class="curr-rp">Rp</span>
                            <span class="curr-val">{{ number_format($table['totals']->real ?? 0, 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="curr-cell">
                            <span class="curr-rp">Rp</span>
                            <span class="curr-val">{{ number_format(($table['totals']->target ?? 0) - ($table['totals']->real ?? 0), 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td class="text-center">{{ number_format($table['totals']->persen ?? 0, 2, ',', '.') }}%</td>
                </tr>
            @endforeach

            @if($category === 'SEMUA')
                <tr style="background:#e2e8f0; font-weight:bold; font-size: 8pt;">
                    <td colspan="2" class="text-center">SURPLUS / (DEFISIT)</td>
                    <td style="width: 15%;">
                        <div class="curr-cell">
                            <span class="curr-rp">Rp</span>
                            <span class="curr-val">{{ number_format($totals['target'], 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td style="width: 15%;">
                        <div class="curr-cell">
                            <span class="curr-rp">Rp</span>
                            <span class="curr-val">{{ number_format($totals['realisasi_lalu'], 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td style="width: 15%;">
                        <div class="curr-cell">
                            <span class="curr-rp">Rp</span>
                            <span class="curr-val">{{ number_format($totals['realisasi_kini'], 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td style="width: 15%;">
                        <div class="curr-cell">
                            <span class="curr-rp">Rp</span>
                            <span class="curr-val">{{ number_format($totals['realisasi_total'], 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td style="width: 15%;">
                        <div class="curr-cell">
                            <span class="curr-rp">Rp</span>
                            <span class="curr-val">{{ number_format($totals['target'] - $totals['realisasi_total'], 2, ',', '.') }}</span>
                        </div>
                    </td>
                    <td style="width: 5%;" class="text-center">{{ number_format($totals['persen'], 2, ',', '.') }}%</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table style="width: 100%; border: none; margin-top: 50px;">
        <tr>
            <td style="width: 33%; border: none; text-align: center; vertical-align: top;">
                @if($ptKiri)
                    <p style="margin: 0; min-height: 1.25em;">&nbsp;</p>
                    <p style="margin: 0;">{{ $ptKiri->jabatan }}</p>
                    <div style="height: 60px;"></div>
                    <p style="margin: 0;">{{ $ptKiri->nama }}</p>
                    <p style="margin: 0;">NIP. {{ $ptKiri->nip }}</p>
                @endif
            </td>
            <td style="width: 34%; border: none; text-align: center; vertical-align: top;">
                @if($ptTengah)
                    <p style="margin: 0; min-height: 1.25em;">&nbsp;</p>
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
</body>

</html>