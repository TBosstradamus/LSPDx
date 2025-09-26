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

    public function __construct($organization_id) {
        $this->db = Database::getInstance()->getConnection();
        if (empty($organization_id)) {
            // Throw an exception instead of killing the script. This is much cleaner.
            throw new InvalidArgumentException("Fehler: Organisations-ID muss für TimeClock bereitgestellt werden.");
        }
        $this->organization_id = $organization_id;
    }

    public function getCurrentStatus($officerId) {
        $stmt = $this->db->prepare(
            "SELECT * FROM time_tracking
             WHERE officer_id = ?
             AND organization_id = ?
             AND clockOutTime IS NULL
             ORDER BY clockInTime DESC LIMIT 1"
        );
        $stmt->execute([$officerId, $this->organization_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function clockIn($officerId) {
        if ($this->getCurrentStatus($officerId)) {
            return false; // Already clocked in
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

            $durationStmt = $this->db->prepare("SELECT duration FROM time_tracking WHERE id = ? AND organization_id = ?");
            $durationStmt->execute([$clockInRecordId, $this->organization_id]);
            $sessionDuration = $durationStmt->fetchColumn();

            if ($sessionDuration === false) {
                throw new Exception("Konnte die Sitzungsdauer nicht abrufen.");
            }

            // Also ensure the officer belongs to the same organization.
            $officerStmt = $this->db->prepare("UPDATE officers SET totalHours = totalHours + ? WHERE id = ? AND organization_id = ?");
            $officerStmt->execute([$sessionDuration, $officerId, $this->organization_id]);

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