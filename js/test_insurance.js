function init_test_insurance() {
    const contentEl = document.getElementById('content');
    const form      = document.getElementById('insMatchForm');
    const btn       = document.getElementById('insMatchBtn');
    const spinner   = document.getElementById('insMatchSpinner');
    const alert     = document.getElementById('insMatchAlert');
    const count     = document.getElementById('insMatchCount');
    const placeholder = document.getElementById('insMatchPlaceholder');
    const tableWrap = document.getElementById('insMatchTableWrap');
    const tbody     = document.getElementById('insMatchTbody');

    function showAlert(type, msg) {
        alert.className = `alert alert-${type} py-2`;
        alert.innerHTML = msg;
        alert.classList.remove('d-none');
    }

    function clearAlert() {
        alert.classList.add('d-none');
    }

    function pctBadge(pct) {
        let cls = 'bg-danger';
        if (pct >= 80) cls = 'bg-success';
        else if (pct >= 60) cls = 'bg-warning text-dark';
        else if (pct >= 40) cls = 'bg-secondary';
        return `<span class="badge ${cls}">${pct}%</span>`;
    }

    function renderResults(results) {
        if (!results.length) {
            placeholder.textContent = 'No matches found.';
            placeholder.classList.remove('d-none');
            tableWrap.classList.add('d-none');
            count.classList.add('d-none');
            return;
        }

        tbody.innerHTML = results.map(r => `
            <tr title="Name: ${r.name_pct}% | Address: ${r.addr_pct}% | CSZ: ${r.csz_pct}%">
                <td class="ps-3">${pctBadge(r.match_pct)}</td>
                <td>${escHtml(r.name)}</td>
                <td>${escHtml(r.address)}</td>
                <td>${escHtml(r.csz)}</td>
                <td class="pe-3 text-end text-muted" style="font-size:.78rem">${escHtml(r.id)}</td>
            </tr>
        `).join('');

        placeholder.classList.add('d-none');
        tableWrap.classList.remove('d-none');
        count.textContent = `${results.length} result${results.length !== 1 ? 's' : ''}`;
        count.classList.remove('d-none');
    }

    function escHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    if (contentEl._insMatchHandler) {
        form.removeEventListener('submit', contentEl._insMatchHandler);
    }

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

        const body = new URLSearchParams({ action: 'search', name, address, csz });

        fetch('api/insurance_match.php', { method: 'POST', body })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    showAlert('danger', data.message ?? 'Search failed.');
                    return;
                }
                renderResults(data.results);
            })
            .catch(() => showAlert('danger', 'Request failed. Please try again.'))
            .finally(() => {
                btn.disabled = false;
                spinner.classList.add('d-none');
            });
    };

    form.addEventListener('submit', contentEl._insMatchHandler);
}
