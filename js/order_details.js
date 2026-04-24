function init_order_details() {
    var contentEl = document.getElementById('content');

    var reloadLink = document.getElementById('order-detail-reload');
    if (reloadLink) {
        reloadLink.addEventListener('click', function (e) {
            e.preventDefault();
            var orderId = this.dataset.orderId;
            fetch('pages/order_details.php?order_id=' + encodeURIComponent(orderId), { cache: 'no-store' })
                .then(function (r) {
                    if (r.status === 401) { window.location.href = 'logout.php'; return null; }
                    return r.text();
                })
                .then(function (html) {
                    if (!html) return;
                    contentEl.innerHTML = html;
                    init_order_details();
                });
        });
    }

    contentEl.querySelectorAll('.order-entry-nav').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            fetch('pages/order_entry.php', { cache: 'no-store' })
                .then(function (r) {
                    if (r.status === 401) { window.location.href = 'logout.php'; return null; }
                    return r.text();
                })
                .then(function (html) {
                    if (!html) return;
                    contentEl.innerHTML = html;
                    init_order_entry();
                });
        });
    });

    // Release to System modal
    var releaseBtn = document.getElementById('releaseToSystemBtn');
    var releaseModalEl = document.getElementById('releaseToSystemModal');
    if (releaseBtn && releaseModalEl) {
        var releaseModal = new bootstrap.Modal(releaseModalEl);

        releaseBtn.addEventListener('click', function () {
            document.getElementById('fmConnStatus').innerHTML = '';
            document.getElementById('fmResponseWrap').style.display = 'none';
            releaseModal.show();
        });

        document.getElementById('fmTestConnBtn').addEventListener('click', function () {
            var statusEl = document.getElementById('fmConnStatus');
            statusEl.innerHTML = '<span class="text-muted fs-xs">Testing&hellip;</span>';
            var body = new FormData();
            body.append('action', 'test');
            body.append('fm_server',   document.getElementById('fmServer').value);
            body.append('fm_database', document.getElementById('fmDatabase').value);
            body.append('fm_layout',   document.getElementById('fmLayout').value);
            body.append('fm_user',     document.getElementById('fmUser').value);
            body.append('fm_pass',     document.getElementById('fmPass').value);
            fetch('api/release_order.php', { method: 'POST', body: body })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    statusEl.innerHTML = data.success
                        ? '<span class="badge bg-success"><i class="ti ti-check me-1"></i>' + data.message + '</span>'
                        : '<span class="badge bg-danger"><i class="ti ti-x me-1"></i>' + data.message + '</span>';
                });
        });

        document.getElementById('fmSendBtn').addEventListener('click', function () {
            var sendBtn     = this;
            var responseWrap = document.getElementById('fmResponseWrap');
            var responseBody = document.getElementById('fmResponseBody');
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending&hellip;';
            var body = new FormData();
            body.append('action', 'release');
            body.append('fm_server',   document.getElementById('fmServer').value);
            body.append('fm_database', document.getElementById('fmDatabase').value);
            body.append('fm_layout',   document.getElementById('fmLayout').value);
            body.append('fm_user',     document.getElementById('fmUser').value);
            body.append('fm_pass',     document.getElementById('fmPass').value);
            body.append('payload',     document.getElementById('fmPayload').value);
            fetch('api/release_order.php', { method: 'POST', body: body })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = '<i class="ti ti-send me-1"></i> Send to FileMaker';
                    responseWrap.style.display = '';
                    responseBody.className = 'p-3 rounded border bg-light';
                    if (data.success) {
                        responseBody.style.borderColor = '#198754';
                        responseBody.style.color = '#198754';
                    } else {
                        responseBody.style.borderColor = '#dc3545';
                        responseBody.style.color = '#dc3545';
                    }
                    responseBody.textContent = JSON.stringify(data, null, 2);
                });
        });
    }

    // API-RH modal
    var apiRhBtn     = document.getElementById('releaseToApiBtn');
    var apiRhModalEl = document.getElementById('apiRhModal');
    var apiRhSendBtn = document.getElementById('apiRhSendBtn');

    if (apiRhBtn && apiRhModalEl && apiRhSendBtn) {
        var apiRhModal = new bootstrap.Modal(apiRhModalEl);

        apiRhBtn.addEventListener('click', function () {
            apiRhModalEl.querySelector('#apiRhResponseWrap').style.display = 'none';
            apiRhSendBtn.disabled = false;
            apiRhSendBtn.innerHTML = '<i class="ti ti-api me-1"></i> Send to API-RH';
            apiRhModal.show();
        });

        apiRhSendBtn.addEventListener('click', function () {
            var sendBtn     = this;
            var respWrap    = apiRhModalEl.querySelector('#apiRhResponseWrap');
            var respBody    = apiRhModalEl.querySelector('#apiRhResponseBody');
            var statusBadge = apiRhModalEl.querySelector('#apiRhStatusBadge');
            var elapsedEl   = apiRhModalEl.querySelector('#apiRhElapsed');

            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending&hellip;';

            var body = new FormData();
            body.append('method',  apiRhModalEl.querySelector('#apiRhMethod').value);
            body.append('url',     apiRhModalEl.querySelector('#apiRhUrl').value);
            body.append('payload', apiRhModalEl.querySelector('#apiRhPayload').value);

            var controller = new AbortController();
            var timeout = setTimeout(function () { controller.abort(); }, 20000);

            function resetBtn() {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="ti ti-api me-1"></i> Send to API-RH';
            }
            function showResp(ok, status, elapsed, text) {
                respWrap.style.display = '';
                statusBadge.className = 'badge ' + (ok ? 'bg-success' : 'bg-danger');
                statusBadge.textContent = status;
                elapsedEl.textContent = elapsed || '';
                respBody.style.color = ok ? '#198754' : '#dc3545';
                respBody.style.borderColor = ok ? '#198754' : '#dc3545';
                respBody.textContent = text;
            }

            var start = Date.now();
            fetch(apiRhModalEl.querySelector('#apiRhUrl').value, {
                method:  apiRhModalEl.querySelector('#apiRhMethod').value,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body:    apiRhModalEl.querySelector('#apiRhPayload').value,
                signal:  controller.signal
            })
                .then(function (r) {
                    var elapsed = Date.now() - start;
                    var ok = r.ok;
                    var status = r.status;
                    return r.text().then(function (text) {
                        clearTimeout(timeout);
                        resetBtn();
                        var pretty;
                        try { pretty = JSON.stringify(JSON.parse(text), null, 2); } catch (e) { pretty = text; }
                        showResp(ok, status, elapsed + ' ms', pretty);
                    });
                })
                .catch(function (err) {
                    clearTimeout(timeout);
                    resetBtn();
                    showResp(false, 'Error', '', err.name === 'AbortError' ? 'Request timed out (20s)' : 'Fetch error: ' + err.message);
                });
        });
    }

    var modalEl = document.getElementById('changeInfoModal');
    if (!modalEl) return;

    var modal = new bootstrap.Modal(modalEl);
    var activeInput = null;

    contentEl.querySelectorAll('input.change-info-input.is-invalid').forEach(function (input) {
        input.style.cursor = 'pointer';
        input.addEventListener('click', function () {
            activeInput = input;
            document.getElementById('changeInfoSubmitted').textContent = input.value || '(empty)';
            document.getElementById('changeInfoSelect').value = '';
            document.getElementById('selectedCarrierInfo').style.display = 'none';

            var address = input.dataset.address || '';
            var phone   = input.dataset.phone   || '';
            var fax     = input.dataset.fax     || '';

            var addrRow    = document.getElementById('changeInfoAddress');
            var contactRow = document.getElementById('changeInfoContact');

            if (address) {
                document.getElementById('changeInfoAddressText').textContent = address;
                addrRow.style.display = '';
            } else {
                addrRow.style.display = 'none';
            }

            if (phone || fax) {
                document.getElementById('changeInfoPhone').textContent = phone || '—';
                document.getElementById('changeInfoFax').textContent   = fax   || '—';
                contactRow.style.display = '';
            } else {
                contactRow.style.display = 'none';
            }

            modal.show();
        });
    });

    document.getElementById('changeInfoSelect').addEventListener('change', function () {
        var opt = this.options[this.selectedIndex];
        var info = document.getElementById('selectedCarrierInfo');
        if (opt && opt.dataset.address) {
            document.getElementById('selectedCarrierAddress').textContent = opt.dataset.address;
            document.getElementById('selectedCarrierPhone').textContent   = opt.dataset.phone || '—';
            document.getElementById('selectedCarrierFax').textContent     = opt.dataset.fax   || '—';
            info.style.display = '';
        } else {
            info.style.display = 'none';
        }
    });

    document.getElementById('changeInfoSave').addEventListener('click', function () {
        var selected = document.getElementById('changeInfoSelect').value;
        if (activeInput && selected) {
            activeInput.value = selected;
            activeInput.classList.remove('is-invalid');
            activeInput.classList.add('is-valid');
            activeInput.style.cursor = '';
        }
        modal.hide();
    });
}
