@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>
<head>
    <style>
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        table { border-collapse: collapse; width: 100%; border: 1px solid #000; }
        td, th { border: 1px solid #000; padding: 8px; font-family: sans-serif; font-size: 10pt; vertical-align: top; }
        .bab-title { font-weight: bold; background-color: #f2f2f2; font-size: 11pt; padding: 10px; border: 1px solid #000; }
        .content-box { padding: 15px; border: 1px solid #000; min-height: 100px; margin-bottom: 20px; white-space: pre-wrap; font-family: sans-serif; }
        .title-container { text-align: center; margin-bottom: 30px; font-family: sans-serif; }
    </style>
</head>
<body>
    <div class="title-container">
        <div style="font-size: 11pt; text-transform: uppercase;">RSJKO ENGKU HAJI DAUD</div>
        <div style="font-size: 13pt; font-weight: bold; text-decoration: underline; margin: 5px 0;">CATATAN ATAS LAPORAN KEUANGAN (CaLK)</div>
        <div style="font-size: 11pt; font-weight: bold;">PER {{ strtoupper($period['end_date_formatted']) }}</div>
    </div>

    @foreach ([
        'BAB_I' => 'BAB I - INFORMASI UMUM',
        'BAB_II' => 'BAB II - KEBIJAKAN AKUNTANSI',
        'BAB_III' => 'BAB III - PENJELASAN LAPORAN REALISASI ANGGARAN (LRA)',
        'BAB_IV' => 'BAB IV - PENJELASAN LAPORAN OPERASIONAL (LO)',
        'BAB_V' => 'BAB V - PENJELASAN NERACA',
        'BAB_VI' => 'BAB VI - PENJELASAN LAPORAN ARUS KAS (LAK)',
        'BAB_VII' => 'BAB VII - PENJELASAN LAPORAN PERUBAHAN EKUITAS (LPE)'
    ] as $key => $title)
        <div class="bab-title">{{ $title }}</div>
        <div class="content-box">{{ $sections[$key] ?? 'Tidak ada penjelasan.' }}</div>
        
        @if($key === 'BAB_III' && isset($reports['lra']))
            <table>
                <thead>
                    <tr style="background-color: #e2e8f0;">
                        <th>URAIAN</th>
                        <th class="text-right">ANGGARAN (Rp)</th>
                        <th class="text-right">REALISASI (Rp)</th>
                        <th class="text-right">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports['lra']['data'] ?? [] as $row)
                        @if($row['level'] <= 3)
                        <tr>
                            <td style="padding-left: {{ ($row['level'] - 1) * 20 }}px;">{{ $row['nama'] }}</td>
                            <td class="text-right">{{ number_format($row['anggaran'], 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($row['realisasi_berjalan'], 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($row['persen_berjalan'], 2, ',', '.') }}%</td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
            <br>
        @endif

        @if($key === 'BAB_IV' && isset($reports['lo']))
            <table>
                <thead>
                    <tr style="background-color: #e2e8f0;">
                        <th>URAIAN</th>
                        <th class="text-right">JUMLAH (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports['lo']['details'] ?? [] as $group)
                        <tr class="font-bold"><td colspan="2">{{ $group['category'] }}</td></tr>
                        @foreach($group['items'] as $item)
                        <tr>
                            <td>{{ $item['kode_rekening'] }} - {{ $item['nama_rekening'] }}</td>
                            <td class="text-right">{{ number_format($item['total'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="font-bold bg-gray">
                            <td>SUBTOTAL {{ $group['category'] }}</td>
                            <td class="text-right">{{ number_format($group['total'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <br>
        @endif
    @endforeach

    <br><br>
    <table style="border: none;">
        <tr>
            <td style="border:none; width:33%; text-align:center;">
                @if($ptKiri)
                    {{ $ptKiri->jabatan }}<br><br><br><br><br>
                    <strong>{{ $ptKiri->nama }}</strong><br>
                    NIP. {{ $ptKiri->nip }}
                @endif
            </td>
            <td style="border:none; width:33%; text-align:center;">
                @if($ptTengah)
                    {{ $ptTengah->jabatan }}<br><br><br><br><br>
                    <strong>{{ $ptTengah->nama }}</strong><br>
                    NIP. {{ $ptTengah->nip }}
                @endif
            </td>
            <td style="border:none; width:33%; text-align:center;">
                @if($ptKanan)
                    Kepulauan Riau, {{ Carbon::now()->locale('id')->translatedFormat('d F Y') }}<br>
                    {{ $ptKanan->jabatan }}<br><br><br><br><br>
                    <strong>{{ $ptKanan->nama }}</strong><br>
                    NIP. {{ $ptKanan->nip }}
                @endif
            </td>
        </tr>
    </table>
</body>
</html>





