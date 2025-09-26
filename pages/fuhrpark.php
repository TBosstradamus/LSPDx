<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login'); exit;
}
// requirePermission('fleet_view');

require_once BASE_PATH . '/src/Vehicle.php';

$vehicleModel = new Vehicle();
$vehicles = $vehicleModel->getAll();

$pageTitle = 'Fuhrpark';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="flex justify-between items-center mb-6">
    <p class="text-gray-400">Verwalten Sie hier die Stammdaten aller Fahrzeuge Ihrer Organisation.</p>
    <div>
        <a href="index.php?page=add_vehicle" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
             <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Fahrzeug hinzufügen
        </a>
    </div>
</div>

<?php if (isset($_GET['status'])): ?>
    <div class="bg-green-500 text-white p-4 rounded-lg mb-6">
        <?php
        if ($_GET['status'] === 'vehicle_added') echo 'Das Fahrzeug wurde erfolgreich hinzugefügt.';
        if ($_GET['status'] === 'vehicle_updated') echo 'Das Fahrzeug wurde erfolgreich aktualisiert.';
        if ($_GET['status'] === 'vehicle_deleted') echo 'Das Fahrzeug wurde erfolgreich gelöscht.';
        ?>
    </div>
<?php endif; ?>

<div class="bg-gray-800 rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Kategorie</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Kennzeichen</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Kilometerstand</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Aktionen</th>
            </tr>
        </thead>
        <tbody class="bg-gray-800 divide-y divide-gray-700">
            <?php if (empty($vehicles)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-400">Keine Fahrzeuge in der Datenbank gefunden.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($vehicles as $vehicle): ?>
                    <tr class="hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white"><?php echo htmlspecialchars($vehicle['name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars($vehicle['category']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars($vehicle['licensePlate']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars(number_format($vehicle['mileage'], 0, ',', '.')); ?> km</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="index.php?page=edit_vehicle&id=<?php echo $vehicle['id']; ?>" class="text-indigo-400 hover:text-indigo-300">Bearbeiten</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<!-- End of page-specific content -->

<?php
include_once BASE_PATH . '/templates/footer.php';
?>