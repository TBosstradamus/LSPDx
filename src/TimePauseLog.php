<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class TimePauseLog {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Gets all pause log entries.
     * @return array
     */
    public function getAll() {
        try {
            $sql = "SELECT
                        tpl.*,
                        o.firstName, o.lastName, o.badgeNumber,
                        r.firstName as reviewerFirstName, r.lastName as reviewerLastName
                    FROM time_pause_log tpl
                    JOIN officers o ON tpl.officer_id = o.id
                    LEFT JOIN officers r ON tpl.reviewed_by_id = r.id
                    ORDER BY tpl.status = 'pending' DESC, tpl.pause_start_time DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching pause logs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Approves a pause log entry. The time counts as regular duty time.
     * @param int $logId
     * @param int $reviewerId
     * @return bool
     */
    public function approve($logId, $reviewerId) {
        return $this->updateStatus($logId, 'approved', $reviewerId);
    }

    /**
     * Rejects a pause log entry. The paused time is subtracted from the officer's total hours.
     * @param int $logId
     * @param int $reviewerId
     * @return bool
     */
    public function reject($logId, $reviewerId) {
        $this->db->beginTransaction();
        try {
            $logStmt = $this->db->prepare("SELECT officer_id, duration FROM time_pause_log WHERE id = ?");
            $logStmt->execute([$logId]);
            $log = $logStmt->fetch();

            if (!$log || !$log['duration']) {
                throw new Exception("Log entry or duration not found for rejection.");
            }

            // Subtract the duration from the officer's totalHours
            $officerStmt = $this->db->prepare("UPDATE officers SET totalHours = totalHours - ? WHERE id = ?");
            $officerStmt->execute([$log['duration'], $log['officer_id']]);

            // Update the log status
            $this->updateStatus($logId, 'rejected', $reviewerId);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error rejecting pause log: " . $e->getMessage());
            return false;
        }
    }

    private function updateStatus($logId, $status, $reviewerId) {
        try {
            $sql = "UPDATE time_pause_log
                    SET status = ?, reviewed_by_id = ?, reviewed_at = NOW()
                    WHERE id = ? AND status = 'pending'";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$status, $reviewerId, $logId]);
        } catch (PDOException $e) {
            error_log("Error updating pause log status: " . $e->getMessage());
            return false;
        }
    }
}
?>