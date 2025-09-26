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

// requirePermission('system_org_manage'); // Will be enforced later

$pageTitle = 'Organisations-Verwaltung';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="bg-gray-800 rounded-lg shadow-lg p-6">
    <h2 class="text-xl font-bold text-white">Freigabeeinstellungen</h2>
    <p class="mt-2 text-gray-400">Konfigurieren Sie hier, welche Organisationen Daten miteinander teilen dürfen. Die Freigabe ist unidirektional (Quelle gibt für Ziel frei).</p>

    <div id="sharing-matrix" class="mt-6">
        <p class="text-gray-400">Lade Einstellungsmatrix...</p>
    </div>

    <div class="mt-6 flex justify-end">
        <button id="save-org-settings" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">
            Einstellungen speichern
        </button>
    </div>
</div>

<script src="public/js/org_settings.js"></script>
<!-- End of page-specific content -->

<?php
include_once BASE_PATH . '/templates/footer.php';
?>