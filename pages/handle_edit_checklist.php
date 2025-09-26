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
    header('Location: index.php?page=checklists');
    exit;
}

requirePermission('fto_manage_checklists');

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Checklist.php';
require_once BASE_PATH . '/src/Logger.php';

// --- LOGIC ---
$officerId = $_POST['officer_id'] ?? null;
$items = $_POST['items'] ?? [];
$notes = $_POST['notes'] ?? '';
$ftoId = $_POST['assigned_fto_id'] ?? null;

if (!$officerId) {
    header('Location: index.php?page=checklists&error=update_failed');
    exit;
}

// Reconstruct the checklist content string from the form data
$contentLines = [];
foreach ($items as $item) {
    $type = $item['type'];
    $text = $item['text'];

    if ($type === 'task') {
        $checked = isset($item['checked']) ? 'x' : ' ';
        $contentLines[] = "- [{$checked}] {$text}";
    } elseif ($type === 'heading') {
        $contentLines[] = "# {$text}";
    } else {
        $contentLines[] = $text;
    }
}
$content = implode("\n", $contentLines);


$checklistModel = new Checklist();
$success = $checklistModel->update($officerId, $content, $notes, $ftoId);

if ($success) {
    // Success: Log the event and redirect.
    Logger::log('checklist_updated', "Checkliste für Beamten-ID {$officerId} wurde aktualisiert.");

    header('Location: index.php?page=checklists&status=checklist_updated');
    exit;
} else {
    // Failure: Log the event and redirect back.
    Logger::log('checklist_update_failed', "Fehler beim Aktualisieren der Checkliste für Beamten-ID {$officerId}.");

    header('Location: index.php?page=edit_checklist&officer_id=' . $officerId . '&error=update_failed');
    exit;
}
?>