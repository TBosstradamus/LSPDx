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

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Officer.php';

// --- PAGE-SPECIFIC LOGIC ---
$officerModel = new Officer();
$officers = $officerModel->getAll();

// --- TEMPLATE ---
$pageTitle = 'E-Mail verfassen';
include_once BASE_PATH . '/templates/header.php';
?>

<style>
    .form-container { max-width: 900px; margin: 0 auto; background-color: #2d3748; padding: 2rem; border-radius: 0.5rem; }
    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; margin-bottom: 0.5rem; color: #a0aec0; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border-radius: 0.25rem; background-color: #1a202c; border: 1px solid #4a5568; color: #e2e8f0; box-sizing: border-box; font-family: inherit; }
    .form-group select[multiple] { height: 150px; }
    .form-group textarea { min-height: 300px; line-height: 1.6; }
    .form-actions { margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 1rem; }
</style>

<div class="form-container">
    <form action="index.php?page=handle_compose_email" method="POST">

        <div class="form-group">
            <label for="recipients">An</label>
            <select id="recipients" name="recipients[]" multiple required>
                <?php foreach ($officers as $officer): ?>
                    <option value="<?php echo $officer['id']; ?>">
                        <?php echo htmlspecialchars($officer['lastName'] . ', ' . $officer['firstName'] . ' (#' . $officer['badgeNumber'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="cc_recipients">CC</label>
            <select id="cc_recipients" name="cc_recipients[]" multiple>
                <?php foreach ($officers as $officer): ?>
                    <option value="<?php echo $officer['id']; ?>">
                        <?php echo htmlspecialchars($officer['lastName'] . ', ' . $officer['firstName'] . ' (#' . $officer['badgeNumber'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="subject">Betreff</label>
            <input type="text" id="subject" name="subject" required>
        </div>

        <div class="form-group">
            <label for="body">Nachricht</label>
            <textarea id="body" name="body" required></textarea>
        </div>

        <div class="form-actions">
            <a href="index.php?page=mailbox" class="button button-secondary">Abbrechen</a>
            <button type="submit" class="button">Senden</button>
        </div>
    </form>
</div>

<?php
include_once BASE_PATH . '/templates/footer.php';
?>