<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            color: #333;
        }

        .table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }

        .table th {
            background-color: #f8fafc;
            border: 1px solid #000;
            padding: 4px;
            font-size: 7pt;
        }

        .table td {
            border: 1px solid #000;
            padding: 4px;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .header-box {
            text-align: center;
            margin-bottom: 20px;
        }

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
        <div style="font-size: 11pt; font-weight: bold; text-decoration: underline; margin-top: 5px;">LAPORAN DOKUMEN
            PELAKSANAAN ANGGARAN (DPA)</div>
        <div style="font-size: 8pt; margin-top: 2px;">Tahun Anggaran: {{ $tahun }}</div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th class="text-center" style="width: 15%;">Kode Rekening</th>
                <th class="text-center">Uraian Rekening / Komponen</th>
                <th class="text-center" style="width: 8%;">Vol</th>
                <th class="text-center" style="width: 10%;">Satuan</th>
                <th class="text-center" style="width: 15%;">Tarif Satuan</th>
                <th class="text-center" style="width: 15%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($data as $item)
                @php 
                    $isHeader = ($item->tipe === 'header');
                    if ($isHeader && $item->level === 1) {
                        $grandTotal += (float)$item->subtotal;
                    }
                    $indent = ($item->level - 1) * 12;
                @endphp
                <tr style="{{ $isHeader ? 'background-color: #f8fafc; font-weight: bold;' : '' }}">
                    <td class="text-left">
                        {{ $item->kode_rekening }}
                    </td>
                    <td class="text-left">
                        {{ $item->uraian }}
                    </td>
                    <td class="text-center">{{ $isHeader ? '' : (float)$item->volume }}</td>
                    <td class="text-center">{{ $isHeader ? '' : $item->satuan }}</td>
                    <td class="text-right">
                        @if(!$isHeader)
                        <table style="width: 100%; border: none;">
                            <tr>
                                <td style="border: none; text-align: left; padding: 0;">Rp</td>
                                <td style="border: none; text-align: right; padding: 0;">{{ number_format($item->tarif, 2, ',', '.') }}</td>
                            </tr>
                        </table>
                        @endif
                    </td>
                    <td class="text-right font-bold">
                        <table style="width: 100%; border: none;">
                            <tr>
                                <td style="border: none; text-align: left; padding: 0;">Rp</td>
                                <td style="border: none; text-align: right; padding: 0;">{{ number_format($item->subtotal, 2, ',', '.') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            @endforeach
            <tr class="font-bold" style="background-color: #f1f5f9;">
                <td colspan="5" style="text-align: center; padding: 8px; font-size: 9pt;">TOTAL ANGGARAN DPA</td>
                <td class="text-right" style="padding: 8px; font-size: 9pt;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="border: none; text-align: left; padding: 0;">Rp</td>
                            <td style="border: none; text-align: right; padding: 0;">{{ number_format($grandTotal, 2, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%; border: none; margin-top: 30px;">
        <tr>
            <td style="width: 33%; border: none; text-align: center; vertical-align: top;">
                @if($ptKiri)
                    <p style="margin: 0; min-height: 1.25em;">&nbsp;</p>
                    <p style="margin: 0;">{{ $ptKiri->jabatan }}</p>
                    <div style="height: 50px;"></div>
                    <p style="margin: 0;">{{ $ptKiri->nama }}</p>
                    <p style="margin: 0;">NIP. {{ $ptKiri->nip }}</p>
                @endif
            </td>
            <td style="width: 34%; border: none; text-align: center; vertical-align: top;">
                @if($ptTengah)
                    <p style="margin: 0; min-height: 1.25em;">&nbsp;</p>
                    <p style="margin: 0;">{{ $ptTengah->jabatan }}</p>
                    <div style="height: 50px;"></div>
                    <p style="margin: 0;">{{ $ptTengah->nama }}</p>
                    <p style="margin: 0;">NIP. {{ $ptTengah->nip }}</p>
                @endif
            </td>
            <td style="width: 33%; border: none; text-align: center; vertical-align: top;">
                @if($ptKanan)
                    <p style="margin: 0;">Tanjung Uban, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p style="margin: 0;">{{ $ptKanan->jabatan }}</p>
                    <div style="height: 50px;"></div>
                    <p style="margin: 0;">{{ $ptKanan->nama }}</p>
                    <p style="margin: 0;">NIP. {{ $ptKanan->nip }}</p>
                @else
                    <p style="margin: 0;">Tanjung Uban, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p style="margin: 0;">&nbsp;</p>
                    <p style="margin: 0;">&nbsp;</p>
                    <div style="height: 50px;"></div>
                    <p style="margin: 0;">...................................</p>
                    <p style="margin: 0;">NIP. ...................................</p>
                @endif
            </td>
        </tr>
    </table>
</body>

</html>