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
requirePermission('hr_manage_officers');

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Officer.php';
require_once BASE_PATH . '/src/Logger.php';

// --- LOGIC ---
$officerModel = new Officer();
$newOfficerId = $officerModel->create($_POST);

if ($newOfficerId) {
    // Success: Log the event and redirect to the main HR page.
    $officerName = $_POST['firstName'] . ' ' . $_POST['lastName'];
    Logger::log('officer_created', "Neuer Beamter '{$officerName}' (ID: {$newOfficerId}) wurde erstellt.");

    header('Location: index.php?page=hr&status=officer_added');
    exit;
} else {
    // Failure: Redirect back to the form with an error.
    Logger::log('officer_creation_failed', "Fehler beim Erstellen eines neuen Beamten.", null, $_POST);

    header('Location: index.php?page=add_officer&error=creation_failed');
    exit;
}
?>