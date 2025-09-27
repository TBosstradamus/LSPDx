<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

// Ensure user is logged in & has permission
if (!isset($_SESSION['user_id']) || !isset($_SESSION['organization_id'])) {
    header('Location: index.php?page=login');
    exit;
}
require_once BASE_PATH . '/src/Auth.php';
Auth::requirePermission('hr_time_approve');

require_once BASE_PATH . '/src/TimePauseLog.php';

$logModel = new TimePauseLog($_SESSION['organization_id']);
$logs = $logModel->getAll();

function formatDuration($seconds) {
    if ($seconds === null) return 'N/A';
    $m = floor($seconds / 60);
    $s = $seconds % 60;
    return "{$m} Min. {$s} Sek.";
}

$pageTitle = 'Dienstzeit Genehmigung';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-white"><?php echo $pageTitle; ?></h1>
        <p class="mt-1 text-brand-text-secondary">Genehmigen oder lehnen Sie automatisch erfasste Inaktivitäts-Zeiten von Beamten ab.</p>
    </div>
</div>

<?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
    <div class="bg-green-500/20 border border-green-500 text-green-300 p-4 rounded-lg mb-6">
        Die Aktion wurde erfolgreich ausgeführt.
    </div>
<?php endif; ?>

<div class="bg-brand-card border border-brand-border rounded-lg shadow">
    <div class="divide-y divide-brand-border">
        <?php if (empty($logs)): ?>
            <div class="p-6 text-center text-brand-text-secondary">
                Keine ausstehenden Genehmigungen gefunden.
            </div>
        <?php else: ?>
            <?php foreach ($logs as $log): ?>
                <div class="p-4 grid grid-cols-6 gap-4 items-center">
                    <div class="col-span-1">
                        <div class="text-xs text-brand-text-secondary">Beamter</div>
                        <div class="text-sm font-medium text-white"><?php echo htmlspecialchars($log['firstName'] . ' ' . $log['lastName']); ?></div>
                    </div>
                    <div class="col-span-1">
                        <div class="text-xs text-brand-text-secondary">Beginn</div>
                        <div class="text-sm text-brand-text-primary"><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($log['pause_start_time']))); ?></div>
                    </div>
                    <div class="col-span-1">
                        <div class="text-xs text-brand-text-secondary">Dauer</div>
                        <div class="text-sm text-brand-text-primary"><?php echo formatDuration($log['duration']); ?></div>
                    </div>
                    <div class="col-span-1">
                        <div class="text-xs text-brand-text-secondary">Status</div>
                        <div class="text-sm">
                            <?php
                                $status = htmlspecialchars($log['status']);
                                if ($status === 'approved') echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-500/20 text-green-300">Genehmigt</span>';
                                elseif ($status === 'rejected') echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-500/20 text-red-300">Abgelehnt</span>';
                                else echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-500/20 text-yellow-300">Ausstehend</span>';
                            ?>
                        </div>
                    </div>
                    <div class="col-span-2 text-right">
                        <?php if ($log['status'] === 'pending'): ?>
                            <form action="index.php?page=handle_time_approval" method="POST" class="inline-flex space-x-2">
                                <input type="hidden" name="log_id" value="<?php echo $log['id']; ?>">
                                <button type="submit" name="action" value="approve" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-3 rounded-lg text-sm">Genehmigen</button>
                                <button type="submit" name="action" value="reject" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-3 rounded-lg text-sm">Ablehnen</button>
                            </form>
                        <?php else: ?>
                            <span class="text-sm text-brand-text-secondary">
                                Geprüft von <?php echo htmlspecialchars($log['reviewerFirstName'] ?? 'Unbekannt'); ?>
                            </span>
                        <?php endif; ?>
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