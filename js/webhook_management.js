function init_webhook_management() {

    var contentEl = document.getElementById('content');

    function showFlash(type, msg) {
        var el = document.getElementById('webhookFlash');
        if (!el) return;
        el.className = 'alert alert-' + type + ' alert-dismissible fade show';
        el.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    }

    if (window._webhookFlashPending) {
        showFlash(window._webhookFlashPending.type, window._webhookFlashPending.msg);
        window._webhookFlashPending = null;
    }

    function reloadPage(flashType, flashMsg) {
        window._webhookFlashPending = { type: flashType, msg: flashMsg };
        fetch('pages/webhook_management.php', { cache: 'no-store' })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                if (!html) return;
                contentEl.innerHTML = html;
                init_webhook_management();
            })
            .catch(function () {
                showFlash('danger', 'Could not reload. Please refresh.');
            });
    }

    function postAction(body, onSuccess, onError) {
        fetch('api/webhook_management.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) { onSuccess(data); } else { showFlash('danger', data.message); if (onError) onError(); }
        })
        .catch(function () { showFlash('danger', 'Request failed. Please try again.'); if (onError) onError(); });
    }

    // ─── Add endpoint ─────────────────────────────────────────────────────────
    var addBtn = document.getElementById('addEndpointBtn');
    if (addBtn) {
        addBtn.addEventListener('click', function () {
            var event  = document.getElementById('ep-event').value;
            var url    = document.getElementById('ep-url').value.trim();
            var secret = document.getElementById('ep-secret').value.trim();

            if (!event || !url) { showFlash('danger', 'URL and Event are required.'); return; }

            addBtn.disabled = true;
            addBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';

            postAction(
                'action=add&event=' + encodeURIComponent(event) + '&url=' + encodeURIComponent(url) + '&secret=' + encodeURIComponent(secret),
                function (data) {
                    addBtn.disabled = false;
                    addBtn.innerHTML = '<i class="ti ti-plug me-1"></i> Register Endpoint';
                    bootstrap.Modal.getInstance(document.getElementById('addEndpointModal'))?.hide();
                    reloadPage('success', data.message);
                },
                function () {
                    addBtn.disabled = false;
                    addBtn.innerHTML = '<i class="ti ti-plug me-1"></i> Register Endpoint';
                }
            );
        });
    }

    // ─── Generate secret ──────────────────────────────────────────────────────
    var genSecret = document.getElementById('generateSecret');
    if (genSecret) {
        genSecret.addEventListener('click', function () {
            var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            var secret = Array.from(crypto.getRandomValues(new Uint8Array(32)))
                .map(function (b) { return chars[b % chars.length]; }).join('');
            document.getElementById('ep-secret').value = secret;
        });
    }

    // ─── Table action buttons (named handler) ─────────────────────────────────
    if (contentEl._whActionHandler) {
        contentEl.removeEventListener('click', contentEl._whActionHandler);
    }
    contentEl._whActionHandler = function (e) {
        var btn = e.target.closest('.wh-delete-btn, .wh-toggle-btn, .wh-test-btn');
        if (!btn) return;

        var id = btn.dataset.id;

        if (btn.classList.contains('wh-delete-btn')) {
            if (!confirm('Delete this endpoint?')) return;
            btn.disabled = true;
            postAction('action=delete&id=' + encodeURIComponent(id),
                function (data) { reloadPage('success', data.message); },
                function () { btn.disabled = false; }
            );

        } else if (btn.classList.contains('wh-toggle-btn')) {
            btn.disabled = true;
            postAction('action=toggle&id=' + encodeURIComponent(id),
                function (data) { reloadPage('success', data.message); },
                function () { btn.disabled = false; }
            );

        } else if (btn.classList.contains('wh-test-btn')) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            postAction('action=test&id=' + encodeURIComponent(id),
                function (data) {
                    reloadPage(data.success ? 'success' : 'warning', data.message);
                },
                function () {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ti ti-send"></i>';
                }
            );
        }
    };
    contentEl.addEventListener('click', contentEl._whActionHandler);

    // ─── Init popovers ────────────────────────────────────────────────────────
    document.querySelectorAll('.wh-log-popover').forEach(function (el) {
        new bootstrap.Popover(el, { sanitize: false });
    });
}
