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
requirePermission('hr_access');

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Officer.php';

// --- PAGE-SPECIFIC LOGIC ---
$officerModel = new Officer();
$officers = $officerModel->getAll();


// --- TEMPLATE ---
$pageTitle = 'Personalabteilung';
include_once BASE_PATH . '/templates/header.php';
?>

<style>
.hr-actions {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}
</style>

<!-- Start of page-specific content -->
<?php if (isset($_GET['status']) && $_GET['status'] === 'officer_added'): ?>
    <div class="message-success">
        Der Beamte wurde erfolgreich hinzugefügt.
    </div>
<?php endif; ?>
<?php if (isset($_GET['status']) && $_GET['status'] === 'officer_updated'): ?>
    <div class="message-success">
        Der Beamte wurde erfolgreich aktualisiert.
    </div>
<?php endif; ?>


<p>Verwalten Sie hier alle Aspekte der Personalangelegenheiten des LSPD.</p>

<section class="hr-actions">
    <a href="index.php?page=add_officer" class="button">Beamten hinzufügen</a>
    <a href="index.php?page=sanctions" class="button button-secondary">Sanktionen verwalten</a>
    <a href="index.php?page=credentials" class="button button-secondary">Zugangsdaten verwalten</a>
</section>


<section id="officer-list">
    <h2>Beamtenliste</h2>
    <table>
        <thead>
            <tr>
                <th>Dienstnummer</th>
                <th>Vorname</th>
                <th>Nachname</th>
                <th>Rang</th>
                <th>Status</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($officers)): ?>
                <tr>
                    <td colspan="6" style="text-align: center;">Keine Beamten in der Datenbank gefunden.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($officers as $officer): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($officer['badgeNumber']); ?></td>
                        <td><?php echo htmlspecialchars($officer['firstName']); ?></td>
                        <td><?php echo htmlspecialchars($officer['lastName']); ?></td>
                        <td><?php echo htmlspecialchars($officer['rank']); ?></td>
                        <td>
                            <span style="color: <?php echo $officer['isActive'] ? '#48bb78' : '#f56565'; ?>;">
                                <?php echo $officer['isActive'] ? 'Aktiv' : 'Inaktiv'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="index.php?page=edit_officer&id=<?php echo $officer['id']; ?>" class="button button-secondary">Bearbeiten</a>
                            <a href="index.php?page=edit_user_roles&officer_id=<?php echo $officer['id']; ?>" class="button button-secondary">Rollen</a>
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