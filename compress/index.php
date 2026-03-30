<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PDF Compressor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f0f2f5; }
    .drop-zone {
      border: 2px dashed #adb5bd;
      border-radius: 10px;
      padding: 40px;
      text-align: center;
      cursor: pointer;
      transition: border-color 0.2s, background 0.2s;
    }
    .drop-zone:hover, .drop-zone.dragover {
      border-color: #0d6efd;
      background: #e8f0fe;
    }
    .drop-zone .icon { font-size: 2.5rem; }
  </style>
</head>
<body>

<div class="container py-5" style="max-width:540px">
  <div class="card shadow-sm p-4">
    <h5 class="mb-1 fw-bold">PDF Compressor</h5>
    <p class="text-muted small mb-3">Powered by IlovePDF API</p>

    <div class="drop-zone mb-3" id="dropZone">
      <div class="icon">📄</div>
      <div class="mt-2 fw-semibold">Drag &amp; drop a PDF here</div>
      <div class="text-muted small">or click to browse</div>
      <input type="file" id="fileInput" accept="application/pdf" class="d-none">
    </div>

    <div id="fileInfo" class="d-none mb-3 p-3 bg-light rounded border small">
      <strong id="fileName"></strong>
      <span class="text-muted ms-2" id="fileSize"></span>
    </div>

    <div class="progress d-none mb-3" id="progressWrapper" style="height:22px">
      <div class="progress-bar progress-bar-striped progress-bar-animated"
           id="progressBar" role="progressbar" style="width:0%">0%</div>
    </div>

    <div id="result"></div>
  </div>
</div>

<script>
  const dropZone    = document.getElementById('dropZone');
  const fileInput   = document.getElementById('fileInput');
  const fileInfo    = document.getElementById('fileInfo');
  const fileName    = document.getElementById('fileName');
  const fileSize    = document.getElementById('fileSize');
  const progressWrap = document.getElementById('progressWrapper');
  const progressBar = document.getElementById('progressBar');
  const result      = document.getElementById('result');

  // Click to browse
  dropZone.addEventListener('click', () => fileInput.click());

  // Drag & drop
  dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
  dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
  dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (file) handleFile(file);
  });

  fileInput.addEventListener('change', function() {
    if (this.files[0]) handleFile(this.files[0]);
  });

  function formatBytes(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(2) + ' MB';
  }

  function handleFile(file) {
    if (file.type !== 'application/pdf') {
      result.innerHTML = "<div class='alert alert-warning'>Please select a valid PDF file.</div>";
      return;
    }

    // Show file info
    fileName.textContent = file.name;
    fileSize.textContent = formatBytes(file.size);
    fileInfo.classList.remove('d-none');

    // Reset UI
    result.innerHTML = '';
    progressWrap.classList.remove('d-none');
    progressBar.style.width = '0%';
    progressBar.textContent = '0%';

    const formData = new FormData();
    formData.append('pdf', file);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'compress.php', true);
    xhr.responseType = 'blob';

    xhr.upload.onprogress = function(e) {
      if (e.lengthComputable) {
        const pct = Math.round((e.loaded / e.total) * 100);
        progressBar.style.width = pct + '%';
        progressBar.textContent = pct === 100 ? 'Compressing...' : pct + '%';
      }
    };

    xhr.onload = function() {
      progressWrap.classList.add('d-none');
      const contentType = xhr.getResponseHeader('Content-Type');

      if (xhr.status === 200 && contentType && contentType.includes('application/pdf')) {
        const originalSize  = file.size;
        const compressedSize = parseInt(xhr.getResponseHeader('X-Compressed-Size')) || xhr.response.size;
        const saved         = originalSize - compressedSize;
        const savedPct      = ((saved / originalSize) * 100).toFixed(1);

        const url = URL.createObjectURL(xhr.response);
        const a   = document.createElement('a');
        a.href     = url;
        a.download = file.name.replace(/\.pdf$/i, '') + '_compressed.pdf';
        document.body.appendChild(a);
        a.click();
        a.remove();

        result.innerHTML = `
          <div class="alert alert-success mb-0">
            ✅ <strong>Compressed successfully!</strong><br>
            <span class="small">
              Original: <strong>${formatBytes(originalSize)}</strong> →
              Compressed: <strong>${formatBytes(compressedSize)}</strong>
              ${saved > 0 ? `<span class="text-success">(saved ${savedPct}%)</span>` : ''}
            </span>
          </div>`;
      } else {
        xhr.response.text().then(text => {
          let msg = 'Compression failed.';
          try { msg = JSON.parse(text).error || msg; } catch(e) {}
          result.innerHTML = `<div class='alert alert-danger'>❌ ${msg}</div>`;
        });
      }
    };

    xhr.onerror = function() {
      progressWrap.classList.add('d-none');
      result.innerHTML = "<div class='alert alert-danger'>❌ Network error. Please try again.</div>";
    };

    xhr.send(formData);
  }
</script>

</body>
</html>
