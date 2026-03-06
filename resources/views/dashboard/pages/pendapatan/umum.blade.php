<div class="page-container">
  <div id="masterListSectionUmum">
    {{-- HEADER --}}
    <div class="page-header">
      <div class="page-header-left">
        <h2><i class="ph ph-folder-open"></i> Kelompok Pendapatan Umum</h2>
        <p>Kelola data pendapatan umum berdasar kelompok/tanggal</p>
      </div>

      <div class="page-header-right">
        @if(auth()->user()->hasPermission('PENDAPATAN_UMUM_CREATE') || auth()->user()->hasPermission('PENDAPATAN_UMUM_CRUD') || auth()->user()->hasPermission('PENDAPATAN_UMUM_POST'))
          <button class="btn-toolbar btn-toolbar-info" onclick="bulkPostMasterUmum()">
            <i class="ph ph-check-square-offset"></i>
            <span>Posting Masal</span>
          </button>
          <button class="btn-toolbar btn-toolbar-outline" onclick="bulkUnpostMasterUmum()"
            style="border-color: #f59e0b; color: #d97706;">
            <i class="ph ph-arrow-counter-clockwise"></i>
            <span>Batal Posting Masal</span>
          </button>
          @if(auth()->user()->hasPermission('REVENUE_SYNC'))
            <button class="btn-toolbar btn-toolbar-outline" onclick="syncOldData()"
              style="border-color: #3b82f6; color: #2563eb;">
              <i class="ph ph-arrows-counter-clockwise"></i>
              <span>Sinkronisasi</span>
            </button>
          @endif
          <button class="btn-tambah-data" onclick="openMasterFormUmum()">
            <i class="ph-bold ph-plus"></i>
            <span>Kelompok Baru</span>
          </button>
        @endif
      </div>
    </div>

    {{-- SUMMARY CARDS (MASTER) --}}
    <style>
      .dashboard {
        gap: 16px !important;
      }

      .pendapatan-summary-container {
        display: flex;
        justify-content: center;
        margin-bottom: 0px;
      }

      .pendapatan-summary-container .dashboard-cards {
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        width: 100%;
        max-width: 850px;
        gap: 16px;
      }
    </style>
    <div class="pendapatan-summary-container">
      <div class="dashboard-cards">
        <div class="dash-card blue">
          <div class="dash-card-icon"><i class="ph ph-hospital"></i></div>
          <div class="dash-card-content">
            <span class="label">Jasa Rumah Sakit</span>
            <h3 id="masterSummaryRsUmum">Rp 0</h3>
          </div>
        </div>
        <div class="dash-card purple">
          <div class="dash-card-icon"><i class="ph ph-user-gear"></i></div>
          <div class="dash-card-content">
            <span class="label">Jasa Pelayanan</span>
            <h3 id="masterSummaryPelayananUmum">Rp 0</h3>
          </div>
        </div>
        <div class="dash-card green">
          <div class="dash-card-icon"><i class="ph ph-bank"></i></div>
          <div class="dash-card-content">
            <span class="label">Total Pendapatan</span>
            <h3 id="masterSummaryTotalUmum" style="color: #16a34a;">Rp 0</h3>
          </div>
        </div>
      </div>
    </div>

    {{-- MAIN CONTENT (MASTER) --}}
    <div class="dashboard-box">
      <div class="table-toolbar">
        <div class="table-search-wrapper">
          <i class="ph ph-magnifying-glass"></i>
          <input type="text" id="searchMasterUmum" class="table-search" placeholder="Cari di daftar kelompok..."
            data-table="masterTableUmum">
        </div>
        <div class="filter-wrapper">
          <select id="filterStatusMasterUmum"
            style="height: 48px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 14px; padding: 0 16px; background: #fff; color: #475569; font-weight: 600; cursor: pointer; outline: none; transition: all 0.2s;">
            <option value="">Semua Status</option>
            <option value="DRAFT">📑 Draft</option>
            <option value="POSTED">✅ Diposting</option>
          </select>
        </div>
      </div>

      <div id="selectionBannerUmum"
        style="display:none; background: #eff6ff; padding: 12px; border-radius: 8px; margin-bottom: 12px; border: 1px solid #bfdbfe; text-align: center; font-size: 13px; color: #1e40af;">
        Semua <span id="countCurrentPageUmum">-</span> kelompok di halaman ini telah terpilih.
      </div>

      <div class="table-container">
        <table id="masterTableUmum" class="universal-table">
          <thead>
            <tr>
              <th class="checkbox-col">
                <input type="checkbox" id="checkAllMasterUmum" onclick="toggleAllMasterUmum(this)" />
              </th>
              <th class="text-center sortable">Tanggal PDPT/RK</th>
              <th class="text-center sortable">Keterangan / No. Bukti</th>
              <th class="text-right sortable">Jasa RS</th>
              <th class="text-right sortable">Jasa Pelayanan</th>
              <th class="text-right sortable">Total (Rp)</th>
              <th class="text-center sortable">Status</th>
              <th class="action-col">Aksi</th>
            </tr>
          </thead>
          <tbody id="masterTableBodyUmum">
            <tr>
              <td colspan="8" class="text-center">Memuat data...</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="flex justify-between items-center mt-2">
        <p id="paginationInfoMasterUmum" class="text-slate-500" style="font-size: 13px;">Menampilkan 0–0 dari 0 data</p>
        <div class="flex items-center gap-2">
          <button id="prevPageMasterUmum" class="btn-aksi" disabled><i class="ph ph-caret-left"></i></button>
          <span id="pageInfoMasterUmum" class="font-medium"
            style="font-size: 14px; min-width: 100px; text-align: center;">1
            /
            1</span>
          <button id="nextPageMasterUmum" class="btn-aksi" disabled><i class="ph ph-caret-right"></i></button>
        </div>
      </div>
    </div>
  </div>

  {{-- DETAIL SECTION --}}
  <div id="detailListSectionUmum" style="display: none; animation: fadeIn 0.3s ease-out;">
    <div class="dashboard-header" style="margin-bottom: 24px;">
      <div class="dashboard-header-left">
        <button onclick="closeDetailUmum()"
          style="display: inline-flex; align-items: center; gap: 8px; background: #fff; border: 1px solid #e2e8f0; padding: 8px 16px; border-radius: 10px; cursor: pointer; color: #64748b; font-weight: 600; margin-bottom: 12px; transition: all 0.2s;">
          <i class="ph ph-arrow-left"></i> Kembali ke Daftar Kelompok
        </button>
        <h2><i class="ph ph-list-numbers"></i> Rincian Pendapatan Pasien</h2>
        <p style="font-size: 14px; color: #64748b;">Grup: <span id="detailMasterInfoUmum"
            style="font-weight: 700; color: #1e293b;">-</span></p>
      </div>
      <div class="dashboard-header-right" style="display: flex; gap: 8px;">
        <div class="toolbar-group" style="display: flex; gap: 8px;">
          @if(auth()->user()->hasPermission('PENDAPATAN_UMUM_CREATE') || auth()->user()->hasPermission('PENDAPATAN_UMUM_CRUD'))
            <a href="/dashboard/pendapatan/umum/template" class="btn-toolbar btn-toolbar-outline"
              title="Download Template CSV"><i class="ph ph-download-simple"></i><span>Template</span></a>
          @endif
          @if(auth()->user()->hasPermission('PENDAPATAN_UMUM_CREATE') || auth()->user()->hasPermission('PENDAPATAN_UMUM_CRUD'))
            <button class="btn-toolbar btn-toolbar-outline" id="btnImportUmum" title="Import Data dari CSV"><i
                class="ph ph-file-arrow-up"></i><span>Import</span></button>
          @endif
          @if(auth()->user()->hasPermission('PENDAPATAN_UMUM_DELETE') || auth()->user()->hasPermission('PENDAPATAN_UMUM_CRUD'))
            <button class="btn-toolbar btn-toolbar-outline btn-bulk-delete" id="btnBulkDeleteUmum"
              title="Hapus Massal Rincian Pasien" style="color: #ef4444; border-color: #fca5a5;">
              <i class="ph ph-trash"></i><span>Hapus Massal</span>
            </button>
          @endif
        </div>
        @if(auth()->user()->hasPermission('PENDAPATAN_UMUM_CREATE') || auth()->user()->hasPermission('PENDAPATAN_UMUM_CRUD'))
          <button class="btn-tambah-data" id="btnTambahPendapatanUmum" style="background:#059669; height: 44px;"
            onclick="openPendapatanModal()">
            <i class="ph-bold ph-plus"></i>
            <span>Tambah Pasien</span>
          </button>
        @endif
      </div>
    </div>

    {{-- SUMMARY CARDS (DETAIL) --}}
    <div class="pendapatan-summary-container">
      <div class="dashboard-cards">
        <div class="dash-card blue">
          <div class="dash-card-icon"><i class="ph ph-hospital"></i></div>
          <div class="dash-card-content">
            <span class="label">Jasa Rumah Sakit</span>
            <h3 id="detailSummaryRsUmum">Rp 0</h3>
          </div>
        </div>
        <div class="dash-card purple">
          <div class="dash-card-icon"><i class="ph ph-user-gear"></i></div>
          <div class="dash-card-content">
            <span class="label">Jasa Pelayanan</span>
            <h3 id="detailSummaryPelayananUmum">Rp 0</h3>
          </div>
        </div>
        <div class="dash-card green">
          <div class="dash-card-icon"><i class="ph ph-bank"></i></div>
          <div class="dash-card-content">
            <span class="label">Total Pendapatan</span>
            <h3 id="detailSummaryTotalUmum" style="color: #16a34a;">Rp 0</h3>
          </div>
        </div>
      </div>
    </div>

    <div class="dashboard-box" style="padding: 0;">
      <div class="table-toolbar" style="padding: 16px; margin-bottom: 0; border-bottom: 1px solid #f1f5f9;">
        <div class="table-search-wrapper" style="width: 100%;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="text" id="searchPendapatanUmum" class="table-search" placeholder="Cari nama pasien, ruangan..."
            data-table="pendapatanUmumTable">
        </div>
      </div>
      <div class="table-container" style="margin-top: 0; border-radius: 0; border: none; max-height: 60vh;">
        <table id="pendapatanUmumTable" class="universal-table">
          <thead>
            <tr>
              <th class="text-center checkbox-col">No</th>
              <th class="text-center sortable">Tanggal</th>
              <th class="text-center sortable">Nama Pasien</th>
              <th class="text-center sortable">Ruangan</th>
              <th class="text-right sortable">Jasa RS</th>
              <th class="text-right sortable">Jasa Pelayanan</th>
              <th class="text-right sortable">Total (Rp)</th>
              <th class="action-col">Aksi</th>
            </tr>
          </thead>
          <tbody id="pendapatanUmumBody">
            <tr>
              <td colspan="8" class="text-center">Memuat rincian...</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="flex justify-between items-center" style="padding: 16px;">
        <p id="paginationInfoUmum" class="text-slate-500" style="font-size: 13px;">Menampilkan 0–0 dari 0 data</p>
        <div class="flex items-center gap-2">
          <button id="prevPageUmum" class="btn-aksi" disabled><i class="ph ph-caret-left"></i></button>
          <span id="pageInfoUmum" class="font-medium" style="font-size: 14px; min-width: 100px; text-align: center;">1 /
            1</span>
          <button id="nextPageUmum" class="btn-aksi" disabled><i class="ph ph-caret-right"></i></button>
        </div>
      </div>
    </div>
  </div>

  {{-- MODALS --}}
  @include('dashboard.partials.pendapatan-umum-form')
  @include('dashboard.partials.pendapatan-umum-detail')


</div>