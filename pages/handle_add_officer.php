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
    header('Location: index.php?page=hr');
    exit;
}

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Officer.php';

// --- LOGIC ---
$officerModel = new Officer();
$newOfficerId = $officerModel->create($_POST);

if ($newOfficerId) {
    // Success: Redirect to the main HR page.
    header('Location: index.php?page=hr&status=officer_added');
    exit;
} else {
    // Failure: Redirect back to the form with an error.
    header('Location: index.php?page=add_officer&error=creation_failed');
    exit;
}
?>