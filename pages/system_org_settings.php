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

// This is a high-level admin function
requirePermission('system_org_manage');

$pageTitle = 'Organisations-Verwaltung';
include_once BASE_PATH . '/templates/header.php';
?>

<style>
    .settings-container {
        background-color: #2d3748;
        padding: 2rem;
        border-radius: 0.5rem;
    }
    #sharing-matrix table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }
    #sharing-matrix th, #sharing-matrix td {
        border: 1px solid #4a5568;
        padding: 0.75rem;
        text-align: center;
    }
    #sharing-matrix th {
        background-color: #1a202c;
    }
    #sharing-matrix .source-org {
        text-align: left;
        font-weight: bold;
    }
    #sharing-matrix .data-type-label {
        display: block;
        margin-top: 0.5rem;
        font-size: 0.9rem;
    }
    .settings-actions {
        margin-top: 1.5rem;
        text-align: right;
    }
</style>

<div class="settings-container">
    <p>Konfigurieren Sie hier, welche Organisationen Daten miteinander teilen dürfen. Die Freigabe ist unidirektional (Quelle gibt für Ziel frei).</p>

    <div id="sharing-matrix">
        <p>Lade Einstellungsmatrix...</p>
    </div>

    <div class="settings-actions">
        <button id="save-org-settings" class="button">Einstellungen speichern</button>
    </div>
</div>

<script src="public/js/org_settings.js"></script>

<?php
include_once BASE_PATH . '/templates/footer.php';
?>