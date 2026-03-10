<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Rekening Koran - {{ $tahun }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            color: #333;
            line-height: 1.4;
        }

        .table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
        }

        .table th {
            background-color: #f2f2f2;
            border: 1px solid #000;
            padding: 8px 5px;
            font-size: 9pt;
            text-transform: uppercase;
        }

        .table td {
            border: 1px solid #000;
            padding: 6px 5px;
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
            border-bottom: 3px solid #000;
            margin: 5px 0 15px;
        }

        .header-logo {
            height: 100px;
            width: auto;
        }

        @page {
            margin: 1.5cm;
        }
    </style>
</head>

<body>
    <div style="display: flex; align-items: center; width: 100%; min-height: 160px; margin-bottom: 0;">
        <div style="width: 140px; display: flex; justify-content: flex-start;">
            <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w400"
                style="height: 140px; width: auto; object-fit: contain;">
        </div>
        <div style="flex: 1; text-align: center; padding: 0 10px;">
            <h1 style="margin: 0; padding: 0; font-size: 15pt; font-weight: normal; color: #000; line-height: 1.2;">
                PEMERINTAH PROVINSI KEPULAUAN RIAU</h1>
            <h2 style="margin: 0; padding: 0; font-size: 16pt; font-weight: bold; color: #000; line-height: 1.2;">
                RUMAH SAKIT JIWA DAN KETERGANTUNGAN OBAT</h2>
            <h2 style="margin: 0; padding: 0; font-size: 16pt; font-weight: bold; color: #000; line-height: 1.2;">
                ENGKU HAJI DAUD</h2>
            <div style="line-height: 1.4; margin-top: 5px; font-size: 9pt; font-weight: normal; color: #000;">
                Jalan Indun Suri - Simpang Busung Nomor. 1 Tanjung Uban Kode Pos 29152<br>
                Telepon ( 0771 ) 482655, 482796 Faksimile. ( 0771 ) 482795<br>
                Pos-el: rsjkoehd@kepriprov.go.id Laman : www.rsudehd.kepriprov.go.id
            </div>
        </div>
        <div style="width: 140px;"></div>
    </div>
    <div style="height: 4px; background: #000; margin: 5px 0 20px;"></div>

    <div style="text-align: center; margin-bottom: 20px;">
        <div style="font-size: 12pt; font-weight: bold; text-decoration: underline;">LAPORAN REKENING KORAN PENDAPATAN
        </div>
        <div style="font-size: 10pt; margin-top: 5px;">Bank: {{ $bank }}</div>
        @if($start || $end)
            <div style="font-size: 9pt;">Periode:
                {{ $start ? \Carbon\Carbon::parse($start)->translatedFormat('d F Y') : 'Awal' }} s/d
                {{ $end ? \Carbon\Carbon::parse($end)->translatedFormat('d F Y') : 'Akhir' }}
            </div>
        @endif
    </div>

    <div
        style="margin-bottom: 20px; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; width: fit-content;">
        <span style="font-weight: bold; color: #475569;">SALDO AWAL :</span>
        <span style="font-weight: bold; font-size: 11pt; margin-left: 10px;">Rp
            {{ number_format($saldoAwal, 2, ',', '.') }}</span>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th width="30">No</th>
                <th width="80">Tanggal</th>
                <th>Keterangan / Uraian</th>
                <th width="40">C/D</th>
                <th width="110">Jumlah (Rp)</th>
                <th width="110">Saldo (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d/m/Y') }}</td>
                    <td>{{ $item->keterangan }}</td>
                    <td class="text-center" style="color: {{ $item->cd === 'C' ? 'green' : 'red' }};">{{ $item->cd }}</td>
                    <td class="text-right">{{ number_format($item->jumlah, 2, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($item->saldo_running, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="5" class="text-right">SALDO AKHIR PERIODE</td>
                <td class="text-right">{{ number_format($items->last()->saldo_running ?? $saldoAwal, 2, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- SIGNATURE AREA --}}
    <div style="margin-top: 50px; display: flex; justify-content: space-between; align-items: flex-start; gap: 15px;">
        {{-- LEFT SIGNATORY SLOT --}}
        <div style="width: 32%; text-align: center; {{ !$ptKiri ? 'visibility: hidden;' : '' }}">
            <p style="margin: 0; min-height: 1.25em;">&nbsp;</p>
            <p style="margin: 0; min-height: 1.25em;">{{ $ptKiri ? $ptKiri->jabatan : '' }}</p>
            <div style="height: 60px;"></div>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">
                {{ $ptKiri ? '(' . $ptKiri->nama . ')' : '( ......................................... )' }}
            </p>
            <p style="margin: 0;">NIP. {{ $ptKiri ? $ptKiri->nip : '.........................................' }}</p>
        </div>

        {{-- MIDDLE SIGNATORY SLOT --}}
        <div style="width: 32%; text-align: center; {{ !$ptTengah ? 'visibility: hidden;' : '' }}">
            <p style="margin: 0; min-height: 1.25em;">&nbsp;</p>
            <p style="margin: 0; min-height: 1.25em;">{{ $ptTengah ? $ptTengah->jabatan : '' }}</p>
            <div style="height: 60px;"></div>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">
                {{ $ptTengah ? '(' . $ptTengah->nama . ')' : '( ......................................... )' }}
            </p>
            <p style="margin: 0;">NIP. {{ $ptTengah ? $ptTengah->nip : '.........................................' }}
            </p>
        </div>

        {{-- RIGHT SIGNATORY SLOT --}}
        <div style="width: 32%; text-align: center;">
            <p style="margin: 0;">Tanjung Uban, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p style="margin: 0; min-height: 1.25em;">{{ $ptKanan ? $ptKanan->jabatan : 'Bendahara Penerimaan' }}</p>
            <div style="height: 60px;"></div>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">
                {{ $ptKanan ? '(' . $ptKanan->nama . ')' : '( ......................................... )' }}
            </p>
            <p style="margin: 0;">NIP. {{ $ptKanan ? $ptKanan->nip : '.........................................' }}</p>
        </div>
    </div>
</body>

</html>





