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
    header('Location: index.php?page=documents');
    exit;
}

// TODO: Add permission check here

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Document.php';
require_once BASE_PATH . '/src/Logger.php';

// --- LOGIC ---
$id = $_POST['id'] ?? null;

if (!$id) {
    header('Location: index.php?page=documents&error=delete_failed');
    exit;
}

$documentModel = new Document();
$success = $documentModel->delete($id);

if ($success) {
    // Success: Log the event and redirect.
    Logger::log('document_deleted', "Dokument mit ID {$id} wurde gelöscht.");

    header('Location: index.php?page=documents&status=document_deleted');
    exit;
} else {
    // Failure: Log the event and redirect back.
    Logger::log('document_deletion_failed', "Fehler beim Löschen von Dokument-ID {$id}.");

    header('Location: index.php?page=documents&error=delete_failed');
    exit;
}
?>