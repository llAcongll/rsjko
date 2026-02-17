<div class="dashboard">

  {{-- HEADER --}}
  <div class="dashboard-header">
    <div class="dashboard-header-left">
      <h2><i class="ph ph-book-open-text"></i> Master Kode Rekening</h2>
      <p>Kelola struktur kode rekening pendapatan</p>
    </div>

    @if(auth()->user()->hasPermission('KODE_REKENING_CRUD'))
      <div class="dashboard-header-right">
        <button class="btn-tambah-data" onclick="openKodeRekeningForm()">
          <i class="ph-bold ph-plus"></i>
          <span>Tambah Kode</span>
        </button>
      </div>
    @endif
  </div>

  {{-- MAIN BOX --}}
  <div class="dashboard-box">
    <div id="kodeRekeningTree"></div>
  </div>

</div>