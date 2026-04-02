@php
    function format_number($val) {
        return number_format($val, 2, ',', '.');
    }
@endphp
<table>
    <tr>
        <td colspan="8" style="text-align: center; font-size: 14pt;">PEMERINTAH PROVINSI KEPULAUAN RIAU</td>
    </tr>
    <tr>
        <td colspan="8" style="text-align: center; font-size: 13pt; font-weight: bold;">RUMAH SAKIT JIWA DAN KETERGANTUNGAN OBAT ENGKU HAJI DAUD</td>
    </tr>
    <tr>
        <td colspan="8" style="text-align: center; font-size: 8pt;">Jalan Indun Suri - Simpang Busung Nomor. 1 Tanjung Uban Kode Pos 29152</td>
    </tr>
    <tr>
        <td colspan="8" style="text-align: center; font-size: 8pt;">Telepon ( 0771 ) 482655, 482796 Faksimile. ( 0771 ) 482795</td>
    </tr>
    <tr>
        <td colspan="8" style="text-align: center; border-bottom: 2px solid #000; height: 10px;"></td>
    </tr>
    <tr><td colspan="8" style="height: 20px;"></td></tr>
    <tr>
        <td colspan="8" style="font-size: 16pt; font-weight: bold; text-align: center;">LAPORAN PENDAPATAN PASIEN</td>
    </tr>
    <tr>
        <td colspan="8" style="font-size: 11pt; text-align: center;">Periode: {{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}</td>
    </tr>
    <tr><td colspan="8" style="height: 30px;"></td></tr>

    <!-- SECTION 1 -->
    <tr>
        <td colspan="5" style="background-color: #d9ead3; font-size: 12pt; font-weight: bold; border: 1px solid #000;">1. RINGKASAN PENDAPATAN</td>
    </tr>
    <tr>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">Kategori Pendapatan</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">Jumlah Transaksi</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">Jasa Rumah Sakit</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">Jasa Pelayanan</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">Total Pendapatan</th>
    </tr>
    @php $totTrans = 0; $totRs = 0; $totPel = 0; $totAll = 0; @endphp
    @foreach(['UMUM' => 'Pasien Umum', 'BPJS' => 'BPJS', 'JAMINAN' => 'Jaminan', 'KERJASAMA' => 'Kerjasama', 'LAIN' => 'Lain-lain'] as $key => $label)
        @php
            $item = $summary[$key] ?? ['count' => 0, 'rs' => 0, 'pelayanan' => 0, 'total' => 0];
            $totTrans += $item['count']; $totRs += $item['rs']; $totPel += $item['pelayanan']; $totAll += $item['total'];
        @endphp
        <tr>
            <td style="border: 1px solid #000;">{{ $label }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $item['count'] }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $item['rs'] }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $item['pelayanan'] }}</td>
            <td style="border: 1px solid #000; text-align: right; font-weight: bold;">{{ $item['total'] }}</td>
        </tr>
    @endforeach
    <tr style="background-color: #f2f2f2; font-weight: bold;">
        <td style="border: 1px solid #000; text-align: center;">TOTAL</td>
        <td style="border: 1px solid #000; text-align: center;">{{ $totTrans }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $totRs }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $totPel }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $totAll }}</td>
    </tr>
    <tr><td colspan="5" style="height: 20px;"></td></tr>

    <!-- SECTION 2 -->
    <tr>
        <td colspan="5" style="background-color: #fce5cd; font-size: 12pt; font-weight: bold; border: 1px solid #000;">2. RINCIAN METODE JASA (RS & PELAYANAN)</td>
    </tr>
    <tr>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">Kode Rekening</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">Uraian Akun Pendapatan</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">Jasa Rumah Sakit</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">Jasa Pelayanan</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">Total</th>
    </tr>
    @php $tJrs = 0; $tJpel = 0; $tJTotal = 0; @endphp
    @foreach($breakdown as $key => $item)
        @php
            $jrs = $item['jasa']['RS'] ?? 0; $jpel = $item['jasa']['PELAYANAN'] ?? 0; $jtot = $item['jasa']['TOTAL'] ?? 0;
            $tJrs += $jrs; $tJpel += $jpel; $tJTotal += $jtot;
        @endphp
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $item['kode'] }}</td>
            <td style="border: 1px solid #000;">{{ $item['nama'] }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $jrs }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $jpel }}</td>
            <td style="border: 1px solid #000; text-align: right; font-weight: bold;">{{ $jtot }}</td>
        </tr>
    @endforeach
    <tr style="background-color: #f9f9f9; font-weight: bold;">
        <td colspan="2" style="border: 1px solid #000; text-align: center;">JUMLAH KESELURUHAN</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $tJrs }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $tJpel }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $tJTotal }}</td>
    </tr>
    <tr><td colspan="5" style="height: 20px;"></td></tr>

    <!-- SECTION 3: METODE PEMBAYARAN -->
    <tr>
        <td colspan="5" style="background-color: #cfe2f3; font-size: 12pt; font-weight: bold; border: 1px solid #000;">3. RINCIAN METODE PEMBAYARAN (TUNAI & NON-TUNAI)</td>
    </tr>
    <tr>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">Kode Rekening</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">Uraian Akun Pendapatan</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">Tunai</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">Non-Tunai</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">Total</th>
    </tr>
    @php $tTunai = 0; $tNon = 0; $tTotalPay = 0; @endphp
    @foreach($breakdown as $key => $item)
        @php
            $tunai = $item['payments']['TUNAI'] ?? 0; $non = $item['payments']['NON_TUNAI'] ?? 0; $tot = $item['payments']['TOTAL'] ?? 0;
            $tTunai += $tunai; $tNon += $non; $tTotalPay += $tot;
        @endphp
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $item['kode'] }}</td>
            <td style="border: 1px solid #000;">{{ $item['nama'] }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $tunai }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $non }}</td>
            <td style="border: 1px solid #000; text-align: right; font-weight: bold;">{{ $tot }}</td>
        </tr>
    @endforeach
    <tr style="background-color: #f9f9f9; font-weight: bold;">
        <td colspan="2" style="border: 1px solid #000; text-align: center;">JUMLAH KESELURUHAN</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $tTunai }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $tNon }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $tTotalPay }}</td>
    </tr>
    <tr><td colspan="5" style="height: 20px;"></td></tr>

    <!-- SECTION 4: PENERIMAAN BANK -->
    <tr>
        <td colspan="5" style="background-color: #ead1dc; font-size: 12pt; font-weight: bold; border: 1px solid #000;">4. RINCIAN PENERIMAAN BANK (BRK & BSI)</td>
    </tr>
    <tr>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">Kode Rekening</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">Uraian Akun Pendapatan</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">BRK</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">BSI</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">Total</th>
    </tr>
    @php $tBrk = 0; $tBsi = 0; $tTotalBank = 0; @endphp
    @foreach($breakdown as $key => $item)
        @php
            $brk = $item['banks']['BRK'] ?? 0; $bsi = $item['banks']['BSI'] ?? 0; $tot = $item['banks']['TOTAL'] ?? 0;
            $tBrk += $brk; $tBsi += $bsi; $tTotalBank += $tot;
        @endphp
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $item['kode'] }}</td>
            <td style="border: 1px solid #000;">{{ $item['nama'] }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $brk }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $bsi }}</td>
            <td style="border: 1px solid #000; text-align: right; font-weight: bold;">{{ $tot }}</td>
        </tr>
    @endforeach
    <tr style="background-color: #f9f9f9; font-weight: bold;">
        <td colspan="2" style="border: 1px solid #000; text-align: center;">JUMLAH PENERIMAAN BANK</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $tBrk }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $tBsi }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $tTotalBank }}</td>
    </tr>
    <tr><td colspan="5" style="height: 20px;"></td></tr>

    <!-- SECTION 5: RUANGAN -->
    <tr>
        <td colspan="3" style="background-color: #fff2cc; font-size: 12pt; font-weight: bold; border: 1px solid #000;">5. PENDAPATAN PER RUANGAN</td>
    </tr>
    <tr>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">Nama Ruangan</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">Total Pasien</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">Total Pendapatan</th>
    </tr>
    @php $tRCount = 0; $tRTotal = 0; @endphp
    @foreach($rooms as $name => $data)
        @php $tRCount += $data['count']; $tRTotal += $data['total']; @endphp
        <tr>
            <td style="border: 1px solid #000;">{{ $name }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $data['count'] }}</td>
            <td style="border: 1px solid #000; text-align: right; font-weight: bold;">{{ $data['total'] }}</td>
        </tr>
    @endforeach
    <tr style="background-color: #f9f9f9; font-weight: bold;">
        <td style="border: 1px solid #000; text-align: center;">GRAND TOTAL (SEMUA RUANGAN)</td>
        <td style="border: 1px solid #000; text-align: center;">{{ $tRCount }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $tRTotal }}</td>
    </tr>
    <tr><td colspan="5" style="height: 30px;"></td></tr>

    <!-- ADDITIVE SECTIONS -->
    <tr>
        <td colspan="4" style="background-color: #fbbf24; font-size: 12pt; font-weight: bold; border: 1px solid #000; color: #000;">1. PENERIMAAN PASIEN TUNAI</td>
    </tr>
    <tr>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">NO</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">UNIT</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">TOTAL PASIEN</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">JUMLAH (RP)</th>
    </tr>
    @php $stTotal = 0; $stCount = 0; @endphp
    @foreach($additive_report['tunai'] as $idx => $item)
        @php $stTotal += $item->total; $stCount += $item->count; @endphp
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $idx + 1 }}</td>
            <td style="border: 1px solid #000;">{{ $item->unit }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $item->count }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $item->total }}</td>
        </tr>
    @endforeach
    <tr style="background-color: #f9f9f9; font-weight: bold;">
        <td colspan="2" style="border: 1px solid #000; text-align: center;">TOTAL TUNAI</td>
        <td style="border: 1px solid #000; text-align: center;">{{ $stCount }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $stTotal }}</td>
    </tr>
    <tr><td colspan="5" style="height: 20px;"></td></tr>

    <tr>
        <td colspan="8" style="background-color: #fbbf24; font-size: 12pt; font-weight: bold; border: 1px solid #000; color: #000;">2. PENERIMAAN PASIEN NON TUNAI</td>
    </tr>
    <tr>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">NO</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">UNIT</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">PASIEN QRIS</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">PASIEN TRF</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">TOT PSN</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">QRIS (RP)</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">TRF (RP)</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">TOTAL (RP)</th>
    </tr>
    @php $sntQris = 0; $sntTrans = 0; $sntTotal = 0; $sntPQris = 0; $sntPTrans = 0; $sntPAll = 0; @endphp
    @foreach($additive_report['non_tunai'] as $idx => $item)
        @php 
            $sntQris += $item->qris_amount; $sntTrans += $item->transfer_amount; $sntTotal += $item->total_amount; 
            $sntPQris += $item->pasien_qris; $sntPTrans += $item->pasien_transfer; $sntPAll += $item->total_pasien;
        @endphp
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $idx + 1 }}</td>
            <td style="border: 1px solid #000;">{{ $item->unit }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $item->pasien_qris }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $item->pasien_transfer }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $item->total_pasien }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $item->qris_amount }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $item->transfer_amount }}</td>
            <td style="border: 1px solid #000; text-align: right; font-weight: bold;">{{ $item->total_amount }}</td>
        </tr>
    @endforeach
    <tr style="background-color: #f9f9f9; font-weight: bold;">
        <td colspan="2" style="border: 1px solid #000; text-align: center;">TOTAL NON TUNAI</td>
        <td style="border: 1px solid #000; text-align: center;">{{ $sntPQris }}</td>
        <td style="border: 1px solid #000; text-align: center;">{{ $sntPTrans }}</td>
        <td style="border: 1px solid #000; text-align: center;">{{ $sntPAll }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $sntQris }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $sntTrans }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $sntTotal }}</td>
    </tr>
    <tr><td colspan="8" style="height: 20px;"></td></tr>

    <tr>
        <td colspan="7" style="background-color: #fbbf24; font-size: 12pt; font-weight: bold; border: 1px solid #000; color: #000;">3. PENERIMAAN PASIEN BPJS KESEHATAN</td>
    </tr>
    <tr>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">NO</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">UNIT</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">TOTAL PASIEN</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">BPJS (GROSS)</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">VPK / POTONGAN</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">ADM BANK</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">JUMLAH (NET)</th>
    </tr>
    @php $sbpTotal = 0; $sbpCount = 0; @endphp
    @foreach($additive_report['bpjs']['data'] as $idx => $item)
        @php $sbpTotal += $item->total; $sbpCount += $item->count; @endphp
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $idx + 1 }}</td>
            <td style="border: 1px solid #000;">{{ $item->unit }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $item->count }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $item->total }}</td>
            <td style="border: 1px solid #000; text-align: right;">0</td>
            <td style="border: 1px solid #000; text-align: right;">0</td>
            <td style="border: 1px solid #000; text-align: right; font-weight: bold;">{{ $item->total }}</td>
        </tr>
    @endforeach
    @php 
        $vpk = $additive_report['bpjs']['deductions']->vpk ?? 0;
        $adm = $additive_report['bpjs']['deductions']->adm ?? 0;
        $net = $sbpTotal - $vpk - $adm;
    @endphp
    <tr style="background-color: #f9f9f9; font-weight: bold;">
        <td colspan="2" style="border: 1px solid #000; text-align: center;">TOTAL BPJS</td>
        <td style="border: 1px solid #000; text-align: center;">{{ $sbpCount }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $sbpTotal }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $vpk }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $adm }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $net }}</td>
    </tr>
    <tr><td colspan="7" style="height: 20px;"></td></tr>

    <tr>
        <td colspan="5" style="background-color: #fbbf24; font-size: 12pt; font-weight: bold; border: 1px solid #000; color: #000;">4. PENERIMAAN PASIEN JAMINAN</td>
    </tr>
    <tr>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">NO</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">PENJAMIN / PERUSAHAAN</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">UNIT</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">TOTAL PASIEN</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">JUMLAH (RP)</th>
    </tr>
    @php $sjTotal = 0; $sjCount = 0; @endphp
    @foreach($additive_report['jaminan'] as $idx => $item)
        @php $sjTotal += $item->total; $sjCount += $item->count; @endphp
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $idx + 1 }}</td>
            <td style="border: 1px solid #000;">{{ $item->penjamin }}</td>
            <td style="border: 1px solid #000;">{{ $item->unit }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $item->count }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $item->total }}</td>
        </tr>
    @endforeach
    <tr style="background-color: #f9f9f9; font-weight: bold;">
        <td colspan="3" style="border: 1px solid #000; text-align: center;">TOTAL JAMINAN</td>
        <td style="border: 1px solid #000; text-align: center;">{{ $sjCount }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $sjTotal }}</td>
    </tr>
    <tr><td colspan="5" style="height: 20px;"></td></tr>

    <tr>
        <td colspan="4" style="background-color: #fbbf24; font-size: 12pt; font-weight: bold; border: 1px solid #000; color: #000;">5. PENERIMAAN KERJA SAMA</td>
    </tr>
    <tr>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">NO</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">KERJA SAMA (INSTANSI)</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">JUMLAH KEGIATAN</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">JUMLAH (RP)</th>
    </tr>
    @php $skTotal = 0; $skCount = 0; @endphp
    @foreach($additive_report['kerjasama'] as $idx => $item)
        @php $skTotal += $item->total; $skCount += $item->count; @endphp
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $idx + 1 }}</td>
            <td style="border: 1px solid #000;">{{ $item->instansi }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $item->count }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $item->total }}</td>
        </tr>
    @endforeach
    <tr style="background-color: #f9f9f9; font-weight: bold;">
        <td colspan="2" style="border: 1px solid #000; text-align: center;">TOTAL KERJA SAMA</td>
        <td style="border: 1px solid #000; text-align: center;">{{ $skCount }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $skTotal }}</td>
    </tr>
    <tr><td colspan="4" style="height: 20px;"></td></tr>

    <tr>
        <td colspan="4" style="background-color: #fbbf24; font-size: 12pt; font-weight: bold; border: 1px solid #000; color: #000;">6. PENERIMAAN LAIN-LAIN</td>
    </tr>
    <tr>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">NO</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">KETERANGAN</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">JUMLAH KEGIATAN</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">JUMLAH (RP)</th>
    </tr>
    @php $slTotal = 0; $slCount = 0; @endphp
    @foreach($additive_report['lain'] as $idx => $item)
        @php $slTotal += $item->total; $slCount += $item->count; @endphp
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $idx + 1 }}</td>
            <td style="border: 1px solid #000;">{{ $item->keterangan }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $item->count }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $item->total }}</td>
        </tr>
    @endforeach
    <tr style="background-color: #f9f9f9; font-weight: bold;">
        <td colspan="2" style="border: 1px solid #000; text-align: center;">TOTAL LAIN-LAIN</td>
        <td style="border: 1px solid #000; text-align: center;">{{ $slCount }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $slTotal }}</td>
    </tr>
    <tr><td colspan="4" style="height: 20px;"></td></tr>

    <tr>
        <td colspan="4" style="background-color: #fbbf24; font-size: 12pt; font-weight: bold; border: 1px solid #000; color: #000;">REKAPITULASI PENERIMAAN PER BANK</td>
    </tr>
    <tr>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">NO</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">NAMA BANK</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">TOTAL TRANSAKSI</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">JUMLAH (RP)</th>
    </tr>
    @php $sbTotal = 0; $sbCount = 0; @endphp
    @foreach($additive_report['bank_summary'] as $idx => $item)
        @php $sbTotal += $item['total']; $sbCount += $item['count']; @endphp
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $idx + 1 }}</td>
            <td style="border: 1px solid #000;">{{ $item['bank'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $item['count'] }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $item['total'] }}</td>
        </tr>
    @endforeach
    <tr style="background-color: #f9f9f9; font-weight: bold;">
        <td colspan="2" style="border: 1px solid #000; text-align: center;">TOTAL PER BANK</td>
        <td style="border: 1px solid #000; text-align: center;">{{ $sbCount }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $sbTotal }}</td>
    </tr>
    <tr><td colspan="4" style="height: 20px;"></td></tr>

    <tr>
        <td colspan="4" style="background-color: #fbbf24; font-size: 12pt; font-weight: bold; border: 1px solid #000; color: #000;">REKAPITULASI PENDAPATAN PER UNIT</td>
    </tr>
    <tr>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">NO</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">UNIT</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">TOTAL PASIEN</th>
        <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f3f3f3;">JUMLAH (RP)</th>
    </tr>
    @php $suTotal = 0; $suCount = 0; @endphp
    @foreach($additive_report['unit_summary'] as $idx => $item)
        @php $suTotal += $item['total']; $suCount += $item['count']; @endphp
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $idx + 1 }}</td>
            <td style="border: 1px solid #000;">{{ $item['unit'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $item['count'] }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $item['total'] }}</td>
        </tr>
    @endforeach
    <tr style="background-color: #f9f9f9; font-weight: bold;">
        <td colspan="2" style="border: 1px solid #000; text-align: center;">TOTAL PER UNIT</td>
        <td style="border: 1px solid #000; text-align: center;">{{ $suCount }}</td>
        <td style="border: 1px solid #000; text-align: right;">{{ $suTotal }}</td>
    </tr>
    <tr><td colspan="4" style="height: 30px;"></td></tr>

    <!-- SIGNATURES -->
    <tr>
        <td colspan="2" style="text-align: center; vertical-align: top;">
            @if($ptKiri)
                {{ $ptKiri->jabatan }}<br><br><br><br>
                {{ $ptKiri->nama }}<br>
                NIP. {{ $ptKiri->nip }}
            @endif
        </td>
        <td colspan="2" style="text-align: center; vertical-align: top;">
            @if($ptTengah)
                {{ $ptTengah->jabatan }}<br><br><br><br>
                {{ $ptTengah->nama }}<br>
                NIP. {{ $ptTengah->nip }}
            @endif
        </td>
        <td colspan="2" style="text-align: center; vertical-align: top;">
            Tanjung Uban, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
            @if($ptKanan)
                {{ $ptKanan->jabatan }}<br><br><br><br>
                {{ $ptKanan->nama }}<br>
                NIP. {{ $ptKanan->nip }}
            @endif
        </td>
    </tr>
</table>
