@php
    $title = 'Pengeluaran';
    if ($param == 'PEGAWAI')
        $title = 'Pengeluaran Pegawai';
    elseif ($param == 'BARANG_JASA')
        $title = 'Pengeluaran Barang dan Jasa';
    elseif ($param == 'MODAL')
        $title = 'Pengeluaran Modal & Aset';
@endphp

<div class="dashboard">
    <div class="dashboard-header">
        <div class="dashboard-header-left">
            <h2><i class="ph ph-hand-coins"></i> {{ $title }}</h2>
            <p>Kelola data transaksi {{ strtolower($title) }}</p>
        </div>

        <div class="dashboard-header-right">
            @if(auth()->user()->hasPermission('PENGELUARAN_CREATE') || auth()->user()->isAdmin())
                <button class="btn-tambah-data" onclick="openPengeluaranForm('{{ $param }}')">
                    <i class="ph-bold ph-plus"></i>
                    <span>Tambah Data</span>
                </button>
            @endif
        </div>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="dashboard-cards" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="dash-card purple">
            <div class="dash-card-icon">
                <i class="ph ph-wallet"></i>
            </div>
            <div class="dash-card-content">
                <span class="label">Uang Persediaan</span>
                <h3 id="totalUP">Rp 0</h3>
                <small id="countUP">0 Transaksi</small>
            </div>
        </div>

        <div class="dash-card orange">
            <div class="dash-card-icon">
                <i class="ph ph-arrows-counter-clockwise"></i>
            </div>
            <div class="dash-card-content">
                <span class="label">Ganti Uang</span>
                <h3 id="totalGU">Rp 0</h3>
                <small id="countGU">0 Transaksi</small>
            </div>
        </div>

        <div class="dash-card green">
            <div class="dash-card-icon">
                <i class="ph ph-lightning"></i>
            </div>
            <div class="dash-card-content">
                <span class="label">Langsung</span>
                <h3 id="totalLS">Rp 0</h3>
                <small id="countLS">0 Transaksi</small>
            </div>
        </div>

        <div class="dash-card blue">
            <div class="dash-card-icon">
                <i class="ph ph-bank"></i>
            </div>
            <div class="dash-card-content">
                <span class="label">Total Belanja</span>
                <h3 id="totalNominalPengeluaran">Rp 0</h3>
                <small id="totalCountPengeluaran">0 Transaksi</small>
            </div>
        </div>
    </div>

    <div class="dashboard-box">
        <div class="dashboard-box-header">
            <div class="flex items-center gap-4" style="width: 100%;">

                <div class="search-wrapper flex-1" style="display: flex; flex-direction: column; gap: 4px;">
                    <label
                        style="font-size: 11px; font-weight: 600; color: #64748b; margin-left: 4px;">Pencarian</label>
                    <div class="input-group" style="position: relative;">
                        <i class="ph ph-magnifying-glass"
                            style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 18px;"></i>
                        <input type="text" id="searchPengeluaran" placeholder="Cari uraian atau kode..."
                            onkeyup="handleSearchPengeluaran(event)"
                            style="width: 100%; height: 48px; padding-left: 48px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 14px;">
                    </div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table id="tablePengeluaran">
                <thead>
                    <tr>
                        <th width="40" class="text-center">No</th>
                        <th width="100">Tanggal</th>
                        <th width="140">Kode Rekening</th>
                        <th width="400">Nama Rekening</th>
                        <th>Uraian</th>
                        <th width="200" class="text-right" style="white-space: nowrap;">Nominal</th>
                        <th width="80" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7" class="text-center">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-between items-center mt-4">
            <p id="paginationInfoPengeluaran" class="text-slate-500" style="font-size: 13px;">Menampilkan 0–0 dari 0
                data</p>

            <div class="flex items-center gap-2">
                <button id="prevPagePengeluaran" class="btn-aksi" disabled><i class="ph ph-caret-left"></i></button>
                <span id="pageInfoPengeluaran" class="font-medium"
                    style="font-size: 14px; min-width: 100px; text-align: center;">1 / 1</span>
                <button id="nextPagePengeluaran" class="btn-aksi" disabled><i class="ph ph-caret-right"></i></button>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize when view is loaded
    if (typeof initPengeluaran === 'function') {
        initPengeluaran('{{ $param }}');
    }
</script>