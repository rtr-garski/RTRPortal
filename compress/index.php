<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>PDF Compressor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
  <div class="card shadow p-4">
    <h4 class="mb-3">Upload PDF (Auto Compress) test</h4>

    <input type="file" id="fileInput" class="form-control mb-3" accept="application/pdf">

    <div class="progress d-none" id="progressWrapper">
      <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%">0%</div>
    </div>

    <div id="result" class="mt-3"></div>
  </div>
</div>
<script>
document.getElementById('fileInput').addEventListener('change', function() {
    let file = this.files[0];
    if (!file) return;

    let formData = new FormData();
    formData.append("pdf", file);

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "compress.php", true);
    xhr.responseType = "blob";

    let progressWrapper = document.getElementById('progressWrapper');
    let progressBar = document.getElementById('progressBar');
    let result = document.getElementById('result');

    progressWrapper.classList.remove("d-none");
    result.innerHTML = "<div class='text-muted'>Uploading...</div>";

    // ✅ Upload progress
    xhr.upload.onprogress = function(e) {
        if (e.lengthComputable) {
            let percent = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = percent + "%";
            progressBar.innerText = percent + "%";

            if (percent === 100) {
                progressBar.innerText = "Processing...";
            }
        }
    };

    xhr.onload = function() {

        let contentType = xhr.getResponseHeader("Content-Type");

        // ✅ Check if response is actually PDF
        if (xhr.status === 200 && contentType && contentType.includes("application/pdf")) {

            let blob = xhr.response;

            let url = window.URL.createObjectURL(blob);

            let a = document.createElement("a");
            a.href = url;
            a.download = "compressed.pdf";
            document.body.appendChild(a);
            a.click();
            a.remove();

            result.innerHTML = "<div class='alert alert-success'>✅ Compressed successfully</div>";

        } else {
            // ❌ Handle backend error properly
            xhr.response.text().then(text => {
                result.innerHTML = "<div class='alert alert-danger'>❌ " + text + "</div>";
            });
        }
    };

    xhr.onerror = function() {
        result.innerHTML = "<div class='alert alert-danger'>❌ Network error</div>";
    };

    xhr.send(formData);
});
</script>

</body>
</html>