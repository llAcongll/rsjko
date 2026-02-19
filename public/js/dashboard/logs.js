console.log('📜 logs.js loaded');

let currentLogPage = 1;
let logSearchTimer = null;
let allLogs = [];

window.initLogs = function () {
    loadLogs();
};

window.handleLogSearch = function () {
    clearTimeout(logSearchTimer);
    logSearchTimer = setTimeout(() => {
        currentLogPage = 1;
        loadLogs();
    }, 500);
};

window.loadLogs = function (page = 1) {
    currentLogPage = page;
    const search = document.getElementById('logSearch')?.value || '';
    const module = document.getElementById('filterModule')?.value || '';
    const tbody = document.getElementById('logTableBody');

    if (!tbody) return;

    fetch(`/dashboard/master/activity-logs?page=${page}&search=${search}&module=${module}`)
        .then(r => r.json())
        .then(res => {
            allLogs = res.data;
            renderLogTable(allLogs);
            renderPagination('logPagination', res, 'loadLogs');
        })
        .catch(err => {
            console.error('Logs Error:', err);
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Gagal memuat data</td></tr>`;
        });
};

function renderLogTable(logs) {
    const tbody = document.getElementById('logTableBody');
    if (!tbody) return;

    if (logs.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5">Tidak ada riwayat aktivitas</td></tr>`;
        return;
    }

    tbody.innerHTML = logs.map((log, idx) => {
        const date = new Date(log.created_at);
        const dateStr = date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) +
            ' ' + date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

        const actionBadge = getActionBadge(log.action);
        const moduleLabel = getModuleLabel(log.module);

        return `
            <tr>
                <td style="font-size: 13px;">${dateStr}</td>
                <td class="fw-bold">${log.user ? log.user.username : 'System'}</td>
                <td>${actionBadge}</td>
                <td>${moduleLabel}</td>
                <td><div class="text-truncate" style="max-width: 250px;">${log.description || '-'}</div></td>
                <td><small>${log.ip_address || '-'}</small></td>
                <td class="text-center">
                    <div class="flex justify-center gap-1">
                        <button class="btn-aksi detail" onclick="showLogDetail(${idx})" title="Detail">
                            <i class="ph ph-eye"></i>
                        </button>
                        <button class="btn-aksi delete" onclick="deleteLog(${log.id})" title="Hapus">
                            <i class="ph ph-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

window.deleteLog = function (id) {
    openConfirm('Hapus Log', 'Yakin ingin menghapus baris riwayat ini? Tindakan ini tidak dapat dibatalkan.', () => {
        fetch(`/dashboard/master/activity-logs/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json'
            }
        })
            .then(r => r.json())
            .then(res => {
                toast('Log berhasil dihapus', 'success');
                loadLogs(currentLogPage);
            })
            .catch(err => toast('Gagal menghapus log', 'error'));
    });
};

window.purgeLogs = function () {
    const modal = document.getElementById('modalPurgeLogs');
    if (modal) {
        document.getElementById('purgeDaysInput').value = 30;
        modal.classList.add('show');
    }
};

window.submitPurgeLogs = function () {
    const dayInput = document.getElementById('purgeDaysInput');
    const dayNum = parseInt(dayInput.value);

    if (isNaN(dayNum) || dayNum < 1) {
        toast('Jumlah hari tidak valid (Minimal 1 hari)', 'error');
        return;
    }

    openConfirm('Konfirmasi Terakhir', `Ingin menghapus selamanya log yang lebih lama dari ${dayNum} hari?`, () => {
        const btn = document.querySelector('#modalPurgeLogs .btn-toolbar-danger');
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = 'Membersihkan...';

        fetch(`/dashboard/master/activity-logs/purge?days=${dayNum}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json'
            }
        })
            .then(r => r.json())
            .then(res => {
                toast(res.message || 'Log lama berhasil dibersihkan', 'success');
                closeModal('modalPurgeLogs');
                loadLogs(1);
            })
            .catch(err => toast('Gagal membersihkan log', 'error'))
            .finally(() => {
                btn.disabled = false;
                btn.innerText = originalText;
            });
    }, 'Ya, Hapus Semua');
};

function getModuleLabel(mod) {
    const map = {
        'PENDAPATAN_UMUM': { label: 'Pend. Umum', icon: 'ph-person' },
        'PENDAPATAN_BPJS': { label: 'Pend. BPJS', icon: 'ph-cardholder' },
        'PENDAPATAN_JAMINAN': { label: 'Pend. Jaminan', icon: 'ph-shield-check' },
        'PENDAPATAN_KERJA': { label: 'Pend. Kerjasama', icon: 'ph-handshake' },
        'PENDAPATAN_LAIN': { label: 'Pend. Lain-lain', icon: 'ph-coins' },
        'PENGELUARAN': { label: 'Pengeluaran', icon: 'ph-receipt' },
        'PIUTANG': { label: 'Piutang', icon: 'ph-credit-card' },
        'RUANGAN': { label: 'Ruangan', icon: 'ph-buildings' },
        'USER': { label: 'User', icon: 'ph-user' },
    };

    const config = map[mod] || { label: mod, icon: 'ph-cube' };
    return `
        <div style="display:flex; align-items:center; gap:6px;">
            <i class="ph ${config.icon}" style="color:#64748b;"></i>
            <small class="text-muted fw-600">${config.label}</small>
        </div>
    `;
}

function getActionBadge(action) {
    let color = '#64748b';
    let icon = 'ph-cube';

    if (action === 'CREATE') { color = '#10b981'; icon = 'ph-plus-circle'; }
    if (action === 'UPDATE') { color = '#3b82f6'; icon = 'ph-pencil-simple'; }
    if (action === 'DELETE') { color = '#ef4444'; icon = 'ph-trash'; }
    if (action === 'IMPORT') { color = '#7c3aed'; icon = 'ph-file-arrow-up'; }

    return `
        <span style="background: ${color}15; color: ${color}; padding: 4px 10px; border-radius: 8px; font-size: 10px; font-weight: 800; border: 1px solid ${color}30; display: inline-flex; align-items: center; gap: 4px; text-transform: uppercase;">
            <i class="ph ${icon}" style="font-size: 14px;"></i>
            ${action}
        </span>
    `;
}

window.showLogDetail = function (idx) {
    const log = allLogs[idx];
    if (!log) return;

    const date = new Date(log.created_at);
    const dateStr = date.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) +
        ' ' + date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

    document.getElementById('dtLogTime').innerText = dateStr;
    document.getElementById('dtLogUser').innerText = log.user ? log.user.username : 'System';
    document.getElementById('dtLogAction').innerHTML = getActionBadge(log.action);
    document.getElementById('dtLogModule').innerText = log.module;
    document.getElementById('dtLogDescription').innerText = log.description || '-';

    renderJsonDetail('dtLogOldValues', log.old_values);
    renderJsonDetail('dtLogNewValues', log.new_values);

    const modal = document.getElementById('modalLogDetail');
    if (modal) modal.classList.add('show');
};

function renderJsonDetail(elementId, data) {
    const el = document.getElementById(elementId);
    if (!el) return;

    if (!data || Object.keys(data).length === 0) {
        el.innerHTML = `<div class="text-slate-500 p-4 italic text-center">Tidak ada data perubahan</div>`;
        return;
    }

    // Technical fields to hide
    const hidden = ['id', 'created_at', 'updated_at', 'deleted_at', 'user_id', 'password', 'remember_token'];

    let html = '<div style="display: flex; flex-direction: column; gap: 12px; padding: 4px;">';

    for (const [key, value] of Object.entries(data)) {
        if (hidden.includes(key)) continue;

        // Beautify key: snake_case to Title Case
        const label = key.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');

        let displayValue = value;

        // Type-based formatting
        if (value === null) {
            displayValue = '<span style="color: #64748b;">-</span>';
        } else if (typeof value === 'number' || (typeof value === 'string' && !isNaN(value) && value.includes('.') && value.length > 2)) {
            // Detect if it's likely a financial/numeric value
            displayValue = formatRibuan(value);
        } else if (typeof value === 'string' && value.match(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/)) {
            // Detect ISO Date and format it
            const dt = new Date(value);
            displayValue = isNaN(dt.getTime()) ? value : dt.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
        }

        html += `
            <div style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 8px;">
                <label style="display: block; font-size: 10px; text-transform: uppercase; color: #94a3b8; font-weight: 700; letter-spacing: 0.05em; margin-bottom: 4px;">${label}</label>
                <div style="color: #f1f5f9; font-weight: 600; font-size: 13px;">${displayValue}</div>
            </div>
        `;
    }
    html += '</div>';
    el.innerHTML = html;
}
