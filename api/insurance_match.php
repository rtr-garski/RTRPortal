<?php
require_once __DIR__ . '/../config/session.php';
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'search') {
    $inputName    = trim($_POST['name']    ?? '');
    $inputAddress = trim($_POST['address'] ?? '');
    $inputCSZ     = trim($_POST['csz']     ?? '');

    if ($inputName === '' && $inputAddress === '' && $inputCSZ === '') {
        echo json_encode(['success' => false, 'message' => 'At least one search field is required.']);
        exit;
    }

    $stmt = $pdo->query("SELECT __kp_INS_ID, INS_Name, Address_Street, Address_CSZ FROM INS");
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results = [];
    foreach ($candidates as $row) {
        similar_text($inputName,    (string)($row['INS_Name']       ?? ''), $namePct);
        similar_text($inputAddress, (string)($row['Address_Street'] ?? ''), $addrPct);
        similar_text($inputCSZ,     (string)($row['Address_CSZ']    ?? ''), $cszPct);

        $score = ($namePct * 0.50) + ($addrPct * 0.30) + ($cszPct * 0.20);

        $results[] = [
            'id'         => $row['__kp_INS_ID'],
            'name'       => $row['INS_Name'],
            'address'    => $row['Address_Street'],
            'csz'        => $row['Address_CSZ'],
            'match_pct'  => round($score, 1),
            'name_pct'   => round($namePct, 1),
            'addr_pct'   => round($addrPct, 1),
            'csz_pct'    => round($cszPct, 1),
        ];
    }

    usort($results, fn($a, $b) => $b['match_pct'] <=> $a['match_pct']);

    echo json_encode([
        'success' => true,
        'results' => array_slice($results, 0, 10),
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
