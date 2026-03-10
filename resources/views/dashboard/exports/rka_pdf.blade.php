<!DOCTYPE html>
<html>

<head>
    <title>LAPORAN RKA {{ $tahun }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 14px;
            text-transform: uppercase;
            color: #000;
        }

        .header h2 {
            margin: 5px 0;
            font-size: 12px;
            font-weight: normal;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 8px 10px;
            line-height: 1.4;
            vertical-align: top;
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

        .footer {
            margin-top: 30px;
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
            font-size: 10px;
        }

        .signature-space {
            height: 60px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>RSJKO ENGKU HAJI DAUD</h1>
        <h2>RENCANA KERJA ANGGARAN (RKA)</h2>
        <h2>TAHUN ANGGARAN {{ $tahun }}</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 20%;">KODE REKENING</th>
                <th>URAIAN</th>
                <th style="width: 20%;">ANGGARAN (RP)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    <td class="{{ $item->level <= 3 ? 'font-bold' : '' }}">{{ $item->kode }}</td>
                    <td style="padding-left: {{ ($item->level - 1) * 15 }}px;"
                        class="{{ $item->level <= 3 ? 'font-bold' : '' }}">
                        {{ $item->nama }}
                    </td>
                    <td class="text-right {{ $item->level <= 3 ? 'font-bold' : '' }}">
                        {{ number_format($item->anggaran, 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach
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





