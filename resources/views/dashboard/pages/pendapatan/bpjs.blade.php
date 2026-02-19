<div class="dashboard">

    {{-- HEADER --}}
    <div class="dashboard-header">
        <div class="dashboard-header-left">
            <h2><i class="ph ph-cardholder"></i> Pendapatan BPJS</h2>
            <p>Kelola klaim dan penerimaan dari BPJS Kesehatan</p>
        </div>

        @if(auth()->user()->hasPermission('PENDAPATAN_BPJS_CRUD'))
            <div class="dashboard-header-right">
                <div class="bpjs-tab-group">
                    <button class="bpjs-tab active" onclick="switchBpjsTab('REGULAR', this)" id="tabRegular">
                        <i class="ph ph-clipboard-text"></i> Regular
                    </button>
                    <button class="bpjs-tab" onclick="switchBpjsTab('EVAKUASI', this)" id="tabEvakuasi">
                        <i class="ph ph-ambulance"></i> Evakuasi
                    </button>
                    <button class="bpjs-tab" onclick="switchBpjsTab('OBAT', this)" id="tabObat">
                        <i class="ph ph-pill"></i> Obat
                    </button>
                </div>

                <button class="btn-tambah-data" id="btnTambahPendapatanBpjs">
                    <i class="ph-bold ph-plus"></i>
                    <span>Entri Data</span>
                </button>
            </div>
        @else
            <div class="dashboard-header-right">
                <div class="bpjs-tab-group">
                    <button class="bpjs-tab active" onclick="switchBpjsTab('REGULAR', this)" id="tabRegular">
                        <i class="ph ph-clipboard-text"></i> Regular
                    </button>
                    <button class="bpjs-tab" onclick="switchBpjsTab('EVAKUASI', this)" id="tabEvakuasi">
                        <i class="ph ph-ambulance"></i> Evakuasi
                    </button>
                    <button class="bpjs-tab" onclick="switchBpjsTab('OBAT', this)" id="tabObat">
                        <i class="ph ph-pill"></i> Obat
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="dashboard-cards">
        <div class="dash-card blue">
            <div class="dash-card-icon">
                <i class="ph ph-hospital"></i>
            </div>
            <div class="dash-card-content">
                <span class="label">Jasa Rumah Sakit</span>
                <h3 data-summary-bpjs="rs">Rp 0</h3>
                <small data-summary-percent-bpjs="rs" class="growth-up">0% dari total</small>
            </div>
        </div>

        <div class="dash-card purple">
            <div class="dash-card-icon">
                <i class="ph ph-user-gear"></i>
            </div>
            <div class="dash-card-content">
                <span class="label">Jasa Pelayanan</span>
                <h3 data-summary-bpjs="pelayanan">Rp 0</h3>
                <small data-summary-percent-bpjs="pelayanan" class="growth-up">0% dari total</small>
            </div>
        </div>

        <div class="dash-card green">
            <div class="dash-card-icon">
                <i class="ph ph-bank"></i>
            </div>
            <div class="dash-card-content">
                <span class="label">Total Klaim</span>
                <h3 data-summary-bpjs="total" style="color: #16a34a;">Rp 0</h3>
                <small>Terakumulasi</small>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="dashboard-box">
        <div class="box-header">
            <div class="toolbar-row">
                <div class="search-wrapper">
                    <div class="input-group" style="position: relative;">
                        <i class="ph ph-magnifying-glass"
                            style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 18px;"></i>
                        <input type="text" id="searchPendapatanBpjs" placeholder="Cari nama, No SEP, atau ruangan..."
                            style="width: 100%; height: 44px; padding-left: 48px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 14px;">
                    </div>
                </div>

                <div class="toolbar-actions">
                    @if(auth()->user()->hasPermission('PENDAPATAN_BPJS_TEMPLATE'))
                        <a href="/dashboard/pendapatan/bpjs/template" class="btn-toolbar btn-toolbar-outline">
                            <i class="ph ph-download-simple"></i>
                            <span>Template</span>
                        </a>
                    @endif
                    @if(auth()->user()->hasPermission('PENDAPATAN_BPJS_IMPORT'))
                        <button class="btn-toolbar btn-toolbar-info" id="btnImportBpjs">
                            <i class="ph ph-upload-simple"></i>
                            <span>Import Excel</span>
                        </button>
                    @endif
                    @if(auth()->user()->hasPermission('PENDAPATAN_BPJS_BULK'))
                        <button class="btn-toolbar btn-toolbar-danger" id="btnBulkDeleteBpjs">
                            <i class="ph ph-trash"></i>
                            <span>Hapus Massal</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="table-container">
            <table id="pendapatanBpjsTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 60px;">No</th>
                        <th style="width: 120px;">Tanggal</th>
                        <th id="thNoSep" style="width: 160px;">No SEP</th>
                        <th>Nama Pasien</th>
                        <th>Perusahaan</th>
                        <th>Ruangan</th>
                        <th class="text-right" style="width: 160px;">Jumlah</th>
                        <th class="text-center" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="pendapatanBpjsBody">
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 40px; color: #94a3b8;">
                            <i class="ph ph-tray" style="font-size: 32px; margin-bottom: 8px;"></i>
                            <p>Memuat data klaim BPJS...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-between items-center mt-2">
            <p id="paginationInfoBpjs" class="text-slate-500" style="font-size: 13px;">Menampilkan 0–0 dari 0 data</p>

            <div class="flex items-center gap-2">
                <button id="prevPageBpjs" class="btn-aksi" disabled><i class="ph ph-caret-left"></i></button>
                <span id="pageInfoBpjs" class="font-medium"
                    style="font-size: 14px; min-width: 100px; text-align: center;">1 / 1</span>
                <button id="nextPageBpjs" class="btn-aksi" disabled><i class="ph ph-caret-right"></i></button>
            </div>
        </div>
    </div>

</div>

{{-- MODAL DETAIL --}}
@include('dashboard.partials.pendapatan-bpjs-detail')
@include('dashboard.partials.pendapatan-bpjs-import')
@include('dashboard.partials.pendapatan-bpjs-bulk-delete')