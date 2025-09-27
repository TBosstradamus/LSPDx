<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

// Ensure user is logged in & has permission
if (!isset($_SESSION['user_id']) || !isset($_SESSION['organization_id'])) {
    header('Location: index.php?page=login');
    exit;
}
require_once BASE_PATH . '/src/Auth.php';
Auth::requirePermission('hr_view');

require_once BASE_PATH . '/src/Officer.php';
$officerModel = new Officer($_SESSION['organization_id']);
$officers = $officerModel->getAll();

$pageTitle = 'Personal';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-white">Personal</h1>
        <p class="mt-1 text-brand-text-secondary">Verwalten Sie alle Beamten in Ihrer Organisation.</p>
    </div>
    <?php if (Auth::hasPermission('hr_officers_manage')): ?>
    <div>
        <a href="index.php?page=add_officer" class="bg-brand-blue hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Beamten hinzufügen
        </a>
    </div>
    <?php endif; ?>
</div>

<?php if (isset($_GET['status'])): ?>
    <div class="bg-green-500/20 border border-green-500 text-green-300 p-4 rounded-lg mb-6">
        <?php
        if ($_GET['status'] === 'officer_added') echo 'Der Beamte wurde erfolgreich hinzugefügt.';
        if ($_GET['status'] === 'officer_updated') echo 'Der Beamte wurde erfolgreich aktualisiert.';
        if ($_GET['status'] === 'roles_updated') echo 'Die Rollen des Beamten wurden erfolgreich aktualisiert.';
        ?>
    </div>
<?php endif; ?>

<div class="bg-brand-card border border-brand-border rounded-lg shadow">
    <div class="p-4 border-b border-brand-border">
         <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-brand-text-secondary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text" name="search" id="search" class="block w-full pl-10 pr-3 py-2 bg-brand-bg border border-brand-border rounded-md leading-5 text-brand-text-primary placeholder-brand-text-secondary focus:outline-none focus:bg-brand-sidebar focus:border-brand-blue" placeholder="Beamte suchen...">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 p-4">
        <?php if (empty($officers)): ?>
            <div class="col-span-full text-center text-brand-text-secondary py-10">
                Keine Beamten in der Datenbank gefunden.
            </div>
        <?php else: ?>
            <?php foreach ($officers as $officer): ?>
                <a href="index.php?page=edit_officer&id=<?php echo $officer['id']; ?>" class="block bg-brand-bg border border-brand-border rounded-lg p-4 hover:border-brand-blue transition-colors">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <span class="inline-block h-12 w-12 rounded-full overflow-hidden bg-gray-700">
                                <svg class="h-full w-full text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </span>
                        </div>
                        <div class="ml-4">
                            <div class="text-lg font-bold text-white"><?php echo htmlspecialchars($officer['firstName'] . ' ' . $officer['lastName']); ?></div>
                            <div class="text-sm text-brand-text-secondary"><?php echo htmlspecialchars($officer['rank']); ?></div>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-between items-center">
                        <div class="text-sm text-brand-text-secondary">
                            #<?php echo htmlspecialchars($officer['badgeNumber']); ?>
                        </div>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $officer['isActive'] ? 'bg-green-500/20 text-green-300' : 'bg-red-500/20 text-red-300'; ?>">
                            <?php echo $officer['isActive'] ? 'Aktiv' : 'Inaktiv'; ?>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
include_once BASE_PATH . '/templates/footer.php';
?>