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

// TODO: Add permission check here to ensure only authorized users (e.g., HR) can add officers.

// --- TEMPLATE ---
$pageTitle = 'Beamten hinzufügen';
include_once BASE_PATH . '/templates/header.php';

// We need the rank list from the old types.ts, let's define it here for now.
$ranks = [
  'Police Officer I', 'Police Officer II', 'Police Officer III', 'Detective',
  'Sergeant', 'Sr. Sergeant', 'Lieutenant', 'Captain', 'Commander',
  'Deputy Chief of Police', 'Assistant Chief of Police', 'Chief of Police',
];

?>

<style>
    .form-container {
        max-width: 800px;
        margin: 0 auto;
        background-color: #2d3748;
        padding: 2rem;
        border-radius: 0.5rem;
    }
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
    .form-group {
        margin-bottom: 1rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #a0aec0;
    }
    .form-group input, .form-group select {
        width: 100%;
        padding: 0.75rem;
        border-radius: 0.25rem;
        background-color: #1a202c;
        border: 1px solid #4a5568;
        color: #e2e8f0;
        box-sizing: border-box;
    }
    .form-actions {
        grid-column: 1 / -1; /* Span full width */
        margin-top: 1.5rem;
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
    }
</style>

<div class="form-container">
    <form action="index.php?page=handle_add_officer" method="POST">
        <div class="form-grid">
            <div class="form-group">
                <label for="firstName">Vorname</label>
                <input type="text" id="firstName" name="firstName" required>
            </div>
            <div class="form-group">
                <label for="lastName">Nachname</label>
                <input type="text" id="lastName" name="lastName" required>
            </div>
            <div class="form-group">
                <label for="badgeNumber">Dienstnummer</label>
                <input type="text" id="badgeNumber" name="badgeNumber" required>
            </div>
            <div class="form-group">
                <label for="phoneNumber">Telefonnummer</label>
                <input type="text" id="phoneNumber" name="phoneNumber">
            </div>
            <div class="form-group">
                <label for="rank">Rang</label>
                <select id="rank" name="rank" required>
                    <?php foreach ($ranks as $rank): ?>
                        <option value="<?php echo htmlspecialchars($rank); ?>"><?php echo htmlspecialchars($rank); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="gender">Geschlecht</label>
                <select id="gender" name="gender" required>
                    <option value="male">Männlich</option>
                    <option value="female">Weiblich</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <a href="index.php?page=hr" class="button button-secondary">Abbrechen</a>
            <button type="submit" class="button">Beamten speichern</button>
        </div>
    </form>
</div>


<?php
include_once BASE_PATH . '/templates/footer.php';
?>