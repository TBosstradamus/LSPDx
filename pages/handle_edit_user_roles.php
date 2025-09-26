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

// TODO: Add permission check for Admin

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Roles.php';
require_once BASE_PATH . '/src/Logger.php';

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
    // Success: Log the event and redirect.
    Logger::log('user_roles_updated', "Rollen für Beamten-ID {$officerId} wurden aktualisiert.");

    header('Location: index.php?page=hr&status=roles_updated');
    exit;
} else {
    // Failure: Log the event and redirect back.
    Logger::log('user_roles_update_failed', "Fehler beim Aktualisieren der Rollen für Beamten-ID {$officerId}.");

    header('Location: index.php?page=edit_user_roles&officer_id=' . $officerId . '&error=update_failed');
    exit;
}
?>