<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

// Get the current page from the query string to set the 'active' class
$currentPage = $_GET['page'] ?? '';

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- The title will be passed as a variable to the template -->
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'LSPD Intranet'; ?> - LSPD Intranet</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="main-layout">
        <aside class="sidebar">
            <h2>LSPD Intranet</h2>
            <nav>
                <a href="index.php?page=dashboard" class="<?php echo ($currentPage === 'dashboard') ? 'active' : ''; ?>">Dashboard</a>
                <a href="index.php?page=mein_dienst" class="<?php echo ($currentPage === 'mein_dienst') ? 'active' : ''; ?>">Mein Dienst</a>
                <a href="index.php?page=hr" class="<?php echo ($currentPage === 'hr') ? 'active' : ''; ?>">Personalabteilung</a>
                <a href="index.php?page=fuhrpark" class="<?php echo ($currentPage === 'fuhrpark') ? 'active' : ''; ?>">Fuhrpark</a>
                <a href="index.php?page=training_modules" class="<?php echo ($currentPage === 'training_modules') ? 'active' : ''; ?>">Trainings-Module</a>
                <a href="index.php?page=checklists" class="<?php echo ($currentPage === 'checklists') ? 'active' : ''; ?>">FTO Checklisten</a>
                <a href="index.php?page=documents" class="<?php echo ($currentPage === 'documents') ? 'active' : ''; ?>">Dokumente</a>
                <a href="index.php?page=mailbox" class="<?php echo ($currentPage === 'mailbox') ? 'active' : ''; ?>">Postfach</a>
                <a href="index.php?page=it_logs" class="<?php echo ($currentPage === 'it_logs') ? 'active' : ''; ?>">IT-Protokolle</a>
                <!-- More links can be added here -->
            </nav>
            <a href="index.php?page=logout" class="logout-link">Abmelden</a>
        </aside>
        <main class="content">
            <!-- Page content will be inserted here -->
            <h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard'; ?></h1>