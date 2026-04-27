<?php
require_once __DIR__ . '/../config/session.php';
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

// Normalize: lowercase, strip punctuation, collapse spaces
function normalizeStr($s) {
    $s = strtolower(trim((string)$s));
    $s = preg_replace('/[^\w\s]/', ' ', $s);
    return trim(preg_replace('/\s+/', ' ', $s));
}

// Score one field using three methods, return the best
function fieldScore($input, $candidate) {
    $input     = normalizeStr($input);
    $candidate = normalizeStr($candidate);

    if ($input === '' || $candidate === '') return 0;

    // 1. Direct similarity
    similar_text($input, $candidate, $simPct);

    // 2. Word overlap — whole-word match only ("pa" must not match inside "tampa")
    $words   = array_filter(explode(' ', $input), fn($w) => strlen($w) > 1);
    $wordPct = 0;
    if ($words) {
        $hits = 0;
        foreach ($words as $w) {
            if (preg_match('/\b' . preg_quote($w, '/') . '\b/', $candidate)) $hits++;
        }
        $wordPct = ($hits / count($words)) * 100;
    }

    // 3. Whole-word contains check
    $containsPct = 0;
    if (preg_match('/\b' . preg_quote($input, '/') . '\b/', $candidate)) $containsPct = 95;
    elseif ($candidate !== '' && preg_match('/\b' . preg_quote($candidate, '/') . '\b/', $input)) $containsPct = 85;

    return max($simPct, $wordPct, $containsPct);
}

// Weighted score — skip fields the user left blank (redistribute weight)
function matchScore($inputName, $inputAddress, $inputCSZ, $row) {
    $fields = [
        'name' => [$inputName,    $row['INS_Name']       ?? '', 0.50],
        'addr' => [$inputAddress, $row['Address_Street'] ?? '', 0.30],
        'csz'  => [$inputCSZ,     $row['Address_CSZ']    ?? '', 0.20],
    ];

    $totalWeight = 0;
    $score       = 0;
    $breakdown   = [];

    foreach ($fields as $key => [$input, $candidate, $weight]) {
        if (trim($input) === '') {
            $breakdown[$key] = null;
            continue;
        }
        $pct = fieldScore($input, $candidate);
        $breakdown[$key] = round($pct, 1);
        $score       += $pct * $weight;
        $totalWeight += $weight;
    }

    // Normalize to filled fields only
    $final = $totalWeight > 0 ? $score / $totalWeight : 0;

    return [round($final, 1), $breakdown];
}

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
        [$score, $bd] = matchScore($inputName, $inputAddress, $inputCSZ, $row);

        $results[] = [
            'id'        => $row['__kp_INS_ID'],
            'name'      => $row['INS_Name'],
            'address'   => $row['Address_Street'],
            'csz'       => $row['Address_CSZ'],
            'match_pct' => $score,
            'name_pct'  => $bd['name'],
            'addr_pct'  => $bd['addr'],
            'csz_pct'   => $bd['csz'],
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
