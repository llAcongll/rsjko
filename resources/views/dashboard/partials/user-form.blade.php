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
        style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; max-height: 400px; overflow-y: auto; padding-right: 10px;">

        {{-- COLUMN 1: PENDAPATAN --}}
        <div>
          <p
            style="font-weight: 700; font-size: 12px; margin-bottom: 8px; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 4px;">
            1. MODUL PENDAPATAN</p>

          {{-- UMUM --}}
          <div
            style="margin-bottom: 15px; background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px solid #f1f5f9;">
            <p
              style="font-size: 11px; font-weight: 700; color: #1e293b; margin-bottom: 6px; display: flex; align-items: center; gap: 5px;">
              <i class="ph ph-cash-register"></i> PASIEN UMUM
            </p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px;">
              <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_UMUM_VIEW"> Lihat</label>
              </div>
              <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_UMUM_CREATE">
                  Tambah/Edit</label></div>
              <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_UMUM_DELETE"> Hapus</label>
              </div>
              <div class="permission-item"><label style="color: #0369a1;"><input type="checkbox"
                    value="PENDAPATAN_UMUM_POST"> Posting</label></div>
            </div>
          </div>

          {{-- BPJS --}}
          <div
            style="margin-bottom: 15px; background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px solid #f1f5f9;">
            <p
              style="font-size: 11px; font-weight: 700; color: #1e293b; margin-bottom: 6px; display: flex; align-items: center; gap: 5px;">
              <i class="ph ph-shield-check"></i> BPJS
            </p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px;">
              <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_BPJS_VIEW"> Lihat</label>
              </div>
              <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_BPJS_CREATE">
                  Tambah/Edit</label></div>
              <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_BPJS_DELETE"> Hapus</label>
              </div>
              <div class="permission-item"><label style="color: #0369a1;"><input type="checkbox"
                    value="PENDAPATAN_BPJS_POST"> Posting</label></div>
            </div>
          </div>

          {{-- JAMINAN --}}
          <div
            style="margin-bottom: 15px; background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px solid #f1f5f9;">
            <p
              style="font-size: 11px; font-weight: 700; color: #1e293b; margin-bottom: 6px; display: flex; align-items: center; gap: 5px;">
              <i class="ph ph-handshake"></i> JAMINAN
            </p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px;">
              <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_JAMINAN_VIEW"> Lihat</label>
              </div>
              <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_JAMINAN_CREATE">
                  Tambah/Edit</label></div>
              <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_JAMINAN_DELETE">
                  Hapus</label></div>
              <div class="permission-item"><label style="color: #0369a1;"><input type="checkbox"
                    value="PENDAPATAN_JAMINAN_POST"> Posting</label></div>
            </div>
          </div>

          {{-- KERJASAMA --}}
          <div
            style="margin-bottom: 15px; background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px solid #f1f5f9;">
            <p
              style="font-size: 11px; font-weight: 700; color: #1e293b; margin-bottom: 6px; display: flex; align-items: center; gap: 5px;">
              <i class="ph ph-users-three"></i> KERJASAMA
            </p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px;">
              <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_KERJA_VIEW"> Lihat</label>
              </div>
              <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_KERJA_CREATE">
                  Tambah/Edit</label></div>
              <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_KERJA_DELETE"> Hapus</label>
              </div>
              <div class="permission-item"><label style="color: #0369a1;"><input type="checkbox"
                    value="PENDAPATAN_KERJA_POST"> Posting</label></div>
            </div>
          </div>

          {{-- LAIN-LAIN --}}
          <div
            style="margin-bottom: 15px; background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px solid #f1f5f9;">
            <p
              style="font-size: 11px; font-weight: 700; color: #1e293b; margin-bottom: 6px; display: flex; align-items: center; gap: 5px;">
              <i class="ph ph-dots-three-circle"></i> LAIN-LAIN
            </p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px;">
              <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_LAIN_VIEW"> Lihat</label>
              </div>
              <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_LAIN_CREATE">
                  Tambah/Edit</label></div>
              <div class="permission-item"><label><input type="checkbox" value="PENDAPATAN_LAIN_DELETE"> Hapus</label>
              </div>
              <div class="permission-item"><label style="color: #0369a1;"><input type="checkbox"
                    value="PENDAPATAN_LAIN_POST"> Posting</label></div>
            </div>
          </div>

          {{-- PIUTANG & PENYESUAIAN --}}
          <div style="margin-top: 20px; border-top: 1px dashed #e2e8f0; padding-top: 15px;">
            <p style="font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 8px;">PIUTANG & POTONGAN</p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
              <div style="background: #fff; padding: 8px; border: 1px solid #f1f5f9; border-radius: 6px;">
                <div class="permission-item"><label><input type="checkbox" value="PIUTANG_VIEW"> Lihat Piutang</label>
                </div>
                <div class="permission-item"><label><input type="checkbox" value="PIUTANG_CREATE"> Tambah
                    Piutang</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PIUTANG_DELETE"> Hapus Piutang</label>
                </div>
              </div>
              <div style="background: #fff; padding: 8px; border: 1px solid #f1f5f9; border-radius: 6px;">
                <div class="permission-item"><label><input type="checkbox" value="PENYESUAIAN_VIEW"> Lihat
                    Potongan</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PENYESUAIAN_CREATE"> Tambah
                    Potongan</label></div>
                <div class="permission-item"><label><input type="checkbox" value="PENYESUAIAN_DELETE"> Hapus
                    Potongan</label></div>
              </div>
            </div>
          </div>
        </div>

        {{-- COLUMN 2: MASTER, PENGELUARAN, UTILITY --}}
        <div>
          <p
            style="font-weight: 700; font-size: 12px; margin-bottom: 8px; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 4px;">
            2. DATA MASTER (DASAR)</p>
          <div
            style="background: #fdf2f2; padding: 10px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #fee2e2;">
            <div class="permission-item"><label><input type="checkbox" value="MASTER_RUANGAN_VIEW"> Master
                Ruangan</label></div>
            <div class="permission-item"><label><input type="checkbox" value="MASTER_PERUSAHAAN_VIEW"> Master
                Perusahaan/Penjamin</label></div>
            <div class="permission-item"><label><input type="checkbox" value="MASTER_MOU_VIEW"> Master MOU & Pihak
                Ke-3</label></div>
            <div style="border-top: 1px solid #fecaca; margin: 8px 0; padding-top: 8px;"></div>
            <div class="permission-item"><label style="color: #0369a1; font-weight: 600;"><input type="checkbox"
                  value="MASTER_VIEW"> LIHAT SEMUA MASTER</label></div>
            <div class="permission-item"><label style="color: #b91c1c; font-weight: 600;"><input type="checkbox"
                  value="MASTER_CREATE"> KELOLA MASTER (TAMBAH/EDIT)</label></div>
            <div class="permission-item"><label style="color: #991b1b; font-weight: 700;"><input type="checkbox"
                  value="MASTER_DELETE"> HAPUS DATA MASTER</label></div>
          </div>

          <p
            style="font-weight: 700; font-size: 12px; margin-top: 20px; margin-bottom: 8px; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 4px;">
            3. MODUL PENGELUARAN (BELANJA)</p>
          <div class="permission-item"><label><input type="checkbox" value="PENGELUARAN_VIEW"> Lihat Transaksi
              Belanja</label></div>
          <div class="permission-item"><label><input type="checkbox" value="PENGELUARAN_CREATE"> Tambah/Edit
              Belanja</label></div>
          <div class="permission-item"><label><input type="checkbox" value="PENGELUARAN_DELETE"> Hapus Belanja</label>
          </div>
          <div class="permission-item"><label style="color: #0369a1;"><input type="checkbox" value="PENGELUARAN_CAIR">
              Posting Cair (SP2D)</label></div>
          <div class="permission-item"><label><input type="checkbox" value="PENGELUARAN_BKU"> Lihat Buku Kas Umum
              (BKU)</label></div>

          <p
            style="font-weight: 700; font-size: 12px; margin-top: 20px; margin-bottom: 8px; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 4px;">
            4. REK. KORAN & PENGESAHAN</p>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px;">
            <div class="permission-item"><label><input type="checkbox" value="REKENING_VIEW"> Lihat RK</label></div>
            <div class="permission-item"><label><input type="checkbox" value="REKENING_CREATE"> Kelola RK</label></div>
            <div class="permission-item"><label><input type="checkbox" value="REKENING_DELETE"> Hapus RK</label></div>
            <div class="permission-item"><label style="color: #0369a1;"><input type="checkbox" value="REKENING_IMPORT">
                Import RK</label></div>
          </div>
          <div style="border-top: 1px dashed #cbd5e1; margin: 6px 0;"></div>
          <div class="permission-item"><label><input type="checkbox" value="PENGESAHAN_VIEW"> Lihat Pengesahan
              (SP3BP/LRKB)</label></div>
          <div class="permission-item"><label><input type="checkbox" value="PENGESAHAN_CREATE"> Generate
              Pengesahan</label></div>
          <div class="permission-item"><label><input type="checkbox" value="PENGESAHAN_DELETE"> Hapus Pengesahan</label>
          </div>
          <div class="permission-item"><label style="color: #0369a1; font-weight: 600;"><input type="checkbox"
                value="PENGESAHAN_POST"> SAHKAN/BATAL SAHKAN</label></div>

          <p
            style="font-weight: 700; font-size: 12px; margin-top: 20px; margin-bottom: 8px; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 4px;">
            5. ANGGARAN & LAPORAN</p>
          <div class="permission-item"><label><input type="checkbox" value="KODE_REKENING_VIEW"> Lihat Kode Rek. &
              Anggaran</label></div>
          <div class="permission-item"><label><input type="checkbox" value="KODE_REKENING_CREATE"> Kelola Kode Rek. &
              Anggaran</label></div>
          <div class="permission-item"><label><input type="checkbox" value="LAPORAN_VIEW"> Lihat Dashboard &
              Laporan</label></div>
          <div class="permission-item"><label><input type="checkbox" value="LAPORAN_EXPORT"> Ekspor Laporan
              (Excel/PDF)</label></div>

          <p
            style="font-weight: 700; font-size: 12px; margin-top: 20px; margin-bottom: 8px; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 4px;">
            6. UTILITY & ADMIN</p>
          <div class="permission-item"><label><input type="checkbox" value="USER_VIEW"> Lihat User</label></div>
          <div class="permission-item"><label><input type="checkbox" value="USER_CREATE"> Kelola User</label></div>
          <div class="permission-item"><label><input type="checkbox" value="ACTIVITY_LOG_VIEW"> Lihat Log Sistem</label>
          </div>
        </div>
      </div> {{-- End Grid Div --}}
    </div> {{-- End permissionSection --}}

    <div class="modal-actions" style="margin-top: 25px;">
      <button class="btn-secondary" onclick="closeUserModal()">
        <i class="ph ph-x"></i> Batal
      </button>
      <button class="btn-primary" onclick="submitUser()">
        <i class="ph ph-floppy-disk"></i> Simpan
      </button>
    </div>
  </div> {{-- End confirm-box --}}
</div> {{-- End userModal --}}