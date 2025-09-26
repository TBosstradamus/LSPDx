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

// TODO: Add permission check for Admin

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Logger.php';

// --- PAGE-SPECIFIC LOGIC ---
$logs = Logger::getAll();

// --- TEMPLATE ---
$pageTitle = 'IT-Systemprotokolle';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<p>Hier werden alle relevanten Systemereignisse protokolliert.</p>

<section id="log-list">
    <h2>Protokollliste</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 160px;">Datum & Uhrzeit</th>
                <th style="width: 180px;">Akteur</th>
                <th style="width: 180px;">Ereignistyp</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">Keine Protokolleinträge gefunden.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(date('d.m.Y H:i:s', strtotime($log['timestamp']))); ?></td>
                        <td>
                            <?php
                            if ($log['firstName']) {
                                echo htmlspecialchars($log['firstName'] . ' ' . $log['lastName']);
                            } else {
                                echo '<span style="color:#a0aec0;">System/Unbekannt</span>';
                            }
                            ?>
                        </td>
                        <td><code><?php echo htmlspecialchars($log['eventType']); ?></code></td>
                        <td><?php echo htmlspecialchars($log['details']); ?></td>
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