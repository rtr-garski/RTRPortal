<?php

// ═══════════════════════════════════════════════════════════════════════════════
//  API Receiver — Production
//  Endpoint: POST /apiportal/receiver.php
//  Auth:     X-API-Key header
// ═══════════════════════════════════════════════════════════════════════════════

// ─── Config ───────────────────────────────────────────────────────────────────

define('API_TOKEN', 'K4AwY7EZCRMkUfRPnc2qFCZusN9uPvBH9cT8HjXcrBfHJ492HH');

define('ALLOWED_SUBTYPES', [
    'IMR',
    'SIBTF',
    'Special Notice of Lawsuit',
    'Trial Depo Subpoena for WCAB',
]);

// Record type alias map.
// Keys are every accepted input value (lowercased, stripped of spaces/punctuation).
// Values are the canonical display names stored/returned.
define('RECORD_TYPE_ALIASES', [
    // Medical
    'medical'                   => 'Medical',
    'medicalrecords'            => 'Medical',
    'medrecords'                => 'Medical',
    'med'                       => 'Medical',

    // Billing
    'billing'                   => 'Billing',
    'billingrecords'            => 'Billing',
    'bill'                      => 'Billing',
    'bills'                     => 'Billing',

    // XRay / MRI / Images / Films
    'xray'                      => 'XRay/MRI Images/Films',
    'xrays'                     => 'XRay/MRI Images/Films',
    'mri'                       => 'XRay/MRI Images/Films',
    'xraymri'                   => 'XRay/MRI Images/Films',
    'xrayimages'                => 'XRay/MRI Images/Films',
    'imaging'                   => 'XRay/MRI Images/Films',
    'images'                    => 'XRay/MRI Images/Films',
    'films'                     => 'XRay/MRI Images/Films',
    'xrayfilms'                 => 'XRay/MRI Images/Films',
    'radiology'                 => 'XRay/MRI Images/Films',

    // Claim File
    'claimfile'                 => 'Claim File',
    'claim'                     => 'Claim File',
    'claims'                    => 'Claim File',
    'claimrecords'              => 'Claim File',

    // Employment and Payroll (combined)
    'employmentandpayroll'      => 'Employment and Payroll',
    'employmentpayroll'         => 'Employment and Payroll',
    'empandpayroll'             => 'Employment and Payroll',
    'emppayroll'                => 'Employment and Payroll',

    // Payroll only
    'payroll'                   => 'Payroll',
    'payrollrecords'            => 'Payroll',

    // Employment only
    'employment'                => 'Employment',
    'employmentrecords'         => 'Employment',
    'emp'                       => 'Employment',

    // WCIC Information for Defendant/Employer
    'wcic'                      => 'WCIC Information for Defendant/Employer',
    'wcicinformation'           => 'WCIC Information for Defendant/Employer',
    'wcicdefendant'             => 'WCIC Information for Defendant/Employer',
    'wcicemployer'              => 'WCIC Information for Defendant/Employer',
    'wcicinfo'                  => 'WCIC Information for Defendant/Employer',
    'defendant'                 => 'WCIC Information for Defendant/Employer',
    'defendantemployer'         => 'WCIC Information for Defendant/Employer',

    // Non-Privileged
    'nonprivileged'             => 'Non-Privileged',
    'non-privileged'            => 'Non-Privileged',
    'nonpriv'                   => 'Non-Privileged',
    'nonprov'                   => 'Non-Privileged',   // common misspelling
    'nonprivledged'             => 'Non-Privileged',   // common misspelling
    'nonprivliged'              => 'Non-Privileged',   // common misspelling

    // Pharmacy / Prescription
    'pharmacy'                  => 'Pharmacy Prescription',
    'prescription'              => 'Pharmacy Prescription',
    'prescriptions'             => 'Pharmacy Prescription',
    'pharmacyprescription'      => 'Pharmacy Prescription',
    'rx'                        => 'Pharmacy Prescription',
    'pharmacyrecords'           => 'Pharmacy Prescription',
]);

define('ALLOWED_PRIORITIES', ['standard', 'rush']);

define('ALLOWED_MIME_TYPES', [
    'application/pdf',
    'image/jpeg',
    'image/png',
    'image/tiff',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
]);

define('MAX_ATTACHMENT_BYTES', 10 * 1024 * 1024); // 10 MB per attachment

// ─── CORS (adjust origins for production) ─────────────────────────────────────

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function sendResponse(bool $success, string $message, $data = null, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode([
        'success'   => $success,
        'message'   => $message,
        'data'      => $data,
        'timestamp' => date('c'),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function getApiKey(): ?string {
    $headers = getallheaders();
    foreach ($headers as $key => $value) {
        if (strtolower($key) === 'x-api-key') {
            return trim($value);
        }
    }
    return null;
}

function clean(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function requireString(array $data, string $field, string $context = ''): string {
    $key = $context ? "$context.$field" : $field;
    if (empty($data[$field]) || !is_string($data[$field])) {
        sendResponse(false, "$key is required", null, 400);
    }
    return clean($data[$field]);
}

// ─── Validators ───────────────────────────────────────────────────────────────

function validateCourtVenue(array $data): array {
    foreach (['name', 'address', 'city', 'state', 'phone'] as $f) {
        if (empty($data[$f])) {
            sendResponse(false, "court_venue.$f is required", null, 400);
        }
    }
    return [
        'name'    => clean($data['name']),
        'address' => clean($data['address']),
        'city'    => clean($data['city']),
        'state'   => clean($data['state']),
        'phone'   => clean($data['phone']),
    ];
}

function validateInsuranceCarrier(array $carrier, int $index): array {
    $ctx = "insurance_carriers[$index]";
    if (empty($carrier['name'])) {
        sendResponse(false, "$ctx.name is required", null, 400);
    }
    if (!empty($carrier['adjuster_email']) && !filter_var($carrier['adjuster_email'], FILTER_VALIDATE_EMAIL)) {
        sendResponse(false, "$ctx.adjuster_email is not a valid email address", null, 400);
    }
    return [
        'name'           => clean($carrier['name']),
        'address'        => isset($carrier['address'])        ? clean($carrier['address'])        : null,
        'city'           => isset($carrier['city'])           ? clean($carrier['city'])           : null,
        'state'          => isset($carrier['state'])          ? clean($carrier['state'])          : null,
        'zip'            => isset($carrier['zip'])            ? clean($carrier['zip'])            : null,
        'phone'          => isset($carrier['phone'])          ? clean($carrier['phone'])          : null,
        'adjuster_name'  => isset($carrier['adjuster_name'])  ? clean($carrier['adjuster_name'])  : null,
        'adjuster_phone' => isset($carrier['adjuster_phone']) ? clean($carrier['adjuster_phone']) : null,
        'adjuster_fax'   => isset($carrier['adjuster_fax'])   ? clean($carrier['adjuster_fax'])   : null,
        'adjuster_email' => isset($carrier['adjuster_email']) ? filter_var($carrier['adjuster_email'], FILTER_SANITIZE_EMAIL) : null,
        'claim_no'       => isset($carrier['claim_no'])       ? clean($carrier['claim_no'])       : null,
    ];
}

function validateOpposingCounsel(array $data, int $index): array {
    $ctx = "opposing_counsel[$index]";
    if (empty($data['name'])) {
        sendResponse(false, "$ctx.name is required", null, 400);
    }
    return [
        'name'    => clean($data['name']),
        'address' => isset($data['address']) ? clean($data['address']) : null,
        'city'    => isset($data['city'])    ? clean($data['city'])    : null,
        'state'   => isset($data['state'])   ? clean($data['state'])   : null,
        'zip'     => isset($data['zip'])     ? clean($data['zip'])     : null,
        'phone'   => isset($data['phone'])   ? clean($data['phone'])   : null,
    ];
}

function validatePatient(array $data): array {
    if (empty($data['name'])) {
        sendResponse(false, "patient.name is required", null, 400);
    }
    if (empty($data['dob'])) {
        sendResponse(false, "patient.dob is required", null, 400);
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['dob'])) {
        sendResponse(false, "patient.dob must be in YYYY-MM-DD format", null, 400);
    }
    return [
        'name'   => clean($data['name']),
        'dob'    => $data['dob'],
        'ssn'    => isset($data['ssn'])    ? clean($data['ssn'])    : null,
        'street' => isset($data['street']) ? clean($data['street']) : null,
        'city'   => isset($data['city'])   ? clean($data['city'])   : null,
        'state'  => isset($data['state'])  ? clean($data['state'])  : null,
        'zip'    => isset($data['zip'])    ? clean($data['zip'])    : null,
    ];
}

function validateRecordsLocation(array $rec, int $index): array {
    $ctx = "records_locations[$index]";

    if (empty($rec['record_type'])) {
        sendResponse(false, "$ctx.record_type is required", null, 400);
    }

    // Normalize: lowercase, strip spaces, hyphens, underscores, slashes
    $rtNormalized = preg_replace('/[\s\-_\/]+/', '', strtolower(trim($rec['record_type'])));
    $aliases      = RECORD_TYPE_ALIASES;

    if (!isset($aliases[$rtNormalized])) {
        $canonical = array_unique(array_values($aliases));
        sort($canonical);
        sendResponse(false, "$ctx.record_type \"$rec[record_type]\" was not recognized. Accepted values: " . implode(', ', $canonical), null, 400);
    }

    $resolvedRecordType = $aliases[$rtNormalized];
    if (empty($rec['date_needed'])) {
        sendResponse(false, "$ctx.date_needed is required", null, 400);
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $rec['date_needed'])) {
        sendResponse(false, "$ctx.date_needed must be in YYYY-MM-DD format", null, 400);
    }
    if (empty($rec['location']) || !is_array($rec['location'])) {
        sendResponse(false, "$ctx.location is required", null, 400);
    }
    foreach (['name', 'address', 'phone'] as $f) {
        if (empty($rec['location'][$f])) {
            sendResponse(false, "$ctx.location.$f is required", null, 400);
        }
    }

    $priority = isset($rec['priority']) ? strtolower(trim($rec['priority'])) : 'standard';
    if (!in_array($priority, ALLOWED_PRIORITIES)) {
        sendResponse(false, "$ctx.priority must be 'standard' or 'rush'", null, 400);
    }

    return [
        'priority'            => $priority,
        'record_type'         => $resolvedRecordType,
        'date_needed'         => $rec['date_needed'],
        'location'            => [
            'name'    => clean($rec['location']['name']),
            'address' => clean($rec['location']['address']),
            'phone'   => clean($rec['location']['phone']),
        ],
        'special_instruction' => !empty($rec['special_instruction'])
                                    ? clean($rec['special_instruction'])
                                    : null,
    ];
}

function validateAttachment(array $att, int $index): array {
    $ctx = "attachments[$index]";

    if (empty($att['filename'])) {
        sendResponse(false, "$ctx.filename is required", null, 400);
    }
    if (empty($att['mime_type'])) {
        sendResponse(false, "$ctx.mime_type is required", null, 400);
    }
    if (!in_array($att['mime_type'], ALLOWED_MIME_TYPES)) {
        sendResponse(false, "$ctx.mime_type '$att[mime_type]' is not allowed", null, 400);
    }
    if (empty($att['data'])) {
        sendResponse(false, "$ctx.data (base64) is required", null, 400);
    }

    $decoded = base64_decode($att['data'], true);
    if ($decoded === false) {
        sendResponse(false, "$ctx.data is not valid base64", null, 400);
    }
    if (strlen($decoded) > MAX_ATTACHMENT_BYTES) {
        sendResponse(false, "$ctx exceeds maximum allowed size of 10 MB", null, 400);
    }

    return [
        'filename'  => clean($att['filename']),
        'mime_type' => $att['mime_type'],
        'size_bytes' => strlen($decoded),
        // Re-encode to ensure clean base64
        'data'      => base64_encode($decoded),
    ];
}

// ─── Main Handler ─────────────────────────────────────────────────────────────

function handleRequest(): void {

    // POST only
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, "Only POST method is allowed", null, 405);
    }

    // Authentication
    $apiKey = getApiKey();
    if ($apiKey === null || $apiKey !== API_TOKEN) {
        sendResponse(false, "Unauthorized — invalid or missing API key", null, 401);
    }

    // Parse JSON body
    $raw   = file_get_contents('php://input');
    $input = json_decode($raw, true);

    if ($input === null) {
        sendResponse(false, "Invalid or missing JSON body", null, 400);
    }

    // ── Subtype ──────────────────────────────────────────────────────────────
    if (empty($input['subtype'])) {
        sendResponse(false, "subtype is required", null, 400);
    }
    if (!in_array($input['subtype'], ALLOWED_SUBTYPES)) {
        sendResponse(false, "subtype must be one of: " . implode(', ', ALLOWED_SUBTYPES), null, 400);
    }

    // ── Case No / ADJ ────────────────────────────────────────────────────────
    $caseNo = requireString($input, 'case_no');

    // ── DOI Start ────────────────────────────────────────────────────────────
    if (empty($input['doi_start'])) {
        sendResponse(false, "doi_start is required", null, 400);
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['doi_start'])) {
        sendResponse(false, "doi_start must be in YYYY-MM-DD format", null, 400);
    }

    // ── DOI End (optional) ───────────────────────────────────────────────────
    $doiEnd = null;
    if (!empty($input['doi_end'])) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['doi_end'])) {
            sendResponse(false, "doi_end must be in YYYY-MM-DD format", null, 400);
        }
        $doiEnd = $input['doi_end'];
    }

    // ── Court / Venue ────────────────────────────────────────────────────────
    if (empty($input['court_venue']) || !is_array($input['court_venue'])) {
        sendResponse(false, "court_venue is required", null, 400);
    }
    $courtVenue = validateCourtVenue($input['court_venue']);

    // ── Letter of Rep Date ───────────────────────────────────────────────────
    if (empty($input['letter_of_rep_date'])) {
        sendResponse(false, "letter_of_rep_date is required", null, 400);
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['letter_of_rep_date'])) {
        sendResponse(false, "letter_of_rep_date must be in YYYY-MM-DD format", null, 400);
    }

    // ── Insurance Carriers (optional, multiple) ──────────────────────────────
    $insuranceCarriers = [];
    if (!empty($input['insurance_carriers'])) {
        if (!is_array($input['insurance_carriers'])) {
            sendResponse(false, "insurance_carriers must be an array", null, 400);
        }
        foreach ($input['insurance_carriers'] as $i => $carrier) {
            $insuranceCarriers[] = validateInsuranceCarrier($carrier, $i);
        }
    }

    // ── Opposing Counsel (optional, multiple) ────────────────────────────────
    $opposingCounsel = [];
    if (!empty($input['opposing_counsel'])) {
        if (!is_array($input['opposing_counsel'])) {
            sendResponse(false, "opposing_counsel must be an array", null, 400);
        }
        foreach ($input['opposing_counsel'] as $i => $counsel) {
            $opposingCounsel[] = validateOpposingCounsel($counsel, $i);
        }
    }

    // ── Employer Name ────────────────────────────────────────────────────────
    $employerName = requireString($input, 'employer_name');

    // ── Patient ──────────────────────────────────────────────────────────────
    if (empty($input['patient']) || !is_array($input['patient'])) {
        sendResponse(false, "patient is required", null, 400);
    }
    $patient = validatePatient($input['patient']);

    // ── Records Locations (multiple) ─────────────────────────────────────────
    if (empty($input['records_locations']) || !is_array($input['records_locations'])) {
        sendResponse(false, "records_locations is required and must be a non-empty array", null, 400);
    }
    if (count($input['records_locations']) === 0) {
        sendResponse(false, "records_locations must contain at least one entry", null, 400);
    }
    $recordsLocations = [];
    foreach ($input['records_locations'] as $i => $rec) {
        $recordsLocations[] = validateRecordsLocation($rec, $i);
    }

    // ── Attachments (optional, multiple) ─────────────────────────────────────
    $attachments = [];
    if (!empty($input['attachments'])) {
        if (!is_array($input['attachments'])) {
            sendResponse(false, "attachments must be an array", null, 400);
        }
        foreach ($input['attachments'] as $i => $att) {
            $attachments[] = validateAttachment($att, $i);
        }
    }

    // ── Build response payload ────────────────────────────────────────────────
    $payload = [
        'submission_id'      => 'SUB-' . strtoupper(bin2hex(random_bytes(5))),
        'received_at'        => date('c'),
        'status'             => 'received',
        'subtype'            => $input['subtype'],
        'case_no'            => $caseNo,
        'doi_start'          => $input['doi_start'],
        'doi_end'            => $doiEnd,
        'court_venue'        => $courtVenue,
        'letter_of_rep_date' => $input['letter_of_rep_date'],
        'insurance_carriers' => $insuranceCarriers,
        'opposing_counsel'   => $opposingCounsel,
        'employer_name'      => $employerName,
        'patient'            => $patient,
        'records_locations'  => $recordsLocations,
        'attachments_count'  => count($attachments),
        'attachments'        => $attachments,
    ];

    sendResponse(true, "Submission received successfully", $payload, 201);
}

handleRequest();
