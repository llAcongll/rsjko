<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>SP3BP - {{ $sp3bp->periode->bulan }}/{{ $sp3bp->periode->tahun }}</title>
    <style>
        @page {
            margin: 1cm 1.5cm;
            size: 21.59cm 33.02cm;
            /* F4 Size */
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 10pt;
            line-height: 1.2;
            color: #000;
        }

        .kop-surat {
            border-bottom: 3px solid #000;
            padding-bottom: 5px;
            margin-bottom: 15px;
            overflow: hidden;
        }

        .kop-logo {
            float: left;
            width: 70px;
            height: auto;
        }

        .kop-text {
            text-align: center;
            margin-left: 70px;
        }

        .kop-text h2 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .kop-text h1 {
            margin: 2px 0;
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .kop-text p {
            margin: 0;
            font-size: 9pt;
            font-style: italic;
        }

        .doc-title {
            text-align: center;
            margin: 15px 0;
        }

        .doc-title h3 {
            margin: 0;
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .doc-info {
            margin-top: 5px;
            font-size: 10pt;
        }

        .summary-container {
            margin-bottom: 15px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            border: 1px solid #000;
            padding: 5px 10px;
            width: 25%;
        }

        .summary-label {
            font-size: 8pt;
            color: #444;
            display: block;
        }

        .summary-value {
            font-weight: bold;
            font-size: 10pt;
        }

        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .info-label {
            width: 140px;
        }

        .info-separator {
            width: 15px;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .content-table th,
        .content-table td {
            border: 1px solid #000;
            padding: 6px;
        }

        .content-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }

        .bg-gray {
            background-color: #f9f9f9;
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

        .footer {
            margin-top: 20px;
        }

        .footer-table {
            width: 100%;
        }

        .footer-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .signature-space {
            height: 60px;
        }
    </style>
</head>

<body>
    <!-- 1. KOP SURAT -->
    <div class="kop-surat">
        <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w200" class="kop-logo"
            alt="Logo">
        <div class="kop-text">
            <h2>Pemerintah Provinsi Kepulauan Riau</h2>
            <h1>Rumah Sakit Jiwa dan Ketergantungan Obat</h1>
            <h1>Engku Haji Daud</h1>
            <p>Jl. Indun Suri No. 1, Tanjung Uban, Kec. Bintan Utara, Kab. Bintan, Kepulauan Riau 29152</p>
            <p>Telepon: (0771) 482613 | Email: rsjkobintan@gmail.com | Website: rsjkoehd.kepriprov.go.id</p>
        </div>
    </div>

    <!-- 2. JUDUL DOKUMEN -->
    <div class="doc-title">
        <h3>Surat Permintaan Pengesahan</h3>
        <h3>Pendapatan, Belanja dan Pembiayaan</h3>
        <h3>Badan Layanan Umum Daerah</h3>

        <table style="width: 250px; margin: 10px auto 0; text-align: left;">
            <tr>
                <td style="width: 70px;">Tanggal</td>
                <td>:
                    {{ $sp3bp->periode->tgl_pengesahan ? \Carbon\Carbon::parse($sp3bp->periode->tgl_pengesahan)->translatedFormat('d F Y') : '-' }}
                </td>
            </tr>
            <tr>
                <td>Nomor</td>
                <td>: {{ $sp3bp->nomor_dokumen ?? '____/SP3BP/RSJKO/' . $sp3bp->periode->tahun }}</td>
            </tr>
        </table>
    </div>

    <!-- 3. RINGKASAN PENGESAHAN -->
    <div class="summary-container">
        <table class="summary-table">
            <tr>
                <td>
                    <span class="summary-label">Saldo Awal</span>
                    <span class="summary-value">Rp {{ number_format($sp3bp->saldo_awal, 2, ',', '.') }}</span>
                </td>
                <td>
                    <span class="summary-label">Pendapatan</span>
                    <span class="summary-value">Rp {{ number_format($sp3bp->pendapatan, 2, ',', '.') }}</span>
                </td>
                <td>
                    <span class="summary-label">Belanja</span>
                    <span class="summary-value">Rp {{ number_format($sp3bp->belanja, 2, ',', '.') }}</span>
                </td>
                <td>
                    <span class="summary-label">Saldo Akhir</span>
                    <span class="summary-value">Rp {{ number_format($sp3bp->saldo_akhir, 2, ',', '.') }}</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- 4. INFORMASI ORGANISASI -->
    <table class="info-table">
        <tr>
            <td class="info-label">Dasar Pengesahan</td>
            <td class="info-separator">:</td>
            <td>Pergub No. 71 Tahun 2021 tentang Sistem Akuntansi Pemerintah Provinsi Kepulauan Riau</td>
        </tr>
        <tr>
            <td class="info-label">Urusan Pemerintah</td>
            <td class="info-separator">:</td>
            <td>1.02 URUSAN PEMERINTAHAN BIDANG KESEHATAN</td>
        </tr>
        <tr>
            <td class="info-label">Organisasi</td>
            <td class="info-separator">:</td>
            <td>1.02.0.00.0.00.01.0000 RUMAH SAKIT JIWA DAN KETERGANTUNGAN OBAT</td>
        </tr>
        <tr>
            <td class="info-label">Nama BLUD</td>
            <td class="info-separator">:</td>
            <td>RSJKO ENGKU HAJI DAUD</td>
        </tr>
    </table>

    <!-- 5. RINCIAN PENDAPATAN -->
    <p class="font-bold" style="margin-bottom: 5px;">I. RINCIAN PENDAPATAN BLUD</p>
    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 150px;">KODE REKENING</th>
                <th>URAIAN PENDAPATAN</th>
                <th style="width: 140px;">JUMLAH (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sp3bp->detailPendapatan as $p)
                <tr>
                    <td class="text-center">{{ $p->kode_rekening }}</td>
                    <td>{{ $p->uraian }}</td>
                    <td class="text-right">{{ number_format($p->jumlah, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="bg-gray font-bold">
                <td colspan="2" class="text-right">TOTAL PENDAPATAN</td>
                <td class="text-right">{{ number_format($sp3bp->pendapatan, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- 6. RINCIAN BELANJA -->
    <p class="font-bold" style="margin-bottom: 5px;">II. RINCIAN BELANJA BLUD</p>
    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 150px;">KODE REKENING</th>
                <th>URAIAN BELANJA</th>
                <th style="width: 140px;">JUMLAH (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sp3bp->detailBelanja as $b)
                <tr>
                    <td class="text-center">{{ $b->kode_rekening }}</td>
                    <td>{{ $b->uraian }}</td>
                    <td class="text-right">{{ number_format($b->jumlah, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="bg-gray font-bold">
                <td colspan="2" class="text-right">TOTAL BELANJA</td>
                <td class="text-right">{{ number_format($sp3bp->belanja, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- 7. PEMBIAYAAN -->
    <p class="font-bold" style="margin-bottom: 5px;">III. PEMBIAYAAN</p>
    <table class="content-table">
        <tbody>
            <tr>
                <td style="width: 150px;" class="text-center font-bold">6.1</td>
                <td class="font-bold">PENERIMAAN PEMBIAYAAN</td>
                <td style="width: 140px;" class="text-right">
                    {{ number_format($sp3bp->pembiayaan_terima ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">6.2</td>
                <td class="font-bold">PENGELUARAN PEMBIAYAAN</td>
                <td class="text-right">{{ number_format($sp3bp->pembiayaan_keluar ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr class="bg-gray font-bold">
                <td colspan="2" class="text-right">PEMBIAYAAN NETTO</td>
                <td class="text-right">
                    {{ number_format(($sp3bp->pembiayaan_terima ?? 0) - ($sp3bp->pembiayaan_keluar ?? 0), 2, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- FOOTER SIGNATURE -->
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td></td>
                <td>
                    <p>Tanjung Uban,
                        {{ $sp3bp->periode->tgl_pengesahan ? \Carbon\Carbon::parse($sp3bp->periode->tgl_pengesahan)->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                    </p>
                    <p class="font-bold">Direktur / Pemimpin BLUD</p>
                    <div class="signature-space"></div>
                    <p class="font-bold" style="text-decoration: underline;">dr. KHAIRUL, M.H.</p>
                    <p>NIP. 19760515 200502 1 002</p>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>





