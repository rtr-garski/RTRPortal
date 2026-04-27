function init_test_insurance() {
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
