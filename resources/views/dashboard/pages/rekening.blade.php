<div class="dashboard">

  {{-- HEADER --}}
  <div class="dashboard-header">
    <div class="dashboard-header-left">
      <h2><i class="ph ph-article"></i> Rekening Koran</h2>
      <p>Kelola dan monitor histori transaksi rekening bank</p>
    </div>

    <div class="dashboard-header-right" style="display: flex; align-items: flex-end; gap: 12px;">
      {{-- FILTER INLINE --}}
      <div class="rekening-filter-inline" style="display: flex; gap: 12px; align-items: flex-end;">
        @if(auth()->user()->hasPermission('REKENING_TEMPLATE'))
          <button class="btn-toolbar btn-toolbar-outline"
            onclick="window.location.href='/dashboard/rekening-korans/template'" title="Download Template CSV">
            <i class="ph ph-download-simple"></i> Template
          </button>
        @endif

        @if(auth()->user()->hasPermission('REKENING_IMPORT'))
          <input type="file" id="importRekeningFile" style="display: none;" accept=".csv"
            onchange="uploadRekeningImport(this)">
          <button class="btn-toolbar btn-toolbar-outline" onclick="document.getElementById('importRekeningFile').click()"
            title="Import CSV">
            <i class="ph ph-upload-simple"></i> Import
          </button>
        @endif
        @if(auth()->user()->hasPermission('REKENING_BULK'))
          <button class="btn-toolbar btn-toolbar-danger" onclick="deleteBulkRekening()" title="Hapus Massal">
            <i class="ph ph-trash"></i> Hapus Massal
          </button>
        @endif

        <div class="filter-group-inline">
          <select id="filterBank" class="form-input-sm">
            <option value="">Semua Bank</option>
            <option>Bank Riau Kepri Syariah</option>
            <option>Bank Syariah Indonesia</option>
          </select>
        </div>
        <div class="filter-group-inline">
          <input type="date" id="filterStart" class="form-input-sm">
        </div>
        <div class="filter-group-inline">
          <input type="date" id="filterEnd" class="form-input-sm">
        </div>
        <button class="btn-filter-sm" onclick="applyRekeningFilter()" title="Terapkan Filter">
          <i class="ph ph-magnifying-glass"></i>
        </button>
        <button class="btn-filter" onclick="openPreviewRekening()" title="Preview & Unduh PDF"
          style="background: #0ea5e9; color: white; display: flex; align-items: center; gap: 8px;">
          <i class="ph ph-printer"></i>
          <span>Preview & Unduh</span>
        </button>
      </div>

      @if(auth()->user()->hasPermission('REKENING_CRUD'))
        <button class="btn-tambah-data" onclick="openRekeningForm()">
          <i class="ph-bold ph-plus"></i>
          <span>Tambah</span>
        </button>
      @endif
    </div>
  </div>


  {{-- SUMMARY CARDS --}}
  <style>
    /* Tighten page layout */
    .dashboard {
      gap: 16px !important;
    }

    .rekening-summary-container {
      display: flex;
      justify-content: center;
      margin-bottom: 0px;
    }

    .rekening-summary-container .dashboard-cards {
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      width: 100%;
      max-width: 900px;
      gap: 16px;
    }
  </style>

  <div class="rekening-summary-container">
    <div class="dashboard-cards">
      <div class="dash-card blue">
        <div class="dash-card-icon">
          <i class="ph ph-bank"></i>
        </div>
        <div class="dash-card-content">
          <span class="label">Saldo BRK Syariah</span>
          <h3 id="saldoBRKS">Rp 0</h3>
          <small id="percentBRKS" class="growth-up">0% dari total</small>
        </div>
      </div>

      <div class="dash-card purple">
        <div class="dash-card-icon">
          <i class="ph ph-bank"></i>
        </div>
        <div class="dash-card-content">
          <span class="label">Saldo BSI</span>
          <h3 id="saldoBSI">Rp 0</h3>
          <small id="percentBSI" class="growth-up">0% dari total</small>
        </div>
      </div>

      <div class="dash-card green">
        <div class="dash-card-icon">
          <i class="ph ph-wallet"></i>
        </div>
        <div class="dash-card-content">
          <span class="label">Total Saldo</span>
          <h3 id="saldoTotal" style="color: #16a34a;">Rp 0</h3>
          <small>Terakumulasi</small>
        </div>
      </div>
    </div>
  </div>

  {{-- TABLE BOX --}}
  <div class="dashboard-box">
    <div class="table-container">
      <table class="users-table rekening-table" id="rekeningTable">
        <thead>
          <tr>
            <th class="text-center">No</th>
            <th class="text-center">Tanggal</th>
            <th class="text-center">Bank</th>
            <th class="text-center">Keterangan</th>
            <th class="text-center">C/D</th>
            <th class="text-center">Jumlah</th>
            <th class="text-center">Saldo</th>
            <th class="text-center">Aksi</th>
          </tr>
        </thead>

        <tbody></tbody>
      </table>
    </div>
    <div class="flex justify-between items-center mt-4">
      <p id="rekeningInfo" class="text-slate-500" style="font-size: 13px;">Menampilkan 0–0 dari 0 data</p>

      <div class="flex items-center gap-2">
        <button id="prevPageRekening" class="btn-aksi" onclick="changeRekeningPage(-1)" disabled>
          <i class="ph ph-caret-left"></i>
        </button>
        <span id="pageInfoRekening" class="font-medium" style="font-size: 14px; min-width: 100px; text-align: center;">1
          / 1</span>
        <button id="nextPageRekening" class="btn-aksi" onclick="changeRekeningPage(1)" disabled>
          <i class="ph ph-caret-right"></i>
        </button>
      </div>
    </div>
  </div>

</div>

{{-- MODAL PREVIEW --}}
<div id="rekeningPreviewModal" class="confirm-overlay">
  <div class="confirm-box"
    style="max-width: 1100px; width: 95%; max-height: 95vh; display: flex; flex-direction: column; padding: 25px;">
    <div class="modal-header"
      style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px;">
      <h3 style="margin: 0; display: flex; align-items: center; gap: 8px;">
        <i class="ph ph-printer" style="color: #0ea5e9;"></i> Preview Rekening Koran
      </h3>
      <button onclick="closeRekeningPreview()"
        style="background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b;">&times;</button>
    </div>

    <div id="rekeningPreviewBody"
      style="flex: 1; overflow-y: auto; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
      {{-- Content will be rendered here --}}
    </div>

    <div class="modal-footer"
      style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 20px;">
      <div style="display: flex; align-items: center; gap: 15px;">
        <button class="btn-secondary" onclick="closeRekeningPreview()">
          <i class="ph ph-x-circle"></i> Tutup
        </button>

        <div
          style="display: flex; gap: 10px; background: #f8fafc; padding: 6px 12px; border-radius: 10px; border: 1px solid #e2e8f0;">
          <div class="filter-item" style="display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 10px; font-weight: 700; color: #475569;">PT. KIRI:</label>
            <select id="ptRekeningKiri" onchange="updateRekeningSignatory('Kiri')"
              style="height: 30px; padding: 0 6px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 10px; min-width: 140px; background: #fff;">
              <option value="">-- Kosong --</option>
            </select>
          </div>
          <div class="divider" style="width: 1px; height: 25px; background: #e2e8f0;"></div>
          <div class="filter-item" style="display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 10px; font-weight: 700; color: #475569;">PT. TENGAH:</label>
            <select id="ptRekeningTengah" onchange="updateRekeningSignatory('Tengah')"
              style="height: 30px; padding: 0 6px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 10px; min-width: 140px; background: #fff;">
              <option value="">-- Kosong --</option>
            </select>
          </div>
          <div class="divider" style="width: 1px; height: 25px; background: #e2e8f0;"></div>
          <div class="filter-item" style="display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 10px; font-weight: 700; color: #475569;">PT. KANAN:</label>
            <select id="ptRekeningKanan" onchange="updateRekeningSignatory('Kanan')"
              style="height: 30px; padding: 0 6px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 10px; min-width: 140px; background: #fff;">
              <option value="">-- Kosong --</option>
            </select>
          </div>
        </div>
      </div>

      <div style="display: flex; gap: 12px;">
        <button class="btn-primary" onclick="printRekeningExcel()"
          style="background: #10b981; border-color: #10b981; color: white;">
          <i class="ph ph-file-xls"></i> Unduh Excel
        </button>
        <button class="btn-primary" onclick="printRekening()"
          style="background: #ff4d4d; border-color: #ff4d4d; color: white;">
          <i class="ph ph-file-pdf"></i> Unduh PDF
        </button>
      </div>
    </div>
  </div>
</div>