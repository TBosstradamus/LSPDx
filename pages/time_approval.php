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

// TODO: Replace with a more granular permission like 'hr_manage_time_logs'
requirePermission('hr_access');

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/TimePauseLog.php';

// --- PAGE-SPECIFIC LOGIC ---
$logModel = new TimePauseLog();
$logs = $logModel->getAll();

function formatDuration($seconds) {
    if ($seconds === null) return 'N/A';
    $m = floor($seconds / 60);
    $s = $seconds % 60;
    return "{$m} Min. {$s} Sek.";
}

// --- TEMPLATE ---
$pageTitle = 'Dienstzeit-Genehmigungen';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<p>Hier können Sie automatisch pausierte Dienstzeiten von Beamten genehmigen oder ablehnen. Abgelehnte Zeiten werden dem Beamten wieder gutgeschrieben.</p>

<?php if (isset($_GET['status'])): ?>
    <div class="message-success">
        Die Aktion wurde erfolgreich ausgeführt.
    </div>
<?php endif; ?>

<section id="log-list">
    <h2>Pausen-Protokoll</h2>
    <table>
        <thead>
            <tr>
                <th>Beamter</th>
                <th>Pausen-Start</th>
                <th>Dauer</th>
                <th>Grund</th>
                <th>Status</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="6" style="text-align: center;">Keine Einträge gefunden.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($log['firstName'] . ' ' . $log['lastName']); ?></td>
                        <td><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($log['pause_start_time']))); ?></td>
                        <td><?php echo formatDuration($log['duration']); ?></td>
                        <td><?php echo htmlspecialchars($log['reason']); ?></td>
                        <td>
                            <?php
                                $status = htmlspecialchars($log['status']);
                                if ($status === 'approved') echo '<span style="color:#48bb78;">Genehmigt</span>';
                                elseif ($status === 'rejected') echo '<span style="color:#f56565;">Abgelehnt</span>';
                                else echo '<span style="color:#ed8936;">Ausstehend</span>';
                            ?>
                        </td>
                        <td>
                            <?php if ($log['status'] === 'pending'): ?>
                                <form action="index.php?page=handle_time_approval" method="POST" style="display: inline;">
                                    <input type="hidden" name="log_id" value="<?php echo $log['id']; ?>">
                                    <button type="submit" name="action" value="approve" class="button">Genehmigen</button>
                                    <button type="submit" name="action" value="reject" class="button button-danger">Ablehnen</button>
                                </form>
                            <?php else: ?>
                                <span style="color: #a0aec0;">
                                    Geprüft von <?php echo htmlspecialchars($log['reviewerFirstName']); ?>
                                </span>
                            <?php endif; ?>
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