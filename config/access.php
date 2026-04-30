<?php
/**
 * Checks that the logged-in user's department has access to a nav page_key.
 * Call at the top of every pages/*.php and api/*.php after session check.
 *
 * On failure:
 *   - pages/*.php  → returns HTTP 403 fragment (rendered in #content)
 *   - api/*.php    → returns HTTP 403 JSON
 *
 * Usage in pages/*.php:
 *   require_once '../config/access.php';
 *   require_page_access('order_entry', $pdo2);
 *
 * Usage in api/*.php:
 *   require_once '../config/access.php';
 *   require_page_access('order_entry', $pdo2, true);
 */
function require_page_access(string $page_key, PDO $pdo2, bool $is_api = false): void
{
    $dept = $_SESSION['department'] ?? '';

    // No department in session → deny
    if (!$dept || $dept === '') {
        _deny_access($is_api, 'No department assigned to your account.');
    }

    try {
        $stmt = $pdo2->prepare("
            SELECT 1
            FROM nav_items ni
            INNER JOIN nav_item_departments d ON d.item_id = ni.id
            WHERE ni.page_key  = ?
              AND ni.is_active  = 1
              AND d.department  = ?
              AND d.is_active   = 1
            LIMIT 1
        ");
        $stmt->execute([$page_key, $dept]);
        $allowed = $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log('[access] DB error: ' . $e->getMessage());
        $allowed = false;
    }

    if (!$allowed) {
        _deny_access($is_api, 'You do not have permission to access this page.');
    }
}

function _deny_access(bool $is_api, string $message): void
{
    http_response_code(403);
    if ($is_api) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
    } else {
        echo '<div class="container-fluid pt-4">
            <div class="alert alert-danger">
                <i class="ti ti-lock me-2"></i>' . htmlspecialchars($message) . '
            </div>
        </div>';
    }
    exit;
}
