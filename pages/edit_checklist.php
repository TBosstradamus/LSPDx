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
requirePermission('fto_manage_checklists');

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
$ftos = $officerModel->getAll();

$checklistModel = new Checklist();
$checklist = $checklistModel->getForOfficer($officerId);

if (!$officer || !$checklist) {
    header('Location: index.php?page=checklists&error=not_found');
    exit;
}

/**
 * Parses checklist content into an array of structured items.
 * @param string $content
 * @return array
 */
function parseChecklistContent($content) {
    $lines = explode("\n", $content);
    $items = [];
    foreach ($lines as $index => $line) {
        $line = trim($line);
        if (preg_match('/^- \[([ x])\] (.*)/', $line, $matches)) {
            $items[] = [
                'type' => 'task',
                'checked' => $matches[1] === 'x',
                'text' => trim($matches[2]),
                'original_index' => $index
            ];
        } elseif (preg_match('/^# (.*)/', $line, $matches)) {
            $items[] = ['type' => 'heading', 'text' => trim($matches[1]), 'original_index' => $index];
        } else {
            $items[] = ['type' => 'text', 'text' => $line, 'original_index' => $index];
        }
    }
    return $items;
}

$checklistItems = parseChecklistContent($checklist['content']);

$pageTitle = 'Checkliste bearbeiten: ' . htmlspecialchars($officer['firstName'] . ' ' . $officer['lastName']);
include_once BASE_PATH . '/templates/header.php';
?>

<style>
    .form-container { max-width: 900px; margin: 0 auto; background-color: #2d3748; padding: 2rem; border-radius: 0.5rem; }
    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; margin-bottom: 0.5rem; color: #a0aec0; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border-radius: 0.25rem; background-color: #1a202c; border: 1px solid #4a5568; color: #e2e8f0; box-sizing: border-box; font-family: inherit; }
    .form-group textarea { min-height: 200px; line-height: 1.6; }
    .form-actions { margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 1rem; }
    .checklist-items { border: 1px solid #4a5568; padding: 1rem; border-radius: 0.25rem; background-color: #1a202c; }
    .checklist-item { display: flex; align-items: center; margin-bottom: 0.5rem; }
    .checklist-item input[type="checkbox"] { width: 20px; height: 20px; margin-right: 1rem; }
    .checklist-item label { color: #e2e8f0; margin: 0; }
    .checklist-heading { color: #90cdf4; font-size: 1.2rem; font-weight: bold; margin-top: 1rem; margin-bottom: 0.5rem; border-bottom: 1px solid #4a5568; padding-bottom: 0.25rem; }
    .checklist-text { color: #a0aec0; margin-bottom: 0.5rem; }
</style>

<div class="form-container">
    <form action="index.php?page=handle_edit_checklist" method="POST">
        <input type="hidden" name="officer_id" value="<?php echo $officer['id']; ?>">

        <div class="form-group">
            <label>Checklisten-Inhalt</label>
            <div class="checklist-items">
                <?php foreach ($checklistItems as $index => $item): ?>
                    <input type="hidden" name="items[<?php echo $index; ?>][type]" value="<?php echo $item['type']; ?>">
                    <input type="hidden" name="items[<?php echo $index; ?>][text]" value="<?php echo htmlspecialchars($item['text']); ?>">

                    <?php if ($item['type'] === 'task'): ?>
                        <div class="checklist-item">
                            <input type="checkbox" name="items[<?php echo $index; ?>][checked]" value="1" id="item-<?php echo $index; ?>" <?php echo $item['checked'] ? 'checked' : ''; ?>>
                            <label for="item-<?php echo $index; ?>"><?php echo htmlspecialchars($item['text']); ?></label>
                        </div>
                    <?php elseif ($item['type'] === 'heading'): ?>
                        <h4 class="checklist-heading"><?php echo htmlspecialchars($item['text']); ?></h4>
                    <?php else: ?>
                        <p class="checklist-text"><?php echo htmlspecialchars($item['text']); ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-group">
            <label for="notes">FTO Notizen</label>
            <textarea id="notes" name="notes"><?php echo htmlspecialchars($checklist['notes'] ?? ''); ?></textarea>
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