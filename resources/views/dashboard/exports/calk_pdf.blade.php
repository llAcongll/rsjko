@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        @page { size: A4; margin: 2cm; }
        body { font-family: sans-serif; font-size: 10pt; line-height: 1.5; color: #333; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .title-container { text-align: center; margin-bottom: 30px; }
        .bab-title { font-weight: bold; font-size: 11pt; margin-top: 25px; margin-bottom: 10px; border-bottom: 1px solid #333; padding-bottom: 5px; text-transform: uppercase; }
        .content { margin-bottom: 20px; white-space: pre-wrap; text-align: justify; }
        table { border-collapse: collapse; width: 100%; margin: 15px 0; }
        td, th { border: 1px solid #333; padding: 8px; font-size: 9pt; }
        .bg-gray { background-color: #f3f4f6; }
        .signature-table { border: none !important; width: 100%; margin-top: 50px; }
        .signature-table td { border: none !important; text-align: center; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="title-container">
        <div style="font-size: 11pt; text-transform: uppercase;">RSJKO ENGKU HAJI DAUD</div>
        <div style="font-size: 13pt; font-weight: bold; text-decoration: underline; margin: 4px 0;">CATATAN ATAS LAPORAN KEUANGAN (CaLK)</div>
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
        <div class="content">{{ $sections[$key] ?? 'Tidak ada penjelasan.' }}</div>
        
        @if($key === 'BAB_III' && isset($reports['lra']))
            <table>
                <thead class="bg-gray font-bold">
                    <tr>
                        <th style="width: 40%">URAIAN</th>
                        <th class="text-right">ANGGARAN (Rp)</th>
                        <th class="text-right">REALISASI (Rp)</th>
                        <th style="width: 10%" class="text-right">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports['lra']['data'] ?? [] as $row)
                        @if($row['level'] <= 3)
                        <tr>
                            <td style="padding-left: {{ ($row['level'] - 1) * 15 }}px;">{{ $row['nama'] }}</td>
                            <td class="text-right">{{ number_format($row['anggaran'], 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($row['realisasi_berjalan'], 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($row['persen_berjalan'], 2, ',', '.') }}%</td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        @endif

        @if($key === 'BAB_IV' && isset($reports['lo']))
            <table>
                <thead class="bg-gray font-bold">
                    <tr>
                        <th>URAIAN</th>
                        <th class="text-right">JUMLAH (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports['lo']['details'] ?? [] as $group)
                        <tr class="font-bold bg-gray"><td colspan="2">{{ $group['category'] }}</td></tr>
                        @foreach($group['items'] as $item)
                        <tr>
                            <td>{{ $item['kode_rekening'] }} - {{ $item['nama_rekening'] }}</td>
                            <td class="text-right">{{ number_format($item['total'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="font-bold">
                            <td>SUBTOTAL {{ $group['category'] }}</td>
                            <td class="text-right">{{ number_format($group['total'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if($key === 'BAB_II')
            <div class="page-break"></div>
        @endif
    @endforeach

    <table class="signature-table">
        <tr>
            <td style="width:33%;">
                @if($ptKiri)
                    {{ $ptKiri->jabatan }}<br><br><br><br><br>
                    <strong>{{ $ptKiri->nama }}</strong><br>
                    NIP. {{ $ptKiri->nip }}
                @endif
            </td>
            <td style="width:33%;">
                @if($ptTengah)
                    {{ $ptTengah->jabatan }}<br><br><br><br><br>
                    <strong>{{ $ptTengah->nama }}</strong><br>
                    NIP. {{ $ptTengah->nip }}
                @endif
            </td>
            <td style="width:33%;">
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





