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

// TODO: Add permission check here

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Officer.php';
require_once BASE_PATH . '/src/Logger.php';

// --- LOGIC ---
$officerId = $_POST['id'] ?? null;
if (!$officerId) {
    header('Location: index.php?page=hr&error=update_failed');
    exit;
}

$officerModel = new Officer();
$success = $officerModel->update($officerId, $_POST);

if ($success) {
    // Success: Log the event and redirect.
    $officerName = $_POST['firstName'] . ' ' . $_POST['lastName'];
    Logger::log('officer_updated', "Daten für Beamten '{$officerName}' (ID: {$officerId}) wurden aktualisiert.");

    header('Location: index.php?page=hr&status=officer_updated');
    exit;
} else {
    // Failure: Log the event and redirect back.
    Logger::log('officer_update_failed', "Fehler beim Aktualisieren von Beamten-ID {$officerId}.", null, $_POST);

    header('Location: index.php?page=edit_officer&id=' . $officerId . '&error=update_failed');
    exit;
}
?>