<div class="dashboard">

    {{-- HEADER --}}
    <div class="dashboard-header">
        <div class="dashboard-header-left">
            <h2>Pendapatan Kerjasama</h2>
            <p>Kelola pendapatan dari kerjasama antar lembaga dan instansi</p>
        </div>

        <div class="dashboard-header-right" style="display: flex; gap: 8px;">
            <div class="toolbar-group" style="display: flex; gap: 8px;">
                @if(auth()->user()->hasPermission('PENDAPATAN_KERJA_TEMPLATE'))
                    <a href="/dashboard/pendapatan/kerjasama/template" class="btn-toolbar btn-toolbar-outline"
                        title="Download Template CSV">
                        <i class="ph ph-download-simple"></i>
                        <span>Template</span>
                    </a>
                @endif
                @if(auth()->user()->hasPermission('PENDAPATAN_KERJA_IMPORT'))
                    <button class="btn-toolbar btn-toolbar-outline" id="btnImportKerjasama" title="Import Data dari CSV">
                        <i class="ph ph-file-arrow-up"></i>
                        <span>Import</span>
                    </button>
                @endif
                @if(auth()->user()->hasPermission('PENDAPATAN_KERJA_BULK'))
                    <button class="btn-toolbar btn-toolbar-danger btn-toolbar-outline" id="btnBulkDeleteKerjasama"
                        title="Hapus Massal Per Tanggal">
                        <i class="ph ph-trash-simple"></i>
                        <span>Hapus Massal</span>
                    </button>
                @endif
            </div>
            @if(auth()->user()->hasPermission('PENDAPATAN_KERJA_CRUD'))
                <button class="btn-tambah-data" id="btnTambahPendapatanKerjasama">
                    <i class="ph-bold ph-plus"></i>
                    <span>Tambah Data</span>
                </button>
            @endif
        </div>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="dashboard-cards">
        <div class="dash-card blue">
            <div class="dash-card-icon">
                <i class="ph ph-hospital"></i>
            </div>
            <div class="dash-card-content">
                <span class="label">Jasa Rumah Sakit</span>
                <h3 data-summary-kerjasama="rs">Rp 0</h3>
                <small data-summary-percent-kerjasama="rs" class="growth-up">0% dari total</small>
            </div>
        </div>

        <div class="dash-card purple">
            <div class="dash-card-icon">
                <i class="ph ph-user-gear"></i>
            </div>
            <div class="dash-card-content">
                <span class="label">Jasa Pelayanan</span>
                <h3 data-summary-kerjasama="pelayanan">Rp 0</h3>
                <small data-summary-percent-kerjasama="pelayanan" class="growth-up">0% dari total</small>
            </div>
        </div>

        <div class="dash-card green">
            <div class="dash-card-icon">
                <i class="ph ph-bank"></i>
            </div>
            <div class="dash-card-content">
                <span class="label">Total Pendapatan</span>
                <h3 data-summary-kerjasama="total" style="color: #16a34a;">Rp 0</h3>
                <small>Terakumulasi</small>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="dashboard-box">
        <div class="box-header">
            <div class="flex items-center gap-4" style="width: 100%;">
                <div class="search-wrapper flex-1">
                    <div class="input-group" style="position: relative;">
                        <i class="ph ph-magnifying-glass"
                            style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 18px;"></i>
                        <input type="text" id="searchPendapatanKerjasama"
                            placeholder="Cari nama pasien, instansi, atau ruangan..."
                            style="width: 100%; height: 48px; padding-left: 48px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 14px;">
                    </div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table id="pendapatanKerjasamaTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 60px;">No</th>
                        <th style="width: 140px;">Tanggal</th>
                        <th>Nama Pasien</th>
                        <th>MOU</th>
                        <th>Ruangan</th>
                        <th class="text-right" style="width: 180px;">Jumlah</th>
                        <th class="text-center" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="pendapatanKerjasamaBody">
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 40px; color: #94a3b8;">
                            <i class="ph ph-tray" style="font-size: 32px; margin-bottom: 8px;"></i>
                            <p>Memuat data kerjasama...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-between items-center mt-2">
            <p id="paginationInfoKerjasama" class="text-slate-500" style="font-size: 13px;">Menampilkan 0–0 dari 0 data
            </p>

            <div class="flex items-center gap-2">
                <button id="prevPageKerjasama" class="btn-aksi" disabled><i class="ph ph-caret-left"></i></button>
                <span id="pageInfoKerjasama" class="font-medium"
                    style="font-size: 14px; min-width: 100px; text-align: center;">1 / 1</span>
                <button id="nextPageKerjasama" class="btn-aksi" disabled><i class="ph ph-caret-right"></i></button>
            </div>
        </div>
    </div>

</div>
@include('dashboard.partials.pendapatan-kerjasama-detail')

{{-- MODAL IMPORT --}}
<div id="modalImportKerjasama" class="confirm-overlay">
    <div class="confirm-box">
        <h3><i class="ph ph-file-arrow-up"></i> Import Kerjasama</h3>
        <p style="font-size: 14px; color: #64748b; margin-bottom: 20px;">
            Pilih file CSV yang akan diimport. Pastikan format kolom sesuai dengan template.
        </p>

        <form id="formImportKerjasama">
            <div class="form-group">
                <label>File CSV</label>
                <input type="file" name="file" class="form-input" accept=".csv" required>
            </div>

            <div class="confirm-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('modalImportKerjasama')">Batal</button>
                <button type="submit" class="btn-primary">Mulai Import</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL BULK DELETE --}}
<div id="modalBulkDeleteKerjasama" class="confirm-overlay">
    <div class="confirm-box">
        <h3 style="color: #ef4444;"><i class="ph ph-trash-simple"></i> Hapus Massal</h3>
        <p style="font-size: 14px; color: #64748b; margin-bottom: 20px;">
            Pilih tanggal untuk menghapus SEMUA data kerjasama pada tanggal tersebut. Tindakan ini tidak dapat
            dibatalkan.
        </p>

        <form id="formBulkDeleteKerjasama">
            <div class="form-group">
                <label>Tanggal Data</label>
                <input type="date" id="bulkDeleteDateKerjasama" class="form-input" required>
            </div>

            <div class="confirm-actions">
                <button type="button" class="btn-secondary"
                    onclick="closeModal('modalBulkDeleteKerjasama')">Batal</button>
                <button type="submit" class="btn-danger">Hapus Permanen</button>
            </div>
        </form>
    </div>
</div>