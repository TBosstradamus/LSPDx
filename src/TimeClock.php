<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class TimeClock {
    private $db;
    private $organization_id;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        if (isset($_SESSION['organization_id'])) {
            $this->organization_id = $_SESSION['organization_id'];
        } else {
            die("Fehler: Organisations-ID nicht gefunden.");
        }
    }

    public function getCurrentStatus($officerId) {
        $stmt = $this->db->prepare("SELECT * FROM time_tracking WHERE officer_id = ? AND clockOutTime IS NULL ORDER BY clockInTime DESC LIMIT 1");
        $stmt->execute([$officerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function clockIn($officerId) {
        if ($this->getCurrentStatus($officerId)) {
            return false;
        }
        $stmt = $this->db->prepare("INSERT INTO time_tracking (organization_id, officer_id, clockInTime) VALUES (?, ?, NOW())");
        return $stmt->execute([$this->organization_id, $officerId]);
    }

    public function clockOut($officerId, $clockInRecordId) {
        $this->db->beginTransaction();
        try {
            $updateSql = "UPDATE time_tracking
                          SET clockOutTime = NOW(),
                              duration = TIMESTAMPDIFF(SECOND, clockInTime, NOW())
                          WHERE id = ? AND officer_id = ? AND organization_id = ?";
            $stmt = $this->db->prepare($updateSql);
            $stmt->execute([$clockInRecordId, $officerId, $this->organization_id]);

            $durationStmt = $this->db->prepare("SELECT duration FROM time_tracking WHERE id = ?");
            $durationStmt->execute([$clockInRecordId]);
            $sessionDuration = $durationStmt->fetchColumn();

            if ($sessionDuration === false) throw new Exception("Could not retrieve session duration.");

            $officerStmt = $this->db->prepare("UPDATE officers SET totalHours = totalHours + ? WHERE id = ?");
            $officerStmt->execute([$sessionDuration, $officerId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error clocking out: " . $e->getMessage());
            return false;
        }
    }

    public function pause($timeTrackingId) {
        $stmt = $this->db->prepare("UPDATE time_tracking SET is_paused = TRUE WHERE id = ? AND organization_id = ?");
        return $stmt->execute([$timeTrackingId, $this->organization_id]);
    }

    public function resume($timeTrackingId) {
        $stmt = $this->db->prepare("UPDATE time_tracking SET is_paused = FALSE WHERE id = ? AND organization_id = ?");
        return $stmt->execute([$timeTrackingId, $this->organization_id]);
    }
}
?>