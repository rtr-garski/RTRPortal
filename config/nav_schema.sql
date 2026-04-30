-- ============================================================
-- RTR Portal — Database-Driven Sidebar Navigation Schema
-- Run against the local DB (pdo2 / t287pBSqKsFdc82)
-- ============================================================

-- 1. Add department column to sys_users (skip if already exists)
ALTER TABLE sys_users
  ADD COLUMN IF NOT EXISTS department VARCHAR(100) NOT NULL DEFAULT 'all'
  AFTER name;

-- 2. Section headers (e.g. "Main", "RecordHost Portal", "Settings")
CREATE TABLE IF NOT EXISTS nav_sections (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  label       VARCHAR(100)  NOT NULL,
  sort_order  SMALLINT      NOT NULL DEFAULT 0,
  is_active   TINYINT(1)    NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Menu items (supports one level of sub-items via parent_id)
--    parent_id = NULL  → top-level item inside a section
--    parent_id = N     → child of item N (renders as collapsible sub-menu)
--    page_key  = NULL  → non-SPA plain href link
CREATE TABLE IF NOT EXISTS nav_items (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  section_id  INT UNSIGNED  NOT NULL,
  parent_id   INT UNSIGNED      NULL DEFAULT NULL,
  label       VARCHAR(100)  NOT NULL,
  page_key    VARCHAR(100)      NULL DEFAULT NULL,
  href        VARCHAR(255)  NOT NULL DEFAULT '#',
  icon        VARCHAR(100)      NULL DEFAULT NULL,
  sort_order  SMALLINT      NOT NULL DEFAULT 0,
  is_active   TINYINT(1)    NOT NULL DEFAULT 1,
  FOREIGN KEY (section_id) REFERENCES nav_sections(id) ON DELETE CASCADE,
  FOREIGN KEY (parent_id)  REFERENCES nav_items(id)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Department access list for items
--    An item is visible to a department only if a matching row exists here.
--    To give a department access to an item, insert a row.
--    To revoke access, delete the row.
CREATE TABLE IF NOT EXISTS nav_item_departments (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  item_id     INT UNSIGNED  NOT NULL,
  department  VARCHAR(100)  NOT NULL,
  is_active   TINYINT(1)    NOT NULL DEFAULT 1,  -- 1 = visible, 0 = hidden
  UNIQUE KEY uq_item_dept (item_id, department),
  FOREIGN KEY (item_id) REFERENCES nav_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Departments
-- ============================================================
-- Admin, IT, Routing, Operations, Productions,
-- Customer Service, Order Entry, Quality Assurance,
-- Coordinator, Sales

-- ============================================================
-- Seed: sections (idempotent)
-- ============================================================

INSERT IGNORE INTO nav_sections (id, label, sort_order) VALUES
  (1, 'Main',              10),
  (2, 'RecordHost Portal', 20),
  (3, 'Settings',          30);

-- ============================================================
-- Seed: menu items (idempotent)
-- ============================================================

INSERT IGNORE INTO nav_items (id, section_id, parent_id, label, page_key, href, icon, sort_order) VALUES
  -- Main
  (1,  1, NULL, 'API Portal',         'api_portal_test',       '#', 'ti ti-api',             10),

  -- RecordHost Portal
  (2,  2, NULL, 'Order Entry Portal', 'order_entry',           '#', 'ti ti-clipboard-list',  10),
  (3,  2, NULL, 'Client Portal',      'client_reports',        '#', 'ti ti-users',           20),
  (4,  2, NULL, 'Webhooks',           'webhook_management',    '#', 'ti ti-webhook',         30),
  (5,  2, NULL, 'API Tokens',         'api_token_management',  '#', 'ti ti-key',             40),
  (6,  2, NULL, 'File Upload',        'file_upload',           '#', 'ti ti-cloud-upload',    50),
  (7,  2, NULL, 'B2 Upload Test',     'b2b_test',              '#', 'ti ti-test-pipe',       60),
  (8,  2, NULL, 'Dbase Match',        'dbase_match',           '#', 'ti ti-database-search', 70),

  -- Settings
  (9,  3, NULL, 'Theme Editor',       'theme_editor',          '#', 'ti ti-palette',         10);

-- ============================================================
-- Seed: department access — all items granted to all departments
-- Remove rows later to restrict access per department/item.
-- ============================================================

INSERT IGNORE INTO nav_item_departments (item_id, department) VALUES
  -- API Portal (item 1)
  (1, 'Admin'),           (1, 'IT'),              (1, 'Routing'),
  (1, 'Operations'),      (1, 'Productions'),      (1, 'Customer Service'),
  (1, 'Order Entry'),     (1, 'Quality Assurance'),(1, 'Coordinator'),
  (1, 'Sales'),

  -- Order Entry Portal (item 2)
  (2, 'Admin'),           (2, 'IT'),              (2, 'Routing'),
  (2, 'Operations'),      (2, 'Productions'),      (2, 'Customer Service'),
  (2, 'Order Entry'),     (2, 'Quality Assurance'),(2, 'Coordinator'),
  (2, 'Sales'),

  -- Client Portal (item 3)
  (3, 'Admin'),           (3, 'IT'),              (3, 'Routing'),
  (3, 'Operations'),      (3, 'Productions'),      (3, 'Customer Service'),
  (3, 'Order Entry'),     (3, 'Quality Assurance'),(3, 'Coordinator'),
  (3, 'Sales'),

  -- Webhooks (item 4)
  (4, 'Admin'),           (4, 'IT'),              (4, 'Routing'),
  (4, 'Operations'),      (4, 'Productions'),      (4, 'Customer Service'),
  (4, 'Order Entry'),     (4, 'Quality Assurance'),(4, 'Coordinator'),
  (4, 'Sales'),

  -- API Tokens (item 5)
  (5, 'Admin'),           (5, 'IT'),              (5, 'Routing'),
  (5, 'Operations'),      (5, 'Productions'),      (5, 'Customer Service'),
  (5, 'Order Entry'),     (5, 'Quality Assurance'),(5, 'Coordinator'),
  (5, 'Sales'),

  -- File Upload (item 6)
  (6, 'Admin'),           (6, 'IT'),              (6, 'Routing'),
  (6, 'Operations'),      (6, 'Productions'),      (6, 'Customer Service'),
  (6, 'Order Entry'),     (6, 'Quality Assurance'),(6, 'Coordinator'),
  (6, 'Sales'),

  -- B2 Upload Test (item 7)
  (7, 'Admin'),           (7, 'IT'),              (7, 'Routing'),
  (7, 'Operations'),      (7, 'Productions'),      (7, 'Customer Service'),
  (7, 'Order Entry'),     (7, 'Quality Assurance'),(7, 'Coordinator'),
  (7, 'Sales'),

  -- Dbase Match (item 8)
  (8, 'Admin'),           (8, 'IT'),              (8, 'Routing'),
  (8, 'Operations'),      (8, 'Productions'),      (8, 'Customer Service'),
  (8, 'Order Entry'),     (8, 'Quality Assurance'),(8, 'Coordinator'),
  (8, 'Sales'),

  -- Theme Editor (item 9)
  (9, 'Admin'),           (9, 'IT'),              (9, 'Routing'),
  (9, 'Operations'),      (9, 'Productions'),      (9, 'Customer Service'),
  (9, 'Order Entry'),     (9, 'Quality Assurance'),(9, 'Coordinator'),
  (9, 'Sales');

-- ============================================================
-- Usage reference
-- ============================================================
--
-- HIDE an item from a department:
--   UPDATE nav_item_departments SET is_active = 0 WHERE item_id = 7 AND department = 'Sales';
--
-- SHOW an item to a department again:
--   UPDATE nav_item_departments SET is_active = 1 WHERE item_id = 7 AND department = 'Sales';
--
-- ADD a sub-menu item under "Order Entry Portal" (parent item 2):
--   INSERT INTO nav_items (section_id, parent_id, label, page_key, icon, sort_order)
--     VALUES (2, 2, 'Sub Report', 'sub_report', 'ti ti-report', 15);
--   -- Then grant departments:
--   INSERT IGNORE INTO nav_item_departments (item_id, department)
--     VALUES (LAST_INSERT_ID(), 'Admin'), (LAST_INSERT_ID(), 'IT');
-- ============================================================
