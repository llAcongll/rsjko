<div class="page-container">
  <div class="page-header">
    <div class="page-header-left">
      <h2><i class="ph ph-buildings"></i> Manajemen Ruangan</h2>
      <p>Kelola daftar Master Ruangan di rumah sakit</p>
    </div>

    @if(auth()->user()->hasPermission('MASTER_MANAGE'))
      <div class="page-header-right">
        <button class="btn-tambah-data" onclick="openRuanganForm()">
          <i class="ph-bold ph-plus"></i>
          <span>Tambah Ruangan</span>
        </button>
      </div>
    @endif
  </div>

  <div class="dashboard-box">
    <div class="table-toolbar">
      <div class="table-search-wrapper">
        <i class="ph ph-magnifying-glass"></i>
        <input type="text" id="ruanganSearch" class="table-search" placeholder="Cari kode atau nama ruangan...">
      </div>
    </div>
    <div class="table-container">
      <table id="ruanganTable" class="table universal-table table-mobile-cards">
        <thead>
          <tr>
            <th class="text-center checkbox-col">No</th>
            <th class="text-center sortable">Kode</th>
            <th class="text-center sortable">Nama Ruangan</th>
            <th class="action-col">Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

    <div class="flex justify-between items-center mt-4">
      <p id="ruanganInfo" class="text-slate-500" style="font-size: 13px;"></p>

      <div class="flex items-center gap-2">
        <button id="prevPage" class="btn-aksi">
          <i class="ph ph-caret-left"></i>
        </button>
        <span id="pageInfo" class="font-medium" style="font-size: 14px; min-width: 100px; text-align: center;"></span>
        <button id="nextPage" class="btn-aksi">
          <i class="ph ph-caret-right"></i>
        </button>
      </div>
    </div>
  </div>

</div>





