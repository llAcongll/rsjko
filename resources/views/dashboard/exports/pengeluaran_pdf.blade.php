<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 9pt; color: #333; }
        .table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        .table th { background-color: #f8fafc; border: 1px solid #000; padding: 6px; font-size: 8pt; }
        .table td { border: 1px solid #000; padding: 6px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .header-box { text-align: center; margin-bottom: 20px; }
        .line { border-bottom: 2px solid #000; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="header-box">
        <div style="font-size: 12pt;">PEMERINTAH PROVINSI KEPULAUAN RIAU</div>
        <div style="font-size: 11pt; font-weight: bold;">RUMAH SAKIT JIWA DAN KETERGANTUNGAN OBAT ENGKU HAJI DAUD</div>
        <div style="font-size: 8pt;">Jalan Indun Suri – Simpang Busung Nomor 1 Tanjung Uban Kode Pos 29152</div>
        <div class="line"></div>
        <div style="font-size: 12pt; font-weight: bold; text-decoration: underline; margin-top: 10px;">LAPORAN REALISASI BELANJA</div>
        <div style="font-size: 9pt; margin-top: 5px;">Periode: {{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}</div>
    </div>

    <h4 style="border-left: 4px solid #10b981; padding-left: 10px; margin-bottom: 10px;">1. RINGKASAN PER KATEGORI</h4>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 50%;">Kategori Belanja</th>
                <th style="width: 20%;">Jumlah Transaksi</th>
                <th style="width: 30%;">Total Nominal</th>
            </tr>
        </thead>
        <tbody>
            @php $gCount = 0; $gTotal = 0; @endphp
            @foreach(['PEGAWAI' => 'Belanja Pegawai', 'BARANG_JASA' => 'Belanja Barang & Jasa', 'MODAL' => 'Belanja Modal'] as $key => $label)
                @php 
                    $item = ((array)$summary)[$key] ?? (object)['count' => 0, 'total' => 0];
                    $gCount += $item->count;
                    $gTotal += $item->total;
                @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td class="text-center">{{ number_format($item->count,0,',','.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->total,2,',','.') }}</td>
                </tr>
            @endforeach
            <tr class="font-bold" style="background-color: #f1f5f9;">
                <td class="text-center">TOTAL KESELURUHAN</td>
                <td class="text-center">{{ number_format($gCount,0,',','.') }}</td>
                <td class="text-right">Rp {{ number_format($gTotal,2,',','.') }}</td>
            </tr>
        </tbody>
    </table>

    <h4 style="border-left: 4px solid #3b82f6; padding-left: 10px; margin-top: 30px; margin-bottom: 10px;">2. RINCIAN PER KODE REKENING</h4>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 15%;">Kode Rekening</th>
                <th style="width: 25%;">Nama Rekening</th>
                <th style="width: 20%;">Uraian Belanja</th>
                <th style="width: 10%; text-align: right;">UP</th>
                <th style="width: 10%; text-align: right;">GU</th>
                <th style="width: 10%; text-align: right;">LS</th>
                <th style="width: 10%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    <td class="text-center"><code>{{ $item->kode }}</code></td>
                    <td>{{ $item->nama }}</td>
                    <td>{{ $item->uraian }}</td>
                    <td class="text-right">{{ number_format($item->up,2,',','.') }}</td>
                    <td class="text-right">{{ number_format($item->gu,2,',','.') }}</td>
                    <td class="text-right">{{ number_format($item->ls,2,',','.') }}</td>
                    <td class="text-right">{{ number_format($item->total,2,',','.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
