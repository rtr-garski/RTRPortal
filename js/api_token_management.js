function init_api_token_management() {

    var contentEl = document.getElementById('content');

    function showFlash(type, msg) {
        var el = document.getElementById('tokenFlash');
        if (!el) return;
        el.className = 'alert alert-' + type + ' alert-dismissible fade show';
        el.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    }

    if (window._tokenFlashPending) {
        showFlash(window._tokenFlashPending.type, window._tokenFlashPending.msg);
        window._tokenFlashPending = null;
    }

    function reloadPage(flashType, flashMsg) {
        window._tokenFlashPending = { type: flashType, msg: flashMsg };
        fetch('pages/api_token_management.php', { cache: 'no-store' })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                if (!html) return;
                contentEl.innerHTML = html;
                init_api_token_management();
            })
            .catch(function () {
                showFlash('danger', 'Could not reload. Please refresh.');
            });
    }

    // ─── Create token ─────────────────────────────────────────────────────────
    var createBtn = document.getElementById('createTokenBtn');
    if (createBtn) {
        createBtn.addEventListener('click', function () {
            var label = document.getElementById('tok-label').value.trim();
            if (!label) { showFlash('danger', 'A label is required.'); return; }

            createBtn.disabled = true;
            createBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generating…';

            fetch('api/api_token_management.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=create&label=' + encodeURIComponent(label)
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                createBtn.disabled = false;
                createBtn.innerHTML = '<i class="ti ti-key me-1"></i> Generate Token';
                bootstrap.Modal.getInstance(document.getElementById('createTokenModal'))?.hide();

                if (data.success) {
                    window._tokenFlashPending = {
                        type: 'success',
                        msg: data.message +
                            '<div class="input-group mt-2">' +
                            '<input type="text" id="newTokVal" class="form-control form-control-sm font-monospace" value="' + data.token + '" readonly>' +
                            '<button class="btn btn-sm btn-success" id="copyNewTok"><i class="ti ti-copy me-1"></i> Copy</button>' +
                            '</div>'
                    };
                    fetch('pages/api_token_management.php', { cache: 'no-store' })
                        .then(function (r) { return r.text(); })
                        .then(function (html) {
                            if (!html) return;
                            contentEl.innerHTML = html;
                            init_api_token_management();
                            var copyBtn = document.getElementById('copyNewTok');
                            if (copyBtn) {
                                copyBtn.addEventListener('click', function () {
                                    navigator.clipboard.writeText(document.getElementById('newTokVal').value).then(function () {
                                        copyBtn.innerHTML = '<i class="ti ti-check me-1"></i> Copied!';
                                        setTimeout(function () { copyBtn.innerHTML = '<i class="ti ti-copy me-1"></i> Copy'; }, 2000);
                                    });
                                });
                            }
                        });
                } else {
                    showFlash('danger', data.message);
                }
            })
            .catch(function () {
                createBtn.disabled = false;
                createBtn.innerHTML = '<i class="ti ti-key me-1"></i> Generate Token';
                showFlash('danger', 'Request failed. Please try again.');
            });
        });
    }

    // ─── Delete token (named handler) ─────────────────────────────────────────
    if (contentEl._tokDeleteHandler) {
        contentEl.removeEventListener('click', contentEl._tokDeleteHandler);
    }
    contentEl._tokDeleteHandler = function (e) {
        var btn = e.target.closest('.delete-tok-btn');
        if (!btn) return;
        if (!confirm('Permanently delete this token? Any API calls using it will stop working.')) return;
        btn.disabled = true;
        fetch('api/api_token_management.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=delete&id=' + encodeURIComponent(btn.dataset.id)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                reloadPage('success', data.message);
            } else {
                showFlash('danger', data.message);
                btn.disabled = false;
            }
        })
        .catch(function () {
            showFlash('danger', 'Delete failed. Please try again.');
            btn.disabled = false;
        });
    };
    contentEl.addEventListener('click', contentEl._tokDeleteHandler);

    // ─── Copy token buttons ───────────────────────────────────────────────────
    document.querySelectorAll('.copy-tok-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            navigator.clipboard.writeText(btn.dataset.token).then(function () {
                btn.innerHTML = '<i class="ti ti-check fs-lg"></i>';
                setTimeout(function () { btn.innerHTML = '<i class="ti ti-copy fs-lg"></i>'; }, 2000);
            });
        });
    });
}
