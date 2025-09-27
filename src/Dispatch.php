<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Dispatch {
    private $db;
    private $organization_id;

    public function __construct($organization_id) {
        $this->db = Database::getInstance()->getConnection();
        if (empty($organization_id)) {
            throw new InvalidArgumentException("Organization ID must be provided for Dispatch model.");
        }
        $this->organization_id = $organization_id;
    }

    public function getAssignments() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM dispatch_assignments WHERE organization_id = ?");
            $stmt->execute([$this->organization_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching assignments: " . $e->getMessage());
            return [];
        }
    }

    public function unassignOfficer($officerId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM dispatch_assignments WHERE officer_id = ? AND organization_id = ?");
            return $stmt->execute([$officerId, $this->organization_id]);
        } catch (PDOException $e) {
            error_log("Error unassigning officer: " . $e->getMessage());
            return false;
        }
    }

    public function assignOfficer($officerId, $assignmentType, $assignmentId, $seatIndex = null) {
        $this->db->beginTransaction();
        try {
            // First, unassign the officer from any previous assignment
            $this->unassignOfficer($officerId);

            // Then, create the new assignment
            $sql = "INSERT INTO dispatch_assignments (organization_id, officer_id, assignment_type, assignment_id, seat_index)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->organization_id, $officerId, $assignmentType, $assignmentId, $seatIndex]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error assigning officer: " . $e->getMessage());
            return false;
        }
    }
}
?>