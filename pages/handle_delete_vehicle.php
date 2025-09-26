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
$id = $_POST['id'] ?? null;

if (!$id) {
    header('Location: index.php?page=fuhrpark&error=delete_failed');
    exit;
}

$vehicleModel = new Vehicle();
$success = $vehicleModel->delete($id);

if ($success) {
    // Success: Log the event and redirect.
    Logger::log('vehicle_deleted', "Fahrzeug mit ID {$id} wurde aus der Flotte gelöscht.");

    header('Location: index.php?page=fuhrpark&status=vehicle_deleted');
    exit;
} else {
    // Failure: Log the event and redirect back.
    Logger::log('vehicle_deletion_failed', "Fehler beim Löschen von Fahrzeug-ID {$id}.");

    header('Location: index.php?page=fuhrpark&error=delete_failed');
    exit;
}
?>