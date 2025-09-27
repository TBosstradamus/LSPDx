<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login'); exit;
}
require_once BASE_PATH . '/src/Auth.php';
Auth::requirePermission('system_rights_manage');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=manage_roles');
    exit;
}

require_once BASE_PATH . '/src/Role.php';

$roleModel = new Role($_SESSION['organization_id']);

$roleId = $_POST['role_id'] ?? null;
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$permissions = $_POST['permissions'] ?? [];

if (empty($name)) {
    header('Location: index.php?page=manage_roles&error=missing_name');
    exit;
}

try {
    if ($roleId) {
        // Update existing role
        $roleModel->update($roleId, $name, $description);
    } else {
        // Create new role
        $roleId = $roleModel->create($name, $description);
    }

    if ($roleId) {
        // Update permissions for the role
        $roleModel->updatePermissionsForRole($roleId, $permissions);
        header('Location: index.php?page=manage_roles&status=success');
    } else {
        header('Location: index.php?page=manage_roles&error=operation_failed');
    }
} catch (Exception $e) {
    error_log("Error handling role edit: " . $e->getMessage());
    header('Location: index.php?page=manage_roles&error=unknown');
}

exit;
?>