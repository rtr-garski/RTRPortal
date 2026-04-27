function init_test_insurance() {
    const contentEl  = document.getElementById('content');
    const form       = document.getElementById('insMatchForm');
    const btn        = document.getElementById('insMatchBtn');
    const spinner    = document.getElementById('insMatchSpinner');
    const alertEl    = document.getElementById('insMatchAlert');
    const countBadge = document.getElementById('insMatchCount');
    const tbody      = document.getElementById('insMatchTbody');
    const filterEl   = document.getElementById('insMatchFilter');
    const perPageEl  = document.getElementById('insMatchPerPage');
    const pagInfo    = document.getElementById('insPaginationInfo');
    const pagEl      = document.getElementById('insPagination');

    let allResults     = [];
    let currentPage    = 1;

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

    function filtered() {
        const q = (filterEl.value ?? '').toLowerCase().trim();
        if (!q) return allResults;
        return allResults.filter(r =>
            (r.name    ?? '').toLowerCase().includes(q) ||
            (r.address ?? '').toLowerCase().includes(q) ||
            (r.csz     ?? '').toLowerCase().includes(q)
        );
    }

    function render() {
        const rows     = filtered();
        const perPage  = parseInt(perPageEl.value) || 10;
        const total    = rows.length;
        const maxPage  = Math.max(1, Math.ceil(total / perPage));
        if (currentPage > maxPage) currentPage = maxPage;

        const start = (currentPage - 1) * perPage;
        const slice = rows.slice(start, start + perPage);

        if (!total) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-5">No matches found.</td></tr>`;
            pagInfo.textContent = '';
            pagEl.innerHTML = '';
            return;
        }

        tbody.innerHTML = slice.map(r => `
            <tr title="Name: ${r.name_pct ?? '-'}% | Address: ${r.addr_pct ?? '-'}% | CSZ: ${r.csz_pct ?? '-'}%">
                <td class="ps-3">${pctBadge(r.match_pct)}</td>
                <td><span class="fw-medium">${escHtml(r.name)}</span></td>
                <td class="text-muted">${escHtml(r.address)}</td>
                <td class="text-muted">${escHtml(r.csz)}</td>
                <td class="text-end pe-3 text-muted fs-xs">${escHtml(r.id)}</td>
            </tr>
        `).join('');

        // Pagination info
        const from = start + 1;
        const to   = Math.min(start + perPage, total);
        pagInfo.textContent = `Showing ${from} to ${to} of ${total} results`;

        // Pagination buttons
        let btns = '';
        btns += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage - 1}">&laquo;</a></li>`;
        for (let i = 1; i <= maxPage; i++) {
            btns += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }
        btns += `<li class="page-item ${currentPage === maxPage ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage + 1}">&raquo;</a></li>`;
        pagEl.innerHTML = btns;
    }

    function showAlert(type, msg) {
        alertEl.className = `alert alert-${type} py-2`;
        alertEl.innerHTML = msg;
        alertEl.classList.remove('d-none');
    }

    function clearAlert() { alertEl.classList.add('d-none'); }

    // Pagination clicks
    if (contentEl._insPagHandler) pagEl.removeEventListener('click', contentEl._insPagHandler);
    contentEl._insPagHandler = function(e) {
        const a = e.target.closest('[data-page]');
        if (!a) return;
        e.preventDefault();
        const p = parseInt(a.dataset.page);
        const perPage = parseInt(perPageEl.value) || 10;
        const max = Math.ceil(filtered().length / perPage);
        if (p >= 1 && p <= max) { currentPage = p; render(); }
    };
    pagEl.addEventListener('click', contentEl._insPagHandler);

    // Filter & per-page
    filterEl.addEventListener('input',  () => { currentPage = 1; render(); });
    perPageEl.addEventListener('change', () => { currentPage = 1; render(); });

    // Search form
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

        fetch('api/insurance_match.php', {
            method: 'POST',
            body: new URLSearchParams({ action: 'search', name, address, csz })
        })
            .then(r => r.json())
            .then(data => {
                if (!data.success) { showAlert('danger', data.message ?? 'Search failed.'); return; }
                allResults = data.results;
                currentPage = 1;
                filterEl.value = '';
                countBadge.textContent = `${allResults.length} result${allResults.length !== 1 ? 's' : ''}`;
                countBadge.classList.toggle('d-none', !allResults.length);
                render();
            })
            .catch(() => showAlert('danger', 'Request failed. Please try again.'))
            .finally(() => { btn.disabled = false; spinner.classList.add('d-none'); });
    };
    form.addEventListener('submit', contentEl._insMatchHandler);
}
