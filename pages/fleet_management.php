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

// TODO: Add permission check for Dispatch/Admin

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Vehicle.php';

// --- PAGE-SPECIFIC LOGIC ---
$vehicleModel = new Vehicle();
$vehicles = $vehicleModel->getAll();

// --- TEMPLATE ---
$pageTitle = 'Flotten-Management (Dienst-Status)';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div style="display: flex; justify-content: space-between; align-items: center;">
    <p>Aktivieren oder deaktivieren Sie hier Fahrzeuge für den Dienst. Nur aktive Fahrzeuge erscheinen auf dem Dispatch-Board.</p>
    <a href="index.php?page=dispatch" class="button button-secondary">Zurück zum Dispatch</a>
</div>

<?php if (isset($_GET['status']) && $_GET['status'] === 'duty_toggled'): ?>
    <div class="message-success">
        Der Dienst-Status des Fahrzeugs wurde erfolgreich geändert.
    </div>
<?php endif; ?>

<section id="fleet-management-list">
    <h2>Fahrzeugliste</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Kennzeichen</th>
                <th>Dienst-Status</th>
                <th>Aktion</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($vehicles)): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">Keine Fahrzeuge in der Datenbank gefunden.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($vehicles as $vehicle): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($vehicle['name']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['licensePlate']); ?></td>
                        <td>
                            <span style="font-weight: bold; color: <?php echo $vehicle['on_duty'] ? '#48bb78' : '#f56565'; ?>;">
                                <?php echo $vehicle['on_duty'] ? 'Im Dienst' : 'Außer Dienst'; ?>
                            </span>
                        </td>
                        <td>
                            <form action="index.php?page=handle_toggle_vehicle_duty" method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $vehicle['id']; ?>">
                                <button type="submit" class="button <?php echo $vehicle['on_duty'] ? 'button-danger' : ''; ?>">
                                    <?php echo $vehicle['on_duty'] ? 'Außer Dienst stellen' : 'In Dienst stellen'; ?>
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