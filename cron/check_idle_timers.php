<?php
// This script is intended to be run as a cron job every 5 minutes.
// It checks for idle officers and creates a pause log entry for HR approval.

set_time_limit(300);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/src/Database.php';

echo "Cron Job: Checking for idle officers...\n";

$db = Database::getInstance()->getConnection();

// --- Main Logic ---

// Get all unassigned, clocked-in officers
$sql = "SELECT o.id, o.last_assignment_time, tt.id as time_tracking_id
        FROM officers o
        JOIN time_tracking tt ON o.id = tt.officer_id
        WHERE o.isActive = TRUE
          AND tt.clockOutTime IS NULL
          AND o.id NOT IN (SELECT officer_id FROM dispatch_assignments)";

$idleCheckStmt = $db->prepare($sql);
$idleCheckStmt->execute();
$idleOfficers = $idleCheckStmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($idleOfficers)) {
    echo "No idle officers found. Exiting.\n";
    exit;
}

echo "Found " . count($idleOfficers) . " idle officers to check.\n";

foreach ($idleOfficers as $officer) {
    $officerId = $officer['id'];
    $lastAssignmentTime = $officer['last_assignment_time'];

    // If last assignment time is null, use clock-in time as the start of the idle period
    if ($lastAssignmentTime === null) {
        $clockInStmt = $db->prepare("SELECT clockInTime FROM time_tracking WHERE id = ?");
        $clockInStmt->execute([$officer['time_tracking_id']]);
        $lastAssignmentTime = $clockInStmt->fetchColumn();
    }

    $idleSeconds = time() - strtotime($lastAssignmentTime);
    $idleMinutes = floor($idleSeconds / 60);

    echo "  -> Checking Officer ID {$officerId}: Idle for {$idleMinutes} minutes.\n";

    // If idle for more than 15 minutes (900 seconds), log it.
    if ($idleSeconds > 900) {
        echo "    --> Logging idle time for officer {$officerId}.\n";

        $db->beginTransaction();
        try {
            // Create a log entry for the pause that needs approval
            $pauseStmt = $db->prepare("
                INSERT INTO time_pause_log (officer_id, time_tracking_id, pause_start_time, duration, reason)
                VALUES (?, ?, ?, ?, 'Automatische Erfassung wegen Inaktivität > 15 Min.')");
            $pauseStmt->execute([
                $officerId,
                $officer['time_tracking_id'],
                $lastAssignmentTime,
                $idleSeconds
            ]);

            // IMPORTANT: Update the last_assignment_time to NOW() to reset the timer
            $updateStmt = $db->prepare("UPDATE officers SET last_assignment_time = NOW() WHERE id = ?");
            $updateStmt->execute([$officerId]);

            $db->commit();

            echo "      --> Logged successfully.\n";

        } catch (Exception $e) {
            $db->rollBack();
            error_log("Failed to log idle time for officer {$officerId}: " . $e->getMessage());
        }
    }
}

echo "Cron job finished.\n";
?>