<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login'); exit;
}
require_once BASE_PATH . '/src/Auth.php';
Auth::requirePermission('system_logs_view');

require_once BASE_PATH . '/src/Log.php';
$logs = Log::getAll();

$pageTitle = 'IT-Systemprotokolle';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-white"><?php echo $pageTitle; ?></h1>
        <p class="mt-1 text-brand-text-secondary">Überwachen Sie alle wichtigen Aktionen, die im System durchgeführt werden.</p>
    </div>
</div>

<div class="bg-brand-card border border-brand-border rounded-lg shadow">
    <div class="divide-y divide-brand-border">
        <?php if (empty($logs)): ?>
            <div class="p-6 text-center text-brand-text-secondary">
                Keine Protokolleinträge gefunden.
            </div>
        <?php else: ?>
            <?php foreach ($logs as $log): ?>
                <div class="p-4 hover:bg-gray-800/50">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-brand-bg flex items-center justify-center">
                                <svg class="h-6 w-6 text-brand-text-secondary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-grow">
                            <div class="text-sm font-medium text-white"><?php echo htmlspecialchars($log['details']); ?></div>
                            <div class="text-xs text-brand-text-secondary">
                                <span class="font-mono"><?php echo htmlspecialchars($log['event_type']); ?></span> |
                                Akteur: <span class="font-semibold"><?php echo htmlspecialchars($log['actor']); ?></span> |
                                Zeit: <?php echo htmlspecialchars(date('d.m.Y H:i:s', strtotime($log['timestamp']))); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<!-- End of page-specific content -->

<?php
include_once BASE_PATH . '/templates/footer.php';
?>