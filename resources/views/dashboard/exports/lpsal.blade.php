<table>
    <tr>
        <th colspan="2" style="text-align: center; font-weight: bold;">RSJKO ENGKU HAJI DAUD</th>
    </tr>
    <tr>
        <th colspan="2" style="text-align: center; font-weight: bold;">LAPORAN PERUBAHAN SISA ANGGARAN LEBIH (LPSAL)
        </th>
    </tr>
    <tr>
        <th colspan="2" style="text-align: center; font-weight: bold;">PER
            {{ strtoupper($period['end_date_formatted']) }}</th>
    </tr>
    <tr></tr>
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th style="border: 1px solid #000; font-weight: bold; width: 400px;">URAIAN</th>
            <th style="border: 1px solid #000; font-weight: bold; width: 200px; text-align: right;">JUMLAH (RP)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="border: 1px solid #000;">Sisa Anggaran Lebih Awal (SAL Awal)</td>
            <td style="border: 1px solid #000; text-align: right;">{{ number_format($sal_awal, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;">Penggunaan SAL sebagai Penerimaan Pembiayaan Tahun Berjalan</td>
            <td style="border: 1px solid #000; text-align: right;">({{ number_format($penggunaan_sal, 2, ',', '.') }})
            </td>
        </tr>
        <tr style="background-color: #f9f9f9; font-weight: bold;">
            <td style="border: 1px solid #000; padding-left: 20px;">Subtotal</td>
            <td style="border: 1px solid #000; text-align: right;">
                {{ number_format($sal_awal - $penggunaan_sal, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;">Sisa Lebih/Kurang Pembiayaan Anggaran (SiLPA/SiKPA)</td>
            <td style="border: 1px solid #000; text-align: right;">{{ number_format($silpa, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;">Koreksi Kesalahan Pembukuan Tahun Sebelumnya</td>
            <td style="border: 1px solid #000; text-align: right;">{{ number_format($koreksi, 2, ',', '.') }}</td>
        </tr>
        <tr style="background-color: #e2e8f0; font-weight: bold;">
            <td style="border: 1px solid #000;">SISA ANGGARAN LEBIH AKHIR (SAL AKHIR)</td>
            <td style="border: 1px solid #000; text-align: right;">{{ number_format($sal_akhir, 2, ',', '.') }}</td>
        </tr>
    </tbody>
</table>

@if(isset($ptKiri) || isset($ptTengah) || isset($ptKanan))
    <br>
    <table>
        <tr>
            <td style="width: 33%; text-align: center;">
                @if($ptKiri)
                    {{ $ptKiri->jabatan }}<br><br><br><br>
                    <b>{{ $ptKiri->nama }}</b><br>
                    NIP. {{ $ptKiri->nip }}
                @endif
            </td>
            <td style="width: 33%; text-align: center;">
                @if($ptTengah)
                    {{ $ptTengah->jabatan }}<br><br><br><br>
                    <b>{{ $ptTengah->nama }}</b><br>
                    NIP. {{ $ptTengah->nip }}
                @endif
            </td>
            <td style="width: 33%; text-align: center;">
                @if($ptKanan)
                    Tanjunguban, {{ Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    {{ $ptKanan->jabatan }}<br><br><br><br>
                    <b>{{ $ptKanan->nama }}</b><br>
                    NIP. {{ $ptKanan->nip }}
                @endif
            </td>
        </tr>
    </table>
@endif





