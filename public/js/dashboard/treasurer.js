/* =========================
   SPJ LOGIC
   ========================= */

window.initSpj = function () {
    loadSpj();
};

window.loadSpj = function (search = '') {
    const tbody = document.getElementById('tableSpjBody');
    if (!tbody) return;

    fetch(`/dashboard/spj?search=${search}`, { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(res => {
            const data = res.data || [];
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">Belum ada data SPJ.</td></tr>';
                return;
            }
            tbody.innerHTML = '';
            data.forEach((item, index) => {
                tbody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td>${item.spj_number}</td>
                        <td>${formatTanggal(item.spj_date)}</td>
                        <td>${item.bendahara ? item.bendahara.username : '-'}</td>
                        <td class="text-center">${item.items ? item.items.length : 0} items</td>
                        <td class="text-center"><span class="badge-mini ${getStatusClass(item.status)}">${item.status}</span></td>
                        <td class="text-center">
                            <button class="btn-aksi" onclick="viewSpjDetail(${item.id})"><i class="ph ph-printer"></i></button>
                            <button class="btn-aksi delete" onclick="hapusSpj(${item.id})"><i class="ph ph-trash"></i></button>
                        </td>
                    </tr>
                `);
            });
        });
};

function getStatusClass(status) {
    if (status === 'VALID' || status === 'CAIR') return 'bg-green-100 text-green-700';
    if (status === 'SUBMITTED' || status === 'SPM') return 'bg-blue-100 text-blue-700';
    if (status === 'SPP') return 'bg-amber-100 text-amber-700';
    return 'bg-slate-100 text-slate-700';
}

window.openSpjForm = function () {
    document.getElementById('formSpj').reset();
    document.getElementById('spjId').value = '';
    document.getElementById('spjDate').value = window.getTodayLocal();
    document.getElementById('spjFormModal').classList.add('show');
    loadUnlinkedExpenditures();
};

function loadUnlinkedExpenditures() {
    const container = document.getElementById('unlinkedExpendituresList');
    container.innerHTML = '<p class="text-slate-500 text-center">Memuat belanja...</p>';

    fetch('/dashboard/expenditures/unlinked', { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) {
                container.innerHTML = '<p class="text-slate-500 text-center">Tidak ada belanja UP yang perlu di-SPJ-kan.</p>';
                return;
            }
            container.innerHTML = '';
            data.forEach(item => {
                container.insertAdjacentHTML('beforeend', `
                    <div style="display:flex; align-items:center; gap:10px; padding:8px; border-bottom:1px solid #f1f5f9;">
                        <input type="checkbox" name="expenditure_ids[]" value="${item.id}" id="exp_${item.id}">
                        <label for="exp_${item.id}" style="flex:1; cursor:pointer;">
                            <strong>${formatRupiah(item.gross_value)}</strong> - ${item.description}
                            <br><small class="text-slate-400">${formatTanggal(item.spending_date)} | ${item.kode_rekening.nama}</small>
                        </label>
                    </div>
                `);
            });
        });
}

window.submitSpj = function (e) {
    e.preventDefault();
    const form = document.getElementById('formSpj');
    const checked = Array.from(form.querySelectorAll('input[name="expenditure_ids[]"]:checked')).map(el => el.value);

    if (checked.length === 0) {
        toast('Pilih minimal satu belanja', 'error');
        return;
    }

    const data = {
        spj_number: document.getElementById('spjNumber').value,
        spj_date: document.getElementById('spjDate').value,
        bendahara_id: document.getElementById('spjBendahara').value,
        expenditure_ids: checked
    };

    fetch('/dashboard/spj', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
        .then(async res => {
            if (!res.ok) {
                const err = await res.json();
                if (err.errors) {
                    const msgs = Object.values(err.errors).flat().join(', ');
                    throw new Error(msgs);
                }
                throw new Error(err.message || 'Gagal menyimpan SPJ');
            }
            toast('SPJ berhasil dibuat', 'success');
            closeSpjModal();
            loadSpj();
        })
        .catch(err => toast(err.message, 'error'));
};

window.closeSpjModal = () => document.getElementById('spjFormModal').classList.remove('show');

window.handleSearchSpj = function (e) {
    if (e.key === 'Enter') {
        const search = e.target.value;
        loadSpj(search);
    }
};

window.viewSpjDetail = function (id) {
    window.open(`/dashboard/spj/${id}/print`, '_blank');
};

window.hapusSpj = function (id) {
    const modal = document.getElementById('modalConfirmAction');
    if (modal) {
        document.getElementById('confirmActionIcon').innerHTML = '<i class="ph ph-trash-simple" style="color:#dc2626;"></i>';
        document.getElementById('confirmActionTitle').innerText = 'Hapus SPJ';
        document.getElementById('confirmActionMessage').innerHTML = 'Hapus SPJ ini? Belanja yang terkait akan menjadi UNLINKED kembali.';

        const btn = document.getElementById('btnConfirmActionProceed');
        btn.innerHTML = `<i class="ph ph-trash"></i> Ya, Hapus SPJ`;
        btn.style.backgroundColor = '#dc2626';
        btn.style.color = '#ffffff';
        btn.onclick = function () {
            closeConfirmActionModal();
            executeHapusSpj(id);
        };
        modal.classList.add('show');
    } else {
        if (confirm('Hapus SPJ ini? Belanja yang terkait akan menjadi UNLINKED kembali.')) {
            executeHapusSpj(id);
        }
    }
};

function executeHapusSpj(id) {
    fetch(`/dashboard/spj/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
        .then(async res => {
            if (!res.ok) {
                const err = await res.json();
                throw new Error(err.message || 'Gagal menghapus SPJ');
            }
            toast('SPJ berhasil dihapus', 'success');
            loadSpj();
        })
        .catch((err) => toast(err.message || 'Gagal menghapus SPJ', 'error'));
}

/* =========================
   SALDO DANA LOGIC
   ========================= */

window.initSaldoDana = function () {
    loadSaldoSummary();
    loadSaldoTable();
};

function loadSaldoSummary() {
    const container = document.getElementById('saldoSummaryCards');
    if (!container) return;

    const year = document.getElementById('ledgerYear')?.value || new Date().getFullYear();

    fetch(`/dashboard/disbursements/saldo-summary?year=${year}`)
        .then(res => res.json())
        .then(results => {
            const fmt = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');

            const icons = { UP: 'ph-wallet', GU: 'ph-arrows-clockwise', LS: 'ph-arrow-right' };
            const colors = { UP: '#2563eb', GU: '#059669', LS: '#7c3aed' };
            const bgColors = {
                UP: 'linear-gradient(135deg, #eff6ff, #dbeafe)',
                GU: 'linear-gradient(135deg, #ecfdf5, #d1fae5)',
                LS: 'linear-gradient(135deg, #f5f3ff, #ede9fe)'
            };

            let html = '';
            results.forEach(r => {
                const type = r.type;
                const sisaColor = r.sisa_kas > 0 ? '#059669' : '#dc2626';
                const color = colors[type] || '#64748b';
                const bg = bgColors[type] || 'linear-gradient(135deg, #f8fafc, #f1f5f9)';
                const icon = icons[type] || 'ph-coin';

                html += `
                    <div style="background:${bg}; border:1px solid ${color}30; border-radius:12px; padding:16px 20px;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                            <i class="ph ${icon}" style="font-size:20px; color:${color};"></i>
                            <span style="font-weight:700; font-size:14px; color:${color};">${r.label}</span>
                        </div>
                        <div style="font-size:11px; color:#64748b;">Total Dana Cair</div>
                        <div style="font-weight:700; font-size:13px; margin-bottom:6px;">${fmt(r.total_cair)}</div>
                        <div style="font-size:11px; color:#64748b;">Total Belanja</div>
                        <div style="font-weight:700; font-size:13px; color:#dc2626; margin-bottom:6px;">${fmt(r.total_belanja)}</div>
                        <div style="font-size:11px; color:#64748b;">SPP Dalam Proses</div>
                        <div style="font-weight:700; font-size:13px; color:#b45309; margin-bottom:6px;">${fmt(r.spp_pending)}</div>
                        <div style="border-top:1px solid ${color}30; padding-top:8px; margin-top:4px;">
                            <div style="font-size:11px; color:#64748b;">Sisa Saldo Kas</div>
                            <div style="font-weight:800; font-size:18px; color:${sisaColor};">${fmt(r.sisa_kas)}</div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        });
}

function loadSaldoTable() {
    const tbody = document.getElementById('tableSaldoBody');
    if (!tbody) return;

    fetch('/dashboard/disbursements?limit=100&status=CAIR&is_saldo=1', { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(res => {
            const data = res.data || [];
            tbody.innerHTML = '';

            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">Belum ada data saldo.</td></tr>';
                return;
            }

            data.forEach((item, index) => {
                const statusHtml = '<span style="font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:4px; background:#dcfce7; color:#166534;">CAIR</span>';
                const siklusLabel = item.siklus_up && item.type === 'GU' ? `GU-${item.siklus_up}` : '-';

                const canDelete = window.hasPermission('PENGELUARAN_SALDO_DELETE') || window.isAdmin;
                const deleteBtn = canDelete ? `<button class="btn-aksi delete" title="Hapus" onclick="hapusSaldo(${item.id})"><i class="ph ph-trash"></i></button>` : '';

                tbody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td class="text-center"><span class="badge-mini">${item.type}</span></td>
                        <td class="text-center font-mono">${siklusLabel}</td>
                        <td class="text-center">${formatTanggal(item.sp2d_date)}</td>
                        <td>${item.description || item.uraian || '-'}</td>
                        <td class="text-right font-mono">${formatRupiahTable(item.value)}</td>
                        <td class="text-center">${statusHtml}</td>
                        <td class="text-center">
                            ${deleteBtn}
                        </td>
                    </tr>
                `);
            });
        });
}

window.openSaldoForm = function () {
    document.getElementById('formSaldo').reset();
    document.getElementById('saldoDate').value = window.getTodayLocal();
    document.getElementById('saldoSiklusGroup').style.display = 'none';
    document.getElementById('saldoFormModal').classList.add('show');
};

window.closeSaldoModal = function () {
    document.getElementById('saldoFormModal').classList.remove('show');
};

// Show siklus field for GU only
document.addEventListener('change', function (e) {
    if (e.target && e.target.id === 'saldoType') {
        const type = e.target.value;
        const siklusGroup = document.getElementById('saldoSiklusGroup');
        if (siklusGroup) {
            siklusGroup.style.display = type === 'GU' ? 'block' : 'none';
        }
        // Auto-suggest next siklus
        if (type === 'GU') {
            const year = new Date().getFullYear();
            fetch(`/dashboard/disbursements/next-siklus?type=${type}&year=${year}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('saldoSiklus').value = data.next;
                });
        }
    }
});

window.submitSaldo = function (e) {
    e.preventDefault();
    const form = document.getElementById('formSaldo');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    // Status is always CAIR for saldo entries
    data.status = 'CAIR';

    fetch('/dashboard/disbursements', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
        .then(async res => {
            if (!res.ok) {
                const err = await res.json();
                if (err.errors) {
                    throw new Error(Object.values(err.errors).flat().join(', '));
                }
                throw new Error(err.message || 'Gagal menyimpan saldo');
            }
            toast('Saldo berhasil ditambahkan', 'success');
            closeSaldoModal();
            loadSaldoSummary();
            loadSaldoTable();
        })
        .catch(err => toast(err.message, 'error'));
};

window.hapusSaldo = function (id) {
    const modal = document.getElementById('modalConfirmAction');
    if (modal) {
        document.getElementById('confirmActionIcon').innerHTML = '<i class="ph ph-trash" style="color:#dc2626;"></i>';
        document.getElementById('confirmActionTitle').innerText = 'Hapus Saldo';
        document.getElementById('confirmActionMessage').innerText = 'Hapus data saldo ini? Entri BKU juga akan dihapus.';

        const btn = document.getElementById('btnConfirmActionProceed');
        btn.innerHTML = `<i class="ph ph-trash"></i> Hapus Saldo`;
        btn.style.backgroundColor = '#dc2626';
        btn.style.color = '#ffffff';
        btn.onclick = function () {
            closeConfirmActionModal();
            executeHapusSaldo(id);
        };
        modal.classList.add('show');
    } else {
        if (confirm('Hapus data saldo ini? Entri BKU juga akan dihapus.')) {
            executeHapusSaldo(id);
        }
    }
};

function executeHapusSaldo(id) {
    fetch(`/dashboard/disbursements/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
        .then(async res => {
            if (!res.ok) {
                const err = await res.json();
                throw new Error(err.message || 'Gagal menghapus');
            }
            toast('Saldo berhasil dihapus', 'success');
            loadSaldoSummary();
            loadSaldoTable();
        })
        .catch(err => toast(err.message, 'error'));
}

/* =========================
   DISBURSEMENT LOGIC
   ========================= */

let disbursementStatusFilter = '';
let disbursementPageMode = '';

window.initDisbursement = function () {
    disbursementPageMode = window._disbursementPageMode || 'SPP';

    // Configure page based on mode
    const titleEl = document.querySelector('.dashboard-header h2');
    const subEl = document.querySelector('.dashboard-header p');
    const addBtn = document.querySelector('.btn-tambah-data');

    if (disbursementPageMode === 'SPP') {
        if (titleEl) titleEl.innerHTML = '<i class="ph ph-file-text"></i> SPP (Surat Permintaan Pembayaran)';
        if (subEl) subEl.textContent = 'Buat dan kelola pengajuan SPP';
        if (addBtn) addBtn.style.display = '';
        disbursementStatusFilter = 'DRAFT,SPP';
    } else if (disbursementPageMode === 'SPM') {
        if (titleEl) titleEl.innerHTML = '<i class="ph ph-seal-check"></i> SPM (Surat Perintah Membayar)';
        if (subEl) subEl.textContent = 'Proses SPP menjadi SPM';
        if (addBtn) addBtn.style.display = 'none';
        disbursementStatusFilter = 'SPP,SPM';
    } else if (disbursementPageMode === 'SP2D') {
        if (titleEl) titleEl.innerHTML = '<i class="ph ph-check-circle"></i> SP2D (Surat Perintah Pencairan Dana)';
        if (subEl) subEl.textContent = 'Proses pencairan & Assign nomor SP2D';
        if (addBtn) addBtn.style.display = 'none';
        disbursementStatusFilter = 'SPM,CAIR';
    } else if (disbursementPageMode === 'PENCAIRAN') {
        if (titleEl) titleEl.innerHTML = '<i class="ph ph-wallet"></i> Realisasi Pencairan (UP/GU/LS)';
        if (subEl) subEl.textContent = 'Daftar kegiatan yang telah dicairkan';
        if (addBtn) addBtn.style.display = 'none';
        disbursementStatusFilter = 'CAIR';
    }

    // Set active filter button
    document.querySelectorAll('.btn-filter-status').forEach(b => {
        b.classList.remove('active');
        if (b.dataset.status === disbursementStatusFilter) b.classList.add('active');
    });

    loadDisbursements();
};

window.filterDisbursementStatus = function (status, btn) {
    disbursementStatusFilter = status;
    document.querySelectorAll('.btn-filter-status').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    loadDisbursements();
};

window.loadDisbursements = function () {
    const tbody = document.getElementById('tableDisbursementBody');
    if (!tbody) return;

    let url = '/dashboard/disbursements?limit=50&is_saldo=0';
    if (disbursementStatusFilter) url += `&status=${disbursementStatusFilter}`;

    // Apply Type Filter for PENCAIRAN mode (UP/GU/LS)
    if (disbursementPageMode === 'PENCAIRAN') {
        url += '&type=UP,GU,LS';
    }

    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(res => {
            const data = res.data || [];
            tbody.innerHTML = '';

            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" class="text-center">Belum ada data.</td></tr>';
                return;
            }

            data.forEach((item, index) => {
                const docNumbers = [];
                if (item.spp_no) docNumbers.push(`<div style="color: #b45309; font-weight: 600; font-size: 0.75rem;"><i class="ph ph-file-text"></i> SPP: ${item.spp_no}</div>`);
                if (item.spm_no) docNumbers.push(`<div style="color: #047857; font-weight: 600; font-size: 0.75rem;"><i class="ph ph-seal-check"></i> SPM: ${item.spm_no}</div>`);
                if (item.sp2d_no) docNumbers.push(`<div style="color: #1d4ed8; font-weight: 800; font-size: 0.8rem; margin-top: 2px; border-top: 1px solid #e2e8f0; padding-top: 2px;"><i class="ph ph-check-circle"></i> SP2D: ${item.sp2d_no}</div>`);
                const docNoHtml = docNumbers.length > 0 ? docNumbers.join('') : '<span style="color:#94a3b8">Belum ada</span>';

                // Build action buttons based on current status AND page mode
                let actionHtml = '';
                const canSppCreate = window.hasPermission('PENGELUARAN_SPP_CREATE') || window.isAdmin;
                const canSppDelete = window.hasPermission('PENGELUARAN_SPP_DELETE') || window.isAdmin;
                const canSpmCreate = window.hasPermission('PENGELUARAN_SPM_CREATE') || window.isAdmin;
                const canSpmDelete = window.hasPermission('PENGELUARAN_SPM_DELETE') || window.isAdmin;
                const canSp2dCreate = window.hasPermission('PENGELUARAN_SP2D_CREATE') || window.isAdmin;
                const canSp2dDelete = window.hasPermission('PENGELUARAN_SP2D_DELETE') || window.isAdmin;
                const canCairView = window.hasPermission('PENGELUARAN_CAIR_VIEW') || window.isAdmin;
                const canCairCreate = window.hasPermission('PENGELUARAN_CAIR_CREATE') || window.isAdmin;

                if (disbursementPageMode === 'SPP') {
                    if (item.status === 'DRAFT' || item.status === 'SPP') {
                        if (canSppCreate) actionHtml += `<button class="btn-aksi" title="Edit SPP" onclick="editDisbursement(${item.id})" style="color:#2563eb;"><i class="ph ph-pencil-simple"></i></button>`;
                        if (canSppDelete) actionHtml += `<button class="btn-aksi delete" title="Hapus SPP" onclick="hapusDisbursement(${item.id})"><i class="ph ph-trash"></i></button>`;
                    }
                } else if (disbursementPageMode === 'SPM') {
                    if (item.status === 'SPP') {
                        if (canSpmCreate) actionHtml += `<button class="btn-aksi" title="Proses ke SPM" onclick="advanceStatus(${item.id}, 'SPM')" style="color:#047857;"><i class="ph ph-seal-check"></i> <span style='font-size:0.7rem'>Ke SPM</span></button>`;
                    }
                    if (item.status === 'SPM') {
                        if (canSpmDelete) actionHtml += `<button class="btn-aksi" title="Batalkan SPM" onclick="revertStatus(${item.id}, 'SPP')" style="color:#dc2626;"><i class="ph ph-arrow-counter-clockwise"></i> <span style='font-size:0.7rem'>Batal</span></button>`;
                    }
                } else if (disbursementPageMode === 'SP2D') {
                    if (item.status === 'SPM') {
                        if (canSp2dCreate) actionHtml += `<button class="btn-aksi" title="Cairkan (Assign SP2D)" onclick="advanceStatus(${item.id}, 'CAIR')" style="color:#1d4ed8;"><i class="ph ph-check-circle"></i> <span style='font-size:0.7rem'>Assign SP2D</span></button>`;
                    }
                    if (item.status === 'CAIR') {
                        actionHtml += `<span style="font-size:0.7rem; color:#64748b">Selesai (Lihat di Pencairan)</span>`;
                    }
                } else if (disbursementPageMode === 'PENCAIRAN') {
                    // Horizontal Action Group
                    if (item.status === 'CAIR') {
                        actionHtml += `<div style="display: flex; gap: 6px; justify-content: center; align-items: center;">`;

                        // Detail Belanja (View or Manage)
                        if (canCairView || canCairCreate) {
                            actionHtml += `
                                <button class="btn-aksi" title="Detail Belanja" onclick="openBelanjaItems(${item.id})" 
                                    style="width: auto; height: 32px; padding: 0 12px; border-radius: 8px; background: #ecfdf5; color: #059669; font-weight: 700; display: flex; align-items: center; gap: 5px; border: 1px solid #10b981;">
                                    <i class="ph ph-shopping-cart" style="font-size: 14px;"></i> <span>Belanja</span>
                                </button>
                            `;
                        }

                        // Always allow view detail of record if they can view pencairan listed here
                        actionHtml += `
                            <button class="btn-aksi" title="Lihat Detail" onclick="viewDisbursement(${item.id})" 
                                style="width: 32px; height: 32px; background: #eff6ff; color: #2563eb; border: 1px solid #3b82f6;">
                                <i class="ph ph-eye"></i>
                            </button>
                        `;

                        // Edit record info
                        if (canCairCreate) {
                            actionHtml += `
                                <button class="btn-aksi" title="Edit Data" onclick="editDisbursement(${item.id})" 
                                    style="width: 32px; height: 32px; background: #fffbeb; color: #d97706; border: 1px solid #f59e0b;">
                                    <i class="ph ph-pencil-simple"></i>
                                </button>
                            `;
                        }

                        // Revert SP2D
                        if (canSp2dDelete) {
                            actionHtml += `
                                <button class="btn-aksi" title="Batalkan SP2D" onclick="revertStatus(${item.id}, 'SPM')" 
                                    style="width: auto; height: 32px; padding: 0 12px; border-radius: 8px; background: #fef2f2; color: #dc2626; font-weight: 700; display: flex; align-items: center; gap: 5px; border: 1px solid #ef4444;">
                                    <i class="ph ph-arrow-counter-clockwise" style="font-size: 14px;"></i> <span>Batal</span>
                                </button>
                            `;
                        }

                        actionHtml += `</div>`;
                    }
                }

                // Status badge with step indicator
                const statusBadge = getStatusBadge(item.status);

                // Kegiatan info
                let kegiatanHtml = '-';
                if (item.uraian) {
                    kegiatanHtml = `<div style="font-size:0.8rem; font-weight:600;">${item.uraian}</div>`;
                }
                if (item.kode_rekening) {
                    kegiatanHtml += `<div style="font-size:0.7rem; color:#64748b;">${item.kode_rekening.kode} - ${item.kode_rekening.nama}</div>`;
                }

                tbody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td class="text-center font-mono">${item.paket_number}</td>
                        <td class="text-center"><span class="badge-mini">${item.type}</span></td>
                        <td class="text-left font-mono">${docNoHtml}</td>
                        <td class="text-center font-mono" style="font-size:0.8rem">${item.siklus_number}</td>
                        <td class="text-center">${formatTanggal(item.sp2d_date)}</td>
                        <td>${kegiatanHtml}</td>
                        <td class="text-right font-mono">${formatRupiahTable(item.value)}</td>
                        <td class="text-center">${statusBadge}</td>
                        <td class="text-center" style="white-space:nowrap;">${actionHtml}</td>
                    </tr>
                `);
            });
        });
};

function getStatusBadge(status) {
    const steps = ['SPP', 'SPM', 'CAIR'];
    const currentIdx = steps.indexOf(status);

    if (status === 'DRAFT') {
        return '<span class="badge-mini bg-slate-100 text-slate-700">DRAFT</span>';
    }

    let html = '<div style="display:flex; align-items:center; gap:3px; justify-content:center;">';
    steps.forEach((step, i) => {
        const label = step === 'CAIR' ? 'SP2D' : step;
        if (i <= currentIdx) {
            html += `<span style="font-size:0.65rem; font-weight:700; padding:2px 5px; border-radius:4px; background:${i === currentIdx ? '#059669' : '#d1fae5'}; color:${i === currentIdx ? '#fff' : '#047857'};">${label}</span>`;
        } else {
            html += `<span style="font-size:0.65rem; font-weight:600; padding:2px 5px; border-radius:4px; background:#f1f5f9; color:#94a3b8;">${label}</span>`;
        }
        if (i < steps.length - 1) {
            html += `<i class="ph ph-caret-right" style="font-size:8px; color:${i < currentIdx ? '#059669' : '#cbd5e1'};"></i>`;
        }
    });
    html += '</div>';
    return html;
}

window.closeConfirmActionModal = function () {
    const modal = document.getElementById('modalConfirmAction');
    if (modal) modal.classList.remove('show');
};

window.advanceStatus = function (id, newStatus) {
    const labels = { SPP: 'SPP', SPM: 'SPM', CAIR: 'SP2D (Cairkan)' };
    const label = labels[newStatus] || newStatus;

    const modal = document.getElementById('modalConfirmAction');
    if (modal) {
        document.getElementById('confirmActionIcon').innerHTML = '<i class="ph ph-seal-check" style="color:#047857;"></i>';
        document.getElementById('confirmActionTitle').innerText = 'Proses ' + label;

        let message = `Lanjutkan ke tahap ${label}?`;

        if (newStatus === 'SPM' || newStatus === 'CAIR') {
            message += `<br><span style="font-size:13px; color:#64748b;">Nomor ${label} akan dibuat otomatis oleh sistem.</span>`;
        } else {
            message += `\nNomor dokumen akan dibuat otomatis jika dikosongkan.`;
            message += `
                <div style="margin-top:15px; text-align:left;">
                    <label style="font-size:12px; font-weight:700; color:#475569;">Masukkan Nomor ${label} (Opsional)</label>
                    <input type="text" id="manualDocNumber" class="form-input" placeholder="Kosongkan untuk auto-generate" style="margin-top:5px; width:100%;">
                </div>
            `;
        }

        document.getElementById('confirmActionMessage').innerHTML = message;

        const btn = document.getElementById('btnConfirmActionProceed');
        btn.innerHTML = `<i class="ph ph-check"></i> Ya, Lanjutkan`;
        btn.style.backgroundColor = '#047857';
        btn.style.color = '#ffffff';
        btn.onclick = function () {
            const manualNo = document.getElementById('manualDocNumber')?.value;
            closeConfirmActionModal();
            executeAdvance(id, newStatus, label, manualNo);
        };
        modal.classList.add('show');
    } else {
        let manualNo = '';
        if (newStatus !== 'SPM' && newStatus !== 'CAIR') {
            manualNo = prompt(`Lanjutkan ke tahap ${label}?\nMasukkan nomor manual jika ingin menggunakan nomor tertentu (atau kosongkan untuk otomatis):`);
            if (manualNo === null) return;
        } else {
            if (!confirm(`Lanjutkan ke tahap ${label}?`)) return;
        }
        executeAdvance(id, newStatus, label, manualNo);
    }
};

function executeAdvance(id, newStatus, label, manualNo = '') {
    const body = { status: newStatus };
    if (manualNo) {
        if (newStatus === 'SPP') body.spp_no = manualNo;
        if (newStatus === 'SPM') body.spm_no = manualNo;
        if (newStatus === 'CAIR') body.sp2d_no = manualNo;
    }

    fetch(`/dashboard/disbursements/${id}/status`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(body)
    })
        .then(async res => {
            if (!res.ok) {
                const err = await res.json();
                throw new Error(err.message || `Gagal memproses ${label}`);
            }
            toast(`${label} berhasil diproses`, 'success');
            loadDisbursements();
        })
        .catch(err => toast(err.message, 'error'));
}

window.revertStatus = function (id, targetStatus) {
    const labels = { SPP: 'SPM → SPP', SPM: 'SP2D → SPM' };
    const label = labels[targetStatus] || targetStatus;

    const modal = document.getElementById('modalConfirmAction');
    if (modal) {
        document.getElementById('confirmActionIcon').innerHTML = '<i class="ph ph-warning-circle" style="color:#dc2626;"></i>';
        document.getElementById('confirmActionTitle').innerText = 'Batalkan Tahap';
        document.getElementById('confirmActionMessage').innerText = `Batalkan dan kembalikan status ke ${targetStatus}?\nNomor dokumen dan pembukuan BKU yang sudah dibuat di tahap saat ini akan dihapus/dibatalkan.`;

        const btn = document.getElementById('btnConfirmActionProceed');
        btn.innerHTML = `<i class="ph ph-arrow-counter-clockwise"></i> Batalkan`;
        btn.style.backgroundColor = '#dc2626';
        btn.style.color = '#ffffff';
        btn.onclick = function () {
            closeConfirmActionModal();
            executeRevert(id, targetStatus, label);
        };
        modal.classList.add('show');
    } else {
        if (!confirm(`Batalkan dan kembalikan status ke ${targetStatus}?\nNomor dokumen yang sudah dibuat akan dihapus.`)) return;
        executeRevert(id, targetStatus, label);
    }
};

function executeRevert(id, targetStatus, label) {
    fetch(`/dashboard/disbursements/${id}/revert`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ target_status: targetStatus })
    })
        .then(async res => {
            if (!res.ok) {
                const err = await res.json();
                throw new Error(err.message || `Gagal membatalkan`);
            }
            toast(`Berhasil dibatalkan (${label})`, 'success');
            loadDisbursements();
        })
        .catch(err => toast(err.message, 'error'));
};

window.viewDisbursement = function (id) {
    const modal = document.getElementById('disbursementDetailModal');
    const content = document.getElementById('detailDisbursementContent');
    if (!modal || !content) return;

    content.innerHTML = '<div style="text-align:center; padding:20px;"><i class="ph ph-spinner animate-spin text-2xl"></i></div>';
    modal.classList.add('show');

    fetch(`/dashboard/disbursements?id=${id}`, { headers: { Accept: 'application/json' } })
        .then(res => res.json())
        .then(res => {
            const data = (res.data || [])[0];
            if (!data) throw new Error('Data tidak ditemukan');

            content.innerHTML = `
                <div class="detail-row">
                    <span class="label">Tipe</span>
                    <span class="value">${data.type}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Tanggal</span>
                    <span class="value">${formatTanggal(data.sp2d_date)}</span>
                </div>
                <div class="detail-row">
                    <span class="label">No. SPP</span>
                    <span class="value">${data.spp_no || '-'}</span>
                </div>
                <div class="detail-row">
                    <span class="label">No. SPM</span>
                    <span class="value">${data.spm_no || '-'}</span>
                </div>
                <div class="detail-row">
                    <span class="label">No. SP2D</span>
                    <span class="value" style="color:#1d4ed8; font-weight:800;">${data.sp2d_no || '-'}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Siklus</span>
                    <span class="value">${data.siklus_up ? 'Batch ' + data.siklus_up : '-'}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Rekening</span>
                    <span class="value" style="font-size:13px;">${data.kode_rekening ? `[${data.kode_rekening.kode}] ${data.kode_rekening.nama}` : '-'}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Uraian</span>
                    <span class="value" style="text-align:right;">${data.uraian || '-'}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Status</span>
                    <span class="value">${data.status}</span>
                </div>
                <div class="detail-total mt-4">
                    <span>Total Nilai</span>
                    <strong>${formatRupiah(data.value)}</strong>
                </div>
            `;
        })
        .catch(err => {
            content.innerHTML = `<div style="text-align:center; padding:20px; color:#ef4444;">${err.message}</div>`;
        });
};

window.closeDetailDisbursement = function () {
    const modal = document.getElementById('disbursementDetailModal');
    modal?.classList.remove('show');
};

window.openDisbursementForm = function (item = null) {
    const form = document.getElementById('formDisbursement');
    form.reset();

    const titleEl = document.getElementById('disbursementModalTitle');
    const submitBtn = document.getElementById('btnSimpanDisbursement');
    const idEl = document.getElementById('disbursementId');

    // Reset budget info state
    window.currentSisaAnggaran = undefined;
    window.currentSisaKas = undefined;
    window.isFetchingBudget = false;

    if (item) {
        titleEl.innerText = 'Edit Pengajuan SPP';
        submitBtn.innerHTML = '<i class="ph ph-floppy-disk"></i> Simpan Perubahan';
        idEl.value = item.id;

        // Preserve the current status (don't reset to SPP)
        const statusEl = document.getElementById('disbursementStatus');
        if (statusEl) statusEl.value = item.status || 'SPP';

        document.getElementById('disbursementType').value = item.type;
        document.getElementById('disbursementDate').value = formatTanggalInput(item.sp2d_date);
        document.getElementById('disbursementSiklus').value = item.siklus_up || '';
        document.getElementById('disbursementUraian').value = item.uraian || '';
        document.getElementById('disbursementValue').value = item.value;
        document.getElementById('disbursementDescription').value = item.description || '';

        // Store pending selections for async loaders
        window._pendingRekeningId = item.kode_rekening_id || '';
        window._pendingSpjId = item.spj_id || '';
    } else {
        titleEl.innerText = 'Buat Pengajuan SPP';
        submitBtn.innerHTML = '<i class="ph ph-paper-plane-tilt"></i> Ajukan SPP';
        idEl.value = '';

        // New record always starts as SPP
        const statusEl = document.getElementById('disbursementStatus');
        if (statusEl) statusEl.value = 'SPP';

        document.getElementById('disbursementDate').value = window.getTodayLocal();
        window._pendingRekeningId = '';
        window._pendingSpjId = '';
    }

    document.getElementById('disbursementFormModal').classList.add('show');

    document.getElementById('siklusGroup').style.display = 'none';
    document.getElementById('rekeningGroup').style.display = 'block';
    const sisaPanel = document.getElementById('sisaSaldoInfo');
    if (sisaPanel) sisaPanel.style.display = 'none';
    const saldoPanel = document.getElementById('saldoKasInfo');
    if (saldoPanel) saldoPanel.style.display = 'none';

    // Load kode rekening (kegiatan) for SPP
    loadDisbursementRekening();

    // Trigger type change to load saldo for default type (UP) and ensure it loads
    const typeEl = document.getElementById('disbursementType');
    if (typeEl) {
        // use setTimeout so it happens after modal rendering completes
        setTimeout(() => {
            typeEl.dispatchEvent(new Event('change', { bubbles: true }));
        }, 50);
    }

    // Load SPJs for GU
    fetch('/dashboard/spj?limit=100', { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(res => {
            const select = document.getElementById('disbursementSpj');
            if (!select) return;
            select.innerHTML = '<option value="">-- Pilih SPJ --</option>';
            (res.data || []).forEach(spj => {
                select.insertAdjacentHTML('beforeend', `<option value="${spj.id}">${spj.spj_number} (${formatTanggal(spj.spj_date)})</option>`);
            });

            if (window._pendingSpjId) {
                select.value = window._pendingSpjId;
                delete window._pendingSpjId;
            }
        });
};

async function loadDisbursementRekening() {
    const select = document.getElementById('disbursementRekening');
    if (!select) return;

    // Use existing options if already loaded to prevent flickering/selection loss
    if (select.options.length > 1) {
        if (window._pendingRekeningId) {
            select.value = window._pendingRekeningId;
            select.dispatchEvent(new Event('change'));
            delete window._pendingRekeningId;
        }
        return;
    }

    try {
        const res = await fetch('/dashboard/master/kode-rekening?category=PENGELUARAN', {
            headers: { 'Accept': 'application/json' }
        });
        const tree = await res.json();

        // Re-check before wiping to be super safe
        select.innerHTML = '<option value="">-- Pilih Kegiatan --</option>';

        function flatten(nodes) {
            nodes.forEach(node => {
                if (node.tipe === 'detail') {
                    const opt = document.createElement('option');
                    opt.value = node.id;
                    opt.textContent = `${node.kode} — ${node.nama}`;
                    select.appendChild(opt);
                }
                if (node.children && node.children.length > 0) {
                    flatten(node.children);
                }
            });
        }
        flatten(tree);

        if (window._pendingRekeningId) {
            select.value = window._pendingRekeningId;
            select.dispatchEvent(new Event('change'));
            delete window._pendingRekeningId;
        }
    } catch (err) {
        console.error('Gagal memuat kode rekening:', err);
    }
}

// Fetch sisa saldo when kode_rekening changes
document.addEventListener('change', function (e) {
    if (e.target && e.target.id === 'disbursementRekening') {
        const kodeRekeningId = e.target.value;
        const infoPanel = document.getElementById('sisaSaldoInfo');

        if (!kodeRekeningId) {
            if (infoPanel) infoPanel.style.display = 'none';
            return;
        }

        const year = new Date().getFullYear();
        const id = document.getElementById('disbursementId')?.value;
        let url = `/dashboard/disbursements/sisa-anggaran?kode_rekening_id=${kodeRekeningId}&year=${year}`;
        if (id) url += `&exclude_id=${id}`;

        window.isFetchingBudget = true;
        fetch(url)
            .then(res => res.json())
            .then(data => {
                window.isFetchingBudget = false;
                if (infoPanel) infoPanel.style.display = 'block';

                const fmt = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');

                document.getElementById('infoAnggaran').textContent = fmt(data.anggaran);
                document.getElementById('infoRealisasi').textContent = fmt(data.realisasi);
                document.getElementById('infoSppPending').textContent = fmt(data.spp_pending);

                const sisaEl = document.getElementById('infoSisa');
                sisaEl.textContent = fmt(data.sisa);
                sisaEl.style.color = data.sisa > 0 ? '#059669' : '#dc2626';

                window.currentSisaAnggaran = parseFloat(data.sisa) || 0;
            })
            .catch(err => {
                console.error('Gagal memuat sisa anggaran:', err);
                window.currentSisaAnggaran = 0;
                window.isFetchingBudget = false;
            });
    }
});

function updateSaldoKasInfo() {
    const type = document.getElementById('disbursementType')?.value;
    const siklus = document.getElementById('disbursementSiklus')?.value;
    const year = document.getElementById('ledgerYear')?.value || new Date().getFullYear();
    const saldoPanel = document.getElementById('saldoKasInfo');

    if (!type || !saldoPanel) return;

    // LS does not use internal cash (Saldo Kas), so we hide the panel
    if (type === 'LS') {
        saldoPanel.style.display = 'none';
        window.currentSisaKas = undefined; // Reset to avoid validation issues based on previous type
        return;
    }
    const id = document.getElementById('disbursementId')?.value;
    let url = `/dashboard/disbursements/saldo-kas?type=${type}&year=${year}`;
    if (type === 'GU' && siklus) {
        url += `&siklus_up=${siklus}`;
    }
    if (id) url += `&exclude_id=${id}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (window.disbursementPageMode === 'PENCAIRAN') {
                saldoPanel.style.display = 'block';
            } else {
                saldoPanel.style.display = 'none';
            }
            const sTypeEl = document.getElementById('saldoKasType');
            if (sTypeEl) sTypeEl.textContent = data.label; // UP, LS, or GU-1, etc.

            const fmt = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');

            const sCair = document.getElementById('saldoTotalCair');
            if (sCair) sCair.textContent = fmt(data.total_cair);
            const sBelanja = document.getElementById('saldoTotalBelanja');
            if (sBelanja) sBelanja.textContent = fmt(data.total_belanja);
            const sPending = document.getElementById('saldoSppPending');
            if (sPending) sPending.textContent = fmt(data.spp_pending);

            const sisaEl = document.getElementById('saldoSisaKas');
            if (sisaEl) {
                sisaEl.textContent = fmt(data.sisa_kas);
                sisaEl.style.color = data.sisa_kas > 0 ? '#059669' : '#dc2626';
            }

            window.currentSisaKas = parseFloat(data.sisa_kas) || 0;
        })
        .catch(err => {
            console.error('Gagal memuat saldo kas:', err);
            toast('Gagal memuat informasi saldo kas', 'error');
            window.currentSisaKas = 0;
            if (saldoPanel) saldoPanel.style.display = 'none';
        });
}

document.addEventListener('change', function (e) {
    if (e.target && e.target.id === 'disbursementType') {
        const type = e.target.value;
        const siklusGroup = document.getElementById('siklusGroup');
        const rekeningGroup = document.getElementById('rekeningGroup');

        if (siklusGroup) siklusGroup.style.display = type === 'GU' ? 'block' : 'none';

        if (rekeningGroup) {
            rekeningGroup.style.display = type === 'LS' ? 'block' : 'none';
            if (type !== 'LS') {
                const rekSelect = document.getElementById('disbursementRekening');
                if (rekSelect) rekSelect.value = '';
                // Trigger change to hide sisaSaldoInfo panel
                rekSelect.dispatchEvent(new Event('change'));
            }
        }

        if (type === 'GU') {
            const id = document.getElementById('disbursementId')?.value;
            // Only auto-suggest next cycle for new entries
            if (!id) {
                const year = document.getElementById('ledgerYear')?.value || new Date().getFullYear();
                fetch(`/dashboard/disbursements/next-siklus?type=${type}&year=${year}`)
                    .then(res => res.json())
                    .then(data => {
                        const siklusEl = document.getElementById('disbursementSiklus');
                        if (siklusEl) {
                            siklusEl.value = data.next;
                            updateSaldoKasInfo();
                        }
                    });
            } else {
                updateSaldoKasInfo();
            }
        } else {
            updateSaldoKasInfo();
        }
    }

    // If siklus changes, update the saldo
    if (e.target && e.target.id === 'disbursementSiklus') {
        updateSaldoKasInfo();
    }
});

window.submitDisbursement = function (e) {
    e.preventDefault();
    const form = document.querySelector('#formDisbursement');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    const val = parseFloat(data.value) || 0;

    // Validate if exceeding Saldo Kas (Only for UP or GU and if it's an ACTIVITY SPP)
    // LS is direct from bank, so it bypasses this internal cash check
    if ((data.type === 'UP' || data.type === 'GU') && data.kode_rekening_id && val > (window.currentSisaKas || 0)) {
        return toast('Gagal: Nominal pengajuan melebihi Sisa Saldo Kas yang tersedia!', 'error');
    }

    // Validate if exceeding Anggaran
    if (data.kode_rekening_id) {
        if (window.isFetchingBudget) {
            return toast('Sudang mengecek sisa anggaran, mohon tunggu sebentar...', 'info');
        }

        // If sisa is still undefined, we should wait or fetch first, but usually the change event handles it.
        // For safety, we check if sisa is exactly defined.
        if (typeof window.currentSisaAnggaran !== 'undefined' && val > window.currentSisaAnggaran) {
            return toast('Gagal: Nominal pengajuan melebihi Sisa Pagu Anggaran!', 'error');
        }
    }

    const id = data.id;
    const method = id ? 'PUT' : 'POST';
    const url = id ? `/dashboard/disbursements/${id}` : '/dashboard/disbursements';

    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
        .then(async res => {
            if (!res.ok) {
                const err = await res.json();
                if (err.errors) {
                    const msgs = Object.values(err.errors).flat().join(', ');
                    throw new Error(msgs);
                }
                throw new Error(err.message || 'Gagal menyimpan pengajuan');
            }
            toast(id ? 'Pengajuan berhasil diubah' : 'Pengajuan SPP berhasil dibuat', 'success');
            closeDisbursementModal();
            loadDisbursements();
        })
        .catch(err => toast(err.message, 'error'));
};

window.closeDisbursementModal = () => document.getElementById('disbursementFormModal').classList.remove('show');

window.editDisbursement = function (id) {
    fetch(`/dashboard/disbursements/${id}`, { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(item => {
            window.openDisbursementForm(item);
        })
        .catch(err => toast('Gagal mengambil data: ' + err.message, 'error'));
};

function formatTanggalInput(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

window.hapusDisbursement = function (id) {
    const modal = document.getElementById('modalConfirmAction');
    if (modal) {
        document.getElementById('confirmActionIcon').innerHTML = '<i class="ph ph-trash" style="color:#dc2626;"></i>';
        document.getElementById('confirmActionTitle').innerText = 'Hapus Pengajuan';
        document.getElementById('confirmActionMessage').innerText = 'Yakin ingin menghapus data pengajuan SPP ini secara permanen?';

        const btn = document.getElementById('btnConfirmActionProceed');
        btn.innerHTML = `<i class="ph ph-trash"></i> Hapus Sekarang`;
        btn.style.backgroundColor = '#dc2626';
        btn.style.color = '#ffffff';
        btn.onclick = function () {
            closeConfirmActionModal();
            executeHapusDisbursement(id);
        };
        modal.classList.add('show');
    } else {
        if (!confirm('Hapus data pencairan ini? Ledger akan disesuaikan otomatis.')) return;
        executeHapusDisbursement(id);
    }
};

function executeHapusDisbursement(id) {
    fetch(`/dashboard/disbursements/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
        .then(async res => {
            if (!res.ok) {
                const err = await res.json();
                throw new Error(err.message || 'Gagal menghapus data');
            }
            toast('Pengajuan berhasil dihapus', 'success');
            loadDisbursements();
        })
        .catch((err) => toast(err.message || 'Gagal menghapus data', 'error'));
}

/* =========================
   LEDGER LOGIC
   ========================= */

let ledgerCurrentPage = 1;

window.initLedger = function () {
    const now = new Date();
    document.getElementById('ledgerMonth').value = now.getMonth() + 1;
    ledgerCurrentPage = 1;
    loadLedger();
};

window.loadLedger = function (page = 1) {
    const month = document.getElementById('ledgerMonth').value;
    const year = document.getElementById('ledgerYear').value;
    const tbody = document.getElementById('tableLedgerBody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="11" class="text-center">Memuat data...</td></tr>';

    fetch(`/dashboard/laporan/bku?month=${month}&year=${year}`, { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(res => {
            tbody.innerHTML = '';

            if (!res.data || res.data.length === 0) {
                // If there's an opening balance but no transactions it is also technically 'no data'
            }

            // Always render Opening Balance
            tbody.insertAdjacentHTML('beforeend', `
                    <tr style="background:#f8fafc; font-weight:600;">
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td style="font-weight:bold;">SALDO AWAL</td>
                        <td class="text-center">-</td>
                        <td class="text-right">-</td>
                        <td class="text-right">-</td>
                        <td class="text-right">-</td>
                        <td class="text-right">${formatRupiahTable(res.opening_balance - res.opening_bank)}</td>
                        <td class="text-right">${formatRupiahTable(res.opening_bank)}</td>
                        <td class="text-right font-bold">${formatRupiahTable(res.opening_balance)}</td>
                    </tr>
            `);

            res.data.forEach((item, index) => {
                const sp2dVal = item.sp2d_penerimaan > 0 ? formatRupiahTable(item.sp2d_penerimaan) : '-';
                const tfVal = item.transfer_penerimaan > 0 ? formatRupiahTable(item.transfer_penerimaan) : '-';
                const reqVal = item.realisasi > 0 ? formatRupiahTable(item.realisasi) : '-';

                tbody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td class="text-center">${formatTanggal(item.date)}</td>
                        <td class="text-center font-mono text-sm">${item.no_bukti || '-'}</td>
                        <td>${item.uraian || '-'}</td>
                        <td class="text-center font-mono text-sm">${item.kode_rekening || '-'}</td>
                        <td class="text-right">${tfVal}</td>
                        <td class="text-right">${sp2dVal}</td>
                        <td class="text-right">${reqVal}</td>
                        <td class="text-right">${formatRupiahTable(item.saldo_tunai)}</td>
                        <td class="text-right">${formatRupiahTable(item.saldo_bank)}</td>
                        <td class="text-right font-bold">${formatRupiahTable(item.saldo_akhir)}</td>
                    </tr>
                `);
            });

            if (res.summary) {
                tbody.insertAdjacentHTML('beforeend', `
                    <tr style="background:#f1f5f9; font-weight:bold; border-top: 2px solid #cbd5e1;">
                        <td colspan="5" class="text-center">TOTAL MUTASI & SALDO AKHIR</td>
                        <td class="text-right">${formatRupiahTable(res.summary.total_debit_transfer)}</td>
                        <td class="text-right">${formatRupiahTable(res.summary.total_debit_sp2d)}</td>
                        <td class="text-right">${formatRupiahTable(res.summary.total_credit_realisasi)}</td>
                        <td class="text-right">${formatRupiahTable(res.summary.final_tunai)}</td>
                        <td class="text-right">${formatRupiahTable(res.summary.final_bank)}</td>
                        <td class="text-right font-bold">${formatRupiahTable(res.summary.final_balance)}</td>
                    </tr>
                `);
            }

            updateLedgerPagination(res);
        })
        .catch(err => {
            tbody.innerHTML = '<tr><td colspan="11" class="text-center text-red-500">Gagal memuat data</td></tr>';
        });
};

function updateLedgerPagination(res) {
    const info = document.getElementById('ledgerPaginationInfo');
    const indicator = document.getElementById('ledgerPageIndicator');
    const btnPrev = document.getElementById('btnLedgerPrev');
    const btnNext = document.getElementById('btnLedgerNext');

    if (info) info.innerText = `Menampilkan data bulan ini`;
    if (indicator) indicator.innerText = `1 / 1`;

    if (btnPrev) btnPrev.disabled = true;
    if (btnNext) btnNext.disabled = true;
}

window.changeLedgerPage = function (delta) {
    // pagination not supported for BKU standard report model
};

window.syncLedger = function () {
    const btn = document.getElementById('btnSyncLedger');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-spinner-gap animate-spin"></i> <span>Mensinkronkan...</span>';
    }

    const year = document.getElementById('ledgerYear')?.value || new Date().getFullYear();

    fetch('/dashboard/treasurer-cash/sync', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ year: year })
    })
        .then(async res => {
            if (!res.ok) {
                const err = await res.json();
                throw new Error(err.message || 'Gagal sinkronisasi');
            }
            toast('BKU berhasil disinkronkan', 'success');
            loadLedger();
        })
        .catch(err => toast(err.message, 'error'))
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="ph ph-arrows-clockwise"></i> <span>Sinkronisasi BKU</span>';
            }
        });
};

window.printLedger = function () {
    window.print();
};

/* =========================
   BELANJA ITEMS (ACTIVITIES)
========================= */
window.currentBelanjaDisbursement = null;

window.openBelanjaItems = function (id) {
    const mainList = document.getElementById('disbursementMainList');
    const detailSection = document.getElementById('sectionBelanjaItems');
    if (!mainList || !detailSection) return;

    mainList.style.display = 'none';
    detailSection.style.display = 'block';

    // Reset view position
    window.scrollTo({ top: 0, behavior: 'smooth' });

    loadBelanjaItems(id);
};

window.closeBelanjaItemsModal = function () {
    const mainList = document.getElementById('disbursementMainList');
    const detailSection = document.getElementById('sectionBelanjaItems');
    if (mainList && detailSection) {
        detailSection.style.display = 'none';
        mainList.style.display = 'block';
    }
    window.currentBelanjaDisbursement = null;
    loadDisbursements();
};

window.loadBelanjaItems = function (id) {
    fetch(`/dashboard/disbursements?id=${id}`) // Re-fetch to get specific object with relations
        .then(res => res.json())
        .then(res => {
            const items = res.data || [];
            if (items.length === 0) return;
            const disbursement = items[0];
            window.currentBelanjaDisbursement = disbursement;

            // Header Info
            document.getElementById('belanjaRefNo').textContent = disbursement.sp2d_no || disbursement.spm_no || disbursement.spp_no || '-';
            const total = parseFloat(disbursement.value) || 0;

            const formatNumeric = (num) => Number(num || 0).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            document.getElementById('belanjaTotalValue').textContent = formatNumeric(total);

            // Fetch Expenditures linked to this disbursement
            fetch(`/dashboard/expenditures?fund_disbursement_id=${id}&limit=100`)
                .then(r => r.json())
                .then(rData => {
                    const expenditures = rData.data || [];
                    const tbody = document.getElementById('belanjaItemsTableBody');
                    tbody.innerHTML = '';

                    let usedSum = 0;

                    if (expenditures.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center" style="padding: 20px; color: #94a3b8;">Belum ada rincian kegiatan.</td></tr>';
                    } else {
                        expenditures.forEach((ex, idx) => {
                            usedSum += parseFloat(ex.gross_value) || 0;
                            const rekLabel = ex.kode_rekening ? `[${ex.kode_rekening.kode}] ${ex.kode_rekening.nama}` : '-';
                            const canCairCreate = window.hasPermission('PENGELUARAN_CAIR_CREATE') || window.isAdmin;

                            tbody.insertAdjacentHTML('beforeend', `
                                <tr>
                                    <td class="text-center" style="padding: 10px; border-bottom: 1px solid #f1f5f9;">${idx + 1}</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #f1f5f9; white-space: nowrap;">${formatTanggal(ex.spending_date)}</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #f1f5f9; font-family: monospace; font-size: 11px; font-weight: 700; color: #6366f1; white-space: nowrap;">${ex.no_bukti || '-'}</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #f1f5f9;">${ex.description || '-'}</td>
                                    <td class="text-right font-mono" style="padding: 10px; border-bottom: 1px solid #f1f5f9; font-weight: 600;">${formatRupiahTable(ex.gross_value)}</td>
                                    <td class="text-center" style="padding: 10px; border-bottom: 1px solid #f1f5f9; white-space: nowrap;">
                                        <div style="display: flex; gap: 4px; justify-content: center;">
                                            <button class="btn-aksi" title="Preview" onclick="openPengeluaranDetail(${ex.id})" 
                                                style="background: #eff6ff; color: #2563eb; width: 28px; height: 28px; border: 1px solid #3b82f6;">
                                                <i class="ph ph-eye"></i>
                                            </button>
                                            ${canCairCreate ? `
                                                <button class="btn-aksi" title="Edit" onclick="openPengeluaranForm('ALL', ${ex.id})" 
                                                    style="background: #fffbeb; color: #d97706; width: 28px; height: 28px; border: 1px solid #f59e0b;">
                                                    <i class="ph ph-pencil-simple"></i>
                                                </button>
                                                <button class="btn-aksi delete" title="Hapus" onclick="deleteBelanjaItem(${ex.id})" 
                                                    style="width: 28px; height: 28px; background: #fef2f2; color: #ef4444; border: 1px solid #ef4444;">
                                                    <i class="ph ph-trash"></i>
                                                </button>
                                            ` : ''}
                                        </div>
                                    </td>
                                </tr>
                            `);
                        });
                    }

                    document.getElementById('belanjaUsedValue').textContent = formatNumeric(usedSum);
                    document.getElementById('belanjaRemainingValue').textContent = formatNumeric(total - usedSum);
                });
        });
};

window.addNewBelanjaItem = function () {
    if (!window.currentBelanjaDisbursement) return;
    const d = window.currentBelanjaDisbursement;

    // Open existing pengeluaran modal
    if (typeof openPengeluaranForm === 'function') {
        loadRekeningPengeluaran('ALL');

        const modal = document.getElementById('pengeluaranModal');
        modal.classList.add('show');
        resetPengeluaranForm();

        // Preset values from disbursement
        document.getElementById('pengeluaranTanggal').value = d.sp2d_date ? d.sp2d_date.substring(0, 10) : '';

        // Handle Payment Method restriction
        const metodeSelect = document.getElementById('pengeluaranMetode');
        metodeSelect.value = d.type;

        Array.from(metodeSelect.options).forEach(opt => {
            if (d.type === 'UP') {
                opt.disabled = (opt.value !== 'UP');
            } else if (d.type === 'GU') {
                opt.disabled = (opt.value === 'LS');
            } else if (d.type === 'LS') {
                opt.disabled = (opt.value !== 'LS');
            } else {
                opt.disabled = false;
            }
        });

        document.getElementById('pengeluaranVendor').value = d.recipient_party || '';

        if (d.type === 'GU' && d.siklus_up) {
            document.getElementById('guCycleSection').style.display = 'block';
            // We might need to populate and select the cycle
            const siklusSelect = document.getElementById('pengeluaranSiklus');
            siklusSelect.innerHTML = `<option value="${d.siklus_up}" selected>Batch GU-${d.siklus_up}</option>`;
        }

        const hiddenId = document.getElementById('pengeluaranFundDisbursementId');
        if (hiddenId) hiddenId.value = d.id;

        // Ensure calculation logic is bound
        if (typeof bindCurrencyInputs === 'function') {
            bindCurrencyInputs();
        }
    }
};

window.deleteBelanjaItem = function (id) {
    const modal = document.getElementById('modalConfirmAction');
    if (modal) {
        document.getElementById('confirmActionIcon').innerHTML = '<i class="ph ph-trash" style="color:#ef4444; font-size: 3.5rem;"></i>';
        document.getElementById('confirmActionTitle').innerText = 'Hapus Rincian Kegiatan';
        document.getElementById('confirmActionMessage').innerHTML = 'Hapus rincian kegiatan ini dari BKU?<br><span style="font-size:13px; color:#64748b;">Tindakan ini akan menghapus data belanja secara permanen dan tidak dapat dibatalkan.</span>';

        const btn = document.getElementById('btnConfirmActionProceed');
        btn.innerHTML = `<i class="ph ph-trash"></i> Ya, Hapus`;
        btn.style.backgroundColor = '#ef4444';
        btn.style.borderColor = '#ef4444';
        btn.style.color = '#ffffff';
        btn.onclick = function () {
            closeConfirmActionModal();
            executeDeleteBelanjaItem(id);
        };
        modal.classList.add('show');
    } else {
        if (confirm('Hapus rincian kegiatan ini dari BKU?')) {
            executeDeleteBelanjaItem(id);
        }
    }
};

function executeDeleteBelanjaItem(id) {
    fetch(`/dashboard/expenditures/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
        .then(async res => {
            if (!res.ok) {
                const err = await res.json();
                throw new Error(err.message || 'Gagal menghapus');
            }
            toast('Kegiatan berhasil dihapus', 'success');
            if (window.currentBelanjaDisbursement) {
                loadBelanjaItems(window.currentBelanjaDisbursement.id);
            }
        })
        .catch(err => toast(err.message || 'Gagal menghapus', 'error'));
}
