<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

// Ensure user is authenticated
if (!isset($_SESSION['user_id']) || !isset($_SESSION['organization_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=hr');
    exit;
}

require_once BASE_PATH . '/src/Auth.php';
Auth::requirePermission('hr_manage_roles');

require_once BASE_PATH . '/src/Role.php';

$officerId = $_POST['officer_id'] ?? null;
$roles = $_POST['roles'] ?? [];

if (!$officerId) {
    header('Location: index.php?page=hr&error=missing_id');
    exit;
}

try {
    $roleModel = new Role($_SESSION['organization_id']);
    $success = $roleModel->updateRolesForOfficer($officerId, $roles);

    if ($success) {
        header('Location: index.php?page=hr&status=roles_updated');
    } else {
        header('Location: index.php?page=edit_user_roles&officer_id=' . $officerId . '&error=update_failed');
    }
} catch (Exception $e) {
    error_log("Error updating user roles: " . $e->getMessage());
    header('Location: index.php?page=edit_user_roles&officer_id=' . $officerId . '&error=unknown');
}

exit;
?>