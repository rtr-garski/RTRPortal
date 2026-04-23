function init_b2b_test() {
    const contentEl    = document.getElementById('content');
    const flash        = document.getElementById('b2b-flash');
    const genFlash     = document.getElementById('b2b-gen-flash');
    const genResult    = document.getElementById('b2b-gen-result');
    const progressWrap = document.getElementById('b2b-progress-wrap');
    const progressBar  = document.getElementById('b2b-progress-bar');
    const progressPct  = document.getElementById('b2b-progress-pct');
    const progressLbl  = document.getElementById('b2b-progress-label');
    const resultCard   = document.getElementById('b2b-result-card');

    // Auto-populate a random active token from the DB
    const tokenEl = document.getElementById('b2b-token');
    if (tokenEl && !tokenEl.value) {
        fetch('api/b2b_token.php')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.token) {
                    tokenEl.value = data.token;
                } else {
                    console.warn('b2b_token: no active token found', data);
                }
            })
            .catch(function (err) { console.error('b2b_token fetch failed:', err); });
    }

    function showFlash(el, type, msg) {
        el.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${msg}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    }

    function setProgress(pct, label) {
        progressWrap.classList.remove('d-none');
        progressBar.style.width  = pct + '%';
        progressPct.textContent  = pct + '%';
        progressLbl.textContent  = label;
    }

    function resetProgress() {
        progressWrap.classList.add('d-none');
        progressBar.style.width = '0%';
        progressBar.classList.add('progress-bar-animated');
    }

    if (contentEl._b2bHandler) contentEl.removeEventListener('click', contentEl._b2bHandler);
    contentEl._b2bHandler = async function (e) {

        // ── Step 1: Generate Presigned URL ──────────────────────────────
        if (e.target.closest('#b2b-gen-btn')) {
            const token    = document.getElementById('b2b-token').value.trim();
            const orderId  = document.getElementById('b2b-order-id').value.trim();
            const filename = document.getElementById('b2b-filename').value.trim();
            const genBtn   = document.getElementById('b2b-gen-btn');

            genFlash.innerHTML = '';
            genResult.classList.add('d-none');

            if (!token)   return showFlash(genFlash, 'warning', 'API Token is required.');
            if (!orderId) return showFlash(genFlash, 'warning', 'Order ID is required.');

            genBtn.disabled = true;
            genBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generating…';

            try {
                const body = new URLSearchParams({ token, order_id: orderId });
                if (filename) body.append('filename', filename);

                const r    = await fetch('api/b2b_presign.php', { method: 'POST', body });
                const info = await r.json();

                if (!info.success) {
                    showFlash(genFlash, 'danger', info.message || 'Failed to generate presigned URL.');
                } else {
                    document.getElementById('gen-folder').textContent    = info.folder;
                    document.getElementById('gen-url-display').value     = info.presigned_url;
                    document.getElementById('gen-expires').textContent   = info.expires_in;
                    genResult.classList.remove('d-none');

                    // Populate Step 2
                    document.getElementById('b2b-presigned-url').value = info.presigned_url;

                    // Populate cURL card
                    const curlCmd = 'curl -X PUT "' + info.presigned_url + '" \\\n' +
                                    '  -H "Content-Type: application/octet-stream" \\\n' +
                                    '  --data-binary @/path/to/your/file';
                    document.getElementById('b2b-curl-cmd').textContent = curlCmd;
                    document.getElementById('b2b-curl-card').classList.remove('d-none');

                    // Store info on result card for after upload
                    resultCard.dataset.folder   = info.folder;
                    resultCard.dataset.filename = info.filename;
                    resultCard.dataset.b2path   = info.b2_file_name;
                    resultCard.dataset.url      = info.presigned_url;
                }
            } catch (err) {
                showFlash(genFlash, 'danger', 'Network error: ' + err.message);
            }

            genBtn.disabled = false;
            genBtn.innerHTML = '<i class="ti ti-link me-1"></i> Generate Presigned URL';
            return;
        }

        // ── Copy cURL command ───────────────────────────────────────────
        if (e.target.closest('#b2b-copy-curl')) {
            const cmd = document.getElementById('b2b-curl-cmd').textContent;
            navigator.clipboard.writeText(cmd).then(() => {
                const btn = document.getElementById('b2b-copy-curl');
                btn.textContent = 'Copied!';
                setTimeout(() => { btn.textContent = 'Copy'; }, 1500);
            });
            return;
        }

        // ── Copy URL button ─────────────────────────────────────────────
        if (e.target.closest('#b2b-copy-url')) {
            const url = document.getElementById('gen-url-display').value;
            if (!url) return;
            navigator.clipboard.writeText(url).then(() => {
                const btn = document.getElementById('b2b-copy-url');
                btn.innerHTML = '<i class="ti ti-check"></i>';
                setTimeout(() => { btn.innerHTML = '<i class="ti ti-copy"></i>'; }, 1500);
            });
            return;
        }

        // ── Step 2: Upload File ─────────────────────────────────────────
        if (e.target.closest('#b2b-upload-btn')) {
            const presignedUrl = document.getElementById('b2b-presigned-url').value.trim();
            const fileEl       = document.getElementById('b2b-file');
            const file         = fileEl.files[0];
            const uploadBtn    = document.getElementById('b2b-upload-btn');

            flash.innerHTML = '';
            resultCard.classList.add('d-none');
            resetProgress();

            if (!presignedUrl) return showFlash(flash, 'warning', 'Presigned URL is required. Generate one above first.');
            if (!file)         return showFlash(flash, 'warning', 'Please select a file.');

            uploadBtn.disabled = true;
            setProgress(10, 'Uploading to Backblaze B2...');

            await new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.open('PUT', presignedUrl);

                xhr.upload.onprogress = function (ev) {
                    if (ev.lengthComputable) {
                        const pct = Math.round(10 + (ev.loaded / ev.total) * 88);
                        setProgress(pct, 'Uploading... ' + Math.round((ev.loaded / 1024 / 1024) * 10) / 10 + ' MB');
                    }
                };

                xhr.onload = function () {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        resolve({
                            fileId: xhr.getResponseHeader('x-amz-version-id') || '—',
                            etag:   (xhr.getResponseHeader('ETag') || '—').replace(/"/g, '')
                        });
                    } else {
                        reject(new Error('B2 returned HTTP ' + xhr.status + ': ' + xhr.responseText));
                    }
                };

                xhr.onerror = () => reject(new Error('Network error during upload.'));
                xhr.send(file);
            }).then((b2res) => {
                setProgress(100, 'Done!');
                progressBar.classList.remove('progress-bar-animated');

                const folder  = resultCard.dataset.folder   || '—';
                const b2Path  = resultCard.dataset.b2path   || '';
                const b2File  = resultCard.dataset.filename || file.name;
                document.getElementById('res-folder').textContent   = folder;
                document.getElementById('res-filename').textContent = b2File;
                document.getElementById('res-b2path').textContent   = b2Path || '—';
                document.getElementById('res-file-id').textContent  = b2res.fileId;
                document.getElementById('res-etag').textContent     = b2res.etag;

                document.getElementById('res-download-public').href = 'https://f004.backblazeb2.com/file/RTR-ClientUpload/' + b2Path;

                fetch('api/b2b_download.php?path=' + encodeURIComponent(b2Path || folder + '/' + file.name))
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) document.getElementById('res-download-secure').href = d.url;
                    });
                const link = document.getElementById('res-presigned');
                link.href        = resultCard.dataset.url || presignedUrl;
                link.textContent = resultCard.dataset.url || presignedUrl;

                resultCard.classList.remove('d-none');
            }).catch(err => {
                showFlash(flash, 'danger', 'Upload failed: ' + err.message);
                resetProgress();
            });

            uploadBtn.disabled = false;
        }
    };

    contentEl.addEventListener('click', contentEl._b2bHandler);
}
