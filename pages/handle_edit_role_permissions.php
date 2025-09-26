<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=system_rights_management');
    exit;
}

requirePermission('system_rights_manage');

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Roles.php';
require_once BASE_PATH . '/src/Logger.php';

// --- LOGIC ---
$roleId = $_POST['role_id'] ?? null;
$permissionIds = $_POST['permissions'] ?? [];

if (!$roleId) {
    header('Location: index.php?page=system_rights_management&error=update_failed');
    exit;
}

$rolesModel = new Roles();
$success = $rolesModel->updateRolePermissions($roleId, $permissionIds);

if ($success) {
    Logger::log('role_permissions_updated', "Berechtigungen für Rolle ID {$roleId} wurden aktualisiert.");
    header('Location: index.php?page=system_rights_management&status=permissions_updated');
    exit;
} else {
    Logger::log('role_permissions_update_failed', "Fehler beim Aktualisieren der Berechtigungen für Rolle ID {$roleId}.");
    header('Location: index.php?page=edit_role_permissions&role_id=' . $roleId . '&error=update_failed');
    exit;
}
?>