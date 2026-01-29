  <!-- =========================
       HEADER
  ========================== -->
  <div class="pendapatan-header">
    <h2>💰 Pendapatan Umum</h2>
  </div>  

  <!-- =========================
       SEARCH
  ========================== -->
  <div class="pendapatan-toolbar">

    <div class="pendapatan-search">
      <input
        type="text"
        id="searchPendapatanUmum"
        placeholder="Cari nama / ruangan / tanggal..."
        oninput="filterPendapatanUmum(this.value)"
      >
    </div>

    <button class="btn-add-user" onclick="openPendapatanModal()">
      ➕ Tambah Data Umum
    </button>

  </div>

  <!-- =========================
       SUMMARY
  ========================== -->
  <div class="pendapatan-summary">

    <div class="summary-card">
      <div class="summary-title">TOTAL JASA RUMAH SAKIT UMUM</div>
      <div class="summary-value" id="totalJasaRS">Rp 0,00</div>
      <div class="summary-percent green" id="percentRS">0% dari total</div>
    </div>

    <div class="summary-card">
      <div class="summary-title">TOTAL JASA PELAYANAN UMUM</div>
      <div class="summary-value" id="totalJasaPelayanan">Rp 0,00</div>
      <div class="summary-percent green" id="percentPelayanan">0% dari total</div>
    </div>

    <div class="summary-card highlight">
      <div class="summary-title">TOTAL PENDAPATAN UMUM</div>
      <div class="summary-value big" id="totalPendapatanUmum">Rp 0,00</div>
    </div>

  </div>

  <!-- =========================
       TABLE
  ========================== -->
  <div class="pendapatan-table-wrapper">
    <div class="pendapatan-table-scroll">

      <table class="pendapatan-table" id="pendapatanUmumTable">
        <thead>
          <tr>
            <th style="width:60px">No</th>
            <th style="width:130px">Tanggal</th>
            <th>Nama Pasien</th>
            <th>Ruangan</th>
            <th style="width:140px; text-align:right">Jumlah</th>
            <th style="width:140px; text-align:center">Aksi</th>
          </tr>
        </thead>

        <tbody  id="pendapatanUmumBody">
          {{-- data di-render via JS --}}
        </tbody>
      </table>

    </div>
  </div>

</div>

@include('dashboard.partials.pendapatan-umum-detail')
