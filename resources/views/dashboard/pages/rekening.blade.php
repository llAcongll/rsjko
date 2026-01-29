<div class="rekening-page">

<div class="users-header">
  <h2>📒 Rekening Koran</h2>
</div>

<div class="rekening-filter">

  <div class="filter-group">
    <label>Bank</label>
    <select id="filterBank" class="form-input">
      <option value="">Semua Bank</option>
      <option>Bank Riau Kepri Syariah</option>
      <option>Bank Syariah Indonesia</option>
    </select>
  </div>

  <div class="filter-group">
    <label>Tanggal Awal</label>
    <input type="date" id="filterStart" class="form-input">
  </div>

  <div class="filter-group">
    <label>Tanggal Akhir</label>
    <input type="date" id="filterEnd" class="form-input">
  </div>

  <button class="btn-filter" onclick="applyRekeningFilter()">🔍 Filter</button>

  <button class="btn-add" onclick="openRekeningForm()">➕ Tambah</button>

</div>

<div class="rekening-summary">
  <div class="summary-card">
    <div class="summary-title">SALDO BANK RIAU KEPRI SYARIAH</div>
    <div class="summary-value" id="saldoBRKS">Rp 0,00</div>
    <div class="summary-percent" id="percentBRKS">0%</div>
  </div>

  <div class="summary-card">
    <div class="summary-title">SALDO BANK SYARIAH INDONESIA</div>
    <div class="summary-value" id="saldoBSI">Rp 0,00</div>
    <div class="summary-percent" id="percentBSI">0%</div>
  </div>

  <div class="summary-card highlight">
    <div class="summary-title">TOTAL SALDO REKENING KORAN</div>
    <div class="summary-value" id="saldoTotal">Rp 0,00</div>
  </div>
</div>

<div class="rekening-table-wrapper">
  <div class="rekening-table-scroll">
    <table class="users-table rekening-table" id="rekeningTable">
      <colgroup>
        <col style="width:60px">    <!-- No -->
        <col style="width:120px">   <!-- Tanggal -->
        <col style="width:180px">   <!-- Bank (dipersempit) -->
        <col>                       <!-- ✅ KETERANGAN (FLEX, SISA RUANG) -->
        <col style="width:60px">    <!-- C/D -->
        <col style="width:140px">   <!-- Jumlah -->
        <col style="width:140px">   <!-- Saldo -->
        <col style="width:120px">   <!-- Aksi -->
      </colgroup>

      <thead>
        <tr>
          <th>No</th>
          <th>Tanggal</th>
          <th>Bank</th>
          <th>Keterangan</th>
          <th>C/D</th>
          <th>Jumlah</th>
          <th>Saldo</th>
          <th>Aksi</th>
        </tr>
      </thead>

      <tbody></tbody>
    </table>
  </div>
</div>

<div id="rekeningPagination" class="pagination"></div>

<div id="rekeningInfo" class="table-info">
  Menampilkan 0–0 dari 0 data
</div>

</div>