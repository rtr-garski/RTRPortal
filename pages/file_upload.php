<?php
require_once __DIR__ . '/../config/session.php';
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/access.php';
require_page_access('file_upload', $pdo2);
require_once __DIR__ . '/../config/backblaze.php';
require_once __DIR__ . '/../config/b2_helper.php';

try {
    $files = $pdo2->query("SELECT * FROM b2_files ORDER BY uploaded_at DESC")->fetchAll();
} catch (Throwable $e) {
    $files = [];
}

$totalFiles  = count($files);
$totalBytes  = array_sum(array_column($files, 'file_size'));
$activeLinks = count(array_filter($files, fn($f) => $f['key_expires_at'] && strtotime($f['key_expires_at']) > time()));

$preloadedUrls = [];
$activeFiles   = array_filter($files, fn($f) => $f['key_expires_at'] && strtotime($f['key_expires_at']) > time());
if (!empty($activeFiles)) {
    try {
        $b2 = new BackblazeB2(B2_KEY_ID, B2_APP_KEY, B2_BUCKET_ID, B2_BUCKET_NAME);
        foreach ($activeFiles as $f) {
            $preloadedUrls[$f['id']] = $b2->generatePresignedUrl($f['b2_file_name']);
        }
    } catch (Throwable $e) {}
}

function formatBytes(int $bytes): string {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576)    return round($bytes / 1048576, 2)    . ' MB';
    if ($bytes >= 1024)       return round($bytes / 1024, 2)       . ' KB';
    return $bytes . ' B';
}
?>

<div class="container-fluid">

    <!-- breadcrumb -->
    <div class="page-title-head d-flex align-items-center">
        <div class="flex-grow-1">
            <h4 class="page-main-title m-0">File Storage</h4>
        </div>
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);">RTR</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0);">Storage</a></li>
                <li class="breadcrumb-item active">File Storage</li>
            </ol>
        </div>
    </div>

    <!-- flash -->
    <div id="fileFlash" class="d-none"></div>

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
            <form id="uploadForm" enctype="multipart/form-data" class="d-flex align-items-center gap-2 flex-wrap">
                <input type="hidden" name="action" value="upload">
                <input type="file" name="file" id="uploadFile" class="form-control" style="max-width:400px" required>
                <button type="submit" id="uploadBtn" class="btn btn-primary text-nowrap">
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
            <div class="card-action">
                <a href="#!" class="card-action-item" data-action="card-toggle"><i class="ti ti-chevron-up"></i></a>
                <a href="#!" class="card-action-item" data-action="card-refresh"><i class="ti ti-refresh"></i></a>
            </div>
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
                    <tbody id="fileTableBody">
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
                            $exp        = $f['key_expires_at'] ? strtotime($f['key_expires_at']) : null;
                            $linkActive = $exp && $exp > time();
                            $preUrl     = $preloadedUrls[$f['id']] ?? null;
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
                                    <button type="button"
                                            class="btn btn-xs btn-soft-danger delete-btn"
                                            data-id="<?= (int) $f['id'] ?>"
                                            title="Delete">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <!-- inline link row -->
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
