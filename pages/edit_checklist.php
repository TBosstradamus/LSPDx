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
require_once BASE_PATH . '/src/Officer.php';

// --- PAGE-SPECIFIC LOGIC ---
$officerId = $_GET['officer_id'] ?? null;
if (!$officerId) {
    header('Location: index.php?page=checklists');
    exit;
}

$officerModel = new Officer();
$officer = $officerModel->findById($officerId);
// For the FTO dropdown, we could filter by officers in the 'Field Training Officer' department in a real scenario
$ftos = $officerModel->getAll();

$checklistModel = new Checklist();
$checklist = $checklistModel->getForOfficer($officerId);

if (!$officer || !$checklist) {
    header('Location: index.php?page=checklists&error=not_found');
    exit;
}

$pageTitle = 'Checkliste bearbeiten: ' . htmlspecialchars($officer['firstName'] . ' ' . $officer['lastName']);
include_once BASE_PATH . '/templates/header.php';
?>

<style>
    .form-container { max-width: 900px; margin: 0 auto; background-color: #2d3748; padding: 2rem; border-radius: 0.5rem; }
    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; margin-bottom: 0.5rem; color: #a0aec0; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border-radius: 0.25rem; background-color: #1a202c; border: 1px solid #4a5568; color: #e2e8f0; box-sizing: border-box; font-family: inherit; }
    .form-group textarea { min-height: 300px; line-height: 1.6; font-family: 'Courier New', Courier, monospace; }
    .form-actions { margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 1rem; }
</style>

<div class="form-container">
    <form action="index.php?page=handle_edit_checklist" method="POST">
        <input type="hidden" name="officer_id" value="<?php echo $officer['id']; ?>">

        <div class="form-group">
            <label for="content">Checklisten-Inhalt</label>
            <p style="font-size: 0.9rem; color: #a0aec0; margin-top: -0.5rem; margin-bottom: 0.5rem;">Setzen Sie ein 'x' in die Klammern, um einen Punkt abzuhaken (z.B. `[x]`)</p>
            <textarea id="content" name="content" required><?php echo htmlspecialchars($checklist['content']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="notes">FTO Notizen</label>
            <textarea id="notes" name="notes"><?php echo htmlspecialchars($checklist['notes']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="assigned_fto_id">Zuständiger FTO</label>
            <select id="assigned_fto_id" name="assigned_fto_id" required>
                <option value="">-- FTO auswählen --</option>
                <?php foreach ($ftos as $fto): ?>
                    <option value="<?php echo $fto['id']; ?>" <?php echo ($checklist['assigned_fto_id'] == $fto['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($fto['firstName'] . ' ' . $fto['lastName']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-actions">
            <a href="index.php?page=checklists" class="button button-secondary">Abbrechen</a>
            <button type="submit" class="button">Checkliste speichern</button>
        </div>
    </form>
</div>

<?php
include_once BASE_PATH . '/templates/footer.php';
?>