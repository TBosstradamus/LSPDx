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
requirePermission('training_access');

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/TrainingModule.php';

// --- PAGE-SPECIFIC LOGIC ---
$moduleModel = new TrainingModule();
$modules = $moduleModel->getAll();

// --- TEMPLATE ---
$pageTitle = 'Trainings-Module';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div style="display: flex; justify-content: space-between; align-items: center;">
    <p>Verwalten Sie hier alle Trainings-Module für das LSPD.</p>
    <a href="index.php?page=add_training_module" class="button">Modul hinzufügen</a>
</div>

<?php if (isset($_GET['status'])): ?>
    <div class="message-success">
        <?php
        if ($_GET['status'] === 'module_added') echo 'Das Modul wurde erfolgreich hinzugefügt.';
        if ($_GET['status'] === 'module_deleted') echo 'Das Modul wurde erfolgreich gelöscht.';
        ?>
    </div>
<?php endif; ?>

<section id="module-list">
    <h2>Modul-Liste</h2>
    <table>
        <thead>
            <tr>
                <th>Titel</th>
                <th>Erstellt von</th>
                <th>Erstellt am</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($modules)): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">Keine Module gefunden.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($modules as $module): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($module['title']); ?></td>
                        <td><?php echo htmlspecialchars($module['firstName'] . ' ' . $module['lastName']); ?></td>
                        <td><?php echo htmlspecialchars(date('d.m.Y', strtotime($module['created_at']))); ?></td>
                        <td>
                            <a href="index.php?page=view_training_module&id=<?php echo $module['id']; ?>" class="button button-secondary">Anzeigen</a>
                            <form action="index.php?page=handle_delete_training_module" method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $module['id']; ?>">
                                <button type="submit" class="button button-danger" onclick="return confirm('Sind Sie sicher, dass Sie dieses Modul löschen möchten?');">
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