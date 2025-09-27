<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login'); exit;
}
require_once BASE_PATH . '/src/Auth.php';
Auth::requirePermission('fleet_view');

require_once BASE_PATH . '/src/Vehicle.php';

$vehicleModel = new Vehicle($_SESSION['organization_id']);
$vehicles = $vehicleModel->getAll();

$pageTitle = 'Fuhrpark';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-white">Fuhrpark</h1>
        <p class="mt-1 text-brand-text-secondary">Verwalten Sie alle Fahrzeuge in der Flotte Ihrer Organisation.</p>
    </div>
    <?php if (Auth::hasPermission('fleet_manage')): ?>
    <div>
        <a href="index.php?page=add_vehicle" class="bg-brand-blue hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Fahrzeug hinzufügen
        </a>
    </div>
    <?php endif; ?>
</div>

<?php if (isset($_GET['status'])): ?>
    <div class="bg-green-500/20 border border-green-500 text-green-300 p-4 rounded-lg mb-6">
        <?php
        if ($_GET['status'] === 'vehicle_added') echo 'Das Fahrzeug wurde erfolgreich hinzugefügt.';
        if ($_GET['status'] === 'vehicle_updated') echo 'Das Fahrzeug wurde erfolgreich aktualisiert.';
        if ($_GET['status'] === 'vehicle_deleted') echo 'Das Fahrzeug wurde erfolgreich gelöscht.';
        ?>
    </div>
<?php endif; ?>

<div class="bg-brand-card border border-brand-border rounded-lg shadow">
    <!-- Search Bar -->
    <div class="p-4 border-b border-brand-border">
         <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-brand-text-secondary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text" name="search" id="search" class="block w-full pl-10 pr-3 py-2 bg-brand-bg border border-brand-border rounded-md leading-5 text-brand-text-primary placeholder-brand-text-secondary focus:outline-none focus:bg-brand-sidebar focus:border-brand-blue" placeholder="Fahrzeuge suchen...">
        </div>
    </div>

    <!-- Vehicle List -->
    <div class="divide-y divide-brand-border">
        <?php if (empty($vehicles)): ?>
            <div class="p-6 text-center text-brand-text-secondary">
                Keine Fahrzeuge in der Datenbank gefunden.
            </div>
        <?php else: ?>
            <?php foreach ($vehicles as $vehicle): ?>
                <div class="p-4 hover:bg-gray-800/50 flex justify-between items-center">
                    <div class="flex-grow grid grid-cols-4 gap-4">
                        <div>
                            <div class="text-xs text-brand-text-secondary">Name</div>
                            <div class="text-sm font-medium text-white"><?php echo htmlspecialchars($vehicle['name']); ?></div>
                        </div>
                        <div>
                            <div class="text-xs text-brand-text-secondary">Kategorie</div>
                            <div class="text-sm text-brand-text-primary"><?php echo htmlspecialchars($vehicle['category']); ?></div>
                        </div>
                        <div>
                            <div class="text-xs text-brand-text-secondary">Kennzeichen</div>
                            <div class="text-sm text-brand-text-primary font-mono"><?php echo htmlspecialchars($vehicle['licensePlate']); ?></div>
                        </div>
                        <div>
                            <div class="text-xs text-brand-text-secondary">Kilometerstand</div>
                            <div class="text-sm text-brand-text-primary"><?php echo htmlspecialchars(number_format($vehicle['mileage'], 0, ',', '.')); ?> km</div>
                        </div>
                    </div>
                    <?php if (Auth::hasPermission('fleet_manage')): ?>
                    <div class="ml-4 flex-shrink-0">
                        <a href="index.php?page=edit_vehicle&id=<?php echo $vehicle['id']; ?>" class="text-brand-blue hover:underline">Bearbeiten</a>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<!-- End of page-specific content -->

<?php
include_once BASE_PATH . '/templates/footer.php';
?>