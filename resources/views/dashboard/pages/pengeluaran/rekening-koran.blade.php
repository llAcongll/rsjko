@php
    $title = 'Rekening Koran Pengeluaran';
@endphp

<div class="dashboard">
    <div class="dashboard-header">
        <div class="dashboard-header-left">
            <h2><i class="ph ph-bank"></i> {{ $title }}</h2>
            <p>Kelola saldo rekening bank khusus pencairan dana</p>
        </div>

        <div class="dashboard-header-right">
            @if(auth()->user()->hasPermission('PENGELUARAN_CAIR') || auth()->user()->isAdmin())
                <button class="btn-tambah-data" onclick="openDepositModal()">
                    <i class="ph-bold ph-plus"></i>
                    <span>Deposit / Setor Tunai</span>
                </button>
            @endif
        </div>
    </div>

    <div class="dashboard-box">
        <div class="dashboard-box-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; gap:16px;">
                <div class="form-group" style="margin:0;">
                    <label style="font-size:11px;">Bulan</label>
                    <select id="bankLedgerMonth" class="form-input" style="height:38px; width:150px;"
                        onchange="loadBankLedger()">
                        <option value="">Semua Bulan</option>
                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $m)
                            <option value="{{ $i + 1 }}">{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label style="font-size:11px;">Tahun</label>
                    <select id="bankLedgerYear" class="form-input" style="height:38px; width:100px;"
                        onchange="loadBankLedger()">
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                    </select>
                </div>
            </div>
            <div>
                <div style="font-size:11px; color:#64748b; text-align:right;">Saldo Rekening Saat Ini</div>
                <div id="bankLedgerCurrentBalance" style="font-weight:800; font-size:22px; color:#166534;">Rp 0</div>
            </div>
        </div>

        <div class="table-container">
            <table id="tableBankLedger">
                <thead>
                    <tr>
                        <th width="40" class="text-center">No</th>
                        <th width="140" class="text-center">Tanggal</th>
                        <th width="120" class="text-center">Jenis Mutasi</th>
                        <th>Uraian / Keterangan</th>
                        <th width="160" class="text-right">Debit (Masuk)</th>
                        <th width="160" class="text-right">Kredit (Keluar)</th>
                        <th width="180" class="text-right">Saldo</th>
                        <th width="100" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBankLedgerBody">
                    <tr>
                        <td colspan="8" class="text-center">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>