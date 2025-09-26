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
    header('Location: index.php?page=sanctions');
    exit;
}

// TODO: Add permission check here for HR

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Sanction.php';
require_once BASE_PATH . '/src/Logger.php';

// --- LOGIC ---
$data = $_POST;
// Add the ID of the officer who is issuing the sanction
$data['issued_by_officer_id'] = $_SESSION['officer_id'];


$sanctionModel = new Sanction();
$success = $sanctionModel->create($data);

if ($success) {
    // Success: Log the event and redirect.
    Logger::log('sanction_issued', "Sanktion '{$data['sanctionType']}' wurde gegen Beamten-ID {$data['officer_id']} verhängt.");

    header('Location: index.php?page=sanctions&status=sanction_added');
    exit;
} else {
    // Failure: Log the event and redirect back.
    Logger::log('sanction_issue_failed', "Fehler beim Verhängen einer Sanktion.", null, $data);

    header('Location: index.php?page=add_sanction&error=creation_failed');
    exit;
}
?>