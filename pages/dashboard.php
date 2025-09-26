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

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Officer.php';

// --- PAGE-SPECIFIC LOGIC ---
$officerModel = new Officer();
$currentUser = $officerModel->findById($_SESSION['officer_id']);

if (!$currentUser) {
    header('Location: index.php?page=logout');
    exit;
}

// --- TEMPLATE ---
$pageTitle = 'Dashboard';
include_once BASE_PATH . '/templates/header.php';
?>

<style>
.dashboard-widgets {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}
.widget-link {
    display: block;
    background-color: #2d3748;
    padding: 2rem;
    border-radius: 0.5rem;
    text-decoration: none;
    color: #cbd5e0;
    transition: transform 0.2s, background-color 0.2s;
}
.widget-link:hover {
    transform: translateY(-5px);
    background-color: #4a5568;
}
.widget-link h3 {
    margin-top: 0;
    color: #90cdf4;
    font-size: 1.5rem;
}
.widget-link p {
    color: #a0aec0;
}
</style>

<!-- Start of page-specific content -->
<p>
    Willkommen zurück im LSPD Intranet, <strong><?php echo htmlspecialchars($currentUser['firstName'] . ' ' . $currentUser['lastName']); ?></strong>.
</p>
<p>Wählen Sie einen Bereich aus, um fortzufahren.</p>

<div class="dashboard-widgets">
    <a href="index.php?page=dispatch" class="widget-link">
        <h3>Dispatch</h3>
        <p>Zur Echtzeit-Übersicht aller Einheiten im Dienst.</p>
    </a>
    <a href="index.php?page=mein_dienst" class="widget-link">
        <h3>Mein Dienst</h3>
        <p>Persönliche Dienstübersicht, Stempeluhr und Lizenzen.</p>
    </a>
    <a href="index.php?page=hr" class="widget-link">
        <h3>Personalabteilung</h3>
        <p>Beamte, Sanktionen und Zugangsdaten verwalten.</p>
    </a>
     <a href="index.php?page=fuhrpark" class="widget-link">
        <h3>Fuhrpark</h3>
        <p>Alle Fahrzeuge der Flotte verwalten.</p>
    </a>
</div>
<!-- End of page-specific content -->

<?php
include_once BASE_PATH . '/templates/footer.php';
?>