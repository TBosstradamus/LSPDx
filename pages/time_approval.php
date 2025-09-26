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

// requirePermission('hr_time_approve'); // Will be enforced later

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

$pageTitle = 'Dienstzeit-Genehmigungen';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - LSPD Intranet</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
        <p>Hier können Sie automatisch erfasste Inaktivitäts-Zeiten von Beamten genehmigen oder ablehnen. Bei Ablehnung wird die Zeit von der Gesamtdienstzeit des Beamten abgezogen.</p>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="message-success">Die Aktion wurde erfolgreich ausgeführt.</div>
        <?php endif; ?>

        <section id="log-list">
            <h2>Protokoll der Inaktivität</h2>
            <table>
                <thead>
                    <tr>
                        <th>Beamter</th>
                        <th>Beginn der Inaktivität</th>
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
                                            Geprüft von <?php echo htmlspecialchars($log['reviewerFirstName'] ?? 'Unbekannt'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
        <br>
        <a href="index.php?page=hr">Zurück zur Personalabteilung</a>
    </div>
</body>
</html>