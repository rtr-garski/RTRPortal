<?php
require_once __DIR__ . '/../config/session.php';
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/backblaze.php';
require_once __DIR__ . '/../config/b2_helper.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// ─── Presign ──────────────────────────────────────────────────────────────────
if ($action === 'presign') {
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
if ($action === 'upload') {
    $f = $_FILES['file'] ?? null;

    if (!$f || $f['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Upload failed or no file selected.']);
        exit;
    }

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

        echo json_encode(['success' => true, 'message' => $f['name'] . ' uploaded successfully.']);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => 'Upload error: ' . $e->getMessage()]);
    }
    exit;
}

// ─── Delete ───────────────────────────────────────────────────────────────────
if ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
        exit;
    }

    try {
        $stmt = $pdo2->prepare("SELECT b2_file_id, b2_file_name FROM b2_files WHERE id = ?");
        $stmt->execute([$id]);
        $file = $stmt->fetch();

        if ($file) {
            $b2 = new BackblazeB2(B2_KEY_ID, B2_APP_KEY, B2_BUCKET_ID, B2_BUCKET_NAME);
            $b2->deleteFile($file['b2_file_id'], $file['b2_file_name']);
            $pdo2->prepare("DELETE FROM b2_files WHERE id = ?")->execute([$id]);
        }

        echo json_encode(['success' => true, 'message' => 'File deleted successfully.']);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => 'Delete error: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);
