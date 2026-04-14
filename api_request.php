<?php

header('Content-Type: application/json');

require_once __DIR__ . '/config/db.php';

// test valid tokens
$valid_tokens = [
    'B8k7DqkC4z7LxeGkqGTHNqc7g',
    '4aTmu38uUteWUZ2fJsLLxNNQR',
];

// get header and validate token, for test token as Bearer
$headers = getallheaders();
$auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!preg_match('/^Bearer\s+(\S+)$/i', $auth_header, $matches)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Missing or invalid Authorization header. Use: Bearer <token>']);
    exit;
}

$provided_token = $matches[1];

if (!in_array($provided_token, $valid_tokens, true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid token.']);
    exit;
}

// --- Route: GET /api_request.php?action=search_company&q=keyword ---
$action = $_GET['action'] ?? '';

if ($action === 'search_company') {
    $query = trim($_GET['q'] ?? '');

    if ($query === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing search parameter: q']);
        exit;
    }

    if (strlen($query) < 3) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Search query must be at least 3 characters.']);
        exit;
    }

    $search = '%' . $query . '%';

    $stmt = $pdo2->prepare(
        "SELECT id, company_name, address, city, state, zip
         FROM test_companies
         WHERE company_name LIKE :q
         ORDER BY company_name ASC"
    );
    $stmt->execute([':q' => $search]);
    $results = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'query'   => $query,
        'count'   => count($results),
        'data'    => $results,
    ]);
    exit;
}

// --- Unknown action ---
http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Unknown action. Available: search_company']);
exit;
