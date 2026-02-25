<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>SPJ - {{ $spj->spj_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
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
            padding: 6px;
            font-size: 8pt;
            text-align: center;
        }

        .table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
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

        .line {
            border-bottom: 4px solid #000;
            margin: 10px 0;
        }

        .header-info td {
            border: none;
            padding: 2px 0;
        }
    </style>
</head>

<body>
    <table style="width: 100%; border: none; margin-bottom: 0;">
        <tr>
            <td style="width: 120px; border: none; vertical-align: top;">
                <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w400"
                    style="height: 100px; width: auto; object-fit: contain;">
            </td>
            <td style="border: none; text-align: center; vertical-align: top; padding-right: 120px;">
                <div style="font-size: 14pt; line-height: 1.2;">PEMERINTAH PROVINSI KEPULAUAN RIAU</div>
                <div style="font-size: 13pt; font-weight: bold; line-height: 1.2;">RUMAH SAKIT JIWA DAN KETERGANTUNGAN
                    OBAT</div>
                <div style="font-size: 13pt; font-weight: bold; line-height: 1.2;">ENGKU HAJI DAUD</div>
                <div style="font-size: 8pt; margin-top: 5px;">
                    Jalan Indun Suri – Simpang Busung Nomor. 1 Tanjung Uban Kode Pos 29152<br>
                    Telepon ( 0771 ) 482655, 482796 Faksimile. ( 0771 ) 482795
                </div>
            </td>
        </tr>
    </table>
    <div class="line"></div>

    <div style="text-align: center; margin-top: 10px; margin-bottom: 20px;">
        <div style="font-size: 11pt; font-weight: bold; text-decoration: underline;">SURAT PERTANGGUNGJAWABAN (SPJ)
            PENGELUARAN</div>
        <div style="font-size: 10pt; margin-top: 5px;">Nomor: {{ $spj->spj_number }}</div>
    </div>

    <table class="header-info" style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td style="width: 150px;">Tanggal SPJ</td>
            <td style="width: 10px;">:</td>
            <td>{{ \Carbon\Carbon::parse($spj->spj_date)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td>Bendahara Pengeluaran</td>
            <td>:</td>
            <td>{{ $spj->bendahara->name }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td>:</td>
            <td><span style="font-weight: bold;">{{ $spj->status }}</span></td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 20%;">Kode Rekening</th>
                <th style="width: 35%;">Uraian / Keterangan</th>
                <th style="width: 25%;">Nominal (Bruto)</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($spj->items as $index => $item)
                @php $total += $item->expenditure->gross_value; @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item->expenditure->spending_date)->format('d/m/Y') }}
                    </td>
                    <td class="text-center"><code>{{ $item->expenditure->kodeRekening->kode }}</code></td>
                    <td>
                        {{ $item->expenditure->description }}
                        @if($item->expenditure->vendor)
                            <br><small>Vendor: {{ $item->expenditure->vendor }}</small>
                        @endif
                        @if($item->expenditure->proof_number)
                            <br><small>No. Bukti: {{ $item->expenditure->proof_number }}</small>
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($item->expenditure->gross_value, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background-color: #f1f5f9;">
                <td colspan="4" class="text-center">TOTAL PERTANGGUNGJAWABAN</td>
                <td class="text-right">Rp {{ number_format($total, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 30px;">
        <p>Demikian Surat Pertanggungjawaban ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.
        </p>
    </div>

    <table style="width: 100%; border: none; margin-top: 50px;">
        <tr>
            <td style="width: 50%; border: none; text-align: center;">
                <p style="margin-bottom: 60px;">Mengetahui,<br>Pejabat Pelaksana Teknis Kegiatan</p>
                <p style="margin: 0;">( ............................................... )</p>
                <p style="margin: 0;">NIP. ...............................................</p>
            </td>
            <td style="width: 50%; border: none; text-align: center;">
                <p style="margin-bottom: 60px;">Tanjung Uban,
                    {{ \Carbon\Carbon::parse($spj->spj_date)->translatedFormat('d F Y') }}<br>Bendahara Pengeluaran</p>
                <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $spj->bendahara->name }}</p>
                <p style="margin: 0;">NIP.
                    {{ $spj->bendahara->nip ?? '...............................................' }}</p>
            </td>
        </tr>
    </table>
</body>

</html>