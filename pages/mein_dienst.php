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
require_once BASE_PATH . '/src/TimeClock.php';
require_once BASE_PATH . '/src/License.php';

// --- PAGE-SPECIFIC LOGIC ---
$officerModel = new Officer();
$currentUser = $officerModel->findById($_SESSION['officer_id']);

$timeClockModel = new TimeClock();
$currentClockIn = $timeClockModel->getCurrentStatus($currentUser['id']);

$licenseModel = new License();
$licenses = $licenseModel->getForOfficer($currentUser['id']);

if (!$currentUser) {
    header('Location: index.php?page=logout');
    exit;
}

function formatDuration($totalSeconds) {
    if (!$totalSeconds || $totalSeconds < 0) return '0 Std. 0 Min.';
    $hours = floor($totalSeconds / 3600);
    $minutes = floor(($totalSeconds % 3600) / 60);
    return "{$hours} Std. {$minutes} Min.";
}

function getLicenseStatus($expiresAt) {
    $now = new DateTime();
    $expiryDate = new DateTime($expiresAt);
    $sevenDaysFromNow = (new DateTime())->modify('+7 days');

    if ($expiryDate < $now) {
        return ['text' => 'Abgelaufen', 'color' => '#f56565']; // red
    }
    if ($expiryDate < $sevenDaysFromNow) {
        return ['text' => 'Läuft bald ab', 'color' => '#ed8936']; // orange
    }
    return ['text' => 'Gültig', 'color' => '#48bb78']; // green
}

// --- TEMPLATE ---
$pageTitle = 'Mein Dienst';
include_once BASE_PATH . '/templates/header.php';
?>

<style>
    .widget-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem; }
    .widget { background-color: #2d3748; padding: 1.5rem; border-radius: 0.5rem; }
    .widget h3 { font-size: 1.25rem; font-weight: bold; color: #90cdf4; margin-top: 0; margin-bottom: 1rem; border-bottom: 1px solid #4a5568; padding-bottom: 0.5rem; }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .info-item span { color: #a0aec0; display: block; font-size: 0.9rem; }
    .info-item p { margin: 0; font-weight: bold; font-size: 1.1rem; }
    .timeclock-widget { text-align: center; }
    .timeclock-widget .running-time { font-size: 3rem; font-weight: bold; font-family: 'Courier New', Courier, monospace; color: #f7fafc; margin: 1rem 0; }
    .timeclock-widget .total-hours { font-size: 1.2rem; color: #cbd5e0; }
    .timeclock-widget .clock-button { width: 100%; margin-top: 1rem; padding: 1rem; font-size: 1.2rem; }
    .license-list { list-style: none; padding: 0; }
    .license-item { margin-bottom: 1rem; }
    .license-item .name { font-weight: bold; }
    .license-item .details { font-size: 0.9rem; color: #a0aec0; }
</style>

<p>Ihre persönliche Übersichtsseite für den Dienst.</p>

<div class="widget-grid">
    <div class="widget">
        <h3>Personalakte</h3>
        <div class="info-grid">
            <div class="info-item"><span>Name</span><p><?php echo htmlspecialchars($currentUser['firstName'] . ' ' . $currentUser['lastName']); ?></p></div>
            <div class="info-item"><span>Dienstnummer</span><p><?php echo htmlspecialchars($currentUser['badgeNumber']); ?></p></div>
            <div class="info-item"><span>Rang</span><p><?php echo htmlspecialchars($currentUser['rank']); ?></p></div>
            <div class="info-item"><span>Telefon</span><p><?php echo htmlspecialchars($currentUser['phoneNumber']); ?></p></div>
        </div>
    </div>

    <div class="widget timeclock-widget">
        <h3>Stempeluhr</h3>
        <?php if ($currentClockIn): ?>
            <p>Laufende Dienstzeit:</p>
            <div id="running-time-display" class="running-time">00:00:00</div>
            <form action="index.php?page=handle_clock_out" method="POST">
                <input type="hidden" name="record_id" value="<?php echo $currentClockIn['id']; ?>">
                <button type="submit" class="button button-danger clock-button">Ausstempeln</button>
            </form>
        <?php else: ?>
            <p>Sie sind aktuell nicht eingestempelt.</p>
            <div class="running-time">--:--:--</div>
            <form action="index.php?page=handle_clock_in" method="POST">
                <button type="submit" class="button clock-button">Einstempeln</button>
            </form>
        <?php endif; ?>
        <hr style="border-color: #4a5568; margin: 1.5rem 0;">
        <p>Gesamte Dienstzeit:</p>
        <p class="total-hours"><?php echo formatDuration($currentUser['totalHours']); ?></p>
    </div>

    <div class="widget">
        <h3>Lizenzen</h3>
        <?php if (empty($licenses)): ?>
            <p>Ihnen sind keine Lizenzen zugewiesen.</p>
        <?php else: ?>
            <ul class="license-list">
                <?php foreach($licenses as $license):
                    $status = getLicenseStatus($license['expiresAt']);
                ?>
                    <li class="license-item">
                        <div class="name"><?php echo htmlspecialchars($license['name']); ?></div>
                        <div class="details">
                            Gültig bis: <?php echo date('d.m.Y', strtotime($license['expiresAt'])); ?>
                            <span style="font-weight: bold; color: <?php echo $status['color']; ?>;">(<?php echo $status['text']; ?>)</span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<?php if ($currentClockIn): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const display = document.getElementById('running-time-display');
    const clockInTimestamp = new Date('<?php echo $currentClockIn['clockInTime']; ?>').getTime();

    function updateTimer() {
        const now = new Date().getTime();
        const duration = now - clockInTimestamp;
        const hours = String(Math.floor(duration / 3600000)).padStart(2, '0');
        const minutes = String(Math.floor((duration % 3600000) / 60000)).padStart(2, '0');
        const seconds = String(Math.floor((duration % 60000) / 1000)).padStart(2, '0');
        display.textContent = `${hours}:${minutes}:${seconds}`;
    }
    updateTimer();
    setInterval(updateTimer, 1000);
});
</script>
<?php endif; ?>


<?php
include_once BASE_PATH . '/templates/footer.php';
?>