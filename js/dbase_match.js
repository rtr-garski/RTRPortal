function init_dbase_match() {
    const contentEl = document.getElementById('content');
    const form      = document.getElementById('insMatchForm');
    const btn       = document.getElementById('insMatchBtn');
    const spinner   = document.getElementById('insMatchSpinner');
    const alertEl   = document.getElementById('insMatchAlert');
    const tbody     = document.getElementById('insMatchTbody');

    // Init the custom table once — captures placeholder row, sets up all listeners
    const ct = new CustomTable({ tableSelector: '#insResultsCard' });
    const tableInstance = ct.tables[0] ?? null;

    function escHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function pctBadge(pct) {
        let cls = 'badge-soft-danger';
        if (pct >= 90)      cls = 'badge-soft-success';
        else if (pct >= 70) cls = 'badge-soft-warning';
        else if (pct >= 50) cls = 'badge-soft-secondary';
        return `<span class="badge ${cls} fs-xxs">${pct}%</span>`;
    }

    function showAlert(type, msg) {
        alertEl.className = `alert alert-${type} py-2`;
        alertEl.innerHTML = msg;
        alertEl.classList.remove('d-none');
    }

    function clearAlert() { alertEl.classList.add('d-none'); }

    function renderResults(results) {
        if (!results.length) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-5">No matches found.</td></tr>`;
        } else {
            tbody.innerHTML = results.map(r => `
                <tr title="Name: ${r.name_pct ?? '-'}% | Address: ${r.addr_pct ?? '-'}% | CSZ: ${r.csz_pct ?? '-'}%">
                    <td class="ps-3">${pctBadge(r.match_pct)}</td>
                    <td><span class="fw-medium">${escHtml(r.name)}</span></td>
                    <td class="text-muted">${escHtml(r.address)}</td>
                    <td class="text-muted">${escHtml(r.csz)}</td>
                    <td class="text-end pe-3 text-muted fs-xs">${escHtml(r.id)}</td>
                </tr>
            `).join('');
        }

        if (tableInstance) {
            const newRows = Array.from(tbody.querySelectorAll('tr'));
            tableInstance.rows = newRows;
            tableInstance.filteredRows = [...newRows];
            tableInstance.currentPage = 1;
            if (tableInstance.searchInput) tableInstance.searchInput.value = '';
            tableInstance.update();
        }
    }

    // ── LOC section ──────────────────────────────────────────────
    const locForm    = document.getElementById('locMatchForm');
    const locBtn     = document.getElementById('locMatchBtn');
    const locSpinner = document.getElementById('locMatchSpinner');
    const locAlertEl = document.getElementById('locMatchAlert');
    const locTbody   = document.getElementById('locMatchTbody');

    const locCt            = new CustomTable({ tableSelector: '#locResultsCard' });
    const locTableInstance = locCt.tables[0] ?? null;

    function showLocAlert(type, msg) {
        locAlertEl.className = `alert alert-${type} py-2`;
        locAlertEl.innerHTML = msg;
        locAlertEl.classList.remove('d-none');
    }
    function clearLocAlert() { locAlertEl.classList.add('d-none'); }

    function renderLocResults(results) {
        if (!results.length) {
            locTbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-5">No matches found.</td></tr>`;
        } else {
            locTbody.innerHTML = results.map(r => `
                <tr title="Name: ${r.name_pct ?? '-'}% | CSZ: ${r.csz_pct ?? '-'}% | Phone: ${r.phone_pct ?? '-'}%">
                    <td class="ps-3">${pctBadge(r.match_pct)}</td>
                    <td><span class="fw-medium">${escHtml(r.name)}</span></td>
                    <td class="text-muted">${escHtml(r.csz)}</td>
                    <td class="text-muted" title="Original: ${escHtml(r.phone_raw)}" style="cursor:default">${escHtml(r.phone)}</td>
                    <td class="text-end pe-3 text-muted fs-xs">${escHtml(r.id)}</td>
                </tr>
            `).join('');
        }

        if (locTableInstance) {
            const newRows = Array.from(locTbody.querySelectorAll('tr'));
            locTableInstance.rows = newRows;
            locTableInstance.filteredRows = [...newRows];
            locTableInstance.currentPage = 1;
            if (locTableInstance.searchInput) locTableInstance.searchInput.value = '';
            locTableInstance.update();
        }
    }

    // Strip non-digits on input
    const locPhoneEl = document.getElementById('loc_phone');
    locPhoneEl.addEventListener('input', function() {
        const pos = this.selectionStart;
        const cleaned = this.value.replace(/[^0-9]/g, '');
        if (this.value !== cleaned) {
            this.value = cleaned;
            this.setSelectionRange(pos - 1, pos - 1);
        }
    });

    if (contentEl._locMatchHandler) locForm.removeEventListener('submit', contentEl._locMatchHandler);
    contentEl._locMatchHandler = function(e) {
        if (e.target !== locForm) return;
        e.preventDefault();

        const name  = document.getElementById('loc_name').value.trim();
        const csz   = document.getElementById('loc_csz').value.trim();
        const phone = document.getElementById('loc_phone').value.trim();

        if (!name && !csz && !phone) {
            showLocAlert('danger', 'Please enter at least one search field.');
            return;
        }

        clearLocAlert();
        locBtn.disabled = true;
        locSpinner.classList.remove('d-none');
        const locStart = performance.now();

        fetch('api/insurance_match.php', {
            method: 'POST',
            body: new URLSearchParams({ action: 'search_loc', name, csz, phone })
        })
            .then(r => r.json())
            .then(data => {
                if (!data.success) { showLocAlert('danger', data.message ?? 'Search failed.'); return; }
                const ms  = Math.round(performance.now() - locStart);
                const sec = Math.floor(ms / 1000);
                const rem = ms % 1000;
                const qt  = document.getElementById('locQueryTime');
                qt.textContent = `Query: ${sec > 0 ? sec + 's ' + rem : ms}ms`;
                qt.classList.remove('d-none');
                renderLocResults(data.results);
            })
            .catch(() => showLocAlert('danger', 'Request failed. Please try again.'))
            .finally(() => { locBtn.disabled = false; locSpinner.classList.add('d-none'); });
    };
    locForm.addEventListener('submit', contentEl._locMatchHandler);
    // ── end LOC section ──────────────────────────────────────────

    if (contentEl._insMatchHandler) form.removeEventListener('submit', contentEl._insMatchHandler);
    contentEl._insMatchHandler = function(e) {
        if (e.target !== form) return;
        e.preventDefault();

        const name    = document.getElementById('ins_name').value.trim();
        const address = document.getElementById('ins_address').value.trim();
        const csz     = document.getElementById('ins_csz').value.trim();

        if (!name && !address && !csz) {
            showAlert('danger', 'Please enter at least one search field.');
            return;
        }

        clearAlert();
        btn.disabled = true;
        spinner.classList.remove('d-none');
        const queryStart = performance.now();

        fetch('api/insurance_match.php', {
            method: 'POST',
            body: new URLSearchParams({ action: 'search', name, address, csz })
        })
            .then(r => r.json())
            .then(data => {
                if (!data.success) { showAlert('danger', data.message ?? 'Search failed.'); return; }
                const ms  = Math.round(performance.now() - queryStart);
                const sec = Math.floor(ms / 1000);
                const rem = ms % 1000;
                const timeStr = sec > 0 ? `${sec}s ${rem}ms` : `${ms}ms`;
                const qt = document.getElementById('insQueryTime');
                qt.textContent = `Query: ${timeStr}`;
                qt.classList.remove('d-none');
                renderResults(data.results);
            })
            .catch(() => showAlert('danger', 'Request failed. Please try again.'))
            .finally(() => { btn.disabled = false; spinner.classList.add('d-none'); });
    };
    form.addEventListener('submit', contentEl._insMatchHandler);
}
