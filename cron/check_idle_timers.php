<?php
// This script is intended to be run as a cron job every 5 minutes.
// It checks for idle officers across all organizations and pauses their time clock.

// Set a long execution time limit for cron jobs
set_time_limit(300);

// Set the base path assuming the cron folder is in the root directory
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/src/Database.php';
require_once BASE_PATH . '/src/TimeClock.php';
require_once BASE_PATH . '/src/Logger.php';

echo "Cron Job: Checking for idle officers...\n";

$db = Database::getInstance()->getConnection();

// --- Main Logic ---

// 1. Get all organizations
$orgsStmt = $db->query("SELECT id FROM organizations");
$organizations = $orgsStmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($organizations as $orgId) {
    echo "Processing Organization ID: {$orgId}\n";

    // Temporarily set session for the class constructors
    $_SESSION['organization_id'] = $orgId;

    $timeClockModel = new TimeClock();

    // 2. Get all unassigned, clocked-in, non-paused officers for this organization
    $sql = "SELECT o.id, o.last_assignment_time, tt.id as time_tracking_id, tt.clockInTime
            FROM officers o
            JOIN time_tracking tt ON o.id = tt.officer_id
            WHERE o.organization_id = ?
              AND o.isActive = TRUE
              AND tt.clockOutTime IS NULL
              AND tt.is_paused = FALSE
              AND o.id NOT IN (SELECT officer_id FROM dispatch_assignments WHERE organization_id = ?)";

    $idleCheckStmt = $db->prepare($sql);
    $idleCheckStmt->execute([$orgId, $orgId]);
    $idleOfficers = $idleCheckStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($idleOfficers)) {
        echo " -> No idle officers found.\n";
        continue;
    }

    echo " -> Found " . count($idleOfficers) . " idle officers to check.\n";

    foreach ($idleOfficers as $officer) {
        $officerId = $officer['id'];

        // Use the officer's last assignment time, or fall back to their clock-in time
        $startTime = $officer['last_assignment_time'] ?? $officer['clockInTime'];
        $idleSeconds = time() - strtotime($startTime);
        $idleMinutes = floor($idleSeconds / 60);

        echo "  -> Checking Officer ID {$officerId}: Idle for {$idleMinutes} minutes.\n";

        // 3. If idle for more than 15 minutes (900 seconds), pause them.
        if ($idleSeconds > 900) {
            echo "    --> Pausing officer {$officerId} due to inactivity.\n";

            $timeTrackingId = $officer['time_tracking_id'];

            // Pause the time clock
            $timeClockModel->pause($timeTrackingId);

            // Create a log entry for the pause that needs approval
            $pauseStmt = $db->prepare("
                INSERT INTO time_pause_log (officer_id, time_tracking_id, pause_start_time, duration, reason)
                VALUES (?, ?, ?, ?, 'Automatische Pause wegen Inaktivität')");
            // We set the start time to 15 minutes ago, and the duration to the time since then.
            $pauseStartTime = date('Y-m-d H:i:s', time() - $idleSeconds + 900);
            $pauseDuration = $idleSeconds - 900;
            $pauseStmt->execute([$officerId, $timeTrackingId, $pauseStartTime, $pauseDuration]);

            // Log this system event
            Logger::log('idle_timer_pause', "Zeiterfassung für Beamten-ID {$officerId} wurde automatisch wegen Inaktivität pausiert.", null, ['organization_id' => $orgId]);
        }
    }
}

// Unset session variable after use
unset($_SESSION['organization_id']);

echo "Cron job finished.\n";
?>