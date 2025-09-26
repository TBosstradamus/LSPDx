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
$data = $_POST;
// Add the ID of the officer who is creating the module
$data['created_by_id'] = $_SESSION['officer_id'];


$moduleModel = new TrainingModule();
$newModuleId = $moduleModel->create($data);

if ($newModuleId) {
    // Success: Log the event and redirect.
    Logger::log('training_module_created', "Trainings-Modul '{$data['title']}' (ID: {$newModuleId}) wurde erstellt.");

    header('Location: index.php?page=training_modules&status=module_added');
    exit;
} else {
    // Failure: Log the event and redirect back.
    Logger::log('training_module_creation_failed', "Fehler beim Erstellen eines Trainings-Moduls.", null, $data);

    header('Location: index.php?page=add_training_module&error=creation_failed');
    exit;
}
?>