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
$data = $_POST;
// Add the ID of the officer who is creating the document
$data['created_by_id'] = $_SESSION['officer_id'];


$documentModel = new Document();
$newDocId = $documentModel->create($data);

if ($newDocId) {
    // Success: Log the event and redirect.
    Logger::log('document_created', "Dokument '{$data['title']}' (ID: {$newDocId}) wurde erstellt.");

    header('Location: index.php?page=documents&status=document_added');
    exit;
} else {
    // Failure: Log the event and redirect back.
    Logger::log('document_creation_failed', "Fehler beim Erstellen eines Dokuments.", null, $data);

    header('Location: index.php?page=add_document&error=creation_failed');
    exit;
}
?>