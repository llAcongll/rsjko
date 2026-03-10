<!DOCTYPE html>
<html>

<head>
    <title>LAPORAN RBA {{ $tahun }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
            color: #000;
        }

        .header h2 {
            margin: 5px 0;
            font-size: 14px;
            font-weight: normal;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 12px 15px;
            line-height: 1.5;
        }

        th {
            background: #f2f2f2;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .bg-gray {
            background-color: #f9f9f9;
        }

        .total-row {
            background-color: #e2e8f0;
            font-weight: bold;
            font-size: 13px;
        }

        .footer {
            margin-top: 40px;
        }

        .footer-table {
            width: 100%;
            border: none;
        }

        .footer-table td {
            border: none;
            text-align: center;
            width: 33%;
            vertical-align: top;
            padding: 10px;
        }

        .signature-space {
            height: 70px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>RSJKO ENGKU HAJI DAUD</h1>
        <h2>RENCANA BISNIS ANGGARAN (RBA) BLUD</h2>
        <h2>TAHUN ANGGARAN {{ $tahun }}</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>URAIAN</th>
                <th style="width: 30%;">JUMLAH (RP)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="bg-gray font-bold">
                <td>I. PENDAPATAN BLUD</td>
                <td class="text-right">{{ number_format($summary['pendapatan'], 2, ',', '.') }}</td>
            </tr>
            @foreach($breakdown->where('category', 'PENDAPATAN') as $item)
                <tr>
                    <td style="padding-left: {{ ($item->level) * 15 }}px;">{{ $item->nama }}</td>
                    <td class="text-right">{{ number_format($item->anggaran, 2, ',', '.') }}</td>
                </tr>
            @endforeach

            <tr class="bg-gray font-bold">
                <td>II. BELANJA BLUD</td>
                <td class="text-right">{{ number_format($summary['belanja'], 2, ',', '.') }}</td>
            </tr>
            @foreach($breakdown->where('category', 'PENGELUARAN') as $item)
                <tr>
                    <td style="padding-left: {{ ($item->level) * 15 }}px;">{{ $item->nama }}</td>
                    <td class="text-right">{{ number_format($item->anggaran, 2, ',', '.') }}</td>
                </tr>
            @endforeach

            <tr class="total-row">
                <td>SURPLUS / (DEFISIT)</td>
                <td class="text-right">{{ number_format($summary['surplus_defisit'], 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>
                    @if(isset($ptKiri))
                        {{ $ptKiri->jabatan }}<br><br>
                        <div class="signature-space"></div>
                        <b>{{ $ptKiri->nama }}</b><br>
                        NIP. {{ $ptKiri->nip }}
                    @endif
                </td>
                <td>
                    @if(isset($ptTengah))
                        {{ $ptTengah->jabatan }}<br><br>
                        <div class="signature-space"></div>
                        <b>{{ $ptTengah->nama }}</b><br>
                        NIP. {{ $ptTengah->nip }}
                    @endif
                </td>
                <td>
                    @if(isset($ptKanan))
                        Tanjunguban, {{ Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                        {{ $ptKanan->jabatan }}<br><br>
                        <div class="signature-space"></div>
                        <b>{{ $ptKanan->nama }}</b><br>
                        NIP. {{ $ptKanan->nip }}
                    @endif
                </td>
            </tr>
        </table>
    </div>
</body>

</html>





