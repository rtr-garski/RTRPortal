<?php

header('Content-Type: application/json');

require_once __DIR__ . '/config/db.php';

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
