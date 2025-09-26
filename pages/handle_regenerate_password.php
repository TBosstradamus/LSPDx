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
    header('Location: index.php?page=credentials');
    exit;
}

requirePermission('hr_manage_credentials');

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Auth.php';
require_once BASE_PATH . '/src/Logger.php';

// --- LOGIC ---
$userId = $_POST['user_id'] ?? null;
$username = $_POST['username'] ?? 'Unbekannt';

if (!$userId) {
    header('Location: index.php?page=credentials&error=reset_failed');
    exit;
}

$authModel = new Auth();
$newPassword = $authModel->regeneratePassword($userId);

if ($newPassword) {
    // Success: Log the event, store info in session, and redirect.
    Logger::log('password_reset', "Passwort für Benutzer '{$username}' (User-ID: {$userId}) wurde zurückgesetzt.");

    $_SESSION['new_password_info'] = [
        'username' => $username,
        'password' => $newPassword
    ];
    header('Location: index.php?page=credentials&status=password_reset');
    exit;
} else {
    // Failure: Log the event and redirect back.
    Logger::log('password_reset_failed', "Fehler beim Zurücksetzen des Passworts für User-ID {$userId}.");

    header('Location: index.php?page=credentials&error=reset_failed');
    exit;
}
?>