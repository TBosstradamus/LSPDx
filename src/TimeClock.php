<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class TimeClock {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Gets the current clock-in status for a specific officer.
     * @param int $officerId
     * @return array|false The active clock-in record or false if not clocked in.
     */
    public function getCurrentStatus($officerId) {
        try {
            $sql = "SELECT * FROM time_tracking WHERE officer_id = :officer_id AND clockOutTime IS NULL ORDER BY clockInTime DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':officer_id', $officerId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting clock status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clocks in an officer.
     * @param int $officerId
     * @return bool
     */
    public function clockIn($officerId) {
        // Prevent clocking in if already clocked in
        if ($this->getCurrentStatus($officerId)) {
            return false;
        }

        try {
            $sql = "INSERT INTO time_tracking (officer_id, clockInTime) VALUES (:officer_id, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':officer_id', $officerId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error clocking in: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clocks out an officer and updates their total hours.
     * @param int $officerId
     * @param int $clockInRecordId The ID of the time_tracking record to close.
     * @return bool
     */
    public function clockOut($officerId, $clockInRecordId) {
        $this->db->beginTransaction();
        try {
            // Step 1: Update the clock-out time and duration in time_tracking
            $sql = "UPDATE time_tracking
                    SET clockOutTime = NOW(),
                        duration = TIMESTAMPDIFF(SECOND, clockInTime, NOW())
                    WHERE id = :id AND officer_id = :officer_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $clockInRecordId, PDO::PARAM_INT);
            $stmt->bindParam(':officer_id', $officerId, PDO::PARAM_INT);
            $stmt->execute();

            // Get the duration of the just-ended session
            $durationStmt = $this->db->prepare("SELECT duration FROM time_tracking WHERE id = :id");
            $durationStmt->execute([':id' => $clockInRecordId]);
            $sessionDuration = $durationStmt->fetchColumn();

            if ($sessionDuration === false) {
                throw new Exception("Could not retrieve session duration.");
            }

            // Step 2: Update the totalHours in the officers table
            $updateHoursSql = "UPDATE officers SET totalHours = totalHours + :duration WHERE id = :officer_id";
            $updateStmt = $this->db->prepare($updateHoursSql);
            $updateStmt->bindParam(':duration', $sessionDuration, PDO::PARAM_INT);
            $updateStmt->bindParam(':officer_id', $officerId, PDO::PARAM_INT);
            $updateStmt->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error clocking out: " . $e->getMessage());
            return false;
        }
    }
}
?>