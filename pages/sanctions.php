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

// TODO: Add permission check for HR

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Sanction.php';

// --- PAGE-SPECIFIC LOGIC ---
$sanctionModel = new Sanction();
$sanctions = $sanctionModel->getAll();

// --- TEMPLATE ---
$pageTitle = 'Sanktionsverwaltung';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div style="display: flex; justify-content: space-between; align-items: center;">
    <p>Übersicht aller verhängten Sanktionen.</p>
    <a href="index.php?page=add_sanction" class="button">Sanktion hinzufügen</a>
</div>

<?php if (isset($_GET['status']) && $_GET['status'] === 'sanction_added'): ?>
    <div class="message-success">
        Die Sanktion wurde erfolgreich verhängt.
    </div>
<?php endif; ?>

<section id="sanction-list">
    <h2>Sanktionsliste</h2>
    <table>
        <thead>
            <tr>
                <th>Datum</th>
                <th>Betroffener Beamter</th>
                <th>Art der Sanktion</th>
                <th>Grund</th>
                <th>Ausgestellt von</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($sanctions)): ?>
                <tr>
                    <td colspan="5" style="text-align: center;">Keine Sanktionen gefunden.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($sanctions as $sanction): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($sanction['timestamp']))); ?></td>
                        <td><?php echo htmlspecialchars($sanction['officerFirstName'] . ' ' . $sanction['officerLastName']); ?></td>
                        <td><?php echo htmlspecialchars($sanction['sanctionType']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($sanction['reason'])); ?></td>
                        <td><?php echo htmlspecialchars($sanction['issuerFirstName'] . ' ' . $sanction['issuerLastName']); ?></td>
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