<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

// Ensure user is authenticated and has an organization context
if (!isset($_SESSION['user_id']) || !isset($_SESSION['organization_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=fuhrpark');
    exit;
}

// requirePermission('fleet_manage'); // Future-proofing for permission system

require_once BASE_PATH . '/src/Vehicle.php';

// Collect data from the form
$data = [
    'name' => $_POST['name'] ?? '',
    'category' => $_POST['category'] ?? '',
    'capacity' => $_POST['capacity'] ?? 0,
    'licensePlate' => $_POST['licensePlate'] ?? '',
    'mileage' => $_POST['mileage'] ?? 0,
];

try {
    // Instantiate the Vehicle model with the user's organization ID
    $vehicleModel = new Vehicle($_SESSION['organization_id']);

    // Create the new vehicle
    $vehicleId = $vehicleModel->create($data);

    if ($vehicleId) {
        // Redirect to the main fleet page on success
        header('Location: index.php?page=fuhrpark&status=vehicle_added');
    } else {
        // Redirect with an error if creation failed
        header('Location: index.php?page=add_vehicle&error=creation_failed');
    }
} catch (Exception $e) {
    // Log the error and redirect with a generic error
    error_log("Error creating vehicle: " . $e->getMessage());
    header('Location: index.php?page=add_vehicle&error=unknown');
}

exit;
?>