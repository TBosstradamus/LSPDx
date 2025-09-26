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
requirePermission('fleet_access');

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Vehicle.php';

// --- PAGE-SPECIFIC LOGIC ---
$vehicleModel = new Vehicle();
$vehicles = $vehicleModel->getAll();

// --- TEMPLATE ---
$pageTitle = 'Fuhrpark';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div style="display: flex; justify-content: space-between; align-items: center;">
    <p>Verwalten Sie hier den gesamten Fuhrpark des LSPD.</p>
    <a href="index.php?page=add_vehicle" class="button">Fahrzeug hinzufügen</a>
</div>

<?php if (isset($_GET['status'])): ?>
    <div class="message-success">
        <?php
        if ($_GET['status'] === 'vehicle_added') echo 'Das Fahrzeug wurde erfolgreich hinzugefügt.';
        if ($_GET['status'] === 'vehicle_updated') echo 'Das Fahrzeug wurde erfolgreich aktualisiert.';
        if ($_GET['status'] === 'vehicle_deleted') echo 'Das Fahrzeug wurde erfolgreich gelöscht.';
        ?>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="message-error">
        Ein Fehler ist aufgetreten. Die Aktion konnte nicht abgeschlossen werden.
    </div>
<?php endif; ?>


<section id="vehicle-list">
    <h2>Fahrzeugliste (Master Fleet)</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Kategorie</th>
                <th>Kennzeichen</th>
                <th>Kilometerstand</th>
                <th>Letzter Checkup</th>
                <th>Nächster Checkup</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($vehicles)): ?>
                <tr>
                    <td colspan="7" style="text-align: center;">Keine Fahrzeuge in der Datenbank gefunden.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($vehicles as $vehicle): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($vehicle['name']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['category']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['licensePlate']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($vehicle['mileage'], 0, ',', '.')); ?> km</td>
                        <td><?php echo htmlspecialchars($vehicle['lastCheckup'] ? date('d.m.Y', strtotime($vehicle['lastCheckup'])) : 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['nextCheckup'] ? date('d.m.Y', strtotime($vehicle['nextCheckup'])) : 'N/A'); ?></td>
                        <td>
                            <a href="index.php?page=edit_vehicle&id=<?php echo $vehicle['id']; ?>" class="button button-secondary">Bearbeiten</a>
                            <form action="index.php?page=handle_delete_vehicle" method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $vehicle['id']; ?>">
                                <button type="submit" class="button button-danger" onclick="return confirm('Sind Sie sicher, dass Sie dieses Fahrzeug löschen möchten?');">
                                    Löschen
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<!-- End of page-specific content -->

<?php
include_once BASE_PATH . '/templates/footer.php';
?>