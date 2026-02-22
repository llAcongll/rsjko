<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        .table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
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

        .text-left {
            text-align: left;
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
    <div style="text-align: center; font-size: 14pt; font-weight: bold;">LAPORAN DOKUMEN PELAKSANAAN ANGGARAN (DPA)
    </div>
    <div style="text-align: center; font-size: 11pt; margin-bottom: 20px;">Tahun Anggaran: {{ $tahun }}</div>

    <table class="table">
        <thead>
            <tr>
                <th class="text-center">Kode Rekening</th>
                <th class="text-center">Uraian Rekening / Komponen</th>
                <th class="text-center">Volume</th>
                <th class="text-center">Satuan</th>
                <th class="text-center">Tarif Satuan</th>
                <th class="text-center">Total</th>
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
                    $indent = ($item->level - 1) * 2; // Simple space indentation for Excel
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
                    <td class="text-right">{{ $isHeader ? '' : 'Rp ' . number_format($item->tarif, 2, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ 'Rp ' . number_format($item->subtotal, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="font-bold" style="background-color: #f2f2f2;">
                <td colspan="5" style="text-align: center; padding: 10px;">TOTAL ANGGARAN DPA</td>
                <td class="text-right" style="padding: 10px;">{{ 'Rp ' . number_format($grandTotal, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <br><br>
    <table style="border: none; width: 100%;">
        <tr>
            <td align="center" style="width: 30%;">
                @if($ptKiri)
                    <br>
                    {{ $ptKiri->jabatan }}<br><br><br><br>
                    {{ $ptKiri->nama }}<br>
                    NIP. {{ $ptKiri->nip }}
                @endif
            </td>
            <td style="width: 30%;"></td>
            <td align="center" style="width: 40%;">
                Tanjung Uban, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                @if($ptKanan)
                    {{ $ptKanan->jabatan }}<br><br><br><br>
                    {{ $ptKanan->nama }}<br>
                    NIP. {{ $ptKanan->nip }}
                @else
                    &nbsp;<br>
                    &nbsp;<br><br><br><br>
                    ...................................<br>
                    NIP. ...................................
                @endif
            </td>
        </tr>
    </table>
</body>

</html>