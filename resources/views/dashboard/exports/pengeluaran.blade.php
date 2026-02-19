<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
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
    </style>
</head>

<body>
    <table class="table" style="border: none; margin-bottom: 20px;">
        <tr>
            <td colspan="4" class="text-center" style="border: none; font-size: 14pt;">PEMERINTAH PROVINSI KEPULAUAN
                RIAU</td>
        </tr>
        <tr>
            <td colspan="4" class="text-center" style="border: none; font-size: 13pt; font-weight: bold;">RUMAH SAKIT
                JIWA DAN KETERGANTUNGAN OBAT ENGKU HAJI DAUD</td>
        </tr>
        <tr>
            <td colspan="4" style="border-bottom: 2px solid #000; height: 10px;"></td>
        </tr>
    </table>

    <div style="text-align: center; font-size: 14pt; font-weight: bold; margin-top: 20px;">LAPORAN REALISASI BELANJA
    </div>
    <div style="text-align: center; font-size: 11pt; margin-bottom: 20px;">Periode:
        {{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }} s/d
        {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}</div>

    <table class="table">
        <thead>
            <tr>
                <th colspan="4" style="background-color: #d9ead3; text-align: center;">1. RINGKASAN PER KATEGORI</th>
            </tr>
            <tr>
                <th style="text-align: center;">Kategori Belanja</th>
                <th style="text-align: center;">Jumlah Transaksi</th>
                <th colspan="2" style="text-align: center;">Total Nominal</th>
            </tr>
        </thead>
        <tbody>
            @php $gCount = 0;
            $gTotal = 0; @endphp
            @foreach(['PEGAWAI' => 'Belanja Pegawai', 'BARANG_JASA' => 'Belanja Barang & Jasa', 'MODAL' => 'Belanja Modal'] as $key => $label)
                @php 
                                    $item = ((array) $summary)[$key] ?? (object) ['count' => 0, 'total' => 0];
                    $gCount += $item->count;
                    $gTotal += $item->total;
                @endphp
                <tr>
                        <td>{{ $label }}</td>
                        <td class="text-center">{{ number_format($item->count, 0, ',', '.') }}</td>
                        <td colspan="2" class="text-right">{{ number_format($item->total, 2, ',', '.') }}</td>
                    </tr>
            @endforeach
            <tr class="font-bold" style="background-color: #f2f2f2;">
                
                <td class="text-center">TOTAL KESELURUHAN</td>

                            
                <td class="text-center">{{ number_format($gCount, 0, ',', '.') }}</td>

                            <td colspan="2" class="text-right">{{ number_format($gTotal, 2, ',', '.') }}</td>
            </tr>
            <tr><td colspan="4" style="border: none; height: 30px;"></td></tr>
            <tr><th colspan="4" style="background-color: #cfe2f3; text-align: center;">2. RINCIAN PER KODE REKENING</th></tr>
            <tr>
                <th style="text-align: center;">Kode Rekening</th>
                    <th style="text-align: center;">Nama Rekening</th>
                <th style="text-align: center;">Transaksi</th>
                <th style="text-align: center;">Total Pengeluaran</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $item)
                <tr>
                    <td class="text-center">{{ $item->kode }}</td>
                        <td>{{ $item->nama }}</td>
                        <td class="text-center">{{ $item->count }}</td>
                        <td class="text-right">{{ number_format($item->total, 2, ',', '.') }}</td>

             </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
