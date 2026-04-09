<?php

// ═══════════════════════════════════════════════════════════════════════════════
//  Shared validation & build logic
//  Included by both receiver.php (HTTP endpoint) and index.php (portal tester)
// ═══════════════════════════════════════════════════════════════════════════════

// ─── Constants ────────────────────────────────────────────────────────────────

if (!defined('API_TOKEN')) {
    define('API_TOKEN', 'K4AwY7EZCRMkUfRPnc2qFCZusN9uPvBH9cT8HjXcrBfHJ492HH');
}

define('ALLOWED_SUBTYPES', [
    'IMR',
    'SIBTF',
    'Special Notice of Lawsuit',
    'Trial Depo Subpoena for WCAB',
]);

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
    // WCIC
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
    'nonprov'                   => 'Non-Privileged',
    'nonprivledged'             => 'Non-Privileged',
    'nonprivliged'              => 'Non-Privileged',
    // Pharmacy / Prescription
    'pharmacy'                  => 'Pharmacy Prescription',
    'prescription'              => 'Pharmacy Prescription',
    'prescriptions'             => 'Pharmacy Prescription',
    'pharmacyprescription'      => 'Pharmacy Prescription',
    'rx'                        => 'Pharmacy Prescription',
    'pharmacyrecords'           => 'Pharmacy Prescription',
]);

define('ALLOWED_PRIORITIES',  ['standard', 'rush']);

define('ALLOWED_MIME_TYPES', [
    'application/pdf',
    'image/jpeg',
    'image/png',
    'image/tiff',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
]);

define('MAX_ATTACHMENT_BYTES', 10 * 1024 * 1024); // 10 MB

// ─── Exception ────────────────────────────────────────────────────────────────

class ApiValidationException extends RuntimeException {}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function clean(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function requireField(array $data, string $field, string $prefix = ''): string {
    $label = $prefix ? "$prefix.$field" : $field;
    if (empty($data[$field]) || !is_string($data[$field])) {
        throw new ApiValidationException("$label is required");
    }
    return clean($data[$field]);
}

function requireDate(array $data, string $field, string $prefix = ''): string {
    $label = $prefix ? "$prefix.$field" : $field;
    if (empty($data[$field])) {
        throw new ApiValidationException("$label is required");
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data[$field])) {
        throw new ApiValidationException("$label must be in YYYY-MM-DD format");
    }
    return $data[$field];
}

// ─── Validators ───────────────────────────────────────────────────────────────

function validateCourtVenue(array $data): array {
    foreach (['name', 'address', 'city', 'state', 'phone'] as $f) {
        if (empty($data[$f])) {
            throw new ApiValidationException("court_venue.$f is required");
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

function validateInsuranceCarrier(array $c, int $i): array {
    $ctx = "insurance_carriers[$i]";
    if (empty($c['name'])) {
        throw new ApiValidationException("$ctx.name is required");
    }
    if (!empty($c['adjuster_email']) && !filter_var($c['adjuster_email'], FILTER_VALIDATE_EMAIL)) {
        throw new ApiValidationException("$ctx.adjuster_email is not a valid email address");
    }
    return [
        'name'           => clean($c['name']),
        'address'        => isset($c['address'])        ? clean($c['address'])        : null,
        'city'           => isset($c['city'])           ? clean($c['city'])           : null,
        'state'          => isset($c['state'])          ? clean($c['state'])          : null,
        'zip'            => isset($c['zip'])            ? clean($c['zip'])            : null,
        'phone'          => isset($c['phone'])          ? clean($c['phone'])          : null,
        'adjuster_name'  => isset($c['adjuster_name'])  ? clean($c['adjuster_name'])  : null,
        'adjuster_phone' => isset($c['adjuster_phone']) ? clean($c['adjuster_phone']) : null,
        'adjuster_fax'   => isset($c['adjuster_fax'])   ? clean($c['adjuster_fax'])   : null,
        'adjuster_email' => !empty($c['adjuster_email']) ? filter_var($c['adjuster_email'], FILTER_SANITIZE_EMAIL) : null,
        'claim_no'       => isset($c['claim_no'])       ? clean($c['claim_no'])       : null,
    ];
}

function validateOpposingCounsel(array $c, int $i): array {
    $ctx = "opposing_counsel[$i]";
    if (empty($c['name'])) {
        throw new ApiValidationException("$ctx.name is required");
    }
    return [
        'name'    => clean($c['name']),
        'address' => isset($c['address']) ? clean($c['address']) : null,
        'city'    => isset($c['city'])    ? clean($c['city'])    : null,
        'state'   => isset($c['state'])   ? clean($c['state'])   : null,
        'zip'     => isset($c['zip'])     ? clean($c['zip'])     : null,
        'phone'   => isset($c['phone'])   ? clean($c['phone'])   : null,
    ];
}

function validatePatient(array $data): array {
    if (empty($data['name'])) {
        throw new ApiValidationException("patient.name is required");
    }
    requireDate($data, 'dob', 'patient');
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

function validateRecordsLocation(array $rec, int $i): array {
    $ctx = "records_locations[$i]";

    if (empty($rec['record_type'])) {
        throw new ApiValidationException("$ctx.record_type is required");
    }

    $rtNorm  = preg_replace('/[\s\-_\/]+/', '', strtolower(trim($rec['record_type'])));
    $aliases = RECORD_TYPE_ALIASES;
    if (!isset($aliases[$rtNorm])) {
        $canonical = array_unique(array_values($aliases));
        sort($canonical);
        throw new ApiValidationException("$ctx.record_type \"{$rec['record_type']}\" not recognized. Accepted: " . implode(', ', $canonical));
    }

    requireDate($rec, 'date_needed', $ctx);

    if (empty($rec['location']) || !is_array($rec['location'])) {
        throw new ApiValidationException("$ctx.location is required");
    }
    foreach (['name', 'address', 'phone'] as $f) {
        if (empty($rec['location'][$f])) {
            throw new ApiValidationException("$ctx.location.$f is required");
        }
    }

    $priority = isset($rec['priority']) ? strtolower(trim($rec['priority'])) : 'standard';
    if (!in_array($priority, ALLOWED_PRIORITIES)) {
        throw new ApiValidationException("$ctx.priority must be 'standard' or 'rush'");
    }

    return [
        'priority'            => $priority,
        'record_type'         => $aliases[$rtNorm],
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

function validateAttachment(array $att, int $i): array {
    $ctx = "attachments[$i]";
    if (empty($att['filename'])) {
        throw new ApiValidationException("$ctx.filename is required");
    }
    if (empty($att['mime_type'])) {
        throw new ApiValidationException("$ctx.mime_type is required");
    }
    if (!in_array($att['mime_type'], ALLOWED_MIME_TYPES)) {
        throw new ApiValidationException("$ctx.mime_type '{$att['mime_type']}' is not allowed");
    }
    if (empty($att['data'])) {
        throw new ApiValidationException("$ctx.data (base64) is required");
    }
    $decoded = base64_decode($att['data'], true);
    if ($decoded === false) {
        throw new ApiValidationException("$ctx.data is not valid base64");
    }
    if (strlen($decoded) > MAX_ATTACHMENT_BYTES) {
        throw new ApiValidationException("$ctx exceeds maximum allowed size of 10 MB");
    }
    return [
        'filename'   => clean($att['filename']),
        'mime_type'  => $att['mime_type'],
        'size_bytes' => strlen($decoded),
        'data'       => base64_encode($decoded),
    ];
}

// ─── Main build function ───────────────────────────────────────────────────────

function buildPayload(array $input): array {

    // Subtype
    if (empty($input['subtype'])) {
        throw new ApiValidationException("subtype is required");
    }
    if (!in_array($input['subtype'], ALLOWED_SUBTYPES)) {
        throw new ApiValidationException("subtype must be one of: " . implode(', ', ALLOWED_SUBTYPES));
    }

    // Case No
    $caseNo = requireField($input, 'case_no');

    // DOI Start
    requireDate($input, 'doi_start');

    // DOI End (optional)
    $doiEnd = null;
    if (!empty($input['doi_end'])) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['doi_end'])) {
            throw new ApiValidationException("doi_end must be in YYYY-MM-DD format");
        }
        $doiEnd = $input['doi_end'];
    }

    // Court / Venue
    if (empty($input['court_venue']) || !is_array($input['court_venue'])) {
        throw new ApiValidationException("court_venue is required");
    }
    $courtVenue = validateCourtVenue($input['court_venue']);

    // Letter of Rep Date
    requireDate($input, 'letter_of_rep_date');

    // Insurance Carriers (optional, multiple)
    $insuranceCarriers = [];
    if (!empty($input['insurance_carriers'])) {
        if (!is_array($input['insurance_carriers'])) {
            throw new ApiValidationException("insurance_carriers must be an array");
        }
        foreach ($input['insurance_carriers'] as $i => $c) {
            $insuranceCarriers[] = validateInsuranceCarrier($c, $i);
        }
    }

    // Opposing Counsel (optional, multiple)
    $opposingCounsel = [];
    if (!empty($input['opposing_counsel'])) {
        if (!is_array($input['opposing_counsel'])) {
            throw new ApiValidationException("opposing_counsel must be an array");
        }
        foreach ($input['opposing_counsel'] as $i => $c) {
            $opposingCounsel[] = validateOpposingCounsel($c, $i);
        }
    }

    // Employer Name
    $employerName = requireField($input, 'employer_name');

    // Patient
    if (empty($input['patient']) || !is_array($input['patient'])) {
        throw new ApiValidationException("patient is required");
    }
    $patient = validatePatient($input['patient']);

    // Records Locations
    if (empty($input['records_locations']) || !is_array($input['records_locations'])) {
        throw new ApiValidationException("records_locations is required and must be a non-empty array");
    }
    $recordsLocations = [];
    foreach ($input['records_locations'] as $i => $rec) {
        $recordsLocations[] = validateRecordsLocation($rec, $i);
    }

    // Attachments (optional, multiple)
    $attachments = [];
    if (!empty($input['attachments'])) {
        if (!is_array($input['attachments'])) {
            throw new ApiValidationException("attachments must be an array");
        }
        foreach ($input['attachments'] as $i => $att) {
            $attachments[] = validateAttachment($att, $i);
        }
    }

    return [
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
}
