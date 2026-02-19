@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>

<head>
    <title>Laporan Realisasi Anggaran</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 9pt;
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
    </style>
</head>

<body>
    <div style="text-align: center;">
        <h3>LAPORAN REALISASI ANGGARAN
            {{ $category === 'SEMUA' ? 'PENDAPATAN DAN BELANJA' : ($category === 'PENGELUARAN' ? 'BELANJA' : 'PENDAPATAN') }}
        </h3>
        <p>Periode: {{ Carbon::parse($start)->translatedFormat('d F Y') }} s/d
            {{ Carbon::parse($end)->translatedFormat('d F Y') }}
        </p>
    </div>

    <table>
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th>Kode Rekening</th>
                <th>Uraian</th>
                <th>Target Anggaran</th>
                <th>Realisasi (Lalu)</th>
                <th>Realisasi (Kini)</th>
                <th>Realisasi (Total)</th>
                <th>Selisih</th>
                <th>%</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                @php 
                    $isBold = $item->level < 5; 
                    $isRoot = str_contains($item->nama, 'Rumah Sakit Khusus Jiwa dan Ketergantungan Obat');
                @endphp
                <tr style="{{ $isBold ? 'font-weight:bold; background-color:#f8fafc;' : '' }}">
                    <td>{{ $item->kode }}</td>
                    <td>{{ $item->nama }}</td>
                    <td class="text-right">{{ $isRoot ? '' : 'Rp ' . number_format($item->target, 2, ',', '.') }}</td>
                    <td class="text-right">{{ $isRoot ? '' : 'Rp ' . number_format($item->realisasi_lalu, 2, ',', '.') }}</td>
                    <td class="text-right">{{ $isRoot ? '' : 'Rp ' . number_format($item->realisasi_kini, 2, ',', '.') }}</td>
                    <td class="text-right">{{ $isRoot ? '' : 'Rp ' . number_format($item->realisasi_total, 2, ',', '.') }}</td>
                    <td class="text-right">{{ $isRoot ? '' : 'Rp ' . number_format($item->selisih, 2, ',', '.') }}</td>
                    <td class="text-center">{{ $isRoot ? '' : number_format($item->persen, 2, ',', '.') . '%' }}</td>
                </tr>
            @endforeach
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="2" class="text-center">{{ $category === 'SEMUA' ? 'SURPLUS / (DEFISIT)' : 'TOTAL' }}</td>
                <td class="text-right">Rp {{ number_format($totals->target, 2, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totals->realisasi_lalu, 2, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totals->realisasi_kini, 2, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totals->realisasi_total, 2, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totals->target - $totals->realisasi_total, 2, ',', '.') }}
                </td>
                <td class="text-center">{{ number_format($totals->persen, 2, ',', '.') }}%</td>
            </tr>
        </tbody>
    </table>
</body>

</html>