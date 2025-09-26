<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login'); exit;
}
// requirePermission('view_documents');

require_once BASE_PATH . '/src/Document.php';

$docId = $_GET['id'] ?? null;
if (!$docId) {
    header('Location: index.php?page=documents'); exit;
}

$documentModel = new Document();
$document = $documentModel->findById($docId);

if (!$document) {
    header('Location: index.php?page=documents&error=not_found'); exit;
}

function simpleMarkdownToHtml($text) {
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    // Headers
    $text = preg_replace('/^# (.*)$/m', '<h1 class="text-3xl font-bold text-white mt-6 mb-4">$1</h1>', $text);
    $text = preg_replace('/^## (.*)$/m', '<h2 class="text-2xl font-bold text-white mt-5 mb-3">$1</h2>', $text);
    $text = preg_replace('/^### (.*)$/m', '<h3 class="text-xl font-bold text-white mt-4 mb-2">$1</h3>', $text);
    // Bold
    $text = preg_replace('/(\*\*|__)(.*?)\1/', '<strong>$2</strong>', $text);
    // Italic
    $text = preg_replace('/(\*|_)(.*?)\1/', '<em>$2</em>', $text);
    // List items
    $text = preg_replace('/^- (.*)$/m', '<li class="ml-4">$1</li>', $text);
    $text = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $text);
    $text = preg_replace('/<\/ul>\s*<ul>/', '', $text);
    // Paragraphs
    $text = '<p>' . preg_replace('/\n(\s*\n)+/', '</p><p>', $text) . '</p>';
    $text = preg_replace('/<p><(h[1-3]|ul)>/', '<$1>', $text);
    $text = preg_replace('/<\/(h[1-3]|ul)><\/p>/', '</$1>', $text);

    return $text;
}

$pageTitle = $document['title'];
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="max-w-4xl mx-auto">
    <div class="bg-gray-800 rounded-lg shadow-lg">
        <div class="prose prose-invert prose-lg p-6 md:p-8">
            <?php echo simpleMarkdownToHtml($document['content']); ?>
        </div>
    </div>
    <div class="mt-6">
        <a href="index.php?page=documents" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">
            Zurück zur Übersicht
        </a>
    </div>
</div>
<!-- End of page-specific content -->

<?php
include_once BASE_PATH . '/templates/footer.php';
?>