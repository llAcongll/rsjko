<!-- Modal Tambah Periode LRKB -->
<div id="newLrkbModal" class="confirm-overlay">
    <div class="confirm-box" style="width: 400px; padding: 30px;">
        <div
            style="margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 18px; color: #0f172a;">Tambah Periode LRKB</h3>
            <button type="button" onclick="closeLrkbModal()"
                style="background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b; line-height: 1;">&times;</button>
        </div>
        <div class="modal-body">
            <label
                style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 13px;">Periode
                (Triwulan / Bulan)</label>
            <select id="newLrkbTriwulan" class="filter-date-input"
                style="width: 100%; height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;">
                <optgroup label="Triwulan">
                    <option value="T1">Triwulan I (Jan - Mar)</option>
                    <option value="T2">Triwulan II (Apr - Jun)</option>
                    <option value="T3">Triwulan III (Jul - Sep)</option>
                    <option value="T4">Triwulan IV (Okt - Des)</option>
                </optgroup>
                <optgroup label="Bulanan">
                    <option value="M1">Januari</option>
                    <option value="M2">Februari</option>
                    <option value="M3">Maret</option>
                    <option value="M4">April</option>
                    <option value="M5">Mei</option>
                    <option value="M6">Juni</option>
                    <option value="M7">Juli</option>
                    <option value="M8">Agustus</option>
                    <option value="M9">September</option>
                    <option value="M10">Oktober</option>
                    <option value="M11">November</option>
                    <option value="M12">Desember</option>
                </optgroup>
            </select>
            <div class="form-group" style="margin-bottom: 25px;">
                <label
                    style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 13px;">Tahun</label>
                <input type="number" id="newLrkbYear" class="filter-date-input" value="{{ date('Y') }}"
                    style="width: 100%; height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;">
            </div>
        </div>
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" class="btn-secondary" onclick="closeLrkbModal()"
                style="padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; background: #f1f5f9; border: 1px solid #e2e8f0;">Batal</button>
            <button type="button" class="btn-filter" onclick="createLrkbPeriod()"
                style="padding: 10px 25px; border-radius: 8px; font-weight: 600; cursor: pointer; background: #2563eb; color: white; border: none;">Simpan</button>
        </div>
    </div>
</div>