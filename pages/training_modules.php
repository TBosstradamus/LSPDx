<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

// Ensure user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['organization_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// TODO: Add permission check: Auth::requirePermission('fto_view');

$pageTitle = 'FTO - Ausbildungsakten';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-white"><?php echo $pageTitle; ?></h1>
        <p class="mt-1 text-brand-text-secondary">Diese Funktion wird in einer zukünftigen Phase implementiert.</p>
    </div>
</div>

<div class="bg-brand-card border border-brand-border rounded-lg shadow">
    <div class="p-6 text-center text-brand-text-secondary">
        Hier werden Sie die Ausbildungsakten Ihrer Beamten einsehen und verwalten können.
    </div>
</div>
<!-- End of page-specific content -->

<?php
include_once BASE_PATH . '/templates/footer.php';
?>