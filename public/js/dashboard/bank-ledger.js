/* =========================
   BANK ACCOUNT LEDGER (REKENING KORAN PENGELUARAN)
   ========================= */

(function () {
    const fmt = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
    const fmtTgl = (d) => {
        const dt = new Date(d);
        return dt.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
    };

    window.initBankLedger = function () {
        const now = new Date();
        const mEl = document.getElementById('bankLedgerMonth');
        const yEl = document.getElementById('bankLedgerYear');

        if (mEl && !mEl.value) mEl.value = now.getMonth() + 1;
        if (yEl && !yEl.value) yEl.value = now.getFullYear();

        // Bind currency input
        const display = document.getElementById('bankLedgerAmountDisplay');
        const hidden = document.getElementById('bankLedgerAmountValue');
        if (display && hidden) {
            display.oninput = () => {
                const val = parseAngka(display.value);
                hidden.value = val;
            };
            display.onblur = () => {
                display.value = formatRibuan(hidden.value);
            };
            display.onfocus = () => {
                const val = parseAngka(display.value);
                display.value = val === 0 ? '' : val.toString().replace('.', ',');
            };
        }

        loadBankLedger();
    };

    window.openDepositModal = function () {
        const form = document.getElementById('formBankLedger');
        if (form) form.reset();

        document.getElementById('bankLedgerId').value = '';
        document.getElementById('bankLedgerModalTitle').innerHTML = '<i class="ph ph-bank"></i> Tambah Saldo Rekening';

        const dateEl = document.getElementById('bankLedgerDate');
        if (dateEl) dateEl.value = window.getTodayLocal();

        const hiddenAmount = document.getElementById('bankLedgerAmountValue');
        if (hiddenAmount) hiddenAmount.value = 0;

        const displayAmount = document.getElementById('bankLedgerAmountDisplay');
        if (displayAmount) displayAmount.value = '0';

        const modal = document.getElementById('modalBankLedger');
        if (modal) modal.classList.add('show');
    };

    window.closeBankLedgerModal = function () {
        const modal = document.getElementById('modalBankLedger');
        if (modal) modal.classList.remove('show');
    };

    window.submitBankLedger = function (e) {
        e.preventDefault();
        const form = document.getElementById('formBankLedger');
        const data = Object.fromEntries(new FormData(form));
        const id = document.getElementById('bankLedgerId').value;

        const url = id ? `/dashboard/bank-account-ledger/deposit/${id}` : '/dashboard/bank-account-ledger/deposit';
        const method = id ? 'PUT' : 'POST';

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
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Gagal menyimpan data');
                toast(json.message || 'Berhasil menyimpan data', 'success');
                closeBankLedgerModal();
                loadBankLedger();
            })
            .catch(err => toast(err.message, 'error'));
    };

    window.editDeposit = function (item) {
        window.openDepositModal();
        document.getElementById('bankLedgerId').value = item.id;
        document.getElementById('bankLedgerModalTitle').innerHTML = '<i class="ph ph-pencil-simple"></i> Edit Setoran Manual';
        document.getElementById('bankLedgerDate').value = item.date.split(' ')[0];
        document.getElementById('bankLedgerDescription').value = item.description;
        document.getElementById('bankLedgerAmountValue').value = item.debit;
        document.getElementById('bankLedgerAmountDisplay').value = Number(item.debit).toLocaleString('id-ID');
    };

    window.deleteDeposit = function (id) {
        if (!confirm('Yakin ingin menghapus mutasi setoran manual ini? Data di BKU juga akan ikut dihapus.')) return;

        fetch(`/dashboard/bank-account-ledger/deposit/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
            .then(async res => {
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Gagal menghapus');
                toast('Mutasi deposit berhasil dihapus', 'success');
                loadBankLedger();
            })
            .catch(err => toast(err.message, 'error'));
    };

    window.loadBankLedger = function () {
        const mEl = document.getElementById('bankLedgerMonth');
        const yEl = document.getElementById('bankLedgerYear');
        if (!mEl || !yEl) return;

        const month = mEl.value;
        const year = yEl.value;
        const tbody = document.getElementById('tableBankLedgerBody');
        if (!tbody) return;

        tbody.innerHTML = '<tr><td colspan="8" class="text-center">Memuat data...</td></tr>';

        fetch(`/dashboard/bank-account-ledger?month=${month}&year=${year}`, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(res => {
                const balEl = document.getElementById('bankLedgerCurrentBalance');
                if (balEl) balEl.textContent = fmt(res.current_balance);

                if (res.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center">Belum ada mutasi rekening koran.</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                res.data.forEach((item, index) => {
                    const badgeClass = item.credit > 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700';

                    let actionHtml = '-';
                    if (item.type === 'DEPOSIT_MANUAL') {
                        const itemJson = JSON.stringify(item).replace(/"/g, '&quot;');
                        actionHtml = `
                            <div class="flex justify-center gap-2">
                                <button class="btn-aksi edit" title="Edit" onclick="editDeposit(${itemJson})">
                                    <i class="ph ph-pencil-simple"></i>
                                </button>
                                <button class="btn-aksi delete" title="Hapus" onclick="deleteDeposit(${item.id})">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </div>
                        `;
                    }

                    tbody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td class="text-center">${fmtTgl(item.date)}</td>
                        <td class="text-center">
                            <span class="badge-mini ${badgeClass}" style="font-size:0.65rem; padding: 2px 8px; border-radius: 4px; font-weight: 600;">
                                ${item.type}
                            </span>
                        </td>
                        <td>${item.description || '-'}</td>
                        <td class="text-right text-green-700 font-medium">${item.debit > 0 ? fmt(item.debit) : '-'}</td>
                        <td class="text-right text-red-700 font-medium">${item.credit > 0 ? fmt(item.credit) : '-'}</td>
                        <td class="text-right font-bold" style="color: #0f172a;">${fmt(item.balance)}</td>
                        <td class="text-center">${actionHtml}</td>
                    </tr>
                `);
                });
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-red-500">Gagal memuat data</td></tr>';
            });
    };
})();
