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
                <button id="btnSetSaldoAwal" class="btn-tambah-data btn-secondary" onclick="openBankLedgerSaldoAwalModal()"
                    style="margin-right: 8px;">
                    <i class="ph-bold ph-wallet"></i>
                    <span>Set Saldo Awal</span>
                </button>
                <button class="btn-tambah-data btn-info" onclick="openAdjustmentModal()"
                    style="margin-right: 8px; background: #0369a1;">
                    <i class="ph-bold ph-sliders"></i>
                    <span>Penyesuaian</span>
                </button>
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
                    <label style="font-size:11px;">Bank</label>
                    <select id="bankLedgerBank" class="form-input" style="height:38px; width:200px;"
                        onchange="loadBankLedger()">
                        <option value="Semua Bank">Semua Bank</option>
                        <option value="BRK" selected>Bank Riau Kepri Syariah</option>
                        <option value="BSI">Bank Syariah Indonesia</option>
                    </select>
                </div>
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
            <div style="text-align: right;">
                <div id="bankLedgerSaldoAwalDisplay"
                    style="font-size:12px; color:#0369a1; font-weight:600; margin-bottom:4px;">Saldo Awal Tahun: Rp 0
                </div>
                <div style="font-size:11px; color:#64748b;">Saldo Rekening Saat Ini</div>
                <div id="bankLedgerCurrentBalance" style="font-weight:800; font-size:22px; color:#166534;">Rp 0</div>
            </div>
        </div>


        <div class="table-toolbar" style="margin-top: 20px;">
            <div class="table-search-wrapper">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" id="searchBankLedger" class="table-search" placeholder="Cari di mutasi rekening..."
                    data-table="tableBankLedger">
            </div>
        </div>

        <div class="table-container">
            <table id="tableBankLedger" class="universal-table">
                <thead>
                    <tr>
                        <th width="40" class="text-center checkbox-col">No</th>
                        <th width="140" class="text-center sortable" onclick="sortBankLedger('date')" data-sort="date">
                            Tanggal <i class="ph ph-caret-up-down"></i></th>
                        <th width="120" class="text-center sortable" onclick="sortBankLedger('type')" data-sort="type">
                            Jenis Mutasi <i class="ph ph-caret-up-down"></i></th>
                        <th class="sortable" onclick="sortBankLedger('description')" data-sort="description">Uraian /
                            Keterangan <i class="ph ph-caret-up-down"></i></th>
                        <th width="120" class="text-center sortable" onclick="sortBankLedger('bank')" data-sort="bank">
                            Bank <i class="ph ph-caret-up-down"></i></th>
                        <th width="160" class="text-right sortable" onclick="sortBankLedger('debit')" data-sort="debit">
                            Debit (Masuk) <i class="ph ph-caret-up-down"></i></th>
                        <th width="160" class="text-right sortable" onclick="sortBankLedger('credit')"
                            data-sort="credit">Kredit (Keluar) <i class="ph ph-caret-up-down"></i></th>
                        <th width="180" class="text-right">Saldo</th>
                        <th width="100" class="text-center action-col">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBankLedgerBody">
                    <tr>
                        <td colspan="9" class="text-center">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>