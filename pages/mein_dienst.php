<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}

// Ensure user is authenticated
if (!isset($_SESSION['user_id']) || !isset($_SESSION['officer_id']) || !isset($_SESSION['organization_id'])) {
    header('Location: index.php?page=login'); exit;
}

require_once BASE_PATH . '/src/Officer.php';
require_once BASE_PATH . '/src/TimeClock.php';

$officerModel = new Officer($_SESSION['organization_id']);
$currentUser = $officerModel->findByIdInOrg($_SESSION['officer_id']);

if (!$currentUser) {
    error_log("Could not find officer with ID {$_SESSION['officer_id']} in organization {$_SESSION['organization_id']}");
    header('Location: index.php?page=logout'); exit;
}

try {
    $timeClockModel = new TimeClock($currentUser['organization_id']);
    $currentClockIn = $timeClockModel->getCurrentStatus($currentUser['id']);
} catch (InvalidArgumentException $e) {
    error_log($e->getMessage());
    die("Ein kritischer Fehler ist aufgetreten. Die Stempeluhr-Funktion ist nicht verfügbar.");
}

function formatDuration($totalSeconds) {
    if (!$totalSeconds || $totalSeconds < 0) return '0 Std. 0 Min.';
    $hours = floor($totalSeconds / 3600);
    $minutes = floor(($totalSeconds % 3600) / 60);
    return "{$hours} Std. {$minutes} Min.";
}

$pageTitle = 'Mein Dienst';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left Column -->
    <div class="lg:col-span-2 space-y-8">
        <!-- Officer Details Card -->
        <div class="bg-brand-card border border-brand-border rounded-lg shadow">
            <div class="p-6">
                <h3 class="text-xl font-bold text-white mb-4">Ihre Personalakte</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-sm font-medium text-brand-text-secondary">Name</dt>
                        <dd class="mt-1 text-lg text-white"><?php echo htmlspecialchars($currentUser['firstName'] . ' ' . $currentUser['lastName']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-brand-text-secondary">Dienstnummer</dt>
                        <dd class="mt-1 text-lg text-white">#<?php echo htmlspecialchars($currentUser['badgeNumber']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-brand-text-secondary">Rang</dt>
                        <dd class="mt-1 text-lg text-white"><?php echo htmlspecialchars($currentUser['rank']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-brand-text-secondary">Telefonnummer</dt>
                        <dd class="mt-1 text-lg text-white"><?php echo htmlspecialchars($currentUser['phoneNumber'] ?? 'N/A'); ?></dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Licenses Card -->
        <div class="bg-brand-card border border-brand-border rounded-lg shadow">
            <div class="p-6">
                <h3 class="text-xl font-bold text-white mb-4">Ihre Lizenzen</h3>
                <p class="text-brand-text-secondary">Diese Funktion wird in einem zukünftigen Update implementiert.</p>
            </div>
        </div>
    </div>

    <!-- Right Column (Time Clock) -->
    <div class="lg:col-span-1">
        <div class="bg-brand-card border border-brand-border rounded-lg shadow p-6 text-center">
            <h3 class="text-xl font-bold text-white mb-4">Stempeluhr</h3>
            <?php if ($currentClockIn): ?>
                <p class="text-brand-text-secondary">Aktuelle Dienstzeit:</p>
                <div id="running-time-display" class="text-5xl font-mono font-bold text-white my-4">00:00:00</div>
                <form action="index.php?page=handle_clock_out" method="POST">
                    <input type="hidden" name="record_id" value="<?php echo $currentClockIn['id']; ?>">
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg text-lg transition-colors">
                        Ausstempeln
                    </button>
                </form>
            <?php else: ?>
                <p class="text-brand-text-secondary">Sie sind aktuell nicht im Dienst.</p>
                <div class="text-5xl font-mono font-bold text-gray-600 my-4">--:--:--</div>
                 <form action="index.php?page=handle_clock_in" method="POST">
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg text-lg transition-colors">
                        Einstempeln
                    </button>
                </form>
            <?php endif; ?>
            <div class="mt-6 border-t border-brand-border pt-4">
                <p class="text-sm font-medium text-brand-text-secondary">Gesamte Dienstzeit</p>
                <p class="text-2xl font-bold text-white mt-1"><?php echo formatDuration($currentUser['totalHours']); ?></p>
            </div>
        </div>
    </div>
</div>

<?php if ($currentClockIn): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const display = document.getElementById('running-time-display');
    // Use PHP to format the timestamp into a standard ISO 8601 string that JS can reliably parse.
    const isoTimestamp = '<?php echo date('c', strtotime($currentClockIn['clockInTime'])); ?>';
    const clockInTimestamp = new Date(isoTimestamp).getTime();

    if (isNaN(clockInTimestamp)) {
        console.error("Invalid clock-in timestamp received:", '<?php echo $currentClockIn['clockInTime']; ?>');
        display.textContent = 'Error';
        return;
    }

    function updateTimer() {
        const now = new Date().getTime();
        const duration = now - clockInTimestamp;

        if (duration < 0) return;

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

<!-- End of page-specific content -->
<?php
include_once BASE_PATH . '/templates/footer.php';
?>