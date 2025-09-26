<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

// Get the current page from the query string to set the 'active' class
$currentPage = $_GET['page'] ?? '';

// Define page groups for titles
$pageGroups = [
    'Hauptmenü' => ['dashboard', 'dispatch', 'mein_dienst', 'mailbox'],
    'Verwaltung' => ['hr', 'fuhrpark', 'checklists', 'training_modules', 'documents'],
    'System' => ['it_logs'],
];

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
                <!-- Group 1: Hauptmenü -->
                <h3 class="nav-heading">Hauptmenü</h3>
                <a href="index.php?page=dashboard" class="<?php echo ($currentPage === 'dashboard') ? 'active' : ''; ?>">Dashboard</a>
                <?php if (hasPermission('dispatch_access')): ?>
                    <a href="index.php?page=dispatch" class="<?php echo ($currentPage === 'dispatch') ? 'active' : ''; ?>">Dispatch</a>
                <?php endif; ?>
                <a href="index.php?page=mein_dienst" class="<?php echo ($currentPage === 'mein_dienst') ? 'active' : ''; ?>">Mein Dienst</a>
                <a href="index.php?page=mailbox" class="<?php echo ($currentPage === 'mailbox') ? 'active' : ''; ?>">Postfach</a>

                <!-- Group 2: Verwaltung -->
                <h3 class="nav-heading">Verwaltung</h3>
                <?php if (hasPermission('hr_access')): ?>
                    <a href="index.php?page=hr" class="<?php echo ($currentPage === 'hr') ? 'active' : ''; ?>">Personal</a>
                <?php endif; ?>
                <?php if (hasPermission('fleet_access')): ?>
                    <a href="index.php?page=fuhrpark" class="<?php echo ($currentPage === 'fuhrpark') ? 'active' : ''; ?>">Fuhrpark</a>
                <?php endif; ?>
                <?php if (hasPermission('fto_access')): ?>
                    <a href="index.php?page=checklists" class="<?php echo ($currentPage === 'checklists') ? 'active' : ''; ?>">FTO Checklisten</a>
                <?php endif; ?>
                <?php if (hasPermission('training_access')): ?>
                    <a href="index.php?page=training_modules" class="<?php echo ($currentPage === 'training_modules') ? 'active' : ''; ?>">Trainings-Module</a>
                <?php endif; ?>
                 <?php if (hasPermission('documents_access')): ?>
                    <a href="index.php?page=documents" class="<?php echo ($currentPage === 'documents') ? 'active' : ''; ?>">Dokumente</a>
                <?php endif; ?>

                <!-- Group 3: System -->
                <?php if (hasPermission('logs_access')): ?>
                    <h3 class="nav-heading">System</h3>
                    <a href="index.php?page=it_logs" class="<?php echo ($currentPage === 'it_logs') ? 'active' : ''; ?>">IT-Protokolle</a>
                <?php endif; ?>
            </nav>
            <a href="index.php?page=logout" class="logout-link">Abmelden</a>
        </aside>
        <main class="content">
            <!-- Page content will be inserted here -->
            <h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard'; ?></h1>