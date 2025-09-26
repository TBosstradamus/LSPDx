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

requirePermission('fleet_manage');

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Vehicle.php';
require_once BASE_PATH . '/src/Logger.php';

// --- LOGIC ---
$vehicleId = $_POST['id'] ?? null;
if (!$vehicleId) {
    header('Location: index.php?page=fuhrpark&error=update_failed');
    exit;
}

$vehicleModel = new Vehicle();
$success = $vehicleModel->update($vehicleId, $_POST);

if ($success) {
    // Success: Log the event and redirect.
    Logger::log('vehicle_updated', "Stammdaten für Fahrzeug '{$_POST['name']}' (ID: {$vehicleId}) wurden aktualisiert.");

    header('Location: index.php?page=fuhrpark&status=vehicle_updated');
    exit;
} else {
    // Failure: Log the event and redirect back.
    Logger::log('vehicle_update_failed', "Fehler beim Aktualisieren von Fahrzeug-ID {$vehicleId}.", null, $_POST);

    header('Location: index.php?page=edit_vehicle&id=' . $vehicleId . '&error=update_failed');
    exit;
}
?>