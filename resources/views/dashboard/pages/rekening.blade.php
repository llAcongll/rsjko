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