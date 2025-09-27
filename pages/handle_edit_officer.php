<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

// Ensure user is authenticated
if (!isset($_SESSION['user_id']) || !isset($_SESSION['organization_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=hr');
    exit;
}

require_once BASE_PATH . '/src/Auth.php';
Auth::requirePermission('hr_officers_manage');

require_once BASE_PATH . '/src/Officer.php';
require_once BASE_PATH . '/src/Log.php';

$officerId = $_POST['id'] ?? null;
if (!$officerId) {
    header('Location: index.php?page=hr&error=missing_id');
    exit;
}

$data = [
    'firstName' => $_POST['firstName'],
    'lastName' => $_POST['lastName'],
    'badgeNumber' => $_POST['badgeNumber'],
    'phoneNumber' => $_POST['phoneNumber'],
    'gender' => $_POST['gender'],
    'rank' => $_POST['rank'],
    'isActive' => $_POST['isActive'],
];

try {
    $officerModel = new Officer($_SESSION['organization_id']);
    $success = $officerModel->update($officerId, $data);

    if ($success) {
        // Add a log entry for successful officer update
        Log::add('officer_updated', "Updated details for officer '{$data['firstName']} {$data['lastName']}' (#{$data['badgeNumber']}).", ['officer_id' => $officerId]);
        header('Location: index.php?page=hr&status=officer_updated');
    } else {
        header('Location: index.php?page=edit_officer&id=' . $officerId . '&error=update_failed');
    }
} catch (Exception $e) {
    error_log("Error updating officer: " . $e->getMessage());
    header('Location: index.php?page=edit_officer&id=' . $officerId . '&error=unknown');
}

exit;
?>