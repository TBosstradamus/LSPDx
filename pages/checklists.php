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

// TODO: Add permission check for FTO/Admin

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Checklist.php';

// --- PAGE-SPECIFIC LOGIC ---
$checklistModel = new Checklist();
$checklists = $checklistModel->getAllChecklistsWithOfficerData();

/**
 * Calculates the completion percentage of a checklist.
 * @param string|null $content The content of the checklist with [x] for checked items.
 * @return int The percentage.
 */
function calculateChecklistProgress($content) {
    if (empty($content)) {
        return 0;
    }
    // Find all list items, checked or unchecked
    preg_match_all('/- \[.\]/m', $content, $totalItems);
    // Find only checked list items
    preg_match_all('/- \[x\]/m', $content, $completedItems);

    $total = count($totalItems[0]);
    $completed = count($completedItems[0]);

    if ($total === 0) {
        return 0;
    }

    return round(($completed / $total) * 100);
}

// --- TEMPLATE ---
$pageTitle = 'FTO Checklisten';
include_once BASE_PATH . '/templates/header.php';
?>
<style>
.progress-bar-container {
    width: 100%;
    background-color: #1a202c;
    border-radius: 4px;
    height: 20px;
}
.progress-bar {
    height: 100%;
    background-color: #48bb78;
    border-radius: 4px;
    text-align: center;
    color: white;
    font-weight: bold;
    line-height: 20px;
    transition: width 0.5s ease-in-out;
}
</style>

<!-- Start of page-specific content -->
<div style="display: flex; justify-content: space-between; align-items: center;">
    <p>Übersicht über den Ausbildungsfortschritt aller Beamten.</p>
    <a href="#" class="button" disabled>Vorlage bearbeiten (TODO)</a>
</div>

<?php if (isset($_GET['status']) && $_GET['status'] === 'checklist_updated'): ?>
    <div class="message-success">
        Die Checkliste wurde erfolgreich aktualisiert.
    </div>
<?php endif; ?>

<section id="checklist-overview">
    <h2>Checklisten-Fortschritt</h2>
    <table>
        <thead>
            <tr>
                <th>Beamter</th>
                <th>Rang</th>
                <th style="width: 30%;">Fortschritt</th>
                <th>Zuständiger FTO</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($checklists)): ?>
                <tr>
                    <td colspan="5" style="text-align: center;">Keine Beamten gefunden.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($checklists as $item):
                    $progress = calculateChecklistProgress($item['content']);
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['firstName'] . ' ' . $item['lastName']); ?></td>
                        <td><?php echo htmlspecialchars($item['rank']); ?></td>
                        <td>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: <?php echo $progress; ?>%;">
                                    <?php echo $progress; ?>%
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php echo $item['ftoFirstName'] ? htmlspecialchars($item['ftoFirstName'] . ' ' . $item['ftoLastName']) : '<span style="color:#a0aec0;">Nicht zugewiesen</span>'; ?>
                        </td>
                        <td>
                            <a href="index.php?page=edit_checklist&officer_id=<?php echo $item['officer_id']; ?>" class="button button-secondary">Bearbeiten</a>
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