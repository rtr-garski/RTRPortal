function init_b2b_test() {
    const contentEl  = document.getElementById('content');
    const flash      = document.getElementById('b2b-flash');
    const btn        = document.getElementById('b2b-upload-btn');
    const progressWrap = document.getElementById('b2b-progress-wrap');
    const progressBar  = document.getElementById('b2b-progress-bar');
    const progressPct  = document.getElementById('b2b-progress-pct');
    const progressLbl  = document.getElementById('b2b-progress-label');
    const resultCard   = document.getElementById('b2b-result-card');

    function showFlash(type, msg) {
        flash.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${msg}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    }

    function setProgress(pct, label) {
        progressWrap.classList.remove('d-none');
        progressBar.style.width = pct + '%';
        progressPct.textContent  = pct + '%';
        progressLbl.textContent  = label;
    }

    function resetProgress() {
        progressWrap.classList.add('d-none');
        progressBar.style.width = '0%';
    }

    if (contentEl._b2bHandler) contentEl.removeEventListener('click', contentEl._b2bHandler);
    contentEl._b2bHandler = async function (e) {
        if (!e.target.closest('#b2b-upload-btn')) return;

        const token   = document.getElementById('b2b-token').value.trim();
        const orderId = document.getElementById('b2b-order-id').value.trim();
        const uuid    = document.getElementById('b2b-uuid').value.trim();
        const fileEl  = document.getElementById('b2b-file');
        const file    = fileEl.files[0];

        flash.innerHTML = '';
        resultCard.classList.add('d-none');
        resetProgress();

        if (!token)   return showFlash('warning', 'API Token is required.');
        if (!orderId) return showFlash('warning', 'Order ID is required.');
        if (!file)    return showFlash('warning', 'Please select a file.');

        const ext = file.name.includes('.') ? file.name.split('.').pop() : 'bin';

        btn.disabled = true;
        setProgress(10, 'Requesting presigned URL...');

        // Step 1 — get presigned URL from our API
        let info;
        try {
            const body = new URLSearchParams({ token, order_id: orderId, extension: ext });
            if (uuid) body.append('uuid', uuid);

            const r = await fetch('api/b2b_presign.php', { method: 'POST', body });
            info = await r.json();
        } catch (err) {
            btn.disabled = false;
            resetProgress();
            return showFlash('danger', 'Network error fetching presigned URL: ' + err.message);
        }

        if (!info.success) {
            btn.disabled = false;
            resetProgress();
            return showFlash('danger', info.message || 'Failed to get presigned URL.');
        }

        setProgress(30, 'Uploading to Backblaze B2...');

        // Step 2 — PUT file directly to B2 via XHR (so we get progress events)
        await new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('PUT', info.presigned_url);
            xhr.setRequestHeader('X-Bz-File-Name', encodeURIComponent(info.b2_file_name));
            xhr.setRequestHeader('Content-Type', file.type || 'application/octet-stream');

            xhr.upload.onprogress = function (ev) {
                if (ev.lengthComputable) {
                    const pct = Math.round(30 + (ev.loaded / ev.total) * 65);
                    setProgress(pct, 'Uploading... ' + Math.round((ev.loaded / 1024 / 1024) * 10) / 10 + ' MB');
                }
            };

            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 300) {
                    resolve();
                } else {
                    reject(new Error('B2 returned HTTP ' + xhr.status + ': ' + xhr.responseText));
                }
            };

            xhr.onerror = () => reject(new Error('Network error during upload.'));
            xhr.send(file);
        }).then(() => {
            setProgress(100, 'Done!');
            progressBar.classList.remove('progress-bar-animated');

            // Populate result card
            document.getElementById('res-folder').textContent   = info.folder;
            document.getElementById('res-filename').textContent = info.filename;
            document.getElementById('res-b2path').textContent   = info.b2_file_name;
            const link = document.getElementById('res-presigned');
            link.href        = info.presigned_url;
            link.textContent = info.presigned_url;

            resultCard.classList.remove('d-none');
        }).catch(err => {
            showFlash('danger', 'Upload failed: ' + err.message);
            resetProgress();
        });

        btn.disabled = false;
    };

    contentEl.addEventListener('click', contentEl._b2bHandler);
}
