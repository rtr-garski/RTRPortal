function init_file_upload() {

    var currentFileId = null;
    var contentEl = document.getElementById('content');

    // ─── Flash helper ─────────────────────────────────────────────────────────
    function showFlash(type, msg) {
        var el = document.getElementById('fileFlash');
        if (!el) return;
        el.className = 'alert alert-' + type + ' alert-dismissible fade show';
        el.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    }

    if (window._fileFlashPending) {
        showFlash(window._fileFlashPending.type, window._fileFlashPending.msg);
        window._fileFlashPending = null;
    }

    // ─── Reload helper (bypasses app.js entirely) ─────────────────────────────
    function reloadPage(flashType, flashMsg) {
        window._fileFlashPending = { type: flashType, msg: flashMsg };
        console.log('[fu] reloadPage: fetching pages/file_upload.php');
        fetch('pages/file_upload.php', { cache: 'no-store' })
            .then(function (r) {
                console.log('[fu] reloadPage: status=' + r.status + ' ok=' + r.ok);
                return r.text();
            })
            .then(function (html) {
                console.log('[fu] reloadPage: html.length=' + html.length);
                if (!html) { showFlash('danger', 'Empty response from server.'); return; }
                contentEl.innerHTML = html;
                console.log('[fu] reloadPage: content replaced, calling init');
                init_file_upload();
            })
            .catch(function (err) {
                console.error('[fu] reloadPage error:', err);
                showFlash('danger', 'Could not reload file list. Please refresh.');
            });
    }

    // ─── Upload ───────────────────────────────────────────────────────────────
    var uploadForm = document.getElementById('uploadForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = document.getElementById('uploadBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Uploading…';

            fetch('api/file_upload.php', { method: 'POST', body: new FormData(uploadForm) })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ti ti-cloud-upload me-1"></i> Upload';
                    if (data.success) {
                        reloadPage('success', data.message);
                    } else {
                        showFlash('danger', data.message);
                    }
                })
                .catch(function () {
                    showFlash('danger', 'Upload failed. Please try again.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ti ti-cloud-upload me-1"></i> Upload';
                });
        });
    }

    // ─── Delete (named handler stored on contentEl to prevent accumulation) ───
    if (contentEl._deleteHandler) {
        contentEl.removeEventListener('click', contentEl._deleteHandler);
    }
    contentEl._deleteHandler = function (e) {
        var btn = e.target.closest('.delete-btn');
        if (!btn) return;
        if (!confirm('Delete this file from B2 permanently?')) return;

        var id = btn.dataset.id;
        btn.disabled = true;

        fetch('api/file_upload.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=delete&id=' + encodeURIComponent(id)
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
    contentEl.addEventListener('click', contentEl._deleteHandler);

    // ─── Presign (Get Link) ───────────────────────────────────────────────────
    document.querySelectorAll('.get-link-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            currentFileId = btn.dataset.id;
            var modal   = new bootstrap.Modal(document.getElementById('presignModal'));
            var loading = document.getElementById('presignLoading');
            var result  = document.getElementById('presignResult');

            loading.classList.remove('d-none');
            result.classList.add('d-none');
            document.getElementById('presignUrl').value = '';
            document.getElementById('presignExpiry').textContent = '';
            modal.show();

            fetch('api/file_upload.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=presign&id=' + encodeURIComponent(currentFileId)
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                loading.classList.add('d-none');
                result.classList.remove('d-none');
                if (data.success) {
                    document.getElementById('presignUrl').value = data.url;
                    document.getElementById('openPresignUrl').href = data.url;
                    document.getElementById('presignExpiry').textContent = '(expires ' + data.expires_at + ')';

                    var linkRow   = document.querySelector('.link-row-' + currentFileId);
                    var linkInput = document.querySelector('.row-link-input-' + currentFileId);
                    var openBtn   = document.querySelector('.row-open-btn-' + currentFileId);
                    var expiry    = document.querySelector('.row-expiry-' + currentFileId);
                    linkInput.value    = data.url;
                    openBtn.href       = data.url;
                    expiry.textContent = 'Expires ' + data.expires_at;
                    linkRow.classList.remove('d-none');
                } else {
                    document.getElementById('presignUrl').value = 'Error: ' + data.message;
                }
            })
            .catch(function () {
                loading.classList.add('d-none');
                result.classList.remove('d-none');
                document.getElementById('presignUrl').value = 'Request failed. Please try again.';
            });
        });
    });

    // ─── Copy presign URL (modal) ─────────────────────────────────────────────
    var copyPresignBtn = document.getElementById('copyPresignUrl');
    if (copyPresignBtn) {
        copyPresignBtn.addEventListener('click', function () {
            navigator.clipboard.writeText(document.getElementById('presignUrl').value).then(function () {
                copyPresignBtn.innerHTML = '<i class="ti ti-check me-1"></i> Copied!';
                setTimeout(function () { copyPresignBtn.innerHTML = '<i class="ti ti-copy me-1"></i> Copy'; }, 2000);
            });
        });
    }

    // ─── Copy inline row (named handler to prevent accumulation) ─────────────
    if (contentEl._copyHandler) {
        contentEl.removeEventListener('click', contentEl._copyHandler);
    }
    contentEl._copyHandler = function (e) {
        var btn = e.target.closest('[class*="row-copy-btn-"]');
        if (!btn) return;
        var id    = btn.className.match(/row-copy-btn-(\d+)/)[1];
        var input = document.querySelector('.row-link-input-' + id);
        navigator.clipboard.writeText(input.value).then(function () {
            btn.innerHTML = '<i class="ti ti-check me-1"></i> Copied!';
            setTimeout(function () { btn.innerHTML = '<i class="ti ti-copy me-1"></i> Copy'; }, 2000);
        });
    };
    contentEl.addEventListener('click', contentEl._copyHandler);
}
