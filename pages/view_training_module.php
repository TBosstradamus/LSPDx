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
require_once BASE_PATH . '/src/TrainingModule.php';

// --- PAGE-SPECIFIC LOGIC ---
$moduleId = $_GET['id'] ?? null;
if (!$moduleId) {
    header('Location: index.php?page=training_modules');
    exit;
}

$moduleModel = new TrainingModule();
$module = $moduleModel->findById($moduleId);

if (!$module) {
    header('Location: index.php?page=training_modules&error=not_found');
    exit;
}

// Re-use the simple markdown parser from the documents view
function simpleMarkdownToHtml($text) {
    $text = preg_replace('/^# (.*)$/m', '<h1>$1</h1>', $text);
    $text = preg_replace('/^## (.*)$/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^### (.*)$/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/(\*\*|__)(.*?)\1/', '<strong>$2</strong>', $text);
    $text = preg_replace('/(\*|_)(.*?)\1/', '<em>$2</em>', $text);
    $text = nl2br($text);
    $text = preg_replace('/^\* (.*)/m', '<ul><li>$1</li></ul>', $text);
    $text = preg_replace('/<\/ul>\s?<ul>/', '', $text);
    return $text;
}

$pageTitle = $module['title'];
include_once BASE_PATH . '/templates/header.php';
?>

<style>
    .document-view { background-color: #2d3748; padding: 2rem 3rem; border-radius: 0.5rem; }
    .document-view h1, .document-view h2, .document-view h3 { color: #90cdf4; border-bottom: 1px solid #4a5568; padding-bottom: 0.5rem; }
    .document-view p { line-height: 1.7; font-size: 1.1rem; }
    .document-view ul { padding-left: 20px; }
    .document-view li { margin-bottom: 0.5rem; }
    .document-actions { margin-top: 2rem; }
</style>

<div class="document-view">
    <?php echo simpleMarkdownToHtml(htmlspecialchars($module['content'])); ?>
</div>

<div class="document-actions">
    <a href="index.php?page=training_modules" class="button button-secondary">Zurück zur Übersicht</a>
</div>


<?php
include_once BASE_PATH . '/templates/footer.php';
?>