function init_api_portal() {
    var root = document.getElementById('api-portal-root');
    if (!root) return;

    var endpoint     = root.dataset.endpoint;
    var keyEl        = document.getElementById('api_key');
    var bodyEl       = document.getElementById('json_body');
    var preview      = document.getElementById('curlPreview');
    var jsonStat     = document.getElementById('jsonStatus');
    var resultBox    = document.getElementById('resultBox');
    var statusBadge  = document.getElementById('statusBadge');
    var timeBadge    = document.getElementById('timeBadge');
    var curlCard     = document.getElementById('curlResultCard');
    var curlResultBox = document.getElementById('curlResultBox');
    var form         = document.getElementById('apiForm');
    var formError    = document.getElementById('apiFormError');

    function escapeHtml(s) {
        return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function updatePreview() {
        var key  = keyEl.value  || '<API_KEY>';
        var body = bodyEl.value || '{}';
        preview.innerHTML =
            '<span class="kw">curl</span> <span class="opt">-X POST</span> <span class="str">"' + escapeHtml(endpoint) + '"</span> \\\n' +
            '  <span class="opt">-H</span> <span class="str">"Content-Type: application/json"</span> \\\n' +
            '  <span class="opt">-H</span> <span class="str">"X-API-Key: ' + escapeHtml(key) + '"</span> \\\n' +
            '  <span class="opt">-d</span> <span class="str">\'' + escapeHtml(body) + '\'</span>';
    }

    function validateJson() {
        var v = bodyEl.value.trim();
        if (!v) { jsonStat.textContent = ''; return; }
        try {
            JSON.parse(v);
            jsonStat.className = 'text-success fw-semibold';
            jsonStat.textContent = '✓ valid JSON';
        } catch (e) {
            jsonStat.className = 'text-danger fw-semibold';
            jsonStat.textContent = '✗ ' + e.message;
        }
    }

    function formatJson() {
        try { bodyEl.value = JSON.stringify(JSON.parse(bodyEl.value), null, 2); } catch (_) {}
    }

    // Remove stale listeners before re-attaching
    if (keyEl._apiInput)    keyEl.removeEventListener('input', keyEl._apiInput);
    if (bodyEl._apiInput)   bodyEl.removeEventListener('input', bodyEl._apiInput);
    if (bodyEl._apiBlur)    bodyEl.removeEventListener('blur',  bodyEl._apiBlur);
    if (form._apiSubmit)    form.removeEventListener('submit',  form._apiSubmit);

    keyEl._apiInput  = updatePreview;
    bodyEl._apiInput = function () { updatePreview(); validateJson(); };
    bodyEl._apiBlur  = formatJson;

    keyEl.addEventListener('input',  keyEl._apiInput);
    bodyEl.addEventListener('input', bodyEl._apiInput);
    bodyEl.addEventListener('blur',  bodyEl._apiBlur);

    form._apiSubmit = function (e) {
        e.preventDefault();
        formError.classList.add('d-none');
        resultBox.className = 'result-box is-empty';
        resultBox.textContent = '// Sending…';
        statusBadge.innerHTML = '';
        timeBadge.innerHTML   = '';
        curlCard.classList.add('d-none');

        var fd = new FormData();
        fd.append('action',    'submit');
        fd.append('api_key',   keyEl.value);
        fd.append('json_body', bodyEl.value);

        fetch('api/api_portal.php', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.formError) {
                    formError.textContent = data.message;
                    formError.classList.remove('d-none');
                    resultBox.className = 'result-box is-empty';
                    resultBox.textContent = '// Response will appear here after you send a request.';
                    return;
                }
                resultBox.textContent = data.result || data.message || '';
                resultBox.className   = 'result-box' + (data.statusCode >= 400 ? ' is-error' : '');
                statusBadge.innerHTML = 'Status:&nbsp;<span class="badge bg-' + data.badgeClass + '">' + data.statusCode + '</span>';
                timeBadge.innerHTML   = 'Time: <strong>' + data.responseTime + 'ms</strong>';
                if (data.curlCommand) {
                    curlResultBox.textContent = data.curlCommand;
                    curlCard.classList.remove('d-none');
                }
            })
            .catch(function () {
                resultBox.className   = 'result-box is-error';
                resultBox.textContent = '// Request failed.';
            });
    };
    form.addEventListener('submit', form._apiSubmit);

    // Copy preview button
    var copyPreviewBtn = document.getElementById('copyPreviewBtn');
    if (copyPreviewBtn._apiClick) copyPreviewBtn.removeEventListener('click', copyPreviewBtn._apiClick);
    copyPreviewBtn._apiClick = function () {
        var cmd = 'curl -X POST "' + endpoint + '" \\\n' +
                  '  -H "Content-Type: application/json" \\\n' +
                  '  -H "X-API-Key: ' + keyEl.value + '" \\\n' +
                  "  -d '" + bodyEl.value + "'";
        navigator.clipboard.writeText(cmd).then(function () {
            copyPreviewBtn.textContent = 'Copied!';
            setTimeout(function () { copyPreviewBtn.textContent = 'Copy'; }, 1500);
        });
    };
    copyPreviewBtn.addEventListener('click', copyPreviewBtn._apiClick);

    // Copy curl result button
    var copyCurlResult = document.getElementById('copyCurlResult');
    if (copyCurlResult._apiClick) copyCurlResult.removeEventListener('click', copyCurlResult._apiClick);
    copyCurlResult._apiClick = function () {
        navigator.clipboard.writeText(curlResultBox.textContent).then(function () {
            copyCurlResult.textContent = 'Copied!';
            setTimeout(function () { copyCurlResult.textContent = 'Copy'; }, 1500);
        });
    };
    copyCurlResult.addEventListener('click', copyCurlResult._apiClick);

    updatePreview();
    validateJson();
}
