<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 9pt; color: #333; }
        .table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        .table th { background-color: #f8fafc; border: 1px solid #000; padding: 6px; font-size: 8pt; }
        .table td { border: 1px solid #000; padding: 6px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .header-box { text-align: center; margin-bottom: 20px; }
        .line { border-bottom: 4px solid #000; margin: -10px 0 10px; }
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
                <th style="width: 45%;">Nama Rekening</th>
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
                    <td class="text-right">{{ number_format($item->up,2,',','.') }}</td>
                    <td class="text-right">{{ number_format($item->gu,2,',','.') }}</td>
                    <td class="text-right">{{ number_format($item->ls,2,',','.') }}</td>
                    <td class="text-right">{{ number_format($item->total,2,',','.') }}</td>
                </tr>
            @endforeach
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
