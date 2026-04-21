<?php

require_once 'config/db.php';
require_once 'config/backblaze.php';
require_once 'b2_helper.php';

$flash = null;

// ─── AJAX: generate presigned URL ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'presign') {
    header('Content-Type: application/json');
    $id = (int) ($_POST['id'] ?? 0);
    try {
        $stmt = $pdo2->prepare("SELECT * FROM b2_files WHERE id = ?");
        $stmt->execute([$id]);
        $file = $stmt->fetch();

        if (!$file) throw new RuntimeException('File not found.');

        $b2  = new BackblazeB2(B2_KEY_ID, B2_APP_KEY, B2_BUCKET_ID, B2_BUCKET_NAME);
        $url = $b2->generatePresignedUrl($file['b2_file_name']);

        $pdo2->prepare("UPDATE b2_files SET key_expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?")
             ->execute([$id]);

        echo json_encode([
            'success'    => true,
            'url'        => $url,
            'expires_at' => date('M j, Y g:i A', strtotime('+1 hour')),
        ]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ─── Upload ───────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload') {
    $f = $_FILES['file'] ?? null;

    if (!$f || $f['error'] !== UPLOAD_ERR_OK) {
        $flash = ['type' => 'danger', 'msg' => 'Upload failed or no file selected.'];
    } else {
        try {
            $mime = $f['type'] ?: 'application/octet-stream';
            $b2   = new BackblazeB2(B2_KEY_ID, B2_APP_KEY, B2_BUCKET_ID, B2_BUCKET_NAME);
            $res  = $b2->upload($f['name'], $f['tmp_name'], $mime);

            $pdo2->prepare("
                INSERT INTO b2_files (original_name, b2_file_name, b2_file_id, file_size, mime_type)
                VALUES (:orig, :b2name, :b2id, :size, :mime)
            ")->execute([
                ':orig'   => $f['name'],
                ':b2name' => $res['b2_file_name'],
                ':b2id'   => $res['b2_file_id'],
                ':size'   => $res['file_size'],
                ':mime'   => $mime,
            ]);

            $flash = ['type' => 'success', 'msg' => htmlspecialchars($f['name']) . ' uploaded successfully.'];
        } catch (Throwable $e) {
            $flash = ['type' => 'danger', 'msg' => 'Upload error: ' . $e->getMessage()];
        }
    }

    header('Location: file_upload.php?flash=' . urlencode($flash['type'] . '|' . $flash['msg']));
    exit;
}

// ─── Delete ───────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        try {
            $stmt = $pdo2->prepare("SELECT b2_file_id, b2_file_name FROM b2_files WHERE id = ?");
            $stmt->execute([$id]);
            $file = $stmt->fetch();

            if ($file) {
                $b2 = new BackblazeB2(B2_KEY_ID, B2_APP_KEY, B2_BUCKET_ID, B2_BUCKET_NAME);
                $b2->deleteFile($file['b2_file_id'], $file['b2_file_name']);
                $pdo2->prepare("DELETE FROM b2_files WHERE id = ?")->execute([$id]);
            }

            $flash = ['type' => 'success', 'msg' => 'File deleted from B2 and records removed.'];
        } catch (Throwable $e) {
            $flash = ['type' => 'danger', 'msg' => 'Delete error: ' . $e->getMessage()];
        }
    }
    header('Location: file_upload.php?flash=' . urlencode($flash['type'] . '|' . $flash['msg']));
    exit;
}

// ─── Restore flash ────────────────────────────────────────────────────────────
if (isset($_GET['flash'])) {
    [$ftype, $fmsg] = explode('|', urldecode($_GET['flash']), 2);
    $flash = ['type' => $ftype, 'msg' => $fmsg];
}

// ─── Fetch files ──────────────────────────────────────────────────────────────
try {
    $files = $pdo2->query("SELECT * FROM b2_files ORDER BY uploaded_at DESC")->fetchAll();
} catch (Throwable $e) {
    $files = [];
    $flash = ['type' => 'warning', 'msg' => 'Could not load files.'];
}

$totalFiles  = count($files);
$totalBytes  = array_sum(array_column($files, 'file_size'));
$activeLinks = count(array_filter($files, fn($f) => $f['key_expires_at'] && strtotime($f['key_expires_at']) > time()));

// ─── Pre-generate URLs for active links ───────────────────────────────────────
$preloadedUrls = [];
$activeFiles   = array_filter($files, fn($f) => $f['key_expires_at'] && strtotime($f['key_expires_at']) > time());
if (!empty($activeFiles)) {
    try {
        $b2 = new BackblazeB2(B2_KEY_ID, B2_APP_KEY, B2_BUCKET_ID, B2_BUCKET_NAME);
        foreach ($activeFiles as $f) {
            $preloadedUrls[$f['id']] = $b2->generatePresignedUrl($f['b2_file_name']);
        }
    } catch (Throwable $e) {
        // silently skip — links will still work via Get Link button
    }
}

function formatBytes(int $bytes): string {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576)    return round($bytes / 1048576, 2)    . ' MB';
    if ($bytes >= 1024)       return round($bytes / 1024, 2)       . ' KB';
    return $bytes . ' B';
}

$title = 'File Storage';
?>
<!doctype html>
<html lang="en">
<head>
    <?php include('partials/title-meta.php'); ?>
    <?php include('partials/head-css.php'); ?>
</head>
<body>
<div class="wrapper">
    <?php include('partials/topbar.php'); ?>
    <?php include('partials/sidenav.php'); ?>

    <div class="content-page">
        <div class="container-fluid">

            <!-- breadcrumb -->
            <?php $subtitle = 'Storage'; ?>
            <div class="page-title-head d-flex align-items-center">
                <div class="flex-grow-1">
                    <h4 class="page-main-title m-0"><?= $title ?></h4>
                </div>
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">RTR</a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);"><?= $subtitle ?></a></li>
                        <li class="breadcrumb-item active"><?= $title ?></li>
                    </ol>
                </div>
            </div>

            <!-- flash -->
            <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($flash['msg']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- stats -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card mb-0">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <span class="avatar-md rounded bg-primary-subtle d-flex align-items-center justify-content-center">
                                <i class="ti ti-files fs-xl text-primary"></i>
                            </span>
                            <div>
                                <p class="text-muted mb-0 fs-xs text-uppercase fw-semibold">Total Files</p>
                                <h4 class="mb-0"><?= $totalFiles ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-0">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <span class="avatar-md rounded bg-info-subtle d-flex align-items-center justify-content-center">
                                <i class="ti ti-database fs-xl text-info"></i>
                            </span>
                            <div>
                                <p class="text-muted mb-0 fs-xs text-uppercase fw-semibold">Total Size</p>
                                <h4 class="mb-0"><?= formatBytes($totalBytes) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-0">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <span class="avatar-md rounded bg-success-subtle d-flex align-items-center justify-content-center">
                                <i class="ti ti-link fs-xl text-success"></i>
                            </span>
                            <div>
                                <p class="text-muted mb-0 fs-xs text-uppercase fw-semibold">Active Links</p>
                                <h4 class="mb-0"><?= $activeLinks ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- upload card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti ti-cloud-upload me-1"></i> Upload File to Backblaze B2</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="file_upload.php" enctype="multipart/form-data" class="d-flex align-items-center gap-2 flex-wrap">
                        <input type="hidden" name="action" value="upload">
                        <input type="file" name="file" class="form-control" style="max-width:400px" required>
                        <button type="submit" class="btn btn-primary text-nowrap">
                            <i class="ti ti-cloud-upload me-1"></i> Upload
                        </button>
                    </form>
                    <p class="text-muted fs-xs mt-2 mb-0">Files are stored privately on Backblaze B2. Use "Get Link" to generate a 1-hour presigned download URL.</p>
                </div>
            </div>

            <!-- file table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti ti-cloud me-1"></i> Stored Files</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead class="bg-light bg-opacity-25 thead-sm border-top border-light">
                                <tr class="text-uppercase fs-xxs align-middle">
                                    <th class="ps-3">File Name</th>
                                    <th>Size</th>
                                    <th>Type</th>
                                    <th>Uploaded</th>
                                    <th>Link Expires</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($files)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="ti ti-cloud-off fs-xxl d-block mb-2"></i>
                                        No files uploaded yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($files as $f): ?>
                                <?php
                                    $exp       = $f['key_expires_at'] ? strtotime($f['key_expires_at']) : null;
                                    $linkActive = $exp && $exp > time();
                                ?>
                                <tr>
                                    <td class="ps-3 fw-semibold"><?= htmlspecialchars($f['original_name']) ?></td>
                                    <td class="fs-xs text-muted"><?= formatBytes((int) $f['file_size']) ?></td>
                                    <td class="fs-xs text-muted"><?= htmlspecialchars($f['mime_type']) ?></td>
                                    <td class="fs-xs text-muted"><?= date('M j, Y g:i A', strtotime($f['uploaded_at'])) ?></td>
                                    <td class="fs-xs">
                                        <?php if ($linkActive): ?>
                                            <span class="badge badge-soft-success">Exp. <?= date('g:i A', $exp) ?></span>
                                        <?php elseif ($exp): ?>
                                            <span class="badge badge-soft-danger">Expired</span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="d-flex align-items-center justify-content-end gap-1">
                                            <button type="button"
                                                    class="btn btn-xs btn-soft-primary get-link-btn"
                                                    data-id="<?= (int) $f['id'] ?>"
                                                    title="Generate 1-hour presigned link">
                                                <i class="ti ti-link me-1"></i> Get Link
                                            </button>
                                            <form method="POST" class="d-inline"
                                                  onsubmit="return confirm('Delete this file from B2 permanently?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                                                <button type="submit" class="btn btn-xs btn-soft-danger" title="Delete">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <!-- inline link row -->
                                <?php $preUrl = $preloadedUrls[$f['id']] ?? null; ?>
                                <tr class="link-row-<?= (int) $f['id'] ?> <?= $preUrl ? '' : 'd-none' ?> bg-light">
                                    <td colspan="6" class="px-3 py-2">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="fs-xxs text-muted text-uppercase fw-semibold">Presigned Link</span>
                                            <div class="input-group input-group-sm flex-grow-1">
                                                <input type="text" class="form-control form-control-sm font-monospace row-link-input-<?= (int) $f['id'] ?>"
                                                       value="<?= htmlspecialchars($preUrl ?? '') ?>" readonly>
                                                <button type="button" class="btn btn-sm btn-primary row-copy-btn-<?= (int) $f['id'] ?>" title="Copy">
                                                    <i class="ti ti-copy me-1"></i> Copy
                                                </button>
                                                <a href="<?= htmlspecialchars($preUrl ?? '#') ?>" target="_blank"
                                                   class="btn btn-sm btn-soft-secondary row-open-btn-<?= (int) $f['id'] ?>">
                                                    <i class="ti ti-external-link me-1"></i> Open
                                                </a>
                                            </div>
                                            <span class="row-expiry-<?= (int) $f['id'] ?> fs-xxs text-danger">
                                                <?= $preUrl && $exp ? 'Expires ' . date('M j, Y g:i A', $exp) : '' ?>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
        <?php include('partials/footer.php'); ?>
    </div>
</div>

<!-- presigned URL modal -->
<div class="modal fade" id="presignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-link me-1"></i> Presigned Download Link</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="presignLoading" class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                    Generating secure link…
                </div>
                <div id="presignResult" class="d-none">
                    <p class="text-muted fs-xs mb-2">
                        This link expires in <strong>1 hour</strong>.
                        <span id="presignExpiry" class="fw-semibold text-danger ms-1"></span>
                    </p>
                    <div class="input-group">
                        <input type="text" id="presignUrl" class="form-control form-control-sm font-monospace" readonly>
                        <button class="btn btn-sm btn-primary" id="copyPresignUrl">
                            <i class="ti ti-copy me-1"></i> Copy
                        </button>
                        <a id="openPresignUrl" href="#" target="_blank" class="btn btn-sm btn-soft-secondary">
                            <i class="ti ti-external-link me-1"></i> Open
                        </a>
                    </div>
                    <div class="alert alert-warning mt-3 mb-0 py-2 fs-xs">
                        <i class="ti ti-alert-triangle me-1"></i>
                        Do not share this link publicly. It grants direct file access until it expires.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include('partials/customizer.php'); ?>
<?php include('partials/footer-scripts.php'); ?>

<script>
var currentFileId = null;

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

        fetch('file_upload.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=presign&id=' + encodeURIComponent(currentFileId)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            loading.classList.add('d-none');
            result.classList.remove('d-none');
            if (data.success) {
                // populate modal
                document.getElementById('presignUrl').value = data.url;
                document.getElementById('openPresignUrl').href = data.url;
                document.getElementById('presignExpiry').textContent = '(expires ' + data.expires_at + ')';

                // also populate inline row
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

document.getElementById('copyPresignUrl').addEventListener('click', function () {
    var input = document.getElementById('presignUrl');
    navigator.clipboard.writeText(input.value).then(function () {
        var btn = document.getElementById('copyPresignUrl');
        btn.innerHTML = '<i class="ti ti-check me-1"></i> Copied!';
        setTimeout(function () { btn.innerHTML = '<i class="ti ti-copy me-1"></i> Copy'; }, 2000);
    });
});

document.addEventListener('click', function (e) {
    var btn = e.target.closest('[class*="row-copy-btn-"]');
    if (!btn) return;
    var id    = btn.className.match(/row-copy-btn-(\d+)/)[1];
    var input = document.querySelector('.row-link-input-' + id);
    navigator.clipboard.writeText(input.value).then(function () {
        btn.innerHTML = '<i class="ti ti-check me-1"></i> Copied!';
        setTimeout(function () { btn.innerHTML = '<i class="ti ti-copy me-1"></i> Copy'; }, 2000);
    });
});
</script>

</body>
</html>
