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
    header('Location: index.php?page=fleet_management');
    exit;
}

// TODO: Add permission check here

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Vehicle.php';
require_once BASE_PATH . '/src/Logger.php';

// --- LOGIC ---
$vehicleId = $_POST['id'] ?? null;

if (!$vehicleId) {
    header('Location: index.php?page=fleet_management&error=toggle_failed');
    exit;
}

$vehicleModel = new Vehicle();
$success = $vehicleModel->toggleOnDutyStatus($vehicleId);

if ($success) {
    Logger::log('vehicle_duty_toggled', "Dienst-Status für Fahrzeug-ID {$vehicleId} wurde umgeschaltet.");
    header('Location: index.php?page=fleet_management&status=duty_toggled');
    exit;
} else {
    Logger::log('vehicle_duty_toggle_failed', "Fehler beim Umschalten des Dienst-Status für Fahrzeug-ID {$vehicleId}.");
    header('Location: index.php?page=fleet_management&error=toggle_failed');
    exit;
}
?>