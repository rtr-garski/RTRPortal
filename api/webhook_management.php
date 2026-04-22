<?php
require_once __DIR__ . '/../config/session.php';
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../webhook_sender.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $url    = trim($_POST['url']    ?? '');
    $event  = trim($_POST['event']  ?? '');
    $secret = trim($_POST['secret'] ?? '');

    if ($url === '' || $event === '') {
        echo json_encode(['success' => false, 'message' => 'URL and Event are required.']);
        exit;
    }
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid URL.']);
        exit;
    }
    $pdo2->prepare("
        INSERT INTO webhook_endpoints (event, url, secret, active, created_at)
        VALUES (:event, :url, :secret, 1, NOW())
    ")->execute([':event' => $event, ':url' => $url, ':secret' => $secret]);
    echo json_encode(['success' => true, 'message' => 'Webhook endpoint added successfully.']);
    exit;
}

if ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
        exit;
    }
    $pdo2->prepare("DELETE FROM webhook_endpoints WHERE id = ?")->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'Endpoint deleted.']);
    exit;
}

if ($action === 'toggle') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
        exit;
    }
    $pdo2->prepare("UPDATE webhook_endpoints SET active = IF(active=1,0,1) WHERE id = ?")->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'Endpoint status updated.']);
    exit;
}

if ($action === 'test') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
        exit;
    }
    $stmt = $pdo2->prepare("SELECT * FROM webhook_endpoints WHERE id = ?");
    $stmt->execute([$id]);
    $ep = $stmt->fetch();
    if (!$ep) {
        echo json_encode(['success' => false, 'message' => 'Endpoint not found.']);
        exit;
    }
    $result = webhookDeliver($ep['url'], [
        'event'     => $ep['event'],
        'data'      => ['test' => true, 'message' => 'This is a test webhook delivery.'],
        'timestamp' => date('c'),
    ], $ep['secret']);
    webhookLog($pdo2, $ep['event'], $ep['url'], $result);
    echo json_encode([
        'success' => $result['success'],
        'message' => $result['success']
            ? "Test delivered — HTTP {$result['http_code']}."
            : "Test failed — HTTP {$result['http_code']}. " . ($result['error'] ?? ''),
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);
