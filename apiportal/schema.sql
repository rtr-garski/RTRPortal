-- ═══════════════════════════════════════════════════════════════════════════
--  RTR Portal — API Submission Database Schema
--  Database: rtr_portal
--  Run once to set up all tables.
-- ═══════════════════════════════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS rtr_portal
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE rtr_portal;

-- ─── submissions ─────────────────────────────────────────────────────────────
--  One row per submission. Stores all single-value fields directly.
--  Multi-value fields (carriers, counsel, record locations, attachments)
--  live in their own tables and JOIN back via submission_id.
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS submissions (
    id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    submission_id       VARCHAR(20)     NOT NULL COMMENT 'e.g. SUB-A3F2C19D4B',
    received_at         DATETIME        NOT NULL,
    status              VARCHAR(20)     NOT NULL DEFAULT 'received',

    -- Case info
    subtype             VARCHAR(60)     NOT NULL,
    case_no             VARCHAR(100)    NOT NULL  COMMENT 'WCAB Case No / ADJ',
    doi_start           DATE            NOT NULL  COMMENT 'Date of Injury start',
    doi_end             DATE                NULL  COMMENT 'Date of Injury end',

    -- Court / Venue (single object — stored flat)
    court_name          VARCHAR(255)        NULL,
    court_address       VARCHAR(255)        NULL,
    court_city          VARCHAR(100)        NULL,
    court_state         VARCHAR(50)         NULL,
    court_phone         VARCHAR(50)         NULL,

    letter_of_rep_date  DATE            NOT NULL,
    employer_name       VARCHAR(255)    NOT NULL,

    -- Patient
    patient_name        VARCHAR(255)    NOT NULL,
    patient_dob         DATE            NOT NULL,
    patient_ssn         VARCHAR(20)         NULL,
    patient_street      VARCHAR(255)        NULL,
    patient_city        VARCHAR(100)        NULL,
    patient_state       VARCHAR(50)         NULL,
    patient_zip         VARCHAR(20)         NULL,

    created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE  KEY uq_submission_id (submission_id),
    INDEX   ix_case_no           (case_no),
    INDEX   ix_patient_name      (patient_name),
    INDEX   ix_status            (status),
    INDEX   ix_received_at       (received_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─── insurance_carriers ───────────────────────────────────────────────────────
--  Multiple rows per submission (one per carrier).
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS insurance_carriers (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    submission_id   VARCHAR(20)     NOT NULL,

    name            VARCHAR(255)    NOT NULL,
    address         VARCHAR(255)        NULL,
    city            VARCHAR(100)        NULL,
    state           VARCHAR(50)         NULL,
    zip             VARCHAR(20)         NULL,
    phone           VARCHAR(50)         NULL,

    adjuster_name   VARCHAR(255)        NULL,
    adjuster_phone  VARCHAR(50)         NULL,
    adjuster_fax    VARCHAR(50)         NULL,
    adjuster_email  VARCHAR(255)        NULL,
    claim_no        VARCHAR(100)        NULL,

    PRIMARY KEY (id),
    INDEX ix_ic_submission_id (submission_id),
    CONSTRAINT fk_ic_submission
        FOREIGN KEY (submission_id)
        REFERENCES submissions (submission_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─── opposing_counsel ────────────────────────────────────────────────────────
--  Multiple rows per submission (one per counsel).
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS opposing_counsel (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    submission_id   VARCHAR(20)     NOT NULL,

    name            VARCHAR(255)    NOT NULL,
    address         VARCHAR(255)        NULL,
    city            VARCHAR(100)        NULL,
    state           VARCHAR(50)         NULL,
    zip             VARCHAR(20)         NULL,
    phone           VARCHAR(50)         NULL,

    PRIMARY KEY (id),
    INDEX ix_oc_submission_id (submission_id),
    CONSTRAINT fk_oc_submission
        FOREIGN KEY (submission_id)
        REFERENCES submissions (submission_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─── records_locations ───────────────────────────────────────────────────────
--  Multiple rows per submission (one per records request).
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS records_locations (
    id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    submission_id       VARCHAR(20)     NOT NULL,

    priority            ENUM('standard','rush') NOT NULL DEFAULT 'standard',
    record_type         VARCHAR(100)    NOT NULL,
    date_needed         DATE            NOT NULL,

    location_name       VARCHAR(255)    NOT NULL,
    location_address    VARCHAR(255)    NOT NULL,
    location_phone      VARCHAR(50)     NOT NULL,

    special_instruction TEXT                NULL,

    PRIMARY KEY (id),
    INDEX ix_rl_submission_id (submission_id),
    INDEX ix_rl_record_type   (record_type),
    CONSTRAINT fk_rl_submission
        FOREIGN KEY (submission_id)
        REFERENCES submissions (submission_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─── attachments ─────────────────────────────────────────────────────────────
--  Multiple rows per submission. Binary file stored in LONGBLOB.
--  If you prefer filesystem storage, replace file_data with a file_path column.
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS attachments (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    submission_id   VARCHAR(20)     NOT NULL,

    filename        VARCHAR(255)    NOT NULL,
    mime_type       VARCHAR(100)    NOT NULL,
    size_bytes      INT UNSIGNED    NOT NULL,
    file_data       LONGBLOB        NOT NULL,

    uploaded_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX ix_att_submission_id (submission_id),
    CONSTRAINT fk_att_submission
        FOREIGN KEY (submission_id)
        REFERENCES submissions (submission_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
