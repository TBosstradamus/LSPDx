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
    header('Location: index.php?page=training_modules');
    exit;
}

// TODO: Add permission check here for FTO/Admin

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/TrainingModule.php';
require_once BASE_PATH . '/src/Logger.php';

// --- LOGIC ---
$id = $_POST['id'] ?? null;

if (!$id) {
    header('Location: index.php?page=training_modules&error=delete_failed');
    exit;
}

$moduleModel = new TrainingModule();
$success = $moduleModel->delete($id);

if ($success) {
    // Success: Log the event and redirect.
    Logger::log('training_module_deleted', "Trainings-Modul mit ID {$id} wurde gelöscht.");

    header('Location: index.php?page=training_modules&status=module_deleted');
    exit;
} else {
    // Failure: Log the event and redirect back.
    Logger::log('training_module_deletion_failed', "Fehler beim Löschen von Trainings-Modul-ID {$id}.");

    header('Location: index.php?page=training_modules&error=delete_failed');
    exit;
}
?>