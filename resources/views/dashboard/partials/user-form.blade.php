<div id="userModal" class="confirm-overlay">
  <div class="confirm-box" style="max-width: 600px; width: 90%;">
    <h3 id="userModalTitle"><i class="ph ph-plus-circle"></i> Tambah User</h3>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
      <div class="form-group">
        <label>Username</label>
        <input id="userUsername" type="text" class="form-input">
      </div>

      <div class="form-group">
        <label>Role</label>
        <select id="userRole" class="form-input" onchange="togglePermissionSection()">
          <option value="USER">USER (Staff)</option>
          <option value="ADMIN">ADMIN (Full Access)</option>
        </select>
      </div>
    </div>

    <div class="form-group" id="userPasswordGroup">
      <label>Password</label>
      <input id="userPassword" type="password" class="form-input">
    </div>

    {{-- PERMISSIONS SECTION --}}
    <div id="permissionSection" style="margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 15px;">
      <h4 style="font-size: 14px; color: #64748b; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
        <i class="ph ph-lock-key"></i> Hak Akses (Permissions)
      </h4>

      <div
        style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; max-height: 300px; overflow-y: auto; padding-right: 10px;">

        {{-- REKENING & LAPORAN --}}
        <div>
          <p style="font-weight: 600; font-size: 12px; margin-bottom: 8px; color: #1e293b;">Dasar & Laporan</p>
          <div class="permission-item"><label><input type="checkbox" value="REKENING_VIEW"> Lihat Rek. Koran</label>
          </div>
          <div class="permission-item"><label><input type="checkbox" value="REKENING_CRUD"> Kelola Rek. Koran</label>
          </div>
          <div class="permission-item"><label><input type="checkbox" value="REKENING_TEMPLATE"> Template
              Rekening</label>
          </div>
          <div class="permission-item"><label><input type="checkbox" value="REKENING_IMPORT"> Import Rekening</label>
          </div>
          <div class="permission-item"><label><input type="checkbox" value="REKENING_BULK"> Hapus Massal</label>
          </div>

          <p style="font-weight: 600; font-size: 12px; margin-top: 12px; margin-bottom: 8px; color: #1e293b;">Laporan
            (View)</p>
          <div class="permission-item"><label><input type="checkbox" value="LAPORAN_PENDAPATAN"> Laporan
              Pendapatan</label></div>
          <div class="permission-item"><label><input type="checkbox" value="LAPORAN_PENGELUARAN"> Laporan
              Pengeluaran</label></div>
          <div class="permission-item"><label><input type="checkbox" value="LAPORAN_REKON"> Laporan Rekon Bank</label>
          </div>
          <div class="permission-item"><label><input type="checkbox" value="LAPORAN_PIUTANG"> Laporan Piutang</label>
          </div>
          <div class="permission-item"><label><input type="checkbox" value="LAPORAN_MOU"> Laporan MOU</label></div>
          <div class="permission-item"><label><input type="checkbox" value="LAPORAN_ANGGARAN"> Realisasi
              Anggaran & DPA</label></div>
          <div class="permission-item"><label style="color: #0369a1; font-weight: 600;"><input type="checkbox"
                value="LAPORAN_VIEW"> LIHAT SEMUA LAPORAN</label></div>
          <div class="permission-item"><label><input type="checkbox" value="LAPORAN_EXPORT"> Ekspor Laporan
              (Excel/CSV)</label></div>
          <div class="permission-item"><label><input type="checkbox" value="LAPORAN_EXPORT_PDF"> Ekspor Laporan
              (PDF)</label></div>

          <p style="font-weight: 600; font-size: 12px; margin-top: 12px; margin-bottom: 8px; color: #1e293b;">Piutang
          </p>
          <div class="permission-item"><label><input type="checkbox" value="PIUTANG_VIEW"> Lihat Piutang</label></div>
          <div class="permission-item"><label><input type="checkbox" value="PIUTANG_CRUD"> Kelola Piutang</label></div>

          <p style="font-weight: 600; font-size: 12px; margin-top: 12px; margin-bottom: 8px; color: #1e293b;">Potongan &
            Adm Bank</p>
          <div class="permission-item"><label><input type="checkbox" value="PENYESUAIAN_VIEW"> Lihat Penyesuaian</label>
          </div>
          <div class="permission-item"><label><input type="checkbox" value="PENYESUAIAN_CRUD"> Kelola
              Penyesuaian</label></div>

          <p style="font-weight: 600; font-size: 12px; margin-top: 12px; margin-bottom: 8px; color: #1e293b;">
            Pengeluaran (Belanja)</p>
          <div class="permission-item"><label><input type="checkbox" value="PENGELUARAN_VIEW"> Lihat Data</label></div>
          <div class="permission-item"><label><input type="checkbox" value="PENGELUARAN_CREATE"> Tambah Data</label>
          </div>
          <div class="permission-item"><label><input type="checkbox" value="PENGELUARAN_UPDATE"> Edit Data</label></div>
          <div class="permission-item"><label><input type="checkbox" value="PENGELUARAN_DELETE"> Hapus Data</label>
          </div>

          <p style="font-weight: 600; font-size: 12px; margin-top: 12px; margin-bottom: 8px; color: #1e293b;">Master
            Data (Dasar)</p>
          <div class="permission-item"><label><input type="checkbox" value="MASTER_RUANGAN_VIEW"> Lihat Ruangan</label>
          </div>
          <div class="permission-item"><label><input type="checkbox" value="MASTER_RUANGAN_CRUD"> Kelola Ruangan</label>
          </div>
          <div style="margin-top: 4px;"></div>
          <div class="permission-item"><label><input type="checkbox" value="MASTER_PERUSAHAAN_VIEW"> Lihat
              Perusahaan/Penjamin</label></div>
          <div class="permission-item"><label><input type="checkbox" value="MASTER_PERUSAHAAN_CRUD"> Kelola
              Perusahaan</label></div>
          <div style="margin-top: 4px;"></div>
          <div class="permission-item"><label><input type="checkbox" value="MASTER_MOU_VIEW"> Lihat MOU/Pihak
              Ke-3</label></div>
          <div class="permission-item"><label><input type="checkbox" value="MASTER_MOU_CRUD"> Kelola MOU</label></div>
          <div style="margin-top: 4px;"></div>
          <div class="permission-item"><label style="color: #0369a1; font-weight: 600;"><input type="checkbox"
                value="MASTER_VIEW"> AKSES SEMUA MASTER & TANDA TANGAN</label></div>
          <div style="margin-top: 4px;"></div>
          <p style="font-weight: 600; font-size: 12px; margin-top: 12px; margin-bottom: 8px; color: #1e293b;">Kode
            Rekening & Anggaran</p>
          <div style="font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 4px;">PENDAPATAN</div>
          <div class="permission-item"><label><input type="checkbox" value="KODE_REKENING_PENDAPATAN_VIEW"> Lihat
              Rekening & Anggaran</label></div>
          <div class="permission-item"><label><input type="checkbox" value="KODE_REKENING_PENDAPATAN_CRUD"> Kelola
              Rekening & Anggaran</label></div>

          <div style="font-size: 11px; font-weight: 700; color: #64748b; margin: 8px 0 4px;">PENGELUARAN</div>
          <div class="permission-item"><label><input type="checkbox" value="KODE_REKENING_PENGELUARAN_VIEW"> Lihat
              Rekening & Anggaran</label></div>
          <div class="permission-item"><label><input type="checkbox" value="KODE_REKENING_PENGELUARAN_CRUD"> Kelola
              Rekening & Anggaran</label></div>

          <div style="margin-top: 8px;"></div>
          <div class="permission-item"><label style="color: #0369a1; font-weight: 600;"><input type="checkbox"
                value="KODE_REKENING_VIEW"> LIHAT SEMUA REK & ANGGARAN</label></div>
          <div class="permission-item"><label style="color: #b91c1c; font-weight: 600;"><input type="checkbox"
                value="KODE_REKENING_CRUD"> KELOLA SEMUA REK & ANGGARAN</label></div>

          <p style="font-weight: 600; font-size: 12px; margin-top: 12px; margin-bottom: 8px; color: #1e293b;">User & Log
            Aktivitas</p>
          <div class="permission-item"><label><input type="checkbox" value="USER_VIEW"> Lihat Data User</label></div>
          <div class="permission-item"><label><input type="checkbox" value="USER_CRUD"> Kelola Data User</label></div>
          <div class="permission-item"><label><input type="checkbox" value="ACTIVITY_LOG_VIEW"> Lihat Log
              Aktivitas</label></div>
        </div>

        {{-- PENDAPATAN --}}
        <div>
          <p style="font-weight: 600; font-size: 12px; margin-bottom: 8px; color: #1e293b;">Pendapatan (View & CRUD)</p>

          <p style="font-size: 11px; font-weight: 700; color: #64748b; margin: 10px 0 5px;">PASIEN UMUM</p>
          <div class="permission-group-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px;">
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_UMUM_VIEW"> Lihat</label></div>
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_UMUM_CRUD"> Kelola</label>
            </div>
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_UMUM_TEMPLATE">
                Template</label></div>
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_UMUM_IMPORT"> Import</label>
            </div>
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_UMUM_BULK"> Hapus
                Massal</label></div>
          </div>

          <p style="font-size: 11px; font-weight: 700; color: #64748b; margin: 10px 0 5px;">BPJS</p>
          <div class="permission-group-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px;">
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_BPJS_VIEW"> Lihat</label></div>
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_BPJS_CRUD"> Kelola</label>
            </div>
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_BPJS_TEMPLATE">
                Template</label></div>
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_BPJS_IMPORT"> Import</label>
            </div>
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_BPJS_BULK"> Hapus
                Massal</label></div>
          </div>

          <p style="font-size: 11px; font-weight: 700; color: #64748b; margin: 10px 0 5px;">JAMINAN</p>
          <div class="permission-group-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px;">
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_JAMINAN_VIEW"> Lihat</label>
            </div>
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_JAMINAN_CRUD"> Kelola</label>
            </div>
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_JAMINAN_TEMPLATE">
                Template</label></div>
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_JAMINAN_IMPORT"> Import</label>
            </div>
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_JAMINAN_BULK"> Hapus
                Massal</label></div>
          </div>

          <p style="font-size: 11px; font-weight: 700; color: #64748b; margin: 10px 0 5px;">KERJASAMA</p>
          <div class="permission-group-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px;">
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_KERJA_VIEW"> Lihat</label>
            </div>
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_KERJA_CRUD"> Kelola</label>
            </div>
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_KERJA_TEMPLATE">
                Template</label></div>
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_KERJA_IMPORT"> Import</label>
            </div>
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_KERJA_BULK"> Hapus
                Massal</label></div>
          </div>

          <p style="font-size: 11px; font-weight: 700; color: #64748b; margin: 10px 0 5px;">LAIN-LAIN</p>
          <div class="permission-group-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px;">
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_LAIN_VIEW"> Lihat</label></div>
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_LAIN_CRUD"> Kelola</label>
            </div>
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_LAIN_TEMPLATE">
                Template</label></div>
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_LAIN_IMPORT"> Import</label>
            </div>
            <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_LAIN_BULK"> Hapus
                Massal</label></div>
          </div>
        </div>

      </div>
    </div>

    <div class="modal-actions">
      <button class="btn-secondary" onclick="closeUserModal()">
        <i class="ph ph-x"></i> Batal
      </button>
      <button class="btn-primary" onclick="submitUser()">
        <i class="ph ph-floppy-disk"></i> Simpan
      </button>
    </div>
  </div>
</div>