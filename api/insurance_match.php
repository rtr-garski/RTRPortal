<?php
require_once __DIR__ . '/../config/session.php';
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized, Re-login required.']);
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

// Score one field using four methods, return the best
function fieldScore($input, $candidate) {
    $input     = normalizeStr($input);
    $candidate = normalizeStr($candidate);

    if ($input === '' || $candidate === '') return 0;

    // 1. Direct similarity
    similar_text($input, $candidate, $simPct);

    $inputWords     = array_values(array_filter(explode(' ', $input),     fn($w) => strlen($w) > 1));
    $candidateWords = array_values(array_filter(explode(' ', $candidate), fn($w) => strlen($w) > 1));

    $iCount = count($inputWords);
    $cCount = count($candidateWords) ?: 1;

    // 2. Dice coefficient word overlap — penalises when candidate has more words than input
    //    "cardiology" in "stanislaus cardiology network" → 2×1/(1+3) = 50%, not 100%
    $wordPct = 0;
    if ($inputWords) {
        $hits = 0;
        foreach ($inputWords as $w) {
            if (preg_match('/\b' . preg_quote($w, '/') . '\b/', $candidate)) $hits++;
        }
        $wordPct = (2 * $hits / ($iCount + $cCount)) * 100;
    }

    // 3. Substring overlap — "cardio" or "stan" matches any word containing it.
    //    Require 4+ chars to avoid "pa" matching inside "tampa".
    $substringPct = 0;
    if ($inputWords && $candidateWords) {
        $hits = 0;
        foreach ($inputWords as $iw) {
            if (strlen($iw) < 4) continue;
            foreach ($candidateWords as $cw) {
                if (strpos($cw, $iw) !== false) { $hits++; break; }
            }
        }
        $eligibleWords = count(array_filter($inputWords, fn($w) => strlen($w) >= 4));
        if ($eligibleWords > 0) $substringPct = ($hits / $eligibleWords) * 85;
    }

    // 4. Contains check — scaled by word coverage so a 1-word input in a 3-word candidate
    //    scores lower than a full phrase match
    $containsPct = 0;
    if (preg_match('/\b' . preg_quote($input, '/') . '\b/', $candidate)) {
        $coverage     = min(1.0, $iCount / $cCount);
        $containsPct  = 95 * (0.4 + 0.6 * $coverage);
    } elseif ($candidate !== '' && preg_match('/\b' . preg_quote($candidate, '/') . '\b/', $input)) {
        $containsPct = 85;
    }

    return max($simPct, $wordPct, $substringPct, $containsPct);
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

// Strip phone to digits only
function sanitizePhone($s) {
    return preg_replace('/[^0-9]/', '', (string)$s);
}

// Phone score — compare digit-only strings
function phoneScore($input, $candidate) {
    $input     = sanitizePhone($input);
    $candidate = sanitizePhone($candidate);

    if ($input === '' || $candidate === '') return 0;
    if ($input === $candidate) return 100;

    similar_text($input, $candidate, $simPct);

    $containsPct = 0;
    if (strpos($candidate, $input) !== false) $containsPct = 90;
    elseif (strpos($input, $candidate) !== false) $containsPct = 80;

    return max($simPct, $containsPct);
}

// LOC weighted score
function locMatchScore($inputName, $inputCSZ, $inputPhone, $row) {
    $fields = [
        'name'  => [$inputName,  $row['LOC_Name']    ?? '', 0.50, false],
        'csz'   => [$inputCSZ,   $row['Address_CSZ'] ?? '', 0.30, false],
        'phone' => [$inputPhone, $row['Phone']        ?? '', 0.20, true],
    ];

    $totalWeight = 0;
    $score       = 0;
    $breakdown   = [];

    foreach ($fields as $key => [$input, $candidate, $weight, $isPhone]) {
        if (trim($input) === '') {
            $breakdown[$key] = null;
            continue;
        }
        $pct = $isPhone ? phoneScore($input, $candidate) : fieldScore($input, $candidate);
        $breakdown[$key] = round($pct, 1);
        $score       += $pct * $weight;
        $totalWeight += $weight;
    }

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

if ($action === 'search_loc') {
    $inputName  = trim($_POST['name']  ?? '');
    $inputCSZ   = trim($_POST['csz']   ?? '');
    $inputPhone = trim($_POST['phone'] ?? '');

    if ($inputName === '' && $inputCSZ === '' && $inputPhone === '') {
        echo json_encode(['success' => false, 'message' => 'At least one search field is required.']);
        exit;
    }

    $stmt = $pdo->query("SELECT __kp_LOC_ID, LOC_Name, Address_CSZ, Phone FROM LOC WHERE (`X-inactive` != 1 OR `X-inactive` IS NULL)");
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results = [];
    foreach ($candidates as $row) {
        [$score, $bd] = locMatchScore($inputName, $inputCSZ, $inputPhone, $row);

        $results[] = [
            'id'         => $row['__kp_LOC_ID'],
            'name'       => $row['LOC_Name'],
            'csz'        => $row['Address_CSZ'],
            'phone_raw'  => $row['Phone'],
            'phone'      => sanitizePhone($row['Phone']),
            'match_pct'  => $score,
            'name_pct'   => $bd['name'],
            'csz_pct'    => $bd['csz'],
            'phone_pct'  => $bd['phone'],
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
