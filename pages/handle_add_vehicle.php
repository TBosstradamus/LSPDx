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
    header('Location: index.php?page=fuhrpark');
    exit;
}

// TODO: Add permission check here

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Vehicle.php';
require_once BASE_PATH . '/src/Logger.php';

// --- LOGIC ---
$vehicleModel = new Vehicle();
$newVehicleId = $vehicleModel->create($_POST);

if ($newVehicleId) {
    // Success: Log the event and redirect.
    Logger::log('vehicle_created', "Neues Fahrzeug '{$_POST['name']}' (ID: {$newVehicleId}) wurde zur Flotte hinzugefügt.");

    header('Location: index.php?page=fuhrpark&status=vehicle_added');
    exit;
} else {
    // Failure: Log the event and redirect back.
    Logger::log('vehicle_creation_failed', "Fehler beim Erstellen eines neuen Fahrzeugs.", null, $_POST);

    header('Location: index.php?page=add_vehicle&error=creation_failed');
    exit;
}
?>