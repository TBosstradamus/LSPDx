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
    header('Location: index.php?page=time_approval');
    exit;
}

requirePermission('hr_time_approve');

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/TimePauseLog.php';
require_once BASE_PATH . '/src/Logger.php';

// --- LOGIC ---
$logId = $_POST['log_id'] ?? null;
$action = $_POST['action'] ?? null;
$reviewerId = $_SESSION['officer_id'];

if (!$logId || !in_array($action, ['approve', 'reject'])) {
    header('Location: index.php?page=time_approval&error=invalid_action');
    exit;
}

$logModel = new TimePauseLog();
$success = false;

if ($action === 'approve') {
    $success = $logModel->approve($logId, $reviewerId);
} elseif ($action === 'reject') {
    $success = $logModel->reject($logId, $reviewerId);
}

if ($success) {
    Logger::log('time_log_reviewed', "Pausen-Eintrag ID {$logId} wurde als '{$action}' markiert.");
    header('Location: index.php?page=time_approval&status=success');
    exit;
} else {
    Logger::log('time_log_review_failed', "Fehler beim Bearbeiten von Pausen-Eintrag ID {$logId}.");
    header('Location: index.php?page=time_approval&error=failed');
    exit;
}
?>