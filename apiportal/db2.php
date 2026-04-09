<?php

// ═══════════════════════════════════════════════════════════════════════════════
//  Database connection & save logic
// ═══════════════════════════════════════════════════════════════════════════════

define('DB_HOST', 'localhost');
define('DB_NAME', 'rtr_portal');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_CHARSET', 'utf8mb4');

function getDb(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn  = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
    }
    return $pdo;
}

// ─── Save a validated payload to the database ─────────────────────────────────

function saveSubmission(array $payload): void {
    $db = getDb();

    $db->beginTransaction();

    try {

        // ── submissions ───────────────────────────────────────────────────────
        $stmt = $db->prepare("
            INSERT INTO submissions (
                submission_id, received_at, status,
                subtype, case_no, doi_start, doi_end,
                court_name, court_address, court_city, court_state, court_phone,
                letter_of_rep_date,
                employer_name,
                patient_name, patient_dob, patient_ssn,
                patient_street, patient_city, patient_state, patient_zip
            ) VALUES (
                :submission_id, :received_at, :status,
                :subtype, :case_no, :doi_start, :doi_end,
                :court_name, :court_address, :court_city, :court_state, :court_phone,
                :letter_of_rep_date,
                :employer_name,
                :patient_name, :patient_dob, :patient_ssn,
                :patient_street, :patient_city, :patient_state, :patient_zip
            )
        ");

        $cv = $payload['court_venue'];
        $pt = $payload['patient'];

        $stmt->execute([
            ':submission_id'      => $payload['submission_id'],
            ':received_at'        => date('Y-m-d H:i:s'),
            ':status'             => $payload['status'],
            ':subtype'            => $payload['subtype'],
            ':case_no'            => $payload['case_no'],
            ':doi_start'          => $payload['doi_start'],
            ':doi_end'            => $payload['doi_end'],
            ':court_name'         => $cv['name'],
            ':court_address'      => $cv['address'],
            ':court_city'         => $cv['city'],
            ':court_state'        => $cv['state'],
            ':court_phone'        => $cv['phone'],
            ':letter_of_rep_date' => $payload['letter_of_rep_date'],
            ':employer_name'      => $payload['employer_name'],
            ':patient_name'       => $pt['name'],
            ':patient_dob'        => $pt['dob'],
            ':patient_ssn'        => $pt['ssn'],
            ':patient_street'     => $pt['street'],
            ':patient_city'       => $pt['city'],
            ':patient_state'      => $pt['state'],
            ':patient_zip'        => $pt['zip'],
        ]);

        $sid = $payload['submission_id'];

        // ── insurance_carriers ────────────────────────────────────────────────
        if (!empty($payload['insurance_carriers'])) {
            $ins = $db->prepare("
                INSERT INTO insurance_carriers
                    (submission_id, name, address, city, state, zip, phone,
                     adjuster_name, adjuster_phone, adjuster_fax, adjuster_email, claim_no)
                VALUES
                    (:submission_id, :name, :address, :city, :state, :zip, :phone,
                     :adjuster_name, :adjuster_phone, :adjuster_fax, :adjuster_email, :claim_no)
            ");
            foreach ($payload['insurance_carriers'] as $c) {
                $ins->execute([
                    ':submission_id'  => $sid,
                    ':name'           => $c['name'],
                    ':address'        => $c['address'],
                    ':city'           => $c['city'],
                    ':state'          => $c['state'],
                    ':zip'            => $c['zip'],
                    ':phone'          => $c['phone'],
                    ':adjuster_name'  => $c['adjuster_name'],
                    ':adjuster_phone' => $c['adjuster_phone'],
                    ':adjuster_fax'   => $c['adjuster_fax'],
                    ':adjuster_email' => $c['adjuster_email'],
                    ':claim_no'       => $c['claim_no'],
                ]);
            }
        }

        // ── opposing_counsel ──────────────────────────────────────────────────
        if (!empty($payload['opposing_counsel'])) {
            $opp = $db->prepare("
                INSERT INTO opposing_counsel
                    (submission_id, name, address, city, state, zip, phone)
                VALUES
                    (:submission_id, :name, :address, :city, :state, :zip, :phone)
            ");
            foreach ($payload['opposing_counsel'] as $c) {
                $opp->execute([
                    ':submission_id' => $sid,
                    ':name'          => $c['name'],
                    ':address'       => $c['address'],
                    ':city'          => $c['city'],
                    ':state'         => $c['state'],
                    ':zip'           => $c['zip'],
                    ':phone'         => $c['phone'],
                ]);
            }
        }

        // ── records_locations ─────────────────────────────────────────────────
        if (!empty($payload['records_locations'])) {
            $rl = $db->prepare("
                INSERT INTO records_locations
                    (submission_id, priority, record_type, date_needed,
                     location_name, location_address, location_phone, special_instruction)
                VALUES
                    (:submission_id, :priority, :record_type, :date_needed,
                     :location_name, :location_address, :location_phone, :special_instruction)
            ");
            foreach ($payload['records_locations'] as $r) {
                $rl->execute([
                    ':submission_id'       => $sid,
                    ':priority'            => $r['priority'],
                    ':record_type'         => $r['record_type'],
                    ':date_needed'         => $r['date_needed'],
                    ':location_name'       => $r['location']['name'],
                    ':location_address'    => $r['location']['address'],
                    ':location_phone'      => $r['location']['phone'],
                    ':special_instruction' => $r['special_instruction'],
                ]);
            }
        }

        // ── attachments ───────────────────────────────────────────────────────
        if (!empty($payload['attachments'])) {
            $att = $db->prepare("
                INSERT INTO attachments
                    (submission_id, filename, mime_type, size_bytes, file_data)
                VALUES
                    (:submission_id, :filename, :mime_type, :size_bytes, :file_data)
            ");
            foreach ($payload['attachments'] as $a) {
                $att->execute([
                    ':submission_id' => $sid,
                    ':filename'      => $a['filename'],
                    ':mime_type'     => $a['mime_type'],
                    ':size_bytes'    => $a['size_bytes'],
                    ':file_data'     => base64_decode($a['data']),
                ]);
            }
        }

        $db->commit();

    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}
