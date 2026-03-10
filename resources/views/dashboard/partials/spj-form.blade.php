{{-- MODAL SPJ FORM (Moved to partial to avoid stacking issues) --}}
<div id="spjFormModal" class="confirm-overlay">
    <div class="confirm-modal modal-large">
        <div class="modal-header">
            <h3><i class="ph ph-file-plus"></i> <span id="spjFormTitle">Buat SPJ</span></h3>
            <button class="btn-close" onclick="closeSpjModal()"><i class="ph ph-x"></i></button>
        </div>
        <div class="modal-body">
            <form id="formSpj">
                <input type="hidden" name="id" id="spjId">
                <div class="form-grid grid-2">
                    <div class="form-group">
                        <label>Nomor SPJ</label>
                        <input type="text" name="spj_number" id="spjNumber" class="form-input" required
                            placeholder="Contoh: 001/SPJ-UP/2026">
                    </div>
                    <div class="form-group">
                        <label>Tanggal SPJ</label>
                        <input type="date" name="spj_date" id="spjDate" class="form-input" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Bendahara Pengeluaran</label>
                    <select name="bendahara_id" id="spjBendahara" class="form-input" required>
                        <option value="{{ auth()->id() }}">{{ auth()->user()->username }} (Anda)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Pilih Belanja untuk di-SPJ-kan (Tipe UP yang belum ber-SPJ)</label>
                    <div id="unlinkedExpendituresList"
                        style="max-height: 200px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px;">
                        <p class="text-slate-500 text-center">Memuat belanja...</p>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeSpjModal()">Batal</button>
            <button class="btn-ok" onclick="submitSpj(event)" id="btnSimpanSpj">Simpan SPJ</button>
        </div>
    </div>
</div>





