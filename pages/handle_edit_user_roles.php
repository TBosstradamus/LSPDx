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
    header('Location: index.php?page=hr');
    exit;
}

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Roles.php';

// --- LOGIC ---
$officerId = $_POST['officer_id'] ?? null;
$roleIds = $_POST['roles'] ?? []; // The checkboxes will submit an array of role IDs

if (!$officerId) {
    header('Location: index.php?page=hr&error=update_failed');
    exit;
}

$rolesModel = new Roles();
$success = $rolesModel->updateUserRoles($officerId, $roleIds);

if ($success) {
    // Success: Redirect to the main HR page with a success message.
    header('Location: index.php?page=hr&status=roles_updated');
    exit;
} else {
    // Failure: Redirect back to the form with an error.
    header('Location: index.php?page=edit_user_roles&officer_id=' . $officerId . '&error=update_failed');
    exit;
}
?>