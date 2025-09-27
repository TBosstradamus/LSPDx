<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

// Ensure user is authenticated and has the correct permissions
if (!isset($_SESSION['user_id']) || !isset($_SESSION['organization_id'])) {
    header('Location: index.php?page=login');
    exit;
}
require_once BASE_PATH . '/src/Auth.php';
Auth::requirePermission('fleet_manage');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=fuhrpark');
    exit;
}

require_once BASE_PATH . '/src/Vehicle.php';
$vehicleModel = new Vehicle($_SESSION['organization_id']);

$action = $_POST['action'] ?? '';
$vehicleId = $_POST['id'] ?? null;

if (!$vehicleId) {
    header('Location: index.php?page=fuhrpark&error=missing_id');
    exit;
}

if ($action === 'update') {
    $data = [
        'name' => $_POST['name'],
        'category' => $_POST['category'],
        'capacity' => $_POST['capacity'],
        'licensePlate' => $_POST['licensePlate'],
        'mileage' => $_POST['mileage'],
    ];
    $success = $vehicleModel->update($vehicleId, $data);
    if ($success) {
        header('Location: index.php?page=fuhrpark&status=vehicle_updated');
    } else {
        header('Location: index.php?page=edit_vehicle&id=' . $vehicleId . '&error=update_failed');
    }
} elseif ($action === 'delete') {
    $success = $vehicleModel->delete($vehicleId);
    if ($success) {
        header('Location: index.php?page=fuhrpark&status=vehicle_deleted');
    } else {
        header('Location: index.php?page=edit_vehicle&id=' . $vehicleId . '&error=delete_failed');
    }
} else {
    header('Location: index.php?page=fuhrpark');
}

exit;
?>